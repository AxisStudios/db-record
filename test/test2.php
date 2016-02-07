<?php
/**
 * This PHP script illustrates how to operate over several tables at the same time.
 * 
 * There's a main table, called table0, and three linked tables (table1, table2 and table3).
 * The linked tables are 'left joined' to the main table by the fields
 * table1_id, table2_id and table3_id.
 * 
 * How to test this script:
 * 1. Create a new database (for example 'test')
 * 2. Import test/test.sql to create necessary tables
 * 3. Change config.php with correct values (database, username and password)
 */
header("Content-Type: text/plain; charset=utf-8");
require_once "../vendor/autoload.php";
use soloproyectos\db\DbConnector;
use soloproyectos\db\record\DbRecordTable;

// creates a new connector instance and prints each SQL statement (debugging)
$db = new DbConnector("test", "test", "test");
$db->addDebugListener(function ($sql) {
    echo "--$sql\n";
});

// table manager
$t = new DbRecordTable($db, "table0");

// inserts records into multiple tables
// the following code inserts a record into table0 and, eventually, into table1, table2 and table3
echo "### Inserts records into multiple tables\n";
$id = $t->insert([
    "title" => "Title",
    "created_at" => date("Y-m-d H:i:s"),
    "table1.title" => "Title 1",
    "table2.title" => "Title 2",
    "table3.title" => "Title 3"
]);

// selects records from multiple tables
// the following code selects a record from table0 and, eventually, from table1, table2 and table3
echo "\n### Selects records from multiple tables\n";
list($title, $createdAt, $t1Title, $t2Title, $t3Title) = $t->select(
    ["title", "created_at", "table1.title", "table2.title", "table3.title"],
    $id
);
echo "id: $id, title: $title, created_at: $createdAt, ";
echo "table1.title: $t1Title, table2.title: $t2Title, table3.title: $t3Title\n";

// deletes the previous record
echo "\n### Deletes the previous record\n";
$t->delete($id);
