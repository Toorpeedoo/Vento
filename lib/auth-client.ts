'use client';

export interface SessionUser {
  username: string;
  isAdmin: boolean;
}

export async function getSessionClient(): Promise<SessionUser | null> {
  try {
    const res = await fetch('/api/auth/session');
    const data = await res.json();
    return data.user || null;
  } catch {
    return null;
  }
}

export async function logout(): Promise<void> {
  await fetch('/api/auth/logout', { method: 'POST' });
  window.location.href = '/';
}

