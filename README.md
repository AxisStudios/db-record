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

Usaremos los métodos `fetch()` y `save()` para recuperar o insertar/actualizar registros respectivamente. El método `save()` puede actualizar un registro o insertarlo, dependiendo si existe o no.

### Inserting records

Para insertar registros, omitimos el parámetro `id` en el constructor. Por ejemplo:
```php
// creates a new record (INSERT)
$r = new DbRecord($db, "table0");
$r->save(["title" => "New title", "created_at" => date("Y-m-d H:i:s")]);
```

### Updating records

Para actualizar un registro, indicaremos el `id` en el constructor. Por ejemplo:
```php
// updates a new record (UPDATE)
$r = new DbRecord($db, "table0", 1);
$r->save(["title" => "New title", "created_at" => date("Y-m-d H:i:s")]);
```

## Selecting records

Para seleccionar un registro, indicaremos el `id` en el constructor. Por ejemplo:
```php
// selects a record an fetches column values (SELECT)
$r = new DbRecord($db, "table0", 1);
list($title, $createdAt) = $r->fetch(["title", "created_at" => date("Y-m-d H:i:s")]);
echo "title: $title, Created at: $createdAt";
```

En el caso de que estemos usando una única columnas, los métodos `save()` y `fetch()` se pueden simplificar como sigue:

```php
// no array needed
$r->save("column", "value");
$value = $r->fetch("column");
```

Para un ejemplo completo, vea [test1.php](test/test1.php).

## General example: Accessing several tables at the same time

Supongamos que tenemos una tabla principal (table0) de la que penden tres tablas secundarias (table1, table2 y table3) a través de los campos table1_id, table2_id y table3_id. Esto es:  
![test](https://cloud.githubusercontent.com/assets/5312427/12149778/ec2fa156-b4a5-11e5-8697-f423856bb3cd.png)

En lugar de operar individualmente sobre las tablas table0, table1 y table3, podemos hacerlo de una vez mediante el siguiente código:

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

El ejemplo anterior selecciona un registro de table0 (record ID = 1) y actualiza o inserta los correspondientes registros en table1, table2 y table3.

Si queremos recuperar un registro, podemos hacerlo de la siguiente forma:

```php
$r = new DbRecord($db, "table0", 1);
list($title, $createdOn, $t1Title, $t2Title, $t3Title) = $r->fetch([
  "title",
  "created_at",
  "table1.title",
  "table2.title",
  "table3.title"
]);
```

El ejemplo anterior recupera columnas de las tablas table0, table1, table2 y table3 y las almacena en las variables $title, $createdAt, $t1Title, $t2Title y $t3Title.

Para un ejemplo completo, vea [test2.php](test/test2.php).

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
