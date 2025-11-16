'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import AuthGuard from '@/components/AuthGuard';
import Navbar from '@/components/Navbar';
import Link from 'next/link';
import { Trash2, AlertCircle, CheckCircle, ArrowLeft, X, AlertTriangle, Search } from 'lucide-react';

export default function DeleteProductPage() {
  return (
    <AuthGuard>
      <DeleteProductContent />
    </AuthGuard>
  );
}

function DeleteProductContent() {
  const router = useRouter();
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [deleteConfirm, setDeleteConfirm] = useState<{ show: boolean; product: any | null }>({ show: false, product: null });
  const [allProducts, setAllProducts] = useState<any[]>([]);
  const [loadingProducts, setLoadingProducts] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');

  useEffect(() => {
    fetchAllProducts();
  }, []);

  const fetchAllProducts = async () => {
    try {
      const res = await fetch('/api/products');
      const data = await res.json();
      if (res.ok) {
        setAllProducts(data.products || []);
      }
    } catch (err) {
      console.error('Failed to fetch products');
    } finally {
      setLoadingProducts(false);
    }
  };

  const handleSelectProduct = (product: any) => {
    setDeleteConfirm({ show: true, product });
    setError('');
  };

  const handleDeleteConfirm = async () => {
    if (!deleteConfirm.product) return;

    setDeleting(true);
    setError('');

    try {
      const res = await fetch(`/api/products/${deleteConfirm.product.id}`, {
        method: 'DELETE',
      });

      const data = await res.json();

      if (!res.ok) {
        setError(data.error || 'Failed to delete product');
        setDeleting(false);
        return;
      }

      setSuccess(true);
      setDeleteConfirm({ show: false, product: null });
      fetchAllProducts();
      
      setTimeout(() => setSuccess(false), 3000);
    } catch (err) {
      console.error('Delete product error:', err);
      setError(`Network error: ${err instanceof Error ? err.message : 'Please try again'}`);
    } finally {
      setDeleting(false);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50">
      <Navbar title="VENTO Inventory" />
      
      <main className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-6">
          <Link href="/dashboard" className="inline-flex items-center gap-2 text-gray-600 hover:text-primary-600 transition-colors">
            <ArrowLeft className="w-5 h-5" />
            Back to Dashboard
          </Link>
        </div>
        <div className="mb-8">
          <h1 className="text-4xl font-black bg-gradient-to-r from-red-600 to-rose-600 bg-clip-text text-transparent mb-2">
            Delete Product
          </h1>
          <p className="text-gray-600">Click on a product below to delete it</p>
        </div>

        {error && !deleteConfirm.show && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 text-red-800">
            <AlertCircle className="w-5 h-5 flex-shrink-0" />
            <span>{error}</span>
          </div>
        )}

        {success && (
          <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3 text-green-800">
            <CheckCircle className="w-5 h-5 flex-shrink-0" />
            <span>Product deleted successfully!</span>
          </div>
        )}

        {/* All Products */}
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
          <div className="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div className="flex justify-between items-center mb-4">
              <div>
                <h2 className="text-xl font-bold text-gray-<p className="text-sm text-gray-600 mt-1">Click on a product to delete it</p>
              </div>
              <div className="relative">
                <input
                  type="text"
                  placeholder="Search products..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
                <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={18} />
              </div>
            </div>
          </div>
          {loadingProducts ? (
            <div className="flex justify-center items-center py-12">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
            </div>
          ) : allProducts.filter((p) =>
              p.id.toString().includes(searchTerm.toLowerCase()) ||
              p.productName.toLowerCase().includes(searchTerm.toLowerCase()) ||
              p.price.toString().includes(searchTerm.toLowerCase()) ||
              p.quantity.toString().includes(searchTerm.toLowerCase())
            ).length === 0 ? (
            <div className="p-12 text-center text-gray-500">
              {searchTerm ? 'No products match your search' : 'No products found'}
            </div>
          ) : (
            <div className="overflow-x-auto max-h-[500px] overflow-y-auto">
              <table className="w-full">
                <thead className="bg-gradient-to-r from-primary-500 to-primary-600 text-white sticky top-0 z-10">
                  <tr>
                    <th className="px-6 py-4 text-left font-semibold">ID</th>
                    <th className="px-6 py-4 text-left font-semibold">Product Name</th>
                    <th className="px-6 py-4 text-left font-semibold">Price</th>
                    <th className="px-6 py-4 text-left font-semibold">Quantity</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200">
                  {allProducts.filter((p) =>
                      p.id.toString().includes(searchTerm.toLowerCase()) ||
                      p.productName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                      p.price.toString().includes(searchTerm.toLowerCase()) ||
                      p.quantity.toString().includes(searchTerm.toLowerCase())
                    ).map((prod) => (
                    <tr
                      key={prod._id}
                      onClick={() => handleSelectProduct(prod)}
                      className="hover:bg-red-50 cursor-pointer transition-colors"
                    >
                      <td className="px-6 py-4 font-medium text-gray-900">{prod.id}</td>
                      <td className="px-6 py-4 text-gray-700">{prod.productName}</td>
                      <td className="px-6 py-4 text-gray-700">₱{prod.price.toFixed(2)}</td>
                      <td className="px-6 py-4">
                        <span className="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                          {prod.quantity}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Delete Confirmation Modal */}
        {deleteConfirm.show && deleteConfirm.product && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform animate-fade-in">
              <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <AlertTriangle className="w-6 h-6 text-red-600" />
                  </div>
                  <h3 className="text-xl font-bold text-gray-800">Delete Product</h3>
                </div>
                <button
                  onClick={() => setDeleteConfirm({ show: false, product: null })}
                  className="text-gray-400 hover:text-gray-600 transition-colors"
                >
                  <X className="w-6 h-6" />
                </button>
              </div>
              
              <p className="text-gray-600 mb-6">
                Are you sure you want to delete this product? This action cannot be undone.
              </p>

              {error && (
                <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                  {error}
                </div>
              )}

              <div className="flex gap-3">
                <button
                  onClick={handleDeleteConfirm}
                  disabled={deleting}
                  className="flex-1 btn btn-danger flex items-center justify-center gap-2"
                >
                  {deleting ? (
                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                  ) : (
                    <>
                      <Trash2 className="w-5 h-5" />
                      Delete
                    </>
                  )}
                </button>
                <button
                  onClick={() => setDeleteConfirm({ show: false, product: null })}
                  disabled={deleting}
                  className="flex-1 btn btn-secondary"
                >
                  Cancel
                </button>
              </div>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
