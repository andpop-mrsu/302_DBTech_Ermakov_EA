<?php

function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $dbFile = __DIR__ . '/../data/database.db';
        $dbPath = dirname($dbFile);

        if (!is_dir($dbPath)) {
            mkdir($dbPath, 0777, true);
        }

        $isNewDb = !file_exists($dbFile);

        try {
            $pdo = new PDO("sqlite:" . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            if ($isNewDb) {
                $schemaSql = file_get_contents(__DIR__ . '/../scripts/db_init.sql');
                if ($schemaSql === false) {
                    throw new Exception("Could not read db_init.sql");
                }
                $pdo->exec($schemaSql);

                $seedSql = file_get_contents(__DIR__ . '/../scripts/seed.sql');
                if ($seedSql === false) {
                    throw new Exception("Could not read seed.sql");
                }
                $pdo->exec($seedSql);
            }
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        } catch (Exception $e) {
            die("Database initialization failed: " . $e->getMessage());
        }
    }

    return $pdo;
}
