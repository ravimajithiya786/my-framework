<?php

namespace App\Assembly\Core;

use App\Config\Database;
use PDO;
use PDOException;

abstract class Model3
{
    protected static string $table = '';
    protected static array $fillable = [];
    protected static array $hidden = [];
    protected static array $casts = [];
    protected static array $appends = [];
    protected static bool $timestamps = true;
    protected array $attributes = [];
    protected static ?PDO $db = null;

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

    public function save()
    {
        $columns = array_keys($this->attributes);
        $values = array_values($this->attributes);

        try {
            if (!empty($this->attributes['id'])) {
                $setClause = implode(' = ?, ', $columns) . ' = ?';
                $sql = "UPDATE " . static::$table . " SET $setClause WHERE id = ?";
                $values[] = $this->attributes['id'];
            } else {
                $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
                $sql = "INSERT INTO " . static::$table . " (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            }

            $stmt = self::$db->prepare($sql);
            $stmt->execute($values);

            if (!isset($this->attributes['id'])) {
                $this->attributes['id'] = self::$db->lastInsertId();
            }

            return true;
        } catch (PDOException $e) {
            die("Error saving record: " . $e->getMessage());
        }
    }

    public static function all()
    {
        try {
            $stmt = self::$db->query("SELECT * FROM " . static::$table);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map(fn($record) => new static($record), $records);
        } catch (PDOException $e) {
            die("Error fetching records: " . $e->getMessage());
        }
    }

    public static function find($id)
    {
        try {
            $stmt = self::$db->prepare("SELECT * FROM " . static::$table . " WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            return $record ? new static($record) : null;
        } catch (PDOException $e) {
            die("Error finding record: " . $e->getMessage());
        }
    }

    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function delete($id)
    {
        try {
            $stmt = self::$db->prepare("DELETE FROM " . static::$table . " WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            die("Error deleting record: " . $e->getMessage());
        }
    }
}
