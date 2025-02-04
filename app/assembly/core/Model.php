<?php

namespace App\Assembly\Core;

use App\Config\Database;
use PDO;
use PDOException;

class Model
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
        // Do not set hidden attributes
        if (in_array($key, static::$hidden)) {
            return;
        }

        // Only set fillable attributes
        if (in_array($key, static::$fillable)) {
            $this->attributes[$key] = $value;
        }

        return $this->$key = $value;
    }

    public function __get(string $key)
    {
        // Do not return hidden attributes
        if (in_array($key, static::$hidden)) {
            return null;
        }

        // Check for custom accessors
        if (method_exists($this, 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute')) {
            return $this->{'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute'}();
        }

        // Return the attribute value (cast if necessary)
        return $this->getAttribute($key);
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->__set($key, $value);
        }
    }

    protected function getAttribute(string $key)
    {
        // Do not return hidden attributes
        if (in_array($key, static::$hidden)) {
            return null;
        }

        $value = $this->attributes[$key] ?? null;

        // Cast the attribute if a cast is defined
        if (isset(static::$casts[$key])) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }

    protected function castAttribute(string $key, $value)
    {
        if (!isset(static::$casts[$key])) {
            return $value;
        }

        switch (static::$casts[$key]) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return (bool) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public function save()
    {
        $tableName = static::$table;
        $attributes = $this->attributes;

        // Exclude hidden attributes from the save operation
        $columns = array_diff(array_keys($attributes), static::$hidden);
        $values = array_intersect_key($attributes, array_flip($columns));


        try {
            self::$db = Database::connect();

            if (isset($attributes['id'])) {  // UPDATE logic
                $setStatements = [];
                $bindValues = []; // Use a separate array for binding values

                foreach ($values as $key => $value) { // Use $values here (filtered attributes)
                    if ($key !== 'id') {
                        $setStatements[] = "$key = ?";
                        $bindValues[] = $value;
                    }
                }
                $bindValues[] = $attributes['id']; // ID for WHERE clause

                $sql = "UPDATE $tableName SET " . implode(", ", $setStatements) . " WHERE id = ?";
                $stmt = self::$db->prepare($sql);
                $stmt->execute($bindValues); // Bind the correct filtered values
                return true;
            } else {  // INSERT logic
                $placeholders = array_fill(0, count($values), '?'); // Use $values count

                $sql = "INSERT INTO $tableName (" . implode(", ", array_keys($values)) . ") VALUES (" . implode(", ", $placeholders) . ")";
                $stmt = self::$db->prepare($sql);
                $stmt->execute(array_values($values)); // Bind the correct filtered values

                $this->attributes['id'] = self::$db->lastInsertId();
                return true;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

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
                    $record[$append] = $model->__get($append);
                }

                // Remove hidden attributes
                foreach (static::$hidden as $hidden) {
                    unset($record[$hidden]);
                }
            }

            return $records;
        } catch (PDOException $e) {
            return [];
        }
    }

    public static function find($id)
    {
        try {
            self::$db = Database::connect();
            $stmt = self::$db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                $model = new static($record); // Create the model instance *using the $record data*
                
                $model->fill($record);
                foreach (static::$appends as $append) {
                    $model->$append = $model->__get($append);
                }

                foreach (static::$hidden as $hidden) {
                    unset($model->$hidden);
                }

                return $model;
            }

            return null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public static function first()
    {
        return self::all()[0];
    }

    public static function last()
    {
        $all = self::all();
        return $all[count($all) - 1];
    }

    public static function create(array $attributes)
    {
        self::$db = Database::connect();
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function delete($id)
    {
        try {
            self::$db = Database::connect();
            $stmt = self::$db->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    protected static function log(string $message, bool $isError = false)
    {
        $logMessage = "[" . date("Y-m-d H:i:s") . "] " . ($isError ? "ERROR: " : "") . $message . "\n";
        error_log($logMessage, 3, '/logs/main.log');
    }

    public function __destruct()
    {
        self::$db = null;
    }
}
