import { NextRequest, NextResponse } from 'next/server';
import { verifyUser } from '@/lib/db/user';
import { setSession } from '@/lib/auth';
import { getUser } from '@/lib/db/user';

export async function POST(req: NextRequest) {
  try {
    const { username, password } = await req.json();

    if (!username || !password) {
      return NextResponse.json(
        { error: 'Username and password are required' },
        { status: 400 }
      );
    }

    const isValid = await verifyUser(username.trim(), password);

    if (!isValid) {
      return NextResponse.json(
        { error: 'Invalid username or password' },
        { status: 401 }
      );
    }

    const user = await getUser(username.trim());
    if (!user) {
      return NextResponse.json(
        { error: 'User not found' },
        { status: 404 }
      );
    }

    try {
      await setSession({
        username: user.username,
        isAdmin: user.isAdmin,
      });
    } catch (sessionError) {
      console.error('Session error:', sessionError);
      throw new Error('Failed to create session');
    }

    return NextResponse.json({
      success: true,
      user: {
        username: user.username,
        isAdmin: user.isAdmin,
      },
    });
  } catch (error) {
    console.error('Login error:', error);
    const errorMessage = error instanceof Error ? error.message : 'Unknown error';
    const stack = error instanceof Error ? error.stack : undefined;
    
    // Log full error for debugging
    console.error('Login error details:', {
      message: errorMessage,
      stack,
      env: {
        hasMongoUri: !!process.env.MONGODB_URI,
        hasDbName: !!process.env.MONGODB_DB,
        hasJwtSecret: !!process.env.JWT_SECRET,
      }
    });
    
    return NextResponse.json(
      { 
        error: 'Internal server error',
        message: errorMessage,
      },
      { status: 500 }
    );
  }
}

