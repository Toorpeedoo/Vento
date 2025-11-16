import { NextRequest, NextResponse } from 'next/server';
import { requireAuth } from '@/lib/auth';
import { getProducts, createProduct } from '@/lib/db/product';
import { Product } from '@/lib/types';

export const dynamic = 'force-dynamic';

async function handler(req: NextRequest, user: { username: string; isAdmin: boolean }) {
  try {
    if (req.method === 'GET') {
      const products = await getProducts(user.username);
      return NextResponse.json({ products });
    }

    if (req.method === 'POST') {
      const { id, productName, price, quantity } = await req.json();

      if (!id || !productName || price === undefined) {
        return NextResponse.json(
          { error: 'ID, product name, and price are required' },
          { status: 400 }
        );
      }

      // Default quantity to 0 if not provided
      const finalQuantity = quantity !== undefined ? Number(quantity) : 0;

      if (id < 0 || price < 0 || finalQuantity < 0) {
        return NextResponse.json(
          { error: 'ID, price, and quantity must be non-negative' },
          { status: 400 }
        );
      }

      const product: Omit<Product, '_id'> = {
        id: Number(id),
        productName: productName.trim(),
        price: Number(price),
        quantity: finalQuantity,
        username: user.username,
      };

      const success = await createProduct(product);

      if (!success) {
        return NextResponse.json(
          { error: 'Product ID already exists' },
          { status: 409 }
        );
      }

      return NextResponse.json({ success: true, product });
    }

    return NextResponse.json({ error: 'Method not allowed' }, { status: 405 });
  } catch (error) {
    console.error('Products route error:', error);
    const errorMessage = error instanceof Error ? error.message : 'Unknown error';
    return NextResponse.json(
      { 
        error: 'Internal server error',
        message: errorMessage,
      },
      { status: 500 }
    );
  }
}

export const GET = requireAuth(handler);
export const POST = requireAuth(handler);

