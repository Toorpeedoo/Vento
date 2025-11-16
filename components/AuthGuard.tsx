'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { getSessionClient } from '@/lib/auth-client';

interface AuthGuardProps {
  children: React.ReactNode;
  requireAdmin?: boolean;
  redirectTo?: string;
}

export default function AuthGuard({ 
  children, 
  requireAdmin = false,
  redirectTo = '/login' 
}: AuthGuardProps) {
  const [loading, setLoading] = useState(true);
  const [authorized, setAuthorized] = useState(false);
  const router = useRouter();

  useEffect(() => {
    getSessionClient().then((user) => {
      if (!user) {
        router.push(redirectTo);
        return;
      }

      if (requireAdmin && !user.isAdmin) {
        router.push('/dashboard');
        return;
      }

      setAuthorized(true);
      setLoading(false);
    });
  }, [requireAdmin, redirectTo, router]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  if (!authorized) {
    return null;
  }

  return <>{children}</>;
}

