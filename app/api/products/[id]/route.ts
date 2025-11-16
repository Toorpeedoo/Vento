import { NextRequest, NextResponse } from 'next/server';
import { requireAuth } from '@/lib/auth';
import { getProduct, updateProduct, deleteProduct, changeProductId } from '@/lib/db/product';
import { Product } from '@/lib/types';

export const dynamic = 'force-dynamic';

async function handler(req: NextRequest, user: { username: string; isAdmin: boolean }) {
  let id = Number(req.nextUrl.pathname.split('/').pop());

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
    const { productName, price, quantity, newId } = await req.json();

    if (!productName || price === undefined || quantity === undefined) {
      return NextResponse.json(
        { error: 'All fields are required' },
        { status: 400 }
      );
    }

    // If a newId is provided, validate and attempt to change it first
    if (newId !== undefined && newId !== null) {
      const parsedNewId = Number(newId);
      if (Number.isNaN(parsedNewId) || parsedNewId < 0) {
        return NextResponse.json(
          { error: 'Invalid new product ID' },
          { status: 400 }
        );
      }

      if (parsedNewId !== id) {
        const result = await changeProductId(user.username, id, parsedNewId);
        if (!result.success) {
          if (result.reason === 'conflict') {
            return NextResponse.json(
              { error: 'Target product ID already exists' },
              { status: 409 }
            );
          }
          return NextResponse.json(
            { error: 'Product not found' },
            { status: 404 }
          );
        }
        // Update id to new value for subsequent update
        id = parsedNewId;
      }
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

