### Описание:

Текстовый редактор для блога 

### Установка:
`*` - для laravel версии "5.4.*" и выше.

1. Добавить пакет в свой проект через консоль командой:
```SH
    composer require asmet/ckeditor
``` 


```bash
php artisan vendor:publish --provider="Asmet\Ckeditor\CkeditorServiceProvider" --force
```

3. Для нормальной работы code preview добавить в `webpack.mix.js` :
```javascript
    mix.copy('resources/assets/vendor/ckeditor/ckeditor', 'public/js');
```

4. В шапку страницы

```blade
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    ...
    <script src="{{ asset('js/ckeditor.js') }}"></script>

</head>
<body></body>
</html>
```

### Пример:

```blade
    <ckeditor model-name="content" v-model="content"></ckeditor>
```



```javascript
import Ckeditor from '../vendor/ckeditor/components/Ckeditor';


 const vm = new Vue({
    el: '#semeForm',
    data: {
        content: ''
    },
    components: {
      Ckeditor,
    }
  });
```

 В image.php ('название стиля и его настройки на усмотрение') 
 ХХ название стиля должно совпадать с указаннымм стилем в ckeditor.php файле ХХ
 
```php
        'ckeditor' => [
            'prew_b' => [
                'width' => '900',
                'quality' => 88,
            ],
        ],
```

сохранять  в бд

```php
$item->text = editor_save($new_content, $old_content);
```
принимает сначала новый контент, потом старый (не обязательно), в функции чистятся невалидные теги, картинки и тд

далять из бд

```php
    editor_delete($ckeditor_content);
```

функция `image_for_editor($ckeditor_content)` возвращает массив с src изображений которые есть в контенте

чтобы картинки корректно открывались в попап окнах, там где должны присутствовать картинки в скрипты добавляем:

```angular2html
require('magnific-popup');

if (document.getElementsByClassName('image-text-link').length > 0) {
    $('.image-text-link').magnificPopup({type: 'image'});
}
```

