<?php
/**
 * This PHP script illustrates how to operate over a single table (INSERT, SELECT and DELETE).
 * 
 * For more complex examples see test2.php.
 * 
 * How to test this script:
 * 1. Create a new database (for example 'test')
 * 2. Import test/test.sql to create necessary tables
 * 3. Change config.php with correct values (database, username and password)
 */
header("Content-Type: text/plain; charset=utf-8");
require_once "../vendor/autoload.php";
require_once "config.php";
use soloproyectos\db\DbConnector;
use soloproyectos\db\record\DbRecordTable;

// creates a new connector instance and prints each SQL statement (debugging)
$db = new DbConnector("test", "test", "test");
$db->addDebugListener(function ($sql) {
    echo "--$sql\n";
});

// table manager
$t = new DbRecordTable($db, "table0");

// inserts a record
echo "### Inserts a record\n";
$id = $t->insert(["title" => "Title", "created_at" => date("Y-m-d H:i:s")]);

// selects a record
echo "\n### Selects a record\n";
list($title, $createdAt) = $t->select(["title", "created_at"], $id);
echo "id: $id, title: $title, created_at: $createdAt\n";

// deletes a record
echo "\n### Deletes the previous record\n";
$t->delete($id);
