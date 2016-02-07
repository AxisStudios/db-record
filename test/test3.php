<?php
/**
 * This PHP script illustrates the 'column path' concept.
 * 
 * A 'column path' is a way to access columns from linked tables (left joined tables).
 * 
 * For more complex examples see test4.php.
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
echo "### Inserts a record\n";
$id = $t->insert([
    "title" => "Title",
    "created_at" => date("Y-m-d H:i:s"),
    "table1.title" => "Title 1"
]);

// And now prints a 'left joined' or 'linked' table column
// table1 is the 'left joined' or 'linked' table
// id is a column of table1 (not necessarily the primary key)
// table1_id is a column of table0
// title is a column of table1 (the column to print)
echo "\n### General example: table1[id = table1_id].title\n";
list($table1Title) = $t->select(["table1[id = table1_id].title"], $id);
echo "table1.title: $table1Title\n";

// AS THE PREVIOUS EXAMPLE IS VERY COMMON, it can be written as follows:
// note that 'id' and 'table1_id' have been omitted
echo "\n### Shorthand example: table1.title\n";
list($table1Title) = $t->select(["table1.title"], $id);
echo "table1.title: $table1Title\n";
