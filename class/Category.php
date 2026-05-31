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
}
