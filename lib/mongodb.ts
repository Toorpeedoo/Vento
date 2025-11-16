import { MongoClient, Db, Collection, Document } from 'mongodb';

function getMongoUri(): string {
  if (!process.env.MONGODB_URI) {
    throw new Error('Please add your Mongo URI to .env.local');
  }
  return process.env.MONGODB_URI;
}

function getDbName(): string {
  return process.env.MONGODB_DB || 'vento_inventory';
}

let client: MongoClient;
let clientPromise: Promise<MongoClient> | null = null;

function getClientPromise(): Promise<MongoClient> {
  // Use global variable to cache connection in both dev and serverless (Vercel)
  // This prevents creating new connections on each invocation
  let globalWithMongo = global as typeof globalThis & {
    _mongoClientPromise?: Promise<MongoClient>;
  };

  if (globalWithMongo._mongoClientPromise) {
    return globalWithMongo._mongoClientPromise;
  }

  const uri = getMongoUri();

  // Create MongoDB client with proper connection pool settings
  client = new MongoClient(uri, {
    maxPoolSize: 10,
    minPoolSize: 1,
    serverSelectionTimeoutMS: 5000,
    socketTimeoutMS: 45000,
    connectTimeoutMS: 10000,
  });

  // Cache the connection promise globally
  globalWithMongo._mongoClientPromise = client.connect();
  clientPromise = globalWithMongo._mongoClientPromise;

  return clientPromise;
}

export async function getDatabase(): Promise<Db> {
  const client = await getClientPromise();
  return client.db(getDbName());
}

export async function getCollection<T extends Document>(name: string): Promise<Collection<T>> {
  const db = await getDatabase();
  return db.collection<T>(name);
}

