<?php
final class Supplier
{
    public static function all(): array
    {
        return Database::all(
            "SELECT supplier_no, supplier_id, company_name, contact_person, phone, email,
                    products_supplied, address, status
             FROM suppliers ORDER BY company_name"
        );
    }

    public static function find(int $no): ?array
    {
        return Database::one("SELECT * FROM suppliers WHERE supplier_no = ?", 'i', [$no]);
    }

    public static function create(array $d): int
    {
        $sid = 'S' . str_pad((string)((int)Database::scalar("SELECT IFNULL(MAX(supplier_no),0)+1 FROM suppliers")), 4, '0', STR_PAD_LEFT);
        return Database::insert(
            "INSERT INTO suppliers (supplier_id, company_name, contact_person, phone, email, products_supplied, address, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            'ssssssss',
            [
                $sid,
                (string)($d['company_name']    ?? ''),
                (string)($d['contact_person']  ?? ''),
                (string)($d['phone']           ?? ''),
                (string)($d['email']           ?? ''),
                (string)($d['products_supplied'] ?? ''),
                (string)($d['address']         ?? ''),
                (string)($d['status']          ?? 'Active'),
            ]
        );
    }

    public static function update(int $no, array $d): int
    {
        return Database::exec(
            "UPDATE suppliers
             SET company_name = ?, contact_person = ?, phone = ?, email = ?,
                 products_supplied = ?, address = ?, status = ?
             WHERE supplier_no = ?",
            'sssssssi',
            [
                (string)($d['company_name']    ?? ''),
                (string)($d['contact_person']  ?? ''),
                (string)($d['phone']           ?? ''),
                (string)($d['email']           ?? ''),
                (string)($d['products_supplied'] ?? ''),
                (string)($d['address']         ?? ''),
                (string)($d['status']          ?? 'Active'),
                $no,
            ]
        );
    }

    public static function delete(int $no): int
    {
        return Database::exec("DELETE FROM suppliers WHERE supplier_no = ?", 'i', [$no]);
    }
}
