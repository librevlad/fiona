# Fiona

Библиотека для парсинга ФИО на PHP.

## Установка

 Подключить через composer:

```bash
composer require librevlad/fiona
```

## Использование

В активной разработке.

```php

$fiona = new \Librevlad\Fiona\Detector();
$data = $fiona->detect('Иванов Иван Иванович');

```

```

array:5 [
  "first_name" => Иван
  "last_name" => Иванов
  "patronymic" => Иванович
  "gender" => male
  "unmatched_segments" => []
]
```

## Тестирование

```bash
composer test
```

## Лицензия

The MIT License (MIT).
