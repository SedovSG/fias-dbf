# Dbf - обработка DBF-файлов от ФИАС РФ
[![Packagist](https://img.shields.io/packagist/v/SedovSG/fias-dbf.svg)](https://packagist.org/packages/sedovsg/fias-dbf)
[![Latest Stable Version](https://poser.pugx.org/sedovsg/fias-dbf/v/stable)](https://packagist.org/packages/sedovsg/fias-dbf)
[![License](https://poser.pugx.org/sedovsg/fias-dbf/license)](LICENSE)
[![Build Status](https://travis-ci.org/SedovSG/fias-dbf.svg?branch=master)](https://travis-ci.org/SedovSG/fias-dbf)
[![Codecov](https://codecov.io/gh/SedovSG/dbf-dbf/branch/master/graph/badge.svg)](https://codecov.io/gh/SedovSG/dbf-dbf)
[![Total Downloads](https://poser.pugx.org/sedovsg/fias-dbf/downloads)](https://packagist.org/packages/sedovsg/fias-dbf)

Библиотека для работы с базой данных DBF, получения и обновления данных в формате DBF от Федеральной информационной адресной системы Российской Федерации, которая проста в использовании.

Государственный адресный реестр – это государственный базовый информационный ресурс, содержащий сведения об адресах и реквизитах документов о присвоении, об изменении, аннулировании адреса, путём чтения и разбора файлов DBF

Источник данных: [https://fias.nalog.ru/Updates.aspx](https://fias.nalog.ru/Updates.aspx)

## Требования
- php-dbase >= 7.0 ([PECL](https://pecl.php.net/package/dbase))
- php-rar >= 7.1 ([PECL](https://pecl.php.net/package/rar))

#### Установка пакета PECL
```bash
$ sudo pecl install package_name;
$ echo "extension=/usr/lib/php/20170718/package_name.so" | sudo tee /etc/php/7.2/mods-available/package.ini;
$ sudo ln -s /etc/php/7.2/mods-available/package_name.ini /etc/php/7.2/cli/conf.d/;
$ sudo ln -s /etc/php/7.2/mods-available/package_name.ini /etc/php/7.2/apache2/conf.d/
```

## Установка
Установка через Composer:

```bash
$ composer require sedovsg/fias-dbf
```

> Как установить сам [![Сomposer](https://getcomposer.org/download/)](https://getcomposer.org/download/)

## Использование

Структура и описание DBF-файлов:
[Руководство пользователя ФИАС РФ](https://github.com/SedovSG/Fias-dbf/blob/master/docs/Manual-FIAS.doc)

### Подключение к источнику данных
```php
use Fias\Dbf;

$dbf = (new Dbf('dir_name'));
```

### Получение всех элементов

```php
$result = $dbf->
  select()->
  from('STRSTAT.DBF')->
  exect()->
  fetchAll();
```

### Получение бщего количества элементов в источнике

```php
  $result = $dbf->
      select()->
      from('STRSTAT.DBF')->
      exect()->
      rowCount();
```

### Получение информации о свойствах полей источника

```php
  $result = $dbf->
      select()->
      from('FLATTYPE.DBF')->
      exect()->
      getFieldsInfo();
```

### Получение количества полей источника

```php
  $result = $dbf->
      select()->
      from('ACTSTAT.DBF')->
      exect()->
      numFields();
```

### Получение полей источника

```php
  $result = $dbf->
      select()->
      from('FLATTYPE.DBF')->
      exect()->
      getFields();
```

### Фильтрация данных

**Выборка данных по условию "Равно"**

```php
  $result = $dbf->
      select()->
      from('STRSTAT.DBF')->
      equal('STRSTATID = 2, NAME = Литер')->
      exect()->
      fetch();
```

**Выборка данных по условию "Исключено"**

```php
  $result = $dbf->
      select()->
      from('STRSTAT.DBF')->
      exclude('STRSTATID = 1')->
      exect()->
      fetch();
```

**Выборка данных по условию "Включает"**

```php
  $result = $dbf->
      select()->
      from('ESTSTAT.DBF')->
      include('NAME = Гараж')->
      exect()->
      fetch();
```

Методы установки полей ``` select() ``` и фильтрации данных ``` equal(), exclude(), include() ``` можно использовать несколько раз, через цепочку вызовов, например:

```php
  $result = $dbf->
      select('STRSTATID, NAME')->
      select('SHORTNAME')->
      from('STRSTAT.DBF')->
      include('STRSTATID = 2')->
      exclude('STRSTATID = 1')->
      exect()->
      fetch();
```
Кроме того, можно указать данные какого фильтра будут включены в итоговую выборку:

```php
  $result = $dbf->
      select('STRSTATID, NAME')->
      select('SHORTNAME')->
      from('STRSTAT.DBF')->
      include('STRSTATID = 2')->
      exclude('STRSTATID = 1')->
      exect()->
      fetch(Dbf::FETCH_INCLUDE);
```

### Загрузка архива DBF с сайта ФИАС РФ
```php
$dbf->download();
```

### Обновление DBF-файлов в директории
```php
$dbf->update();
```

### Закрытие соединения с источником
```php
$dbf->сlose();
```

## Журнал Изменений
Пожалуйста, смотрите [список изменений](https://github.com/SedovSG/Fias-dbf/blob/master/CHANGELOG.md) для получения дополнительной информации о том, что изменилось в последнее время.

## Тестирование
```bash
$ vendor/bin/phpunit
```

## Лицензия
Лицензия BSD 3-Clause. Пожалуйста, см. [файл лицензии](LICENSE) для получения дополнительной информации.
