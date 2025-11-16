'use client';

import { useState, FormEvent, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import AuthGuard from '@/components/AuthGuard';
import Navbar from '@/components/Navbar';
import Link from 'next/link';
import { Edit, AlertCircle, CheckCircle, ArrowLeft, Search } from 'lucide-react';

export default function UpdateProductPage() {
  return (
    <AuthGuard>
      <UpdateProductContent />
    </AuthGuard>
  );
}

function UpdateProductContent() {
  const router = useRouter();
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [loading, setLoading] = useState(false);
  const [product, setProduct] = useState<any>(null);
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

  const handleSelectProduct = async (productId: number) => {
    setError('');
    setSuccess(false);

    try {
      const res = await fetch(`/api/products/${productId}`);
      const data = await res.json();

      if (!res.ok) {
        setError(data.error || 'Product not found');
        setProduct(null);
        return;
      }

      setProduct(data.product);
    } catch (err) {
      setError('An error occurred. Please try again.');
      setProduct(null);
    }
  };

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError('');
    setSuccess(false);
    setLoading(true);

    const formData = new FormData(e.currentTarget);
    const productName = formData.get('productName') as string;
    const price = Number(formData.get('price'));
    const quantity = Number(formData.get('quantity'));
      const newIdRaw = formData.get('newId');
      const newId = newIdRaw !== null ? Number(newIdRaw) : undefined;

    try {
      const res = await fetch(`/api/products/${product.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ productName, price, quantity, newId }),
      });

      const data = await res.json();

      if (!res.ok) {
        setError(data.error || 'Failed to update product');
        setLoading(false);
        return;
      }

      setSuccess(true);
      setLoading(false);
      
      // Refresh product list and reset the form
      fetchAllProducts();
      setTimeout(() => {
        setProduct(null);
        setSuccess(false);
      }, 1500);
    } catch (err) {
      setError('An error occurred. Please try again.');
      setLoading(false);
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
          <h1 className="text-4xl font-black bg-gradient-to-r from-primary-600 to-accent-600 bg-clip-text text-transparent mb-2">
            Update Product
          </h1>
          <p className="text-gray-600">Click on a product below to update it</p>
        </div>

        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 text-red-800">
            <AlertCircle className="w-5 h-5 flex-shrink-0" />
            <span>{error}</span>
          </div>
        )}

        {success && (
          <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3 text-green-800">
            <CheckCircle className="w-5 h-5 flex-shrink-0" />
            <span>Product updated successfully!</span>
          </div>
        )}

        {product && (
          <div className="bg-white rounded-2xl shadow-xl p-8 border border-gray-100 mb-8">
            <h2 className="text-xl font-bold text-gray-800 mb-6">Update Information</h2>
            <form onSubmit={handleSubmit} className="space-y-6">
                <div>
                  <label htmlFor="newId" className="label">
                    Product ID
                  </label>
                  <input
                    type="number"
                    id="newId"
                    name="newId"
                    required
                    min="0"
                    defaultValue={product.id}
                    className="input"
                    placeholder="Enter product ID"
                  />
                </div>

                <div>
                  <label htmlFor="productName" className="label">
                    Product Name
                  </label>
                  <input
                    type="text"
                    id="productName"
                    name="productName"
                    required
                    defaultValue={product.productName}
                    className="input"
                    placeholder="Enter product name"
                  />
                </div>

                <div>
                  <label htmlFor="price" className="label">
                    Price
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    id="price"
                    name="price"
                    required
                    min="0"
                    defaultValue={product.price}
                    className="input"
                    placeholder="0.00"
                  />
                </div>

                <div>
                  <label htmlFor="quantity" className="label">
                    Quantity
                  </label>
                  <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    required
                    min="0"
                    defaultValue={product.quantity}
                    className="input"
                    placeholder="0"
                  />
                </div>

                <div className="flex gap-4">
                  <button
                    type="submit"
                    disabled={loading || success}
                    className="flex-1 btn btn-primary flex items-center justify-center gap-2"
                  >
                    {loading ? (
                      <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    ) : (
                      <>
                        <Edit className="w-5 h-5" />
                        Update Product
                      </>
                    )}
                  </button>
                  <button
                    type="button"
                    onClick={() => setProduct(null)}
                    className="btn btn-secondary"
                  >
                    Cancel
                  </button>
                </div>
            </form>
          </div>
        )}

        {/* All Products */}
        <div className="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
          <div className="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <div className="flex justify-between items-center mb-4">
              <div>
                <h2 className="text-xl font-bold text-gray-800">All Products</h2>
                <p className="text-sm text-gray-600 mt-1">Click on a product to update it</p>
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
                      onClick={() => handleSelectProduct(prod.id)}
                      className="hover:bg-blue-50 cursor-pointer transition-colors"
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
      </main>
    </div>
  );
}
