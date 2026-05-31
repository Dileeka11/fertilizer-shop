<?php
/** Staff user (owner/cashier/operator) CRUD. */
final class User
{
    public static function all(): array
    {
        return Database::all(
            "SELECT user_no, user_id, username, full_name, email, phone, role, status, last_login, created_at
             FROM staff_users ORDER BY user_no"
        );
    }

    public static function find(int $no): ?array
    {
        return Database::one("SELECT * FROM staff_users WHERE user_no = ?", 'i', [$no]);
    }

    public static function findByUsername(string $username): ?array
    {
        return Database::one("SELECT * FROM staff_users WHERE username = ?", 's', [$username]);
    }

    public static function create(array $d): int
    {
        $uid  = 'U' . str_pad((string)((int)Database::scalar("SELECT IFNULL(MAX(user_no),0)+1 FROM staff_users")), 4, '0', STR_PAD_LEFT);
        $hash = password_hash((string)$d['password'], PASSWORD_DEFAULT);
        return Database::insert(
            "INSERT INTO staff_users (user_id, username, password, full_name, email, phone, role, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            'ssssssss',
            [
                $uid,
                (string)$d['username'],
                $hash,
                (string)$d['full_name'],
                (string)$d['email'],
                (string)($d['phone']  ?? ''),
                (string)$d['role'],
                (string)($d['status'] ?? 'Active'),
            ]
        );
    }

    public static function update(int $no, array $d): int
    {
        if (!empty($d['password'])) {
            $hash = password_hash((string)$d['password'], PASSWORD_DEFAULT);
            return Database::exec(
                "UPDATE staff_users
                 SET full_name = ?, email = ?, phone = ?, role = ?, status = ?, password = ?
                 WHERE user_no = ?",
                'ssssssi',
                [
                    (string)$d['full_name'], (string)$d['email'], (string)($d['phone'] ?? ''),
                    (string)$d['role'], (string)($d['status'] ?? 'Active'), $hash, $no,
                ]
            );
        }
        return Database::exec(
            "UPDATE staff_users
             SET full_name = ?, email = ?, phone = ?, role = ?, status = ?
             WHERE user_no = ?",
            'sssssi',
            [
                (string)$d['full_name'], (string)$d['email'], (string)($d['phone'] ?? ''),
                (string)$d['role'], (string)($d['status'] ?? 'Active'), $no,
            ]
        );
    }

    public static function setStatus(int $no, string $status): int
    {
        return Database::exec("UPDATE staff_users SET status = ? WHERE user_no = ?", 'si', [$status, $no]);
    }

    public static function delete(int $no): int
    {
        return Database::exec("DELETE FROM staff_users WHERE user_no = ?", 'i', [$no]);
    }
}
