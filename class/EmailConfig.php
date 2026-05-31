<?php
final class EmailConfig
{
    public static function load(): array
    {
        $row = Database::one("SELECT * FROM email_config ORDER BY config_id ASC LIMIT 1");
        return $row ?: [
            'to_email'   => '',
            'from_email' => '',
            'subject'    => 'Low Stock Alert: {product_name}',
            'message'    => '',
        ];
    }

    public static function save(array $d): void
    {
        $existing = Database::one("SELECT config_id FROM email_config ORDER BY config_id ASC LIMIT 1");
        if ($existing) {
            Database::exec(
                "UPDATE email_config SET to_email = ?, from_email = ?, subject = ?, message = ? WHERE config_id = ?",
                'ssssi',
                [
                    (string)($d['to_email']  ?? ''),
                    (string)($d['from_email'] ?? ''),
                    (string)($d['subject']   ?? ''),
                    (string)($d['message']   ?? ''),
                    (int)$existing['config_id'],
                ]
            );
        } else {
            Database::insert(
                "INSERT INTO email_config (to_email, from_email, subject, message) VALUES (?, ?, ?, ?)",
                'ssss',
                [
                    (string)($d['to_email']  ?? ''),
                    (string)($d['from_email'] ?? ''),
                    (string)($d['subject']   ?? ''),
                    (string)($d['message']   ?? ''),
                ]
            );
        }
    }

    /** Render a low-stock alert email for a given product. */
    public static function renderAlert(array $product): array
    {
        $cfg     = self::load();
        $subject = str_replace('{product_name}', (string)$product['name'], $cfg['subject']);
        $extra   = '';
        foreach (['brand','variety','package_size','form','npk_ratio','disease_control','active_ingredient','material'] as $k) {
            if (!empty($product[$k])) $extra .= ucwords(str_replace('_',' ',$k)) . ": " . $product[$k] . "\n";
        }
        $message = strtr($cfg['message'], [
            '{id}'            => (string)($product['product_id'] ?? ''),
            '{name}'          => (string)($product['name']       ?? ''),
            '{category}'      => (string)($product['category_name'] ?? ''),
            '{extra_fields}'  => $extra,
            '{stock}'         => (string)($product['stock']         ?? ''),
            '{reorder}'       => (string)($product['reorder_level'] ?? ''),
        ]);
        return ['to' => $cfg['to_email'], 'from' => $cfg['from_email'], 'subject' => $subject, 'message' => $message];
    }
}
