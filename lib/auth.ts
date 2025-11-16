import { NextRequest, NextResponse } from 'next/server';
import { cookies } from 'next/headers';
import jwt from 'jsonwebtoken';
import { SessionUser } from './types';

function getJwtSecret(): string {
  const secret = process.env.JWT_SECRET || process.env.NEXTAUTH_SECRET;
  if (!secret) {
    console.error('❌ JWT_SECRET or NEXTAUTH_SECRET environment variable is not set!');
    console.error('Please add it to your Vercel environment variables.');
    throw new Error('JWT_SECRET is required. Add it to Vercel environment variables: Settings → Environment Variables');
  }
  if (secret === 'your-secret-key-change-in-production' || secret === 'vento-secret-key-change-in-production-2024') {
    console.warn('⚠️  WARNING: Using default JWT_SECRET. Please change it in production!');
  }
  return secret;
}

export function createToken(user: SessionUser): string {
  const secret = getJwtSecret();
  return jwt.sign(user, secret, { expiresIn: '7d' });
}

export function verifyToken(token: string): SessionUser | null {
  try {
    const secret = getJwtSecret();
    return jwt.verify(token, secret) as SessionUser;
  } catch {
    return null;
  }
}

export async function getSession(): Promise<SessionUser | null> {
  const cookieStore = await cookies();
  const token = cookieStore.get('auth-token')?.value;
  
  if (!token) {
    return null;
  }
  
  return verifyToken(token);
}

export async function setSession(user: SessionUser): Promise<void> {
  try {
    const token = createToken(user);
    const cookieStore = await cookies();
    
    // Check if we're on Vercel (production) or local
    const isProduction = process.env.NODE_ENV === 'production' || process.env.VERCEL === '1';
    
    cookieStore.set('auth-token', token, {
      httpOnly: true,
      secure: isProduction, // Use secure cookies in production/Vercel
      sameSite: 'lax',
      maxAge: 60 * 60 * 24 * 7, // 7 days
      path: '/',
    });
  } catch (error) {
    console.error('setSession error:', error);
    throw error;
  }
}

export async function clearSession(): Promise<void> {
  const cookieStore = await cookies();
  cookieStore.delete('auth-token');
}

export function requireAuth(
  handler: (req: NextRequest, user: SessionUser) => Promise<NextResponse>
) {
  return async (req: NextRequest): Promise<NextResponse> => {
    const user = await getSession();
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }
    
    return handler(req, user);
  };
}

export function requireAdmin(
  handler: (req: NextRequest, user: SessionUser) => Promise<NextResponse>
) {
  return async (req: NextRequest): Promise<NextResponse> => {
    const user = await getSession();
    
    if (!user) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }
    
    if (!user.isAdmin) {
      return NextResponse.json({ error: 'Forbidden' }, { status: 403 });
    }
    
    return handler(req, user);
  };
}

