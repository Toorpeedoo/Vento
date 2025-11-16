import { NextRequest, NextResponse } from 'next/server';
import { requireAuth } from '@/lib/auth';
import { getProduct, updateProduct, deleteProduct } from '@/lib/db/product';
import { Product } from '@/lib/types';

async function handler(req: NextRequest, user: { username: string; isAdmin: boolean }) {
  const id = Number(req.nextUrl.pathname.split('/').pop());

  if (isNaN(id)) {
    return NextResponse.json({ error: 'Invalid product ID' }, { status: 400 });
  }

  if (req.method === 'GET') {
    const product = await getProduct(user.username, id);
    if (!product) {
      return NextResponse.json({ error: 'Product not found' }, { status: 404 });
    }
    return NextResponse.json({ product });
  }

  if (req.method === 'PUT') {
    const { productName, price, quantity } = await req.json();

    if (!productName || price === undefined || quantity === undefined) {
      return NextResponse.json(
        { error: 'All fields are required' },
        { status: 400 }
      );
    }

    const product: Product = {
      id,
      productName: productName.trim(),
      price: Number(price),
      quantity: Number(quantity),
      username: user.username,
    };

    const success = await updateProduct(product);

    if (!success) {
      return NextResponse.json(
        { error: 'Product not found or update failed' },
        { status: 404 }
      );
    }

    return NextResponse.json({ success: true, product });
  }

  if (req.method === 'DELETE') {
    const success = await deleteProduct(user.username, id);

    if (!success) {
      return NextResponse.json(
        { error: 'Product not found' },
        { status: 404 }
      );
    }

    return NextResponse.json({ success: true });
  }

  return NextResponse.json({ error: 'Method not allowed' }, { status: 405 });
}

export const GET = requireAuth(handler);
export const PUT = requireAuth(handler);
export const DELETE = requireAuth(handler);

