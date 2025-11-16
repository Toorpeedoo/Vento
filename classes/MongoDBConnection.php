<?php

class MongoDBConnection {
    private static $client = null;
    private static $database = null;
    
    private const CONNECTION_URI = "mongodb+srv://Vento:Vento@vento.gknvzdv.mongodb.net/?appName=VENTO";
    private const DATABASE_NAME = "vento_inventory";
    
    public static function getDatabase() {
        if (self::$database === null) {
            try {
                // Check if MongoDB extension is loaded
                if (!extension_loaded('mongodb')) {
                    throw new Exception("MongoDB extension is not installed. Please install it to use MongoDB.");
                }
                
                self::$client = new MongoDB\Driver\Manager(self::CONNECTION_URI);
                self::$database = self::DATABASE_NAME;
            } catch (Exception $e) {
                error_log("MongoDB Connection Error: " . $e->getMessage());
                throw new Exception("Failed to connect to MongoDB: " . $e->getMessage());
            }
        }
        return self::$client;
    }
    
    public static function getDatabaseName() {
        return self::DATABASE_NAME;
    }
    
    public static function getCollection($collectionName) {
        return self::DATABASE_NAME . "." . $collectionName;
    }
    
    // Execute a find query
    public static function find($collection, $filter = [], $options = []) {
        try {
            $manager = self::getDatabase();
            $query = new MongoDB\Driver\Query($filter, $options);
            $namespace = self::getCollection($collection);
            $cursor = $manager->executeQuery($namespace, $query);
            return $cursor->toArray();
        } catch (Exception $e) {
            error_log("MongoDB Find Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Execute a findOne query
    public static function findOne($collection, $filter = []) {
        $results = self::find($collection, $filter, ['limit' => 1]);
        return !empty($results) ? $results[0] : null;
    }
    
    // Insert a document
    public static function insertOne($collection, $document) {
        try {
            $manager = self::getDatabase();
            $bulk = new MongoDB\Driver\BulkWrite();
            $bulk->insert($document);
            $namespace = self::getCollection($collection);
            $result = $manager->executeBulkWrite($namespace, $bulk);
            return $result->getInsertedCount() > 0;
        } catch (Exception $e) {
            error_log("MongoDB Insert Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update documents
    public static function updateOne($collection, $filter, $update, $options = []) {
        try {
            $manager = self::getDatabase();
            $bulk = new MongoDB\Driver\BulkWrite();
            $bulk->update($filter, $update, $options);
            $namespace = self::getCollection($collection);
            $result = $manager->executeBulkWrite($namespace, $bulk);
            return $result->getModifiedCount() > 0 || $result->getMatchedCount() > 0;
        } catch (Exception $e) {
            error_log("MongoDB Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete documents
    public static function deleteOne($collection, $filter) {
        try {
            $manager = self::getDatabase();
            $bulk = new MongoDB\Driver\BulkWrite();
            $bulk->delete($filter, ['limit' => 1]);
            $namespace = self::getCollection($collection);
            $result = $manager->executeBulkWrite($namespace, $bulk);
            return $result->getDeletedCount() > 0;
        } catch (Exception $e) {
            error_log("MongoDB Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete multiple documents
    public static function deleteMany($collection, $filter) {
        try {
            $manager = self::getDatabase();
            $bulk = new MongoDB\Driver\BulkWrite();
            $bulk->delete($filter, ['limit' => 0]);
            $namespace = self::getCollection($collection);
            $result = $manager->executeBulkWrite($namespace, $bulk);
            return $result->getDeletedCount();
        } catch (Exception $e) {
            error_log("MongoDB Delete Error: " . $e->getMessage());
            return 0;
        }
    }
    
    // Count documents
    public static function count($collection, $filter = []) {
        try {
            $manager = self::getDatabase();
            $command = new MongoDB\Driver\Command([
                'count' => $collection,
                'query' => $filter ?: new stdClass()
            ]);
            $namespace = self::getDatabaseName();
            $cursor = $manager->executeCommand($namespace, $command);
            $result = $cursor->toArray();
            return isset($result[0]->n) ? (int)$result[0]->n : 0;
        } catch (Exception $e) {
            error_log("MongoDB Count Error: " . $e->getMessage());
            // Fallback: use aggregate for counting
            try {
                $command = new MongoDB\Driver\Command([
                    'aggregate' => $collection,
                    'pipeline' => $filter ? [['$match' => $filter], ['$count' => 'total']] : [['$count' => 'total']],
                    'cursor' => new stdClass()
                ]);
                $cursor = $manager->executeCommand($namespace, $command);
                $result = $cursor->toArray();
                return isset($result[0]->total) ? (int)$result[0]->total : 0;
            } catch (Exception $e2) {
                error_log("MongoDB Count Fallback Error: " . $e2->getMessage());
                return 0;
            }
        }
    }
}
