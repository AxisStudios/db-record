# DbRecord

## Introduction

`DbRecord` is a library that allows us to operate databases without manually execute SQL statements. Unlike other similar libraries, **DbRecord allows us to operate on multiple tables at the same time**, resulting in a more concise and clear code.

## Installation

This package is provided via [composer](https://getcomposer.org/) package manager. Just create a `composer.json` file and execute the following command in the same directory:

```bash
composer install
```

See [Basic Usage](https://getcomposer.org/doc/01-basic-usage.md) for more info.

## Database requirements

This library can operate any MySQL database, with the only condition that each table **MUST** have a primary key composed by a single auto-increment column. By default, the primary key is called `ID`, but you can change it in the constructor.

## Basic examples: save(), fetch() and delete()

We use the `save()`, `fetch()` and `delete()` methods to save, retrieve and delete records respectively. The `save()` method can insert or update a record, depending on whether the `id` parameter is passed to the constructor.

**Inserting records**

To insert records, we ommit the `id` parameter from the constructor:
```php
// insert a record, as the 'id' parameter is not present
$r = new DbRecord($db, "table0");
$r->save(["title" => "New title", "created_at" => date("Y-m-d H:i:s")]);
```

**Updating records**

Updates the record ID=1:
```php
$r = new DbRecord($db, "table0", 1);
$r->save(["title" => "New title", "created_at" => date("Y-m-d H:i:s")]);
```

**Selecting records**

Selects the record ID=1:
```php
$r = new DbRecord($db, "table0", 1);
list($title, $createdAt) = $r->fetch(["title", "created_at" => date("Y-m-d H:i:s")]);
echo "title: $title, Created at: $createdAt";
```

**Deleting records**

Delete the record ID=1:
```php
$r = new DbRecord($db, "table0", 1);
$r->delete();
```

In case we're using a single column, the `save()` and `fetch()` methods can be simplified as follows:
```php
// no array needed
$r->save("column", "value");
$value = $r->fetch("column");
```

For a more complex example see [test1.php](test/test1.php).

## General example: Accessing several tables at the same time

Let's say that we have a main table (`table0`) and three secondary tables (`table1`, `table2` and `table3`). The three tables are 'left joined' to the main table through the columns `table1_id`, `table2_id` and `table3_id`. That is:
![test](https://cloud.githubusercontent.com/assets/5312427/12149778/ec2fa156-b4a5-11e5-8697-f423856bb3cd.png)

Instead of operating on tables individually, we can do it at the same time. The following example selects a record (ID = 1) and updates or inserts records on `table1`, `table2` and `table3`:
```php
$r = new DbRecord($db, "table0", 1);
$r->save([
  "title" => "My title",
  "created_at" => date("Y-m-d H:i:s"),
  "table1.title" => "Title 1",
  "table2.title" => "Title 1",
  "table3.title" => "Title 1"
]);
```

The following example selects a record (ID = 1) and retrieves columns from `table0`, `table1`, `table2` and `table3` at the same time:
```php
$r = new DbRecord($db, "table0", 1);
list($title, $createdAt, $t1Title, $t2Title, $t3Title) = $r->fetch([
  "title",
  "created_at",
  "table1.title",
  "table2.title",
  "table3.title"
]);
echo "title: $title, created_at: $createdAt, table1.title: $t1Title, table2.title, $t2Title, table3.title, $t3Title";
```

For a more complex example see [test2.php](test/test2.php).

## Column path expressions

In the previous examples we accessed the `title` column of `table1` through the following expression: `table1.title`. The general format is as follows:
```text
table1[id = table0_id].title
```

We can omit `id` and `table0_id`, as they are taken by default. So the previous expression can by simplified as follows:
```
table1.title
```

Let's imagine a more complex example. Let's say that `table2` depends on `table1` which, at the same time, depends on `table0`. That is:

![test1](https://cloud.githubusercontent.com/assets/5312427/12151271/924a197e-b4ae-11e5-9ea8-a69b36489e54.png)

In that case, we use the following code to access the `table1` and `table2` columns:
```php
$r = DbRecord($db, "table0", 1);
list($title, $t1Title, $t2Title) = $r->fetch([
  "title",
  "table1.title",
  "table2[table1.table2_id].title"
]);
```

The previous expressios are simplified versions of the general expressions:
```text
table1[id = table1_id].title
table2[id = table1.table2_id].title
```

We can omit `id` and `<table>_id`, as they are taken by default.

For more complex examples see [test3.php](test/test3.php), [test4.php](test/test4.php) y [test5.php](test/test5.php)
