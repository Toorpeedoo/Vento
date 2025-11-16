import { MongoClient, Db, Collection, Document } from 'mongodb';

const MONGODB_URI = 'mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO';
const MONGODB_DB = 'vento_inventory';

function getMongoUri(): string {
  return MONGODB_URI;
}

function getDbName(): string {
  return MONGODB_DB;
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
  // For MongoDB Atlas (mongodb+srv://), SSL/TLS is automatically enabled
  // Don't override TLS settings as they're handled by the connection string
  const options: any = {
    maxPoolSize: 10,
    minPoolSize: 1,
    serverSelectionTimeoutMS: 30000, // Increased from 5s to 30s for better reliability
    socketTimeoutMS: 45000,
    connectTimeoutMS: 30000, // Increased from 10s to 30s for better reliability
    // Enable retries for better reliability (also set in URI, but ensure in options)
    retryWrites: true,
    retryReads: true,
    // Connection pool monitoring
    monitorCommands: false, // Set to true for debugging
    // Let the URI handle SSL/TLS configuration - don't override
    // This is especially important for mongodb+srv:// connections
  };

  client = new MongoClient(uri, options);

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

