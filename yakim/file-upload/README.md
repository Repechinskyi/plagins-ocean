# FileUpload v3

## Содержание

- [Установка](#markdown-header-installation)
    - [Nodejs зависимости](#markdown-header-nodejs-dependencies)
    - [Form.js](#markdown-header-formjs)
- [Обновление со 2 версии](#markdown-header-upgrade-from-2)
- [Отличия версии 2 от версии 3](#markdown-header-difference-between-2-and-3)
- [Использование](#markdown-header-using)
    - [Валидация файлов](#markdown-header-files-validation)
    - [Сохранение файлов](#markdown-header-saving-files)
- [Вспомогательные функции](#markdown-header-helpers)
- [Права доступа](#markdown-header-gates)
- [Пример настройки конфига image.php](#markdown-header-config)
- [Обновление стилей картинок](#markdown-header-updating-styles)


## Установка
###### installation

1. Установить пакет `composer require yakim/fileUpload`
3. Добавить в config/app.php в секцию providers
`Yakim\FileUpload\FileUploadServiceProvider::class,`
`Intervention\Image\ImageServiceProvider::class,`
`Approached\LaravelImageOptimizer\ServiceProvider::class,`
4. Добавить в config/app.php в секцию aliases
`'Image' => Intervention\Image\Facades\Image::class,`
5. Публикуем файлы
`php artisan vendor:publish --provider="Yakim\FileUpload\FileUploadServiceProvider"`
6. Произвести миграции
`php artisan migrate`
7. Сделать линк на storage папку
`php artisan storage:link`
8. Поместить в папку storage/app/public файл watermark.png, image-not-available.jpg
9. Подключить файлы локализации пакета в конфиге localization-js.php
10. Установить nodejs зависимости
11. Установить или обновить Form.js

## Nodejs зависимости
###### nodejs dependencies
1. Vuejs 2
2. vuedraggable": "^2.15.0" https://www.npmjs.com/package/vuedraggable

## Form.js
###### formjs
Для интеграции vuejs компонента с формами нужно установить (или обновить) Form.js и Errors.js из репозитория https://bitbucket.org/VadenPro/form.js  
Просто положите эти файлы в resources/js/core/ 

## Обновление со 2 версии
###### Upgrade from 2
1. Опубликовать файлы `php artisan vendor:publish --provider="Yakim\FileUpload\FileUploadServiceProvider"`
2. Опубликовать с заменой 
    * `php artisan vendor:publish --provider="Yakim\FileUpload\FileUploadServiceProvider" --tag=assets --force`
    * `php artisan vendor:publish --provider="Yakim\FileUpload\FileUploadServiceProvider" --tag=lang --force`
3. Скопировать вручную содержимое конфига uploadform.php в проект.
4. Произвести миграции
5. Обновить Form.js и Errors. js https://bitbucket.org/VadenPro/form.js  
6. Прочитать отличия от 2 версии и внести исправления в проект

## Отличия версии 2 от версии 3
###### difference between 2 and 3
1. Удален класс FileUploadForm. Вместо него, как и для других форм, используется Form.
2. Метод модели FileUpload, getStylepath, переименован в getStyleUrl и теперь требует только один параметр. Старый метод оставлен как болванка для того чтобы не ломать обратную совместимость. В новых релизах getStylepath будет удален. Если ваш пакет использует метод getStylepath - замените его на getStyleUrl.
3. Удалена функция validateImages. Теперь валидация производится стандартными средствами Laravel
4. Удален blade шаблон, вместо него теперь vuejs компонент.
5. Удалена функция file_upload_settings
6. При сохранении файлов определяется их mime type и записывается в БД.
7. Добавлена возможность ручной сортировки файлов
8. Вспомогательные функции saveFile, deleteFile, renameFile переименовы в соответствии с правилами именования: save_file, delete_file, rename_file. Старое именование временно оставлено для обратной совместимости. Если ваш пакет использует старые методы - обновите его. В новых релизах они будут удалены.

## Использование
###### using
1. Пакет имеет трейт Yakim\FileUpload\Traits\FileUploadable. Его нужно подключить к моделям, которые будут иметь файлы.
После подключения трейта модели будут иметь отношение `files`.  
Методы:  
saveFile - используется редко, вместо него использовать saveFiles.  
saveFiles - основной метод, с помощью которого и происходит обработка формы.  
getFilesThumbs - собирает информацию о файлах в массив, используется для отображения превью в форме.  
maxFiles - возвращает количество файлов которые еще можно загрузить в модель.  

2. Подключить стили в sass файле:
`@import "../vendor/FileUpload/sass/file-upload";`
3. Использовать Form.js для формы как обычно, с некоторыми нюансами:
```javascript
new Vue({
    el: '#my-form',
    data: {
        form: new Form({
            fileUpload: {
                pictures: {},
            },
            // ...
            // Другие поля
            // ...
        }),
        thumbs: thumbs,
    },
    components: {
        ElFileUpload : require('../vendor/FileUpload/js/components/fileUpload.vue')
    },
    methods: {
        onSubmit: function () {
            this.form.submit('post', this.$el.action)
                .then(() => {
                    // ...
                });
        },
    }
});
```
Поля с файлами обязательно пишем в form.fileUpload. Это нужно для того чтобы в Form.js было легко понять какого типа форма обрабатывается.  
Названия полей в form.fileUpload не должны совпадать с названиями в form, потому что при отправке данных, изображения будут перекинуты в form.  
На страницу нужно прокинуть переменную с превью сохраненных файлов.  
Сами превью можно сформировать с помощью метода getFilesThumbs, который доступен в трейте FileUploadable.


4. На страницу, в шаблон, кладем vuejs компонент, который будет элементом загрузки файлов
```vue
<el-file-upload name="pictures"
               quantity="{{ files_quantity('product') }}"
               :thumbs="thumbs"
               :loading="form.loading"
               :errors="form.errors"
               delete-url="{{ route('fileUpload.delete') }}"
               v-model="form.fileUpload.pictures">
</el-file-upload>
<span class="help is-danger text-danger" v-if="form.errors.has('pictures')" v-text="form.errors.get('pictures')"></span>
```
name: имя поля - пишем то же самое имя, которое задали в form.fileUpload  
quantity: максимальное количество файлов для этого поля  
thumbs: массив с превью сохраненных изображений (не обязательно передавать из js - этот параметр не реактивен, можно передать данные тут же, в шаблоне)  
loading: флаг, который показывает когда форма загружает файлы. Оставлять form.loading  
errors: передаем объект ошибок в компонент. Оставлять form.errors  
delete-url: юрл, на который будет отправлться запрос на удаление файлов. На этот адрес будет приходить post запрос с параметром fileId.  
v-model: тут должно быть понятно  

## Валидация файлов
###### files validation
Стандартная Laravel валидация.  
Пример:
```php
 $this->validate(request(), [
            'pictures' => "array|max:{$product->maxFiles($product->maxFiles(files_quantity('product')))}",
            'pictures.*' => 'image'
        ]);
```
maxFiles - число, передаваемое параметром обозначает максимальное количество файлов для загрузки  
Метод посчитает количество уже прикрепленных к модели файлов и вернет доступное для загрузки количество  


## Сохранение файлов
###### saving files
```php
$product->saveFiles($request->all(), 'pictures', 'product-preview');
```
Первым параметром передаем массив данных из формы  
Вторым параметром, в массиве перечисляем поля с файлами. Например, если вам нужно обработать несколько полей с файлами:
```php
$product->saveFiles($request->all(), ['profile_photo', 'icon', 'favicon'], 'product-preview');
```
Если поле с файлами одно - его название можно передать строкой, а не массивом.  
Третий параметр - тип сущности из настроек.  
Четвертый параметр - системное имя с помощью которго можно отличать картинки в рамках одного типа (превью, слайдер и т.д.)
Метод saveFiles возвращает массив с информацией о сохраненных, переименованных и пересортированных файлах.  
После сохранения обязательно возвращаем в форму массив с превью, как при построении страницы.
```php
if ($request->ajax()) {
    return response()->json([
        'thumbs' => [
            'pictures' => $product->getFilesThumbs()
            // И так для всех полей с файлами
        ]
    ]);
}
```

## Вспомогательные функции:
###### helpers

```php
save_file($file, $file_name, $fileable_id, $fileable_namespace, $settings_entity_type = 'default', $disk = 'public')

delete_file($file_id)

rename_file($file_id, $new_name)
```
* $file - объект файла из запроса
* $file_name - название, которое необходимо задать файлу
* $fileable_id - к чему крепим файл ид
* $fileable_namespace - к чему крепим файл класс
* $settings_entity_type - какие из настроек в image.php использовать для работы с файлами из данного сохраниения
* $disk - диск, куда все будет сохраняться, пока юзаем дефолтный

files_quantity: возвращает количество файлов, заданных в конфиге "uploadform", которые можно загрузить. Принимает необязательный параметр $name - название ключа настройки в uploadform.quantity 


## Права доступа (гейты)
###### gates

Прописываем в методах где вызываем saveFiles, в самом пакете гейты куда-либо добавлять или создавать не нужно.

## Пример настройки конфига image.php
###### config

исходно в конфиге идут настройки для дефолтного сохранения файлов, мы можем создавать разные конфигурации для сохраниения изображений
с различными стилями, настроенными под отдельные сущности по-разному.

Обратите внимание - чтобы оригинальное изображение сохранялось для него необходимо задать соответствующий стиль, сами по себе
изображения не будут сохраняться файлами.

Разберем настройки слитей подробнее:
Обязательных настроек в стиле нет, чтобы просто сохранить оригинальное изображение в стиль достаточно создать следующий стиль:

```php
'original' => [],
```
В настройки стиля можно передать следующие поднастройки для конфигурации стиля:
```php
'width' => 100,
```
Максимальная ширина, в px

```php
'height' => 100,
```
Максимальная высота, в px

```php
'stretch' => true,
```
Если задана высота и ширина изображения, то по одной из этих осей картинка может стать меньше (картинка 60*40 с ограничителями
40*40 превратится в 40*27). Чтобы она подрезалась до размера 40*40 без ушей - ставим данную настройку. Внимание! чтобы стиль с
обрезкой применился необходимо задать и высоту и ширину!!!

```php
'watermark' => 'watermark.png',
```
Путь к файлу ватермарка от корня нашего хранилища (задавать, чтобы наложить на изображения ватермарки)

```php
'watermark_width' => 100,
```
ширина ватермарка, который будет накладываться на изображение

по-умолчанию будет накладываться оригинальный размер ватермарка

```php
'watermark_position' => 'bottom-right',
```

Место расположения ватермарка, возможные места:  
top-left  
top  
top-right  
left  
center  
right  
bottom-left  
bottom  
bottom-right (default)  


```php
'quality' => 90,
```
снизит качество картинки, указываем % качества изображения

по-умолчанию будет идти 100% качество

Пример настроенного конфига:
```php
return [
    'no_image_storage_path' => 'image-not-available.jpg',
    'original_style' => [
        'width' => '1200',
        'quality' => 90,
    ],
    'entities_image_styles' => [
        'product-preview' => [
            'thumb_s' => [
                'width' => '40',
                'height' => '40',
                'stretch' => true,
            ],
            'thumb_b' => [
                'width' => '120',
                'height' => '120',
                'stretch' => true,
            ],
            'prew_s' => [
                'width' => '350',
                'height' => '500',
                'watermark' => 'watermark.png',
                'watermark_width' => 80,
                'quality' => 90,
            ],
            'prew_b' => [
                'width' => '500',
                'height' => '800',
                'watermark' => 'watermark.png',
                'watermark_width' => 120,
                'quality' => 90,
            ],
            'product_slider' => [
                'width' => '200',
                'height' => '300',
            ],
        ],
        'blog-preview' => [
            'thumb_b' => [
                'width' => '120',
                'height' => '120',
                'stretch' => true,
            ],
            'prew_s' => [
                'width' => '400',
                'height' => '300',
                'quality' => 90,
            ],
        ],
        'summernote' => [
            'prew_b' => [
                'width' => '900',
                'watermark' => 'watermark.png',
                'watermark_width' => 120,
                'quality' => 90,
            ],
        ],
    ],
];
```

'original_style' - используется для оптимизации загружаемых на сервер изображений, обязательная настройка. Параметры поддерживает
такие же как и любой стиль картинок.

## Обновление стилей картинок
###### updating styles
```php
php artisan fileupload:styles_update
```
