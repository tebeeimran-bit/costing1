#!/usr/bin/env php
<?php
/**
 * Script to migrate data from SQLite to MySQL.
 * Run AFTER: php artisan migrate (on MySQL)
 * Usage: php migrate_sqlite_to_mysql.php
 */

$sqliteDb = __DIR__ . '/database/database.sqlite';

// MySQL connection settings
$mysqlHost = '127.0.0.1';
$mysqlPort = 3306;
$mysqlDb   = 'costing1';
$mysqlUser = 'root';
$mysqlPass = 'm68rDmTNHn9r77zokXfA';

// Tables to migrate (order matters for foreign keys)
$tables = [
    'users',
    'products',
    'customers',
    'plants',
    'business_categories',
    'pics',
    'cycle_time_templates',
    'materials',
    'exchange_rates',
    'wires',
    'wire_rates',
    'document_receipts',
    'document_projects',
    'costing_data',
    'document_revisions',
    'cogm_submissions',
    'material_breakdowns',
    'unpriced_parts',
    'audit_logs',
    'migrations',
];

try {
    // Connect to SQLite
    $sqlite = new PDO("sqlite:$sqliteDb");
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connect to MySQL
    $mysql = new PDO("mysql:host=$mysqlHost;port=$mysqlPort;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key checks during import
    $mysql->exec("SET FOREIGN_KEY_CHECKS = 0");
    $mysql->exec("SET NAMES utf8mb4");

    $totalRows = 0;

    foreach ($tables as $table) {
        // Check if table exists in SQLite
        $check = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
        if (!$check->fetch()) {
            echo "  SKIP: Table '$table' not found in SQLite\n";
            continue;
        }

        // Check if table exists in MySQL
        $checkMysql = $mysql->query("SHOW TABLES LIKE '$table'");
        if (!$checkMysql->fetch()) {
            echo "  SKIP: Table '$table' not found in MySQL (migration may not have created it)\n";
            continue;
        }

        // Get all data from SQLite
        $rows = $sqlite->query("SELECT * FROM \"$table\"")->fetchAll(PDO::FETCH_ASSOC);
        $count = count($rows);

        if ($count === 0) {
            echo "  EMPTY: $table (0 rows)\n";
            continue;
        }

        // Truncate MySQL table first
        $mysql->exec("TRUNCATE TABLE `$table`");

        // Get column names from first row
        $columns = array_keys($rows[0]);

        // Build parameterized INSERT
        $colList = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $insertSql = "INSERT INTO `$table` ($colList) VALUES ($placeholders)";

        $stmt = $mysql->prepare($insertSql);

        // Insert in batches
        $mysql->beginTransaction();
        $batchCount = 0;
        foreach ($rows as $row) {
            $values = array_values($row);
            $stmt->execute($values);
            $batchCount++;
            if ($batchCount % 500 === 0) {
                $mysql->commit();
                $mysql->beginTransaction();
            }
        }
        $mysql->commit();

        $totalRows += $count;
        echo "  OK: $table ($count rows)\n";
    }

    // Re-enable foreign key checks
    $mysql->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n=== Migration complete! Total: $totalRows rows transferred ===\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
