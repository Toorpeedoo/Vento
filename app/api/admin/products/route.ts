import { NextRequest, NextResponse } from 'next/server';
import { requireAdmin } from '@/lib/auth';
import { getAllProductsAcrossUsers } from '@/lib/db/product';

export const dynamic = 'force-dynamic';

async function handler(req: NextRequest, user: { username: string; isAdmin: boolean }) {
  if (req.method === 'GET') {
    try {
      const products = await getAllProductsAcrossUsers();
      return NextResponse.json({ products });
    } catch (error) {
      console.error('Admin products route error:', error);
      return NextResponse.json(
        { error: 'Failed to fetch products' },
        { status: 500 }
      );
    }
  }

  return NextResponse.json({ error: 'Method not allowed' }, { status: 405 });
}

export const GET = requireAdmin(handler);

