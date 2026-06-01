<?php
final class Category
{
    public static function all(): array
    {
        return Database::all("SELECT category_id, category_name, slug FROM categories ORDER BY category_name");
    }

    public static function find(int $id): ?array
    {
        return Database::one("SELECT * FROM categories WHERE category_id = ?", 'i', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::one("SELECT * FROM categories WHERE slug = ?", 's', [$slug]);
    }

    public static function findByName(string $name): ?array
    {
        return Database::one("SELECT * FROM categories WHERE category_name = ?", 's', [$name]);
    }

    /** Build a URL-friendly slug from a name. */
    public static function slugify(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function create(array $d): int
    {
        $name = trim((string)($d['category_name'] ?? ''));
        $slug = self::slugify($name);
        return Database::insert(
            "INSERT INTO categories (category_name, slug) VALUES (?, ?)",
            'ss',
            [$name, $slug]
        );
    }

    public static function update(int $id, array $d): int
    {
        $name = trim((string)($d['category_name'] ?? ''));
        $slug = self::slugify($name);
        return Database::exec(
            "UPDATE categories SET category_name = ?, slug = ? WHERE category_id = ?",
            'ssi',
            [$name, $slug, $id]
        );
    }

    public static function delete(int $id): int
    {
        return Database::exec("DELETE FROM categories WHERE category_id = ?", 'i', [$id]);
    }

    /** Number of products attached to this category (used to block deletion). */
    public static function productCount(int $id): int
    {
        return (int)Database::scalar("SELECT COUNT(*) FROM products WHERE category_id = ?", 'i', [$id]);
    }
}
