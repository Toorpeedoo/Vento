import { NextResponse } from 'next/server';
import { getDatabase } from '@/lib/mongodb';

export async function GET() {
  try {
    // Test MongoDB connection
    const db = await getDatabase();
    const collections = await db.listCollections().toArray();
    
    return NextResponse.json({
      success: true,
      message: 'MongoDB connection successful',
      database: db.databaseName,
      collections: collections.map(c => c.name),
      env: {
        hasMongoUri: !!process.env.MONGODB_URI,
        hasDbName: !!process.env.MONGODB_DB,
        nodeEnv: process.env.NODE_ENV,
        vercel: process.env.VERCEL,
      }
    });
  } catch (error) {
    console.error('Test route error:', error);
    const errorMessage = error instanceof Error ? error.message : 'Unknown error';
    return NextResponse.json({
      success: false,
      error: errorMessage,
      env: {
        hasMongoUri: !!process.env.MONGODB_URI,
        hasDbName: !!process.env.MONGODB_DB,
        nodeEnv: process.env.NODE_ENV,
        vercel: process.env.VERCEL,
      }
    }, { status: 500 });
  }
}

