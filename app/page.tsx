'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { logout } from '@/lib/auth-client';

export default function Home() {
  const [user, setUser] = useState<{ username: string; isAdmin: boolean } | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    fetch('/api/auth/session')
      .then((res) => res.json())
      .then((data) => {
        if (data.user) {
          setUser(data.user);
        }
        setLoading(false);
      });
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center p-4">
      <div className="max-w-6xl w-full grid md:grid-cols-2 gap-8 items-center">
        {/* Left Side - Branding */}
        <div className="space-y-6 text-center md:text-left">
          <div className="inline-block">
            <h1 className="text-6xl md:text-7xl font-black bg-gradient-to-r from-primary-600 via-accent-500 to-primary-600 bg-clip-text text-transparent animate-gradient">
              VENTO
            </h1>
          </div>
          <p className="text-2xl md:text-3xl font-semibold text-gray-700">
            Your go-to solution
          </p>
          <p className="text-xl md:text-2xl text-gray-600">
            for efficient Inventory Management
          </p>
          <div className="pt-4 flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
            {user ? (
              <>
                {user.isAdmin ? (
                  <Link
                    href="/admin"
                    className="btn btn-primary text-lg px-8 py-4 rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                  >
                    Admin Dashboard
                  </Link>
                ) : (
                  <Link
                    href="/dashboard"
                    className="btn btn-primary text-lg px-8 py-4 rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                  >
                    Continue to Dashboard
                  </Link>
                )}
                <button
                  onClick={async () => {
                    await logout();
                  }}
                  className="btn btn-secondary text-lg px-8 py-4 rounded-xl"
                >
                  Logout
                </button>
              </>
            ) : (
              <>
                <Link
                  href="/login"
                  className="btn btn-primary text-lg px-8 py-4 rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300"
                >
                  Login
                </Link>
                <Link
                  href="/signup"
                  className="btn btn-secondary text-lg px-8 py-4 rounded-xl"
                >
                  Sign Up
                </Link>
              </>
            )}
          </div>
        </div>

        {/* Right Side - Visual Element */}
        <div className="hidden md:block relative">
          <div className="absolute inset-0 bg-gradient-to-r from-primary-400 to-accent-400 rounded-3xl transform rotate-6 opacity-20"></div>
          <div className="relative bg-white rounded-3xl p-8 shadow-2xl border border-gray-100">
            <div className="space-y-4">
              <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-primary-50 to-accent-50 rounded-xl">
                <div className="w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                  📦
                </div>
                <div>
                  <div className="font-semibold text-gray-800">Product Management</div>
                  <div className="text-sm text-gray-600">Add, update, and track products</div>
                </div>
              </div>
              <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl">
                <div className="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                  📊
                </div>
                <div>
                  <div className="font-semibold text-gray-800">Real-time Inventory</div>
                  <div className="text-sm text-gray-600">Track quantities and prices</div>
                </div>
              </div>
              <div className="flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl">
                <div className="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                  🔐
                </div>
                <div>
                  <div className="font-semibold text-gray-800">Secure & Modern</div>
                  <div className="text-sm text-gray-600">Built with Next.js & MongoDB</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

