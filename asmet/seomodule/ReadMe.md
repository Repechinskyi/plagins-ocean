### Описание:

Модель SEO для хранения SEO данных, таких как title, description.

### Установить:

`*` - для laravel версии "5.4.*" и ниже.

1. Добавить пакет в свой проект через консоль командой:
```text
    composer require asmet/seomodule
```

2. `*` Добавить в `config/app.php`:
```php
    'providers' => [ Asmet\SeoModule\SeoServiceProvider::class ]
```

3. Публикуем config файл и views:
```text
   	php artisan vendor:publish --provider="Asmet\SeoModule\SeoServiceProvider"
```

4. Произвести миграцию:
```text
    php artisan migrate
```


### Использовать:

1. Модель, которая должна иметь meta данные, должна реализовать  метод `seo()`,
    который должен возвращать:
```php
    return $this->morphMany('Asmet\SeoModule\Models\Seo', 'seoable');
```

2. Функция `get_seo()` принимает может принимать в качестве параметра модель
    и возвращает массив с ключами 'title', 'description' и их значениями.
    Либо может быть вызвана без параметров и в таком случае выведет тот же массив,
    но с устаномленными значениями по умолчанию в файле `config/seo.php`

### Пример:
```php

        $post = Post::find($id);

         $vars = [
            'post' => $post,
            'seo'  => get_seo($post)
         ];

         return view('blog.show', $vars);
```

```blade

    {{--и в layouts шаблонизатора Blade--}}
        <!DOCTYPE html>
        <html lang="{{ app()->getLocale() }}">
        <head>
          <title>{{ $seo['title'] or env('APP_NAME')}}</title>
          @if(isset($seo['description']))
            <meta name="description" content="{{ $seo['description'] }}">
          @endif
```