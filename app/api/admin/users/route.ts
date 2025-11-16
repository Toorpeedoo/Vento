import { NextRequest, NextResponse } from 'next/server';
import { requireAdmin } from '@/lib/auth';
import { getAllUsers, updateUserRole, deleteUser } from '@/lib/db/user';
import { getProductCount } from '@/lib/db/product';

export const dynamic = 'force-dynamic';

async function handler(req: NextRequest, user: { username: string; isAdmin: boolean }) {
  if (req.method === 'GET') {
    const users = await getAllUsers();
    
    // Get product counts for each user
    const usersWithStats = await Promise.all(
      users.map(async (u) => ({
        ...u,
        productCount: await getProductCount(u.username),
      }))
    );

    return NextResponse.json({ users: usersWithStats });
  }

  if (req.method === 'PATCH') {
    const { username, isAdmin } = await req.json();

    if (!username || typeof isAdmin !== 'boolean') {
      return NextResponse.json(
        { error: 'Username and isAdmin status are required' },
        { status: 400 }
      );
    }

    if (username === user.username) {
      return NextResponse.json(
        { error: 'Cannot modify your own admin status' },
        { status: 400 }
      );
    }

    const success = await updateUserRole(username, isAdmin);

    if (!success) {
      return NextResponse.json(
        { error: 'User not found or update failed' },
        { status: 404 }
      );
    }

    return NextResponse.json({ success: true, message: `User role updated successfully` });
  }

  if (req.method === 'DELETE') {
    const { username } = await req.json();

    if (!username) {
      return NextResponse.json(
        { error: 'Username is required' },
        { status: 400 }
      );
    }

    if (username === user.username) {
      return NextResponse.json(
        { error: 'Cannot delete your own account' },
        { status: 400 }
      );
    }

    const success = await deleteUser(username);

    if (!success) {
      return NextResponse.json(
        { error: 'User not found' },
        { status: 404 }
      );
    }

    return NextResponse.json({ success: true });
  }

  return NextResponse.json({ error: 'Method not allowed' }, { status: 405 });
}

export const GET = requireAdmin(handler);
export const PATCH = requireAdmin(handler);
export const DELETE = requireAdmin(handler);

