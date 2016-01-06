# DbRecord

## Introduction

`DbRecord` is a library that allows us to operate databases without manually execute SQL statements. Unlike other similar libraries, **DbRecord allows us to operate on multiple tables at the same time**, resulting in a more concise and clear code.

## Installation

This package is provided via [composer](https://getcomposer.org/) package manager. Just create a `composer.json` file and execute the following command in the same directory:

```bash
composer install
```

See [Basic Usage](https://getcomposer.org/doc/01-basic-usage.md) for more info.

## Basic examples: save() and fetch()

We use the `save()` and `fetch()` methods to save or retrieve records, respectively. The `save()` method can insert or update a record, depending on whether the record exists or not.

**Inserting records**

To insert records we omit the `id` parameter from the constructor. For example:
```php
// creates a new record (INSERT)
$r = new DbRecord($db, "table0");
$r->save(["title" => "New title", "created_at" => date("Y-m-d H:i:s")]);
```

**Updating records**

To update records, we inidicate the `id` parameter in the constructor. For example:
```php
// updates a new record (UPDATE)
$r = new DbRecord($db, "table0", 1);
$r->save(["title" => "New title", "created_at" => date("Y-m-d H:i:s")]);
```

**Selecting records**

To select records, we inidicate the `id` parameter in the constructor. For example:
```php
// selects a record an fetches column values (SELECT)
$r = new DbRecord($db, "table0", 1);
list($title, $createdAt) = $r->fetch(["title", "created_at" => date("Y-m-d H:i:s")]);
echo "title: $title, Created at: $createdAt";
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

Instead of operating on tables individually, we can do it at the same time. The following example selects a record (ID = 1) and updates or inserts records on `table1`, `table2`, `table3`:
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

The following example selects a record (ID = 1) and retrieves column values from `table0`, `table1`, `table2` and `table3` at the same time:
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

En el ejemplo anterior accedíamos a la columna `title` de `table1` mediante la siguiente expresión: `table1.title`. El formato general sería el siguiente:
```text
table1[id = table0_id].title
```

pero al tratarse de un caso tan común, podemos omitir `id` y `table0_id`, de manera que la expresión anterior queda simplificada en:
```
table1.title
```

Pongamos un ejemplo más complicado. Supongamos que `table2` depende de `table1` que a su vez depende de `table0`. Eso es:

![test1](https://cloud.githubusercontent.com/assets/5312427/12151271/924a197e-b4ae-11e5-9ea8-a69b36489e54.png)

En ese caso, para acceder a las columnas de `table1` y `table2` usaríamos el siguiente código:
```php
$r = DbRecord($db, "table0", 1);
list($title, $t1Title, $t2Title) = $r->fetch([
  "title",
  "table1.title",
  "table2[table1.table2_id].title"
]);
```

En el ejemplo anterior `table1.title` y `table2[table1.table2_id].title` son abreviaturas de las siguientes expresiones:
```text
table1[id = table1_id].title
table2[id = table1.table2_id].title
```

Podemos omitir `id` y `<table>_id`, ya que se toman por defecto.

Para ejemplos completos, vea [test3.php](test/test3.php), [test4.php](test/test4.php) y [test5.php](test/test5.php)
