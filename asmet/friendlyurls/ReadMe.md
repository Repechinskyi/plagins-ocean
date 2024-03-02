### Описание:

Модель Alias для хранения человекопонятного URL(ЧПУ)


##### Требования:
    behat/transliterator

### Установка:

`*` - для laravel версии "5.4.*" и ниже.

1. Добавить пакет в свой проект через консоль командой:
```text
    composer require asmet/friendlyURLs
```

2. `*` Добавить в `config/app.php`:
```php
    'providers' => [ Asmet\FriendlyURLs\FriendlyURLsServiceProvider::class ]
```

3. Публикуем config файл и views:
```text
   	php artisan vendor:publish --provider="Asmet\FriendlyURLs\FriendlyURLsServiceProvider"
```

4. Произвести миграцию:
```text
    php artisan migrate
```

5. Модель которой требуется alias должна использовать trait `Asmet\FriendlyURLs\Traits\AliasTrait`. 


### Использовать:

Таблица aliases поддерживаетт морфную связь.

Для формирования полного url пути использовать метод createUrl(),
 который принимает объект модели:
   
   
Создайте в своем app/Http/Controllers контроллер AliasController по примеру ниже.


### Пример:
```php
    class AliasController extends Controller
    {

        // возвращает view в зависимости от запроса $all
        function getSomePage($all)
        {
            // обрабатывает маршруты для отбражения
            if ($alias = Alias::where('url', $all)->first()):
                $type = $alias->aliasable_type;
                $model = $alias->aliasable;

                switch ($type):
                    case 'App\Post':
                        return PostController::show($model);
                    case 'App\Category':
                        return CategoryController::show($model);
                    default:
                        return abort(500);
                endswitch;

            else:
                return abort(404);
            endif;
        }


        /**
         * обновление путей 
         * Route::post('update-paths', ...);
         *
         * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
         */
        function updatePaths()
        {
            $aliases = Alias::all();

            foreach ($aliases as $alias):
                $type = $alias->aliasable_type;
                $model = $alias->alaisable;

                if ($type == 'App\Post'):
                    $alias->url = $alias->createUrl('blog', $model);
                    $alias->update();
                elseif ($type == 'App\Category'):
                    $alias->url = $alias->createUrl('category', $model);
                    $alias->update();
                endif;
            endforeach;

            return redirect('/');
        }
    }
```