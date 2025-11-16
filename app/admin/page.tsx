'use client';

import { useEffect, useState } from 'react';
import AuthGuard from '@/components/AuthGuard';
import Navbar from '@/components/Navbar';
import { Users, UserCheck, Crown, Package, Trash2, AlertCircle, Search, Shield } from 'lucide-react';
import { User } from '@/lib/types';

export default function AdminPage() {
  return (
    <AuthGuard requireAdmin>
      <AdminContent />
    </AuthGuard>
  );
}

function AdminContent() {
  const [users, setUsers] = useState<(User & { productCount: number })[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [currentUser, setCurrentUser] = useState<string>('');
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchUsers();
    fetchCurrentUser();
  }, []);

  const fetchCurrentUser = async () => {
    try {
      const res = await fetch('/api/auth/session');
      const data = await res.json();
      if (data.user) {
        setCurrentUser(data.user.username);
      }
    } catch (err) {
      console.error('Failed to get current user');
    }
  };

  const fetchUsers = async () => {
    try {
      const res = await fetch('/api/admin/users');
      const data = await res.json();
      if (res.ok) {
        setUsers(data.users || []);
      } else {
        setError(data.error || 'Failed to load users');
      }
    } catch (err) {
      setError('An error occurred');
    } finally {
      setLoading(false);
    }
  };

  const handleToggleAdmin = async (username: string, currentIsAdmin: boolean) => {
    const action = currentIsAdmin ? 'remove admin privileges from' : 'grant admin privileges to';
    if (!confirm(`Are you sure you want to ${action} user "${username}"?`)) {
      return;
    }

    try {
      const res = await fetch('/api/admin/users', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, isAdmin: !currentIsAdmin }),
      });

      const data = await res.json();

      if (res.ok) {
        fetchUsers();
      } else {
        alert(data.error || 'Failed to update user');
      }
    } catch (err) {
      alert('An error occurred');
    }
  };

  const handleDeleteUser = async (username: string) => {
    if (!confirm(`Are you sure you want to delete user "${username}"? This will also delete all their products and data. This action cannot be undone.`)) {
      return;
    }

    try {
      const res = await fetch('/api/admin/users', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username }),
      });

      const data = await res.json();

      if (res.ok) {
        fetchUsers();
      } else {
        alert(data.error || 'Failed to delete user');
      }
    } catch (err) {
      alert('An error occurred');
    }
  };

  const filteredUsers = users.filter((user) =>
    user.username.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const regularUsers = users.filter((u) => !u.isAdmin);
  const adminUsers = users.filter((u) => u.isAdmin);

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
      <Navbar title="VENTO Admin" />
      
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <h1 className="text-4xl font-black bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent mb-2">
            Admin Dashboard
          </h1>
          <p className="text-gray-600">Manage users and monitor system activity</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 text-red-800">
            <AlertCircle className="w-5 h-5 flex-shrink-0" />
            <span>{error}</span>
          </div>
        )}

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div className="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl p-6 text-white shadow-xl">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-primary-100 text-sm font-medium mb-1">Total Users</p>
                <p className="text-4xl font-black">{users.length}</p>
              </div>
              <Users className="w-12 h-12 opacity-80" />
            </div>
          </div>

          <div className="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-xl">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-green-100 text-sm font-medium mb-1">Regular Users</p>
                <p className="text-4xl font-black">{regularUsers.length}</p>
              </div>
              <UserCheck className="w-12 h-12 opacity-80" />
            </div>
          </div>

          <div className="bg-gradient-to-r from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-amber-100 text-sm font-medium mb-1">Admin Users</p>
                <p className="text-4xl font-black">{adminUsers.length}</p>
              </div>
              <Crown className="w-12 h-12 opacity-80" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
          <div className="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-800">User Management</h2>
              <div className="relative">
                <input
                  type="text"
                  placeholder="Search users..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={18} />
              </div>
            </div>
          </div>

          {loading ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
            </div>
          ) : users.length === 0 ? (
            <div className="p-12 text-center">
              <Users className="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p className="text-gray-600">No users found</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gradient-to-r from-primary-500 to-primary-600 text-white">
                  <tr>
                    <th className="px-6 py-4 text-left font-semibold">Username</th>
                    <th className="px-6 py-4 text-left font-semibold">Role</th>
                    <th className="px-6 py-4 text-left font-semibold">Password</th>
                    <th className="px-6 py-4 text-left font-semibold">Created At</th>
                    <th className="px-6 py-4 text-left font-semibold">Products</th>
                    <th className="px-6 py-4 text-left font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {filteredUsers.map((user) => (
                    <tr key={user._id} className="hover:bg-gray-50 transition-colors">
                      <td className="px-6 py-4 font-medium text-gray-900">{user.username}</td>
                      <td className="px-6 py-4">
                        {user.isAdmin ? (
                          <span className="px-3 py-1 bg-gradient-to-r from-amber-400 to-orange-500 text-white rounded-full text-sm font-semibold inline-flex items-center gap-1">
                            <Crown className="w-3 h-3" />
                            Admin
                          </span>
                        ) : (
                          <span className="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-sm font-semibold">
                            User
                          </span>
                        )}
                      </td>
                      <td className="px-6 py-4 font-mono text-sm text-gray-700">{user.password}</td>
                      <td className="px-6 py-4 text-gray-700">{new Date(user.createdAt).toLocaleDateString()}</td>
                      <td className="px-6 py-4">
                        <span className="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold inline-flex items-center gap-1">
                          <Package className="w-3 h-3" />
                          {user.productCount}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        {user.isAdmin && user.username === currentUser ? (
                          <span className="text-gray-500 text-sm italic">Current Admin</span>
                        ) : (
                          <div className="flex items-center gap-2">
                            <button
                              onClick={() => handleToggleAdmin(user.username, user.isAdmin)}
                              className={`px-3 py-1.5 rounded-lg transition-all text-sm font-medium flex items-center gap-1 ${
                                user.isAdmin 
                                  ? 'bg-orange-500 hover:bg-orange-600 text-white' 
                                  : 'bg-blue-500 hover:bg-blue-600 text-white'
                              }`}
                            >
                              <Shield className="w-4 h-4" />
                              {user.isAdmin ? 'Remove Admin' : 'Make Admin'}
                            </button>
                            <button
                              onClick={() => handleDeleteUser(user.username)}
                              className="px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-all text-sm font-medium flex items-center gap-1"
                            >
                              <Trash2 className="w-4 h-4" />
                              Delete
                            </button>
                          </div>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </main>
    </div>
  );
}

