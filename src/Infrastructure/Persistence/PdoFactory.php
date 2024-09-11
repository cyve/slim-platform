<?php

namespace SlimPlatform\Infrastructure\Persistence;

class PdoFactory
{
    public static function create(string $dsn): \PDO
    {
        filter_var($dsn, FILTER_VALIDATE_URL) ?: throw new \InvalidArgumentException(sprintf('%s: Argument #1 ($dsn) must be a valid URL, string given', __METHOD__));

        $databaseDsn = parse_url($dsn);
        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $databaseDsn['scheme'], $databaseDsn['host'], $databaseDsn['port'] ?? 3306, trim($databaseDsn['path'], '/'));

        return new \PDO($dsn, $databaseDsn['user'], $databaseDsn['password'] ?? null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }
}
