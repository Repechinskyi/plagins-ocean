### Описание:

Пакет дает возможность построить так называемые хлебные крошки, предварительно настроив все в
 `config/breadcrumbs.php` в котором можно задать разделитель и включить или отключить использование
 микроразметки.
 

### Установка:
`*` - для laravel версии "5.4.*" и ниже.

1. Добавить пакет в свой проект через консоль командой:
```text
    composer require asmet/breadcrumbs

2. `*` Добавить в `config/app.php`:
```php
    'providers' => [ Asmet\Breadcrumbs\BreadcrumbsServiceProvider::class ]
```

3. Публикуем config файл и views:
```text
   	php artisan vendor:publish --provider="Asmet\Breadcrumbs\BreadcrumbsServiceProvider"
```

### Использовать:

1. Настроить конфиг файл по своим требованиям (как настроить описано в самом когфиг файле).

2. Вызвать функцию get_breadcrumbs() и передать в нее параметры:
        - строку, название страницы которое определено в `config/breadcrumbs.php`
        - объект, в случае если страница динамическая


### Пример:
```blade
    <div class="container">
        <div class="row">{!! get_breadcrumbs('post', $post) !!}</div>
     </div>
```