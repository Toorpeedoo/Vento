import { getCollection } from '../mongodb';
import { Product } from '../types';

const COLLECTION = 'products';

export async function getProducts(username: string): Promise<Product[]> {
  const collection = await getCollection<Product>(COLLECTION);
  return collection.find({ username }).sort({ id: 1 }).toArray();
}

export async function getAllProducts(username: string): Promise<Product[]> {
  return getProducts(username);
}

export async function getProduct(username: string, id: number): Promise<Product | null> {
  const collection = await getCollection<Product>(COLLECTION);
  return collection.findOne({ username, id });
}

export async function createProduct(product: Omit<Product, '_id'>): Promise<boolean> {
  const collection = await getCollection<Product>(COLLECTION);
  
  // Check if product exists
  const existing = await getProduct(product.username, product.id);
  if (existing) {
    return false;
  }
  
  const result = await collection.insertOne({
    ...product,
    createdAt: new Date(),
  });
  
  return result.insertedId !== null;
}

export async function updateProduct(product: Product): Promise<boolean> {
  const collection = await getCollection<Product>(COLLECTION);
  
  const result = await collection.updateOne(
    { username: product.username, id: product.id },
    { 
      $set: { 
        productName: product.productName,
        price: product.price,
        quantity: product.quantity,
        updatedAt: new Date()
      } 
    }
  );
  
  return result.modifiedCount > 0;
}

export async function deleteProduct(username: string, id: number): Promise<boolean> {
  const collection = await getCollection<Product>(COLLECTION);
  
  const result = await collection.deleteOne({ username, id });
  return result.deletedCount > 0;
}

export async function getProductCount(username: string): Promise<number> {
  const collection = await getCollection<Product>(COLLECTION);
  return collection.countDocuments({ username });
}

export async function deleteUserProducts(username: string): Promise<boolean> {
  const collection = await getCollection<Product>(COLLECTION);
  
  const result = await collection.deleteMany({ username });
  return result.deletedCount >= 0;
}

export async function productExists(username: string, id: number): Promise<boolean> {
  const product = await getProduct(username, id);
  return product !== null;
}

