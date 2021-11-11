# kurcenter/dbi - MySQLi Wrapper 

## Install

### Composer

```
composer require kurcenter/dbi
```

Then include or require the file in your php page.

```php
require 'vendor/autoload.php';
```

## Connection

```php
$mysqli = new mysqli('localhost', 'user', 'password', 'db');
$mysqli->set_charset('UTF-8');

$db = new \Kurcenter\Dbi\Db($mysqli);
```

## Query

### Select

```php
$db->exec("SELECT * FROM `demo` WHERE id = ?", [1])->row(); // return array
$db->exec("SELECT * FROM `demo`")->rows(); // return array

$db->exec("SELECT * FROM `demo` WHERE id = ?", [1])->one(); // return object
$db->exec("SELECT * FROM `demo`")->all(); // return object array

$db->exec("SELECT * FROM `demo`")->count(); // return count rows

$db->exec("SELECT * FROM `demo`")->yield(); // return yield
```

### Insert

```php
$db->insert('demo', ['name' => 'Joe', 'value' => 7]); // return bool
// INSERT INTO `demo` (`name`, 'value') VALUE('Joe', 7)
```

### Update

```php
$db->update('demo', ['name' => 'Joe', 'value' => 7], ['id' => 1]); // return bool
// UPDATE SET `name` = 'Joe', 'value' => 7 WHERE id = 1;
```

### Delete

```php
$db->delete('demo', ['id' => 1]); // return bool
// DELETE FROM demo WHERE id = 1;
```

## Helpers

### Generate UUID

```php
$db->uuid();
```

### LastId

Returns the auto generated id used in the latest query

```php
$db->geLastId();
```

### Escape

```php
$db->escape();
```
