<?php

namespace App\Assembly\Core;

use App\Config\Database;
use PDO;
use PDOException;

class Model2
{
    protected static string $table = ''; // Table Name
    protected static array $fillable = []; // Fillable fields
    protected static array $hidden = []; // Hidden attributes
    protected static array $casts = []; // Attribute casting
    protected static array $appends = []; // Appended attributes
    protected static bool $timestamps = true; // Timestamps flag
    protected array $attributes = []; // Stores raw attributes
    public static ?PDO $db = null; // Database Connection

    public function __construct(array $attributes = [])
    {
        if (self::$db === null) {
            self::$db = Database::connect();
        }
        $this->fill($attributes);
    }

    public function __set(string $key, $value)
    {
        if (in_array($key, static::$fillable)) {
            $this->attributes[$key] = $value;
        }
    }

    public function __get(string $key)
    {
        if (in_array($key, static::$hidden)) {
            return null;
        }

        if (method_exists($this, 'get' . ucfirst($key) . 'Attribute')) {
            return $this->{'get' . ucfirst($key) . 'Attribute'}();
        }

        return $this->attributes[$key] ?? null;
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->__set($key, $value);
        }
    }

    // Get attribute value (handling hidden fields, accessors & custom accessors)
    public function getAttribute(string $key)
    {
        $value = null;
    
        // Check if the key exists in hidden attributes
        if (in_array($key, static::$hidden)) {
            $value = null;
        } elseif (method_exists($this, 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute')) {
            // Custom accessor exists, call it directly
            $value = $this->{'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute'}();
        } else {
            // No accessor, cast the attribute if necessary
            $value = $this->castAttribute($key, $this->attributes[$key] ?? null);
        }
    
        $this->log("Retrieved attribute: $key with value: " . json_encode($value));
        return $value;
    }    
    

    // Set attribute value (handling mutators)
    public function setAttribute(string $key, $value)
    {
        if (method_exists($this, 'set' . ucfirst($key) . 'Attribute')) {
            $this->attributes[$key] = $this->{'set' . ucfirst($key) . 'Attribute'}($value);
        } else {
            $this->attributes[$key] = $value;
        }

        $this->log("Set attribute: $key with value: " . json_encode($value));
    }

    // Cast attribute values based on the defined casts
    protected function castAttribute(string $key, $value)
    {
        if (!isset(static::$casts[$key])) {
            return $value;
        }

        switch (static::$casts[$key]) {
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = (float) $value;
                break;
            case 'bool':
                $value = (bool) $value;
                break;
            case 'json':
                $value = json_decode($value, true);
                break;
        }

        $this->log("Casted attribute: $key to type: " . gettype($value));
        return $value;
    }

    // Save or update record
    public function save()
    {
        $columns = array_keys($this->attributes);
        $values = array_values($this->attributes);

        try {
            if (isset($this->attributes['id'])) {
                // Update existing record
                $setClause = implode(' = ?, ', $columns) . ' = ?';
                $sql = "UPDATE " . static::$table . " SET $setClause WHERE id = ?";
                $values[] = $this->attributes['id'];
            } else {
                // Insert new record
                $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
                $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            }

            $stmt = self::$db->prepare($sql);
            $stmt->execute($values);

            if (!$this->attributes['id']) {
                $this->attributes['id'] = self::$db->lastInsertId();
            }

            $this->log("Saved record: " . json_encode($this->attributes));
            return true;
        } catch (PDOException $e) {
            $this->log("Error in saving record: " . $e->getMessage(), true);
            return false;
        }
    }

    // Fetch all records
    public static function all()
    {
        try {
            self::$db = Database::connect();
            $stmt = self::$db->query("SELECT * FROM " . static::$table);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add appended attributes to each record
            foreach ($records as &$record) {
                $model = new static($record);
                foreach (static::$appends as $append) {
                    $record[$append] = $model->getAttribute($append);
                }
            }
            self::log('Fetched all records');
            return $records;
        } catch (PDOException $e) {
            self::log("Error fetching all records: " . $e->getMessage(), true);
            return [];
        }
    }

    // Find a record by ID
    public static function find($id)
    {
        try {
            self::$db = Database::connect();
            $stmt = self::$db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                $model = new static($record);
                foreach (static::$appends as $append) {
                    $record[$append] = $model->getAttribute($append);
                }
            }

            self::log("Found record with ID: $id");
            return $record;
        } catch (PDOException $e) {
            self::log("Error finding record with ID: $id - " . $e->getMessage(), true);
            return null;
        }
    }

    // First record
    public static function first()
    {
        return self::all()[0];
    }

    // Last record
    public static function last()
    {
        return self::all()[count(self::all()) - 1];
    }

    // Create a new record (static method)
    public static function create(array $attributes)
    {
        self::$db = Database::connect();
        $model = new static($attributes);
        $model->save();
        self::log("Created new record with attributes: " . json_encode($attributes));
        return $model;
    }

    // Delete a record by ID
    public static function delete($id)
    {
        try {
            $stmt = self::$db->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
            $stmt->execute([$id]);
            self::log("Deleted record with ID: $id");
            return true;
        } catch (PDOException $e) {
            self::log("Error deleting record with ID: $id - " . $e->getMessage(), true);
            return false;
        }
    }

    // Logging method
    protected static function log(string $message, bool $isError = false)
    {
        $logMessage = "[" . date("Y-m-d H:i:s") . "] " . ($isError ? "ERROR: " : "") . $message . "\n";
        error_log($logMessage, 3, '/logs/main.log');
    }

    // Close database connection
    public function __destruct()
    {
        self::$db = null;
        self::log('Database connection closed');
    }
}
