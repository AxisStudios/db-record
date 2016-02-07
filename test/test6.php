<?php
/**
 * This PHP script illustrates how to use DbRecordActive.
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
use soloproyectos\db\record\DbRecordActive;

// creates a new connector instance and prints each SQL statement (debugging)
$db = new DbConnector("test", "test", "test");
$db->addDebugListener(function ($sql) {
    echo "--$sql\n";
});

/**
 * Single table access.
 * 
 * The following code affects only one table.
 */

// inserts a record
$a = new DbRecordActive($db, "table0");
$a->title = "Title";
$a->created_at = date("Y-m-d H:i:s");
$a->save();

// selects the previous record
$b = new DbRecordActive($db, "table0", $a->id);
echo "id: {$b->id}, title: {$b->title}, created at: {$b->created_at}\n";

// updates the previous record
$c = new DbRecordActive($db, "table0", $b->id);
$c->title = "Title {$b->id}";
$c->save();

// deletes the previous record
$c->delete();

/**
 * Multiple tables access.
 * 
 * The following code affects multiple tables.
 */

// inserts a record into table0, table1, table2 and table3
$a = new DbRecordActive($db, "table0");
$a->title = "Title";
$a->created_at = date("Y-m-d H:i:s");
$a->{"table1.title"} = "Title 1";
$a->{"table2.title"} = "Title 2";
$a->{"table3.title"} = "Title 3";
$a->save();

// selects the previous record
$b = new DbRecordActive($db, "table0", $a->id);
echo "id: {$b->id}, " .
    "title: {$b->title}, " .
    "table1.title: {$b->{"table1.title"}}, " .
    "table2.title: {$b->{"table2.title"}}, " .
    "table3.title: {$b->{"table3.title"}}";

// updates the previous record
$c = new DbRecordActive($db, "table0", $b->id);
$c->title = "Title " . $b->id;
$c->{"table1.title"} = "Title " . $b->{"table1.id"};
$c->{"table2.title"} = "Title " . $b->{"table2.id"};
$c->{"table3.title"} = "Title " . $b->{"table3.id"};
$c->save();

// deletes the previous record and all linked records
$c->deleteAll();
