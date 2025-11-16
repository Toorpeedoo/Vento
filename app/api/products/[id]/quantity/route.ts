import { NextRequest, NextResponse } from 'next/server';
import { requireAuth } from '@/lib/auth';
import { getProduct, updateProduct } from '@/lib/db/product';
import { Product } from '@/lib/types';

export const dynamic = 'force-dynamic';

async function handler(req: NextRequest, user: { username: string; isAdmin: boolean }) {
  const id = Number(req.nextUrl.pathname.split('/')[3]);

  if (isNaN(id)) {
    return NextResponse.json({ error: 'Invalid product ID' }, { status: 400 });
  }

  if (req.method === 'POST') {
    const { action, quantity } = await req.json();

    if (!action || !quantity || quantity <= 0) {
      return NextResponse.json(
        { error: 'Action and positive quantity are required' },
        { status: 400 }
      );
    }

    const product = await getProduct(user.username, id);
    if (!product) {
      return NextResponse.json(
        { error: 'Product not found' },
        { status: 404 }
      );
    }

    let newQuantity = product.quantity;
    if (action === 'add') {
      newQuantity = product.quantity + Number(quantity);
    } else if (action === 'subtract') {
      newQuantity = product.quantity - Number(quantity);
      if (newQuantity < 0) {
        return NextResponse.json(
          { error: 'Insufficient quantity' },
          { status: 400 }
        );
      }
    } else {
      return NextResponse.json(
        { error: 'Invalid action. Use "add" or "subtract"' },
        { status: 400 }
      );
    }

    const updatedProduct: Product = {
      ...product,
      quantity: newQuantity,
    };

    const success = await updateProduct(updatedProduct);

    if (!success) {
      return NextResponse.json(
        { error: 'Failed to update product' },
        { status: 500 }
      );
    }

    return NextResponse.json({ success: true, product: updatedProduct });
  }

  return NextResponse.json({ error: 'Method not allowed' }, { status: 405 });
}

export const POST = requireAuth(handler);

