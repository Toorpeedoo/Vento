import { getCollection } from '../mongodb';
import { User } from '../types';

const COLLECTION = 'users';

export async function getUser(username: string): Promise<User | null> {
  try {
    const collection = await getCollection<User>(COLLECTION);
    const user = await collection.findOne({
      username: { $regex: new RegExp(`^${username}$`, 'i') }
    });
    return user;
  } catch (error) {
    console.error('getUser error:', error);
    throw error;
  }
}

export async function getAllUsers(): Promise<User[]> {
  const collection = await getCollection<User>(COLLECTION);
  return collection.find({}).toArray();
}

export async function createUser(user: Omit<User, '_id'>): Promise<boolean> {
  try {
    const collection = await getCollection<User>(COLLECTION);
    
    // Check if user exists
    const existing = await getUser(user.username);
    if (existing) {
      return false;
    }
    
    const userDoc: User = {
      ...user,
      createdAt: new Date().toISOString(),
    } as User;
    
    const result = await collection.insertOne(userDoc);
    
    return result.insertedId !== null;
  } catch (error) {
    console.error('createUser error:', error);
    throw error;
  }
}

export async function updateUser(oldUsername: string, user: Partial<User>): Promise<boolean> {
  const collection = await getCollection<User>(COLLECTION);
  
  const result = await collection.updateOne(
    { username: { $regex: new RegExp(`^${oldUsername}$`, 'i') } },
    { $set: { ...user, updatedAt: new Date().toISOString() } }
  );
  
  return result.modifiedCount > 0;
}

export async function deleteUser(username: string): Promise<boolean> {
  const collection = await getCollection<User>(COLLECTION);
  
  // Also delete user's products
  const { deleteUserProducts } = await import('./product');
  await deleteUserProducts(username);
  
  const result = await collection.deleteOne({
    username: { $regex: new RegExp(`^${username}$`, 'i') }
  });
  
  return result.deletedCount > 0;
}

export async function verifyUser(username: string, password: string): Promise<boolean> {
  try {
    const user = await getUser(username);
    if (!user) {
      return false;
    }
    
    // Compare plain text passwords
    return user.password === password;
  } catch (error) {
    console.error('verifyUser error:', error);
    throw error;
  }
}

