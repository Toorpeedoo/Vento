import { MongoClient, Db, Collection, Document } from 'mongodb';

function getMongoUri(): string {
  if (!process.env.MONGODB_URI) {
    console.error('❌ MONGODB_URI environment variable is not set!');
    console.error('Add it to Vercel: Settings → Environment Variables');
    console.error('Example: mongodb+srv://username:password@cluster.mongodb.net/?appName=VENTO');
    throw new Error('MONGODB_URI is required. Please add it to your environment variables.');
  }
  
  let uri = process.env.MONGODB_URI.trim();
  
  // Ensure mongodb+srv:// connections have proper parameters
  if (uri.startsWith('mongodb+srv://')) {
    try {
      // For mongodb+srv://, TLS is automatic, but we should ensure proper connection options
      // Add retryWrites and retryReads to the URI if not already present
      const url = new URL(uri);
      if (!url.searchParams.has('retryWrites')) {
        url.searchParams.set('retryWrites', 'true');
      }
      if (!url.searchParams.has('w')) {
        url.searchParams.set('w', 'majority');
      }
      // Ensure SSL/TLS is not explicitly disabled or set incorrectly
      url.searchParams.delete('ssl'); // Remove if present, as it's not needed for mongodb+srv
      url.searchParams.delete('tls'); // Remove if present, as it's automatic
      uri = url.toString();
    } catch (error) {
      // If URL parsing fails, return the original URI
      // This might happen if the URI format is non-standard
      console.warn('Failed to parse MongoDB URI, using as-is:', error);
      return uri;
    }
  }
  
  return uri;
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

