'use client';

import { useState, FormEvent, useRef } from 'react';
import { useRouter } from 'next/navigation';
import AuthGuard from '@/components/AuthGuard';
import Navbar from '@/components/Navbar';
import Link from 'next/link';
import { Plus, AlertCircle, CheckCircle, ArrowLeft } from 'lucide-react';

export default function AddProductPage() {
  return (
    <AuthGuard>
      <AddProductContent />
    </AuthGuard>
  );
}

function AddProductContent() {
  const router = useRouter();
  const formRef = useRef<HTMLFormElement>(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError('');
    setSuccess(false);
    setLoading(true);

    const form = e.currentTarget;
    const formData = new FormData(form);
    const id = Number(formData.get('id'));
    const productName = formData.get('productName') as string;
    const price = Number(formData.get('price'));
    const quantity = Number(formData.get('quantity'));

    try {
      const res = await fetch('/api/products', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, productName, price, quantity }),
      });

      let data;
      try {
        data = await res.json();
      } catch (parseError) {
        console.error('JSON parse error:', parseError);
        setError('Invalid response from server');
        setLoading(false);
        return;
      }

      if (!res.ok) {
        setError(data.error || 'Failed to add product');
        setLoading(false);
        return;
      }

      setSuccess(true);
      setLoading(false);
      
      // Reset form using ref instead of e.currentTarget
      if (formRef.current) {
        formRef.current.reset();
      }
      
      // Clear success message after 2 seconds
      setTimeout(() => {
        setSuccess(false);
      }, 2000);
    } catch (err) {
      console.error('Add product error:', err);
      setError(`Network error: ${err instanceof Error ? err.message : 'Please try again'}`);
      setLoading(false);
      setSuccess(false);
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
            Add Product
          </h1>
          <p className="text-gray-600">Add a new product to your inventory</p>
        </div>

        <div className="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          {error && (
            <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 text-red-800">
              <AlertCircle className="w-5 h-5 flex-shrink-0" />
              <span>{error}</span>
            </div>
          )}

          {success && (
            <div className="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3 text-green-800">
              <CheckCircle className="w-5 h-5 flex-shrink-0" />
              <span>Product added successfully! Redirecting...</span>
            </div>
          )}

          <form ref={formRef} onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label htmlFor="id" className="label">
                Product ID
              </label>
              <input
                type="number"
                id="id"
                name="id"
                required
                min="0"
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
                    <Plus className="w-5 h-5" />
                    Add Product
                  </>
                )}
              </button>
              <Link href="/dashboard" className="btn btn-secondary">
                Cancel
              </Link>
            </div>
          </form>
        </div>
      </main>
    </div>
  );
}

