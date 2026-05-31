<?php
/**
 * Database — mysqli singleton.
 *
 * Usage:
 *   $db = Database::getConnection();            // raw mysqli
 *   $row = Database::one($sql, 'i', [$id]);     // single row
 *   $rows = Database::all($sql);                // all rows
 *   $id = Database::insert($sql, 'sd', [...]);  // insert + return id
 */
final class Database
{
    private static ?mysqli $conn = null;

    public static function getConnection(): mysqli
    {
        if (self::$conn instanceof mysqli) {
            return self::$conn;
        }

        $c = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($c->connect_error) {
            die('Database connection failed: ' . $c->connect_error);
        }
        $c->set_charset('utf8mb4');
        self::$conn = $c;
        return self::$conn;
    }

    /** Run a prepared statement and return the mysqli_stmt. */
    public static function run(string $sql, string $types = '', array $params = []): mysqli_stmt
    {
        $stmt = self::getConnection()->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . self::getConnection()->error . ' [SQL: ' . $sql . ']');
        }
        if ($types !== '' && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    /** Fetch a single associative row (or null). */
    public static function one(string $sql, string $types = '', array $params = []): ?array
    {
        $stmt = self::run($sql, $types, $params);
        $res  = $stmt->get_result();
        $row  = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row ?: null;
    }

    /** Fetch all rows as an array of associative arrays. */
    public static function all(string $sql, string $types = '', array $params = []): array
    {
        $stmt = self::run($sql, $types, $params);
        $res  = $stmt->get_result();
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    /** Fetch a single scalar (first column of first row). */
    public static function scalar(string $sql, string $types = '', array $params = [])
    {
        $row = self::one($sql, $types, $params);
        if ($row === null) return null;
        return reset($row);
    }

    /** INSERT and return last inserted id. */
    public static function insert(string $sql, string $types = '', array $params = []): int
    {
        $stmt = self::run($sql, $types, $params);
        $id   = self::getConnection()->insert_id;
        $stmt->close();
        return (int)$id;
    }

    /** UPDATE/DELETE — returns affected rows. */
    public static function exec(string $sql, string $types = '', array $params = []): int
    {
        $stmt     = self::run($sql, $types, $params);
        $affected = $stmt->affected_rows;
        $stmt->close();
        return (int)$affected;
    }

    public static function beginTransaction(): void { self::getConnection()->begin_transaction(); }
    public static function commit():           void { self::getConnection()->commit(); }
    public static function rollback():         void { self::getConnection()->rollback(); }
}
