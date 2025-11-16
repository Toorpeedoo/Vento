import { NextRequest, NextResponse } from 'next/server';
import { createUser, getUser } from '@/lib/db/user';
import { setSession } from '@/lib/auth';

export async function POST(req: NextRequest) {
  try {
    const { username, password, confirmPassword } = await req.json();

    if (!username || !password || !confirmPassword) {
      return NextResponse.json(
        { error: 'All fields are required' },
        { status: 400 }
      );
    }

    if (password !== confirmPassword) {
      return NextResponse.json(
        { error: 'Passwords do not match' },
        { status: 400 }
      );
    }

    if (username.length < 3) {
      return NextResponse.json(
        { error: 'Username must be at least 3 characters long' },
        { status: 400 }
      );
    }

    if (password.length < 4) {
      return NextResponse.json(
        { error: 'Password must be at least 4 characters long' },
        { status: 400 }
      );
    }

    // Check if user exists
    const existingUser = await getUser(username.trim());
    if (existingUser) {
      return NextResponse.json(
        { error: 'Username already exists' },
        { status: 409 }
      );
    }

    const success = await createUser({
      username: username.trim(),
      password, // Store as plain text
      createdAt: new Date().toISOString(),
      isAdmin: false,
    });

    if (!success) {
      return NextResponse.json(
        { error: 'Failed to create account' },
        { status: 500 }
      );
    }

    await setSession({
      username: username.trim(),
      isAdmin: false,
    });

    return NextResponse.json({
      success: true,
      user: {
        username: username.trim(),
        isAdmin: false,
      },
    });
  } catch (error) {
    console.error('Signup error:', error);
    const errorMessage = error instanceof Error ? error.message : 'Unknown error';
    const stack = error instanceof Error ? error.stack : undefined;
    
    // Log full error for debugging
    console.error('Signup error details:', {
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

