<?php
// Stub implementation for MongoDB operations to avoid requiring the full library

class ObjectId {
    public $oid;
    
    public function __construct($id = null) {
        if ($id === null) {
            $this->oid = bin2hex(random_bytes(12));
        } else {
            $this->oid = $id;
        }
    }
    
    public function __toString() {
        return $this->oid;
    }
}

class BSON {
    public static function fromJSON($json) {
        return json_decode($json, true);
    }
    
    public static function toJSON($array) {
        return json_encode($array);
    }
}

// Simple file-based storage as a fallback for MongoDB
class FileBasedCollection {
    private $filename;
    
    public function __construct($dbName, $collectionName) {
        $this->filename = $dbName . '_' . $collectionName . '.json';
    }
    
    public function insertOne($document) {
        $documents = $this->getAllDocuments();
        
        // Add an ID if not present
        if (!isset($document['_id'])) {
            $document['_id'] = new ObjectId();
        }
        
        $documents[] = $document;
        $this->saveDocuments($documents);
        
        return new class {
            public $insertedCount = 1;
        };
    }
    
    public function find($filter = [], $options = []) {
        $documents = $this->getAllDocuments();
        
        // Apply filters
        $filteredDocs = array_filter($documents, function($doc) use ($filter) {
            foreach ($filter as $key => $value) {
                if (!isset($doc[$key])) {
                    return false;
                }
                
                if (is_array($value)) {
                    // Handle operators like $lte
                    if (isset($value['$lte'])) {
                        if ($doc[$key] instanceof DateTime) {
                            if ($value['$lte'] instanceof DateTime && $doc[$key] > $value['$lte']) {
                                return false;
                            }
                        } else {
                            if ($doc[$key] > $value['$lte']) {
                                return false;
                            }
                        }
                    } elseif (isset($value['$ne'])) {
                        if ($doc[$key] === $value['$ne']) {
                            return false;
                        }
                    } elseif (isset($value['$exists'])) {
                        if ($value['$exists'] && !isset($doc[$key])) {
                            return false;
                        }
                    }
                } else {
                    if ($doc[$key] != $value) {
                        return false;
                    }
                }
            }
            return true;
        });
        
        // Apply sorting if specified
        if (isset($options['sort'])) {
            $sortBy = key($options['sort']);
            $direction = $options['sort'][$sortBy];
            
            usort($filteredDocs, function($a, $b) use ($sortBy, $direction) {
                if ($a[$sortBy] == $b[$sortBy]) {
                    return 0;
                }
                
                if ($direction === 1) {
                    return $a[$sortBy] < $b[$sortBy] ? -1 : 1;
                } else {
                    return $a[$sortBy] > $b[$sortBy] ? -1 : 1;
                }
            });
        }
        
        // Return array instead of ArrayIterator for compatibility
        return $filteredDocs;
    }
    
    public function deleteOne($filter) {
        $documents = $this->getAllDocuments();
        $initialCount = count($documents);
        
        $documents = array_filter($documents, function($doc) use ($filter) {
            foreach ($filter as $key => $value) {
                if (!isset($doc[$key]) || $doc[$key] != $value) {
                    return true; // Keep this document
                }
            }
            // If all filters matched, remove this document
            return false;
        });
        
        $this->saveDocuments($documents);
        
        return new class($initialCount - count($documents)) {
            private $deletedCount;
            public function __construct($count) {
                $this->deletedCount = $count;
            }
            public function getDeletedCount() {
                return $this->deletedCount;
            }
        };
    }
    
    public function updateOne($filter, $update) {
        $documents = $this->getAllDocuments();
        $updated = false;
        
        for ($i = 0; $i < count($documents); $i++) {
            $match = true;
            foreach ($filter as $key => $value) {
                if (!isset($documents[$i][$key]) || $documents[$i][$key] != $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match && !$updated) {
                // Apply update operations
                foreach ($update as $operation => $fields) {
                    if ($operation === '$set') {
                        foreach ($fields as $field => $value) {
                            $documents[$i][$field] = $value;
                        }
                    }
                }
                $updated = true;
            }
        }
        
        if ($updated) {
            $this->saveDocuments($documents);
        }
        
        return new class($updated) {
            private $matchedCount;
            public function __construct($updated) {
                $this->matchedCount = $updated ? 1 : 0;
            }
            public function getMatchedCount() {
                return $this->matchedCount;
            }
        };
    }
    
    public function createIndex($keys) {
        // In file-based storage, indexing is not applicable
        return true;
    }
    
    private function getAllDocuments() {
        error_log('Reading documents from ' . $this->filename);
        if (!file_exists($this->filename)) {
            error_log('File does not exist, returning empty array');
            return [];
        }
        
        $content = file_get_contents($this->filename);
        if (empty($content)) {
            error_log('File is empty, returning empty array');
            return [];
        }
        
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decode error: ' . json_last_error_msg());
            return [];
        }
        
        // Convert DateTime arrays back to DateTime objects
        $data = $this->convertDateTimeArrays($data);
        // Convert ObjectId arrays back to objects
        $data = $this->convertObjectIds($data);
        
        error_log('Successfully read ' . count($data) . ' documents');
        return $data;
    }
    
    private function convertObjectIds($documents) {
        foreach ($documents as &$doc) {
            foreach ($doc as $key => $value) {
                if (is_array($value) && isset($value['oid']) && count($value) === 1) {
                     $doc[$key] = new ObjectId($value['oid']);
                }
                // Handle nested _id in some cases if needed, but top level is main one
                if ($key === '_id' && is_array($value) && isset($value['oid'])) {
                    $doc[$key] = new ObjectId($value['oid']);
                }
            }
        }
        return $documents;
    }
    
    private function convertDateTimeArrays($documents) {
        foreach ($documents as &$doc) {
            foreach ($doc as $key => $value) {
                if (is_array($value) && isset($value['date']) && isset($value['timezone_type'])) {
                    try {
                        $doc[$key] = new DateTime($value['date']);
                    } catch (Exception $e) {
                        // If we can't parse the date, leave it as is
                        error_log('Failed to convert DateTime array: ' . $e->getMessage());
                    }
                }
            }
        }
        return $documents;
    }
    
    private function saveDocuments($documents) {
        error_log('Saving documents to ' . $this->filename);
        // Convert DateTime objects to arrays for JSON serialization
        $documentsToSave = $this->convertDateTimeObjects($documents);
        $result = file_put_contents($this->filename, json_encode($documentsToSave, JSON_PRETTY_PRINT));
        if ($result === false) {
            error_log('Failed to save documents to ' . $this->filename);
        } else {
            error_log('Successfully saved ' . count($documents) . ' documents');
        }
    }
    
    private function convertDateTimeObjects($documents) {
        foreach ($documents as &$doc) {
            foreach ($doc as $key => $value) {
                if ($value instanceof DateTime) {
                    $doc[$key] = [
                        'date' => $value->format('c'), // ISO 8601 format
                        'timezone_type' => 3,
                        'timezone' => $value->getTimezone()->getName()
                    ];
                }
            }
        }
        return $documents;
    }
}

class Client {
    private $host;
    private $port;
    
    public function __construct($uri) {
        // Parse the URI to extract host and port
        $parsed = parse_url($uri);
        $this->host = $parsed['host'] ?? 'localhost';
        $this->port = $parsed['port'] ?? '27017';
    }
    
    public function selectDatabase($name) {
        return new Database($name, $this->host, $this->port);
    }
}

class Database {
    private $name;
    private $host;
    private $port;
    
    public function __construct($name, $host, $port) {
        $this->name = $name;
        $this->host = $host;
        $this->port = $port;
    }
    
    public function selectCollection($name) {
        return new FileBasedCollection($this->name, $name);
    }
}
?>