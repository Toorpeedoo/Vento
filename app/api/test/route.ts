import { NextResponse } from 'next/server';
import { getDatabase } from '@/lib/mongodb';

export async function GET() {
  try {
    // Test MongoDB connection
    const db = await getDatabase();
    const collections = await db.listCollections().toArray();
    
    // Test JWT_SECRET
    let jwtSecretStatus = 'not set';
    if (process.env.JWT_SECRET) {
      if (process.env.JWT_SECRET === 'your-secret-key-change-in-production') {
        jwtSecretStatus = 'using default (not secure)';
      } else {
        jwtSecretStatus = 'set and valid';
      }
    }
    
    return NextResponse.json({
      success: true,
      message: 'All systems operational',
      database: db.databaseName,
      collections: collections.map(c => c.name),
      environment: {
        hasMongoUri: !!process.env.MONGODB_URI,
        mongoUriLength: process.env.MONGODB_URI?.length || 0,
        hasDbName: !!process.env.MONGODB_DB,
        dbName: process.env.MONGODB_DB || 'not set',
        jwtSecretStatus: jwtSecretStatus,
        nodeEnv: process.env.NODE_ENV,
        vercel: process.env.VERCEL || 'false',
        vercelEnv: process.env.VERCEL_ENV || 'not set',
      }
    });
  } catch (error) {
    console.error('Test route error:', error);
    const errorMessage = error instanceof Error ? error.message : 'Unknown error';
    const stack = error instanceof Error ? error.stack : undefined;
    
    // Test JWT_SECRET even if MongoDB fails
    let jwtSecretStatus = 'not set';
    if (process.env.JWT_SECRET) {
      if (process.env.JWT_SECRET === 'your-secret-key-change-in-production') {
        jwtSecretStatus = 'using default (not secure)';
      } else {
        jwtSecretStatus = 'set and valid';
      }
    }
    
    return NextResponse.json({
      success: false,
      error: errorMessage,
      stack: process.env.NODE_ENV === 'development' ? stack : undefined,
      environment: {
        hasMongoUri: !!process.env.MONGODB_URI,
        mongoUriLength: process.env.MONGODB_URI?.length || 0,
        hasDbName: !!process.env.MONGODB_DB,
        dbName: process.env.MONGODB_DB || 'not set',
        jwtSecretStatus: jwtSecretStatus,
        nodeEnv: process.env.NODE_ENV,
        vercel: process.env.VERCEL || 'false',
        vercelEnv: process.env.VERCEL_ENV || 'not set',
      }
    }, { status: 500 });
  }
}

