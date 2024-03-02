# AjaxCatalog v3.5.0

For Laravel 5.5

Назначение пакета:
 * Возможность быстро создавать страницы каталожного типа с фильтрами и сортировками
 * Формирует Url страниц согласно всем стандартам ЧПУ
 * Содержит возможность превращения каталожных страниц в полноценные страницы для SEO продвижения

 ## История изменений:
 * v1.0 -  Функционал проекта оформлен в пакет
 * v3.* - контроллер страниц вынесен из пакета
## Установка пакета

### Добавление пакета в проект:
```SH
    composer require vaden/ajaxcatalog
```

### Добавляем в app.php в сервис провайдеры (extra не спасет - из-за бага не подхватывает шаблоны вьюх из модуля):
```php
Vaden\AjaxCatalog\AjaxCatalogServiceProvider::class,
Vaden\AjaxCatalog\AjaxCatalogEventServiceProvider::class,
```

### Добавляем в web.php:

Добавляем в конец файла, перед инициализацией ЧПУ
```php
\Vaden\AjaxCatalog\AjaxCatalogController::routes();
```

Вводим в терминал:
```
php artisan vendor:publish --provider="Vaden\AjaxCatalog\AjaxCatalogServiceProvider"
```

Будут скопированны следующие файлы:

config/ajaxCatalog.php - для настроек пакета

~~resources/assets/js/ajaxcatalog.js~~ - этот файл не забываем подключить в наш вебпакмикс
(не будет копироваться начиная с версии 3.5.0, предполагается, что файл уже есть на проекте)

views/ajaxCatalog/ajaxCatalogPage.blade.php - каталожная страница открытия к индексации каталожных страниц...

views/ajaxCatalog/pages/createOrEdit.blade.php - страницы создания и редактирования каталожных страниц

views/ajaxCatalog/elements/ - доступные для темизации элементы каталожных страниц

lang/ru/ajaxCatalog.php - переводы для нашего пакета

resources/assets/vendor/ajaxcatalog/js/ajax_catalog_create_or_edit.js - js админки создания каталожной страницы

После выполняем php artisan migrate, для установки необходимых таблиц в бд

### Добавляем гейты для работы с пакетом:
```php
admin_ajax_catalog
admin_ajax_catalog_pages
```

```js
Подключаем js файл для админки создания каталожной страницы

require('../vendor/ajaxcatalog/js/ajax_catalog_create_or_edit');
```

## Работа с пакетом:

###Настраиваем config/ajaxCatalog.php:

####Подключение новой сущности в пакет:

Базовая надстройка, которая дает доступ пакету к данным из конфиг файла по алиасу сущности из Url это 'entities_list'. Именно
по этой настройке аякс каталог проверяет поддерживаемую ли в целом сущность пытается получить пользователь и если
url алиас находится в настройке entity_alias, то идет дальнейшая обработка данных.

Пример корректно настроенного списка сущносрей:

```php
    'entities_list' => [
        [
            'model' => 'Vaden\AjaxCatalog\AjaxCatalogPage',
            'route_alias' => 'ajaxCatalogPage',
            'settings_key' => 'ajax_catalog_pages',
            'entity_alias' => 'ajax-catalog-pages'
        ],
        [
            'model' => 'App\Product',
            'route_alias' => 'admin.products',
            'settings_key' => 'products_settings',
            'rout_group' => 'admin',
            'entity_alias' => 'products',
        ],
    ],
```

'model' - это полное название класса модели с которым работаем

'route_alias' - это название будет использоваться для генерации имен роутов (читай ниже о доступе к роутам каталога)

'settings_key' - по этому ключу пакет получит прочие настройки сущности. Обычно он называется также, как таблица
в бд, но бывают случаи что для одной таблицы необходимо создать не одну админ панель, потому для избежания казусов
этот параметр вынесен отдельно от 'db_table'.

'entity_alias' - начало url по которому будет идентифицированна сущность с которой необходимо работать.

'rout_group' - необязательный параметр, используется для добавления к пути префиксов, которые будут проигнорированы параметрами url

После того, как мы обозначили нашу сущность создаем на том же уровне вложенности что и 'entities_list' массив с ключем
из 'settings_key'. Данный массив будет содержать все настройки необходимые для нашей сущности. Пример:

```php
    'entities_list' => [
        //entity url alias => ...
        'ajax-catalog-pages' => [
            //...
            'settings_key' => 'ajax_catalog_pages',
        ],
        'packages' => [
            //...
            'settings_key' => 'packages',
         ],
    ],
    
    'ajax_catalog_pages' => [
        //настройки конкретной сущности
    ],
                
    'packages' => [
        //настройки конкретной сущности
    ],
```

#### Доп возможности
В стандартных настройках мы всегда в начале юрл имеем ключ нашей админки, но бывают ситуации, когда нам нужны более короткие юрл, к примеру для категорий 
каталога товаров, чтобы юрл начинался с категории, но при этом весь функционал был един по всему сайту. Для достижения этого результата используем:

```php
'short_urls' => ['App\Product', 'App\Order'],
```

В массив передаем имена моделей, которые содержат информацию о сокращенных юрл, 
в самих моделях для этого необходимо созать публичный статичный метод ajaxCatalogShortUrls, который будет возвращать массив значений в формате:

```php
[
    'алиас пути' => 'ключ entity из entities list к которому относится путь',
    'алиас пути' => 'ключ entity из entities list к которому относится путь',
    //и тд
]
```
нюанс - чтобы не было 404 ошибки при входе на страницу нужно также настроить фильтр в котором данные short_urls будут в possible values

В стандартных настройках мы всегда в начале юрл имеем ключ нашей админки, но бывают ситуации, когда нам нужны более короткие юрл, к примеру для категорий 
каталога товаров, чтобы юрл начинался с категории, но при этом весь функционал был един по всему сайту. Для достижения этого результата используем:

Также мы можем задать настройки для хлебной крошки ведущей на главную страницу, указав ее анкор и тайтл, и переопределить дефолтный разделитель хлебных крошек:
```php
'breadcrumbs' => [
    'name' => 'Главная',
    'title' => 'Перейти на главную',
    'deliminer' => '»',
],
```

####Настройка конкретной сущности:

В минимальной комплектации конфиг выглядит следующим образом:
```php
    'products_settings' => [
        'alias' => 'admin/products',
        'blade' => 'ajaxCatalog.adminProducts',
    ]
```

Так, чтобы у нас запустилась страница достаточно указать какой блейд использовать для ее рендера и какой алиас использовать для формирования ссылок в каталоге.

Касательно алиаса есть нюанс - если в 'entities_list' задан 'rout_group', то алиас должен сочетать ключ сущности и ее группу также как они будут в юрл, если же 'rout_group' нет, то
алиас тождественнен ключу сущности из 'entities_list'.

Теперь же рассмотрим что мы дополнительно можем настроить в нашем каталоге:

```php
'master_blade' => 'layouts.adminMaster',
```
По-умочанию блейды наследуются от 'layouts.master', но при необходимости мы можем передать свой корневой шаблон.

```php
'cache' => false,
```
По-умочанию кеш будет не активен, если нужно кешить, то ставим эту настройку в true.

```php
'pagination' => 30,
```
Сколько позиций сущности будет показываться на 1 странице. По умолчанию задано 12.

```php
'count_by' => 'products.id',
```
Не обязательные параметр, указывает по чему считать кол-во товара на страннице(нужно для пагинации),
указывать если фильтры настроены через scope

```php
'title' => 'category',

'title' => 'default',
'name' => 'Каталог товаров',
```
то, откуда будет браться тайтл страницы. Если страница открыта к индексации, то параметры возьмутся из нее, если же нет, то можно задать данный атрибут.
Он принимает значения: 'default' - из атрибута name в настройках, или же название обязательного фильтра - тогда значение будет взято из
читабельного названия данного фильтра.

данные на странице будут доступны из переменной $seo.

```php
'breadcrumbs' => [
            [
                'url' => '/',
                'ankor' => 'Главная',
                'title' => 'Вернуться на главную',
            ]
        ]
```

Дефолтные хлебные крошки, для каталожных страниц текущей сущности. Будут видны на всех страницах данного каталога. Добавлять в случае, когда необходимо чтобы
между главной страницей и крошками из фильтров что-то было.

```php
'gate' => [
    'name' => 'ajax-catalog-page-see',
    'add_info' => [
        [
            'model' => 'App\Host',
            'filter_name' => 'host',
        ],
    ]
],
```
Гейт, который будет использован для проверки прав доступа к данной странице. В 'name' указываем название гейта, 
в 'add_info' передаем массив в котором указываем объекты каких моделей необходимо передать в гейт параметрами для 
более сложной валидации (!!на текущий момент сделана поддержка передачи только 1 доп сущности). 
'filter_name' говорит из значения какого фильтра мы возьмем объект для поиска, фильтр указываемый в этот гейт должен 
быть отмечен как обязательный.
 
Если нужно чтобы к странице имели доступ любые пользователи, без проверок, то просто не указываем данную настройку.

Если нужно чтобы проверка шла просто по имени гейта:

```php
'gate' => [
    'name' => 'ajax-catalog-page-see',
    'add_info' => false,
],
```

Часто для представления информации на страницы нам не хватит информации из нашей основной сущности и мы будем вынуждены работать с данными из
ее связей, для этого исопльзуем настройку:

```php
'relationships' => ['productGroups', 'productCategories', 'productAttributes', 'prices'],
```

  
 **'filters' - следующая настройка сущности, отвечает за то, какие фильтры
 нам необходимы и как они будут выводиться на страницу.**
 
 ключ в значениях этого массива выступает префиксом фильтра, который попадет в строку url, он
 пишется без больших букв и доп симвоволов. От порядка фильтров в этой таблице зависит 
 то, в каком порядке будут располагаться параметры в строке url.
 
 Пример настройки filters:
 
```php
'filters' => [
   'manufacturer' => [
                'name' => 'Производитель',
                'table_column' => 'name',
                'to_relation' => 'manufacturer',
                'operator' => '=',
                'filter_type' => 'name',
                'possible_values' => 'default',
                'widget' => 'links',
                'show_counter' => true,
                'order_by_counter' => true,
                'related_column' => 'id',
                'base_column' => 'manufacturer_id',
                'group_column' => 'name',
                'multiple_values' => true,
                'breadcrumbs' => [
                    'short_name' => false,
                    'separate' => false,
                    'with_depth' => false,
                ]
            ],
            'prb' => [
                'name' => 'Цена от',
                'table_column' => 'value',
                'by_scope' => 'basePriceFilter',
                'operator' => '>=',
                'filter_type' => 'date',
                'add_info' => 'minBasePrice'
            ],
            'pre' => [
                'name' => 'Цена до',
                'table_column' => 'value',
                'by_scope' => 'basePriceFilter',
                'operator' => '<=',
                'filter_type' => 'date',
                'add_info' => 'maxBasePrice'
            ],
],
```
  
  теперь подробнее о том, какие настройки возможны в конкретном фильтре:
  
  _name_ - читабельное название фильтра. Обязательный параметр, выводит название фильтра в текущих
  активных фильтрах, хлебных крошках и тд.
  
  _table_column_ - колонка в БД по которой работает фильтр. Обязательный параметр. Заполнять в него названия колонок базовой модели
  или же названия колонок из отношений, что мы определили ранее. В случае фильтрации по отношению обязателен следующий параметр:
  
  _to_relation_ - название отношения, к которому относится фильтр. Должно присутствовать только у фильтров работающих не по данным базовой модели, когда 
  достаточно просто установить есть ли связь с необходимым значением (к примеру по категории у товара).
  
  _by_scope_ - для создания более сложных фильтров, к примеру когда у товара имеется ряд цен и нам нужно фильтровать по базовой лучше создать свой scope и 
  тогда значения из этого фильтра будут переданы в него.
  
  пример scope для фильтра (обращаю внимание, что в примере написан scope для фильтра от до, потому добавлена вспомогательная функция и проверка в модель, для 
  предотвращения многократного присоединения таблицы при построении query):
  
```php
public function scopeBasePriceFilter($query, $column, $operator, $value)
    {
        if(!$this->isJoined($query, 'prices')) {
            $query->leftJoin('prices', 'prices.product_id', '=', 'products.id')
                ->select('products.*');
        }

        $query->where('prices.type_code', '00009')
            ->where('prices.' . $column, $operator, $value);
    }

    public function isJoined($query, $table)
    {
        $joins = $query->getQuery()->joins;
        if($joins == null) {
            return false;
        }
        foreach ($joins as $join) {
            if ($join->table == $table) {
                return true;
            }
        }
        return false;
    }
```
  наша настройка будет выглядеть следующим образом: 'by_scope' => 'basePriceFilter'
  
  вместе с to_relation можно также задать параметр  'with_condition' => [['column' => 'code', 'operator' => '=', 'value' => 0009]] - для создания фильтров 
  по связям каталога с дополнительными условиями.
  
  _operator_ - какой будет использоваться оператор для поиска значений в заданной колонке таблицы в бд. Обязательный параметр. При использовании оператора in нужно задать еще
  доп настройку фильтру in_values, к примеру:

```php
'in_values' => [null, '', '0']
```
аккуратно с 0 в этой настройке, если колонка, к которой это применяете integer, то ставим интегер, если стринг, то '0' иначе полетит все к херам.

  при этом в possible values данного фильтра нужно будет передавать значения 0 и 1. При занчении 1 будут выводиться результаты, что попали в массив значений, при 0 те, что не попали (по
  факту будет применяться оператор not in).
  
  !! оператор in работает с 'filter_type' == 'prefix'
  
  также есть еще дополнительный оператор search, который используется для реализации поиска по сайту, который не зависит от раскладки, 
  регистра и порядка вводимых слов в поиск.
  
  _required_ - по-умолчанию значение false, если же нужно, чтобы без этого фильтра отдавалась 404 ошибка на странице, то указываем его со значением true.
  
  _curr_search_ - по-умолчанию значение true, если же нужно, чтобы данный фильтр не выводился в блоке текущих фильтров, то добавляем его со значением false.
  
  _filter_type_ - обязательный параметр. Возможные значения - 'prefix', 'name', 'search'. В случае
  использования 'prefix' (все обычные фильтры с префиксом) и 'search' (текстовые поля в которые человек
  сам вводит что искать) в юрл параметры фильтра передаются по принципу 'ключ этого фильтра-что пользователь ввел в поиск',
  к примеру 'name-ivan'. В случае использования 'name' в юрл передаются только названия параметов
  выбранных пользователем. Данный фильтр используется для создания максимально коротких ЧПУ и 
  применять его стоит только в том случае, когда известно что страницы с данным фильтром будут 
  открываться к индексации.
  
  _multiple_values_ - по умолчанию параметр false. Если же нужно, чтобы фильтр мог иметь множественные значения - задаем его true.
  
  _widget_ - если для создания виджета необходимо иметь список возможных значений (селект лист)
  то необходимо указать возможный виждет. Значениями могут быть: _links, select, vueCascadeSingle, categories, subCategories_. 
  Список всех актуальных значений можно посмотреть в классе widgetsFactory.
  Значениями выступают case данного класса. Если же доп данных для создания виджета поиска не
  нужно (обычное поле поиска) то настройку можно не указывать.
  
  для корректной работы subCategories необходимо также указать настройку parent_filter - в которую передать системное имя фильтра из которого будет взято значение
  родительской категории для которой необходимо показать дочерние.
  
  _possible_values_ - присваивать в случае необходимости проверки юрл на то возможно ли в нем наличие определенных значений фильтров (обязательный параметр для
  фильтров с 'type' => 'name'). Значениями могут быть либо 'default' в   случае, когда необходимо проверять по всем возможным значениям модели без доп условий, 
  либо системное имя фильтра (из определенных нами как обязательные (важно - обязательные фильтры должны быть заданы в настройках ранее тех
  фильртов, на которые они будут влиять)), значение которого будет использоваться как доп параметр при возврате перечня возможных значений.
  
  _show_counter_ - виджеты, которые выводятся ссылками, также могут получать информацию о том, насколько изменится количество товаров в каталоге и выборе данного фильтра, 
  для получения информации о количестве необходимо задать данную настройку со значением true
  
  !! если мы задали show_counter на фильтре, который работает не с базовой сущностью, а с связью, к примеру категорией у товара, то также необходимо задать настройки
  related_column и group_column - в первой мы указываем по которой колонке присоединяется связь к нашей сущности, вторая настройка указывает группировка которых результатов
  из присоединенной связи даст нам цифры для нашего счетчика. + задаем также base_column - это колонка в базовой таблице по которой будет
  идти джоин для счетчика
  
  !! если мы задали show_counter на фильтре, который работает по индексу, то мы должны также указать параметр index_counter = true
  
  Пример:
```php
'sub' => [
                'name' => 'Категория',
                'table_column' => 'id',
                'operator' => '=',
                'to_relation' => 'productCategories',
                'filter_type' => 'prefix',
                'possible_values' => 'default',
                'widget' => 'categories',
                'show_counter' => true,
                'order_by_counter' => true,
                'related_column' => 'product_id',
                'group_column' => 'product_category_id',
                'multiple_values' => true
            ],
```
  
  _order_by_counter_ - в виджетах-ссылках также возможно указать в каком порядке выводить итемы когда создается получение их возможных значений (при создании scope для их possible values), 
  чтобы отсортировать виджет по количеству позиций в них совместно с show_counter можно задать данный парамерт как true, также поддерживается 
  значение 'exist_alphabet', отсортирует те, что есть итемы по алфавиту
   
   Для использования данного параметра в фильтрах в нашей базовой модели необходимо создать публичный метод getAjaxCatalogPossibleValues($filter_name, $settings_key, $param = null, $param_filter_name = null), 
   который будет  вызываться пакетом и записывать необходимые возможные значения для дальнейшей работы с ними. Данный метод должен возвращать массив возмжоных значений в 
   следующем формате:
   
   Переменные достпупные в getAjaxCatalogPossibleValues:
   $filter_name - название фильтра,
   $settings_key - название конфига по которому работает фильтр,
   $param - значение по которому можно фильровать вывод возможных фильтров(например id текущей категории,
    что бы исключить производителей товары относящиеся к которым не выводятся),
   $param_filter_name - значение указаное в фильтре как 'possible_values' => '',
    
```php
  'алиас фильтра' => [
     'name' => 'читабельное название',
     'value' => 'сырое значение',
     'parent' => 'алиас родителя', // не обязательный параметр, нужен для создания виджетов с иерархией вложенности значений
  ],
  //...
```
  
  примеры получения возможных значений:
```php
  //без вложенности
if($filter_name == 'manufacturer') {
            foreach ($this->select('manufacturer')->distinct()->get() as $value) {
                $alias = strtolower($value->manufacturer);
                $alias = Transliterator::transliterate($alias, '-');

                if(strlen($alias) > 0) {
                    $values[$alias] = [
                        'name' => $value->manufacturer,
                        'value' => $value->manufacturer,
                    ];
                }
            }
        }
//со вложенностью
        if($filter_name == 'category') {
            $parent_arr = [];
            foreach (\DB::table('product_categories')->orderBy('parent')->get() as $value) {
                $alias = strtolower($value->name);
                $alias = Transliterator::transliterate($alias, '-');
                $parent_arr[$value->id] = $alias;

                if(strlen($alias) > 0) {
                    $values[$alias] = [
                        'name' => $value->name,
                        'value' => $value->id,
                        'parent' => $value->parent > 0 ? $parent_arr[$value->parent] : null,
                    ];
                }
            }
        }
```
  
  также в фильтре можно указать настройку:
```php
'breadcrumbs' => [
                    'short_name' => false,
                    'separate' => false,
                    'with_depth' => false,
                ]
```
_short_name_ - по умолчанию false, отвечает за то что будет ли добавленно имя фильтра в хлебную крошку им генерируемую.

_separate_ - по умолчанию false, отвечает за то что будут ли несколько значений данного фильтра объединены в 1 хлебную крошку (при false) или же каждое значение данного фильтра
будет отедльной хлебной крошкой

_with_depth_ - по умолчанию false, отвечает за то что будут ли фильтры воспринимать уровни вложенности крошек, группируя фильтры на разных уровнях в разные хлебные крошки, применяется только
с фильтрами у которых multiple_values = false, separate = false

Обратите внимание! Хлебные крошки можно создавать только из фильтров в которых настроены possible_values.

##### Асинхронные фильтры (ver >= 3.5.0)
Краткий алгоритм:
```php
-указываем в конфиге нужного фильтра необходимые параметры
-создаем блейд для рендера каждого из асинхронных фильтров (указываем его в кофиге)
-на старнице с фильтрами размещаем элементы с нужными классами и id
для вывода асинхронных фильтров
-добавляем в ajaxcatalog.js код который будет получать html асинхронных фильтров и вставлять на страницу
```

_async_load_ - параметр фильтра, отвещаюший за асинхронную загрузку фильтра, может быть 
'single' или 'multiple'.

'async_load' => 'single', - _на каждый_ такой фильтр после загрузки страницы 
отправляется доп запрос для рендера и получения html фильтра.

'async_load' => 'multiple', - _на все_ фильтры с значенеим multiple после загрузки страницы 
отправляется один доп. запрос для рендера и получения html фильтров.

_async_blade_ - блейд файл, через который будет рендериться асинхронный фильтр и после выводиться на страницу.

Пример конфига для асинхронной загрузки всех аттрибутов:

```php
'async_load' => 'multiple',
'async_blade' => 'ajaxCatalog.async_widgets.attrs',
```

В blade, указанный в конфиге мы получаем две переменные: 

_$widgetsData_ - данные виджета 

_$widgetsName_ - имя виджета из конфига (может использоваться на атрибутах)

Пример блейда указанного в конфиге для рендера асинхронного фильтра атрибутов:

```php
<div class="facet hide-facet-box">
    <div class="facet-title">{{ $widgetsName }}<span
                class="show-ajax active">+</span>
    </div>
    <ul class="facet_link list-unstyled">
        @foreach($widgetsData as $link)
            <li class="{{$link['count'] == 0 ? ' disactive' : ''}} {{ $link['state'] ? 'active' : '' }}">
                <a rel="{{$link['nofollow'] ? 'follow' : 'nofollow'}}"
                   href="{{ $link['url'] }}"
                   title="{{ $link['name'] }}"
                   class="link ajax_link">
                    <span class="name"> {{ $link['name'] }}</span>
                    <span class="count">{{ $link['count'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
```
Пример блейда указанного в конфиге для рендера асинхронного фильтра категорий:

```php
@if (isset($widgetsData) && count($widgetsData) > 0)
    <div class="facet facet-category">
        <div class="facet-title">@lang('titles.products.category')
            <span class="show-ajax">-</span>
        </div>
        <ul class="list-unstyled">
            @foreach ($widgetsData as $link)
                @include('ajaxCatalog.snippets.categories_widget_link', $link)
            @endforeach
        </ul>
    </div>
@endif
```
            
Пример блока кода для вывода асинхронных фильтров на каталожной странице 
resources/views/ajaxCatalog/snippets/filters.blade.php :
```php 
@if (isset($widgetsDataAsync) && count($widgetsDataAsync) > 0)
     <div id="async-filters-load-zone">
         @foreach($widgetsDataAsync as $name => $widget)
             @if(isset($widget['async_load']) && $widget['async_load'] === 'single')
                 <div class="ajax-catalog-async-single" id={{ $name }}>
                     <img class="b-lazy"
                          src="{{asset('images/default/preload.gif')}}"
                     >
                 </div>
             @endif
         @endforeach
         @foreach($widgetsDataAsync as $name => $widget)
             @if(isset($widget['async_load']) && $widget['async_load'] === 'multiple')
                 <div class="list-unstyled ajax-catalog-async-multiple" id={{ $name }}>
                     <img class="b-lazy"
                          src="{{asset('images/default/preload.gif')}}"
                     >
                 </div>
             @endif
         @endforeach
     </div>
   @endif
   ```
   Обратите внимание в примере выше, html код фильтров будет вставлен ajax'ом в div с такими классами и id:
   
   `<div class="ajax-catalog-async-single" id={{ $name }}> </div>`
   
   `<div class="ajax-catalog-async-multiple" id={{ $name }}> </div>`
   
   Для этого необходимо в файле 
   resources/assets/js/ajaxcatalog.js
   в методе widgetsInitiate();
   
   использовать данный код:
   
     if (document.getElementsByClassName('ajax-catalog-async-single')) {
       let elements = $('.ajax-catalog-async-single');
       //находим на странице элементы для фильтров с типом single
       $.each(elements, function (index, element) {
         $.get('/f-ajax-async-filter-single', { 
           filter_name: element.id,
           url: window.location.pathname,
         }).done(function (data) {
           $(element).html(data[element.id]);
         });
   
       });
     }
   
     if (document.getElementsByClassName('ajax-catalog-async-multiple')) {
       let elements = $('.ajax-catalog-async-multiple');
	   //находим на странице элемент для фильтров с типом multiple
       if (elements.length> 0) {
         $.get('/f-ajax-async-filter-multiple', {
           url: window.location.pathname,
         }).done(function (data) {
           $.each(elements, function (index, element) {
             $(element).html(data[element.id]);
           });
         });
       }
     }
   
###### Окончание гайда по настройке асинхронных фильтров


'import' - если задан этот конфиг, то все остальные настройки в фильтре будут проигнорированы. Значением указываем массив с названием нужной функции и, при 
необходимости, добавляем название параметра, который будет получаться в данную вспомогательную функцию. Эта 
функция должна вернуть массив настроек дополнительных фильтров, которые будут вставлены в данное место конфиг файла. Используется, к примеру для автоматической
генерации фильтров для атрибутов товаров. 

Пример настройки фильтра с атрибутами:

конфиг фильтра:

```php
'attr' => [
    'import' => [
		'function' => 'ajax_catalog_attributes'
		'param' => 'category'
	],
],
```

вспомогательная функция:
```php
function ajax_catalog_attributes() {
    $filters = [];
    $attrs = \DB::table('attributes')->select('name', 'id')->get();

    foreach ($attrs as $attr) {
        $alias = Transliterator::transliterate($attr->name, '');
        $filters[$alias] = [
            'name' => $attr->name,
            'table_column' => 'search_value',
            'to_relation' => 'productAttributes',
            'operator' => '=',
            'filter_type' => 'name',
            'possible_values' => 'category',
            'widget' => 'links',
            'show_counter' => true,
            'order_by_counter' => true,
            'related_column' => 'product_id',
            'base_column' => 'id',
            'group_column' => 'value',
            'multiple_values' => true,
            'breadcrumbs' => [
                'short_name' => false,
                'separate' => false,
                'with_depth' => false,
            ]
        ];
    }
    return $filters;
}
```

генерация possible_values:
```php
 $attrs = \DB::table('attributes')->select('name', 'id')->get();
        foreach ($attrs as $attr) {
            $alias = Transliterator::transliterate($attr->name, '');
            if ($filter_name == $alias) {
                if($param) {
                    $attrs = \DB::table('attribute_product')
                        ->join('products', 'attribute_product.product_id', '=', 'products.id')
                        ->join('product_product_category', 'products.id', '=', 'product_product_category.product_id')
                        ->where('product_product_category.product_category_id', $param)
                        ->where('attribute_product.attribute_id', $attr->id)
                        ->select('attribute_product.search_value as search_value', 'attribute_product.value as value')
                        ->orderBy('attribute_product.value')
                        ->distinct()
                        ->get();
                } else {
                    $attrs = \DB::table('attribute_product')->where('attribute_id', $attr->id)->select('search_value', 'value')->distinct()->orderBy('value')->get();
                }
                foreach ($attrs as $value) {
                    if (strlen($value->search_value) > 0) {
                        $values[$value->search_value] = [
                            'name' => $value->value,
                            'value' => $value->search_value,
                        ];
                    }
                }
            }
        }
```

в блейде каталога где нужно вывести атрибуты делаем
```php
@include('ajaxCatalog.snippets.attributes_widgets')
```

код самого шаблона (перечень фильтров которые мы добавили найдем в массиве $add_info, передаваемом каталогом на страницу):
```php
@foreach($add_info['attached_filters']['attr'] as $filter_data)
    @if (isset($widgetsData[$filter_data['name']]) && count($widgetsData[$filter_data['name']]) > 0)
        <div class="facet">
            <div class="facet-title">{{ $filter_data['readable_name'] }}<span
                        class="show-ajax">-</span>
            </div>
            <ul class="facet_link list-unstyled">
                @foreach($widgetsData[$filter_data['name']] as $link)
                    <li class="{{$link['count'] == 0 ? ' disactive' : ''}} {{ $link['state'] ? 'active' : '' }}">

                        <a href="{{ $link['url'] }}" title="{{ $link['name'] }}"
                           class="link ajax_link"><span
                                    class="fac-name">
                                    {{ $link['name'] }}</span><span
                                    class="cont pull-right">({{ $link['count'] }})</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endforeach
```
  
  Создание фильтров по принципу ОТ ДО:
```php
  'exb' => [
     'name' => 'Окончание от',
     'table_column' => 'expire_date',
     'operator' => '>=',
     'filter_type' => 'date',
  ],
  //end of expire date
  'exe' => [
     'name' => 'Окончание до',
     'table_column' => 'expire_date',
     'operator' => '<=',
     'filter_type' => 'date',
  ],
```
  особенности - как filter_type указываем 'date', в настройках из примера меняем только ключ фильтра, 
  'name', 'operator', а 'table_column' наоборот у сдвоенного фильтра остается единым.
  
  Для создания фильтра цены от до (чтобы по-умолчанию были переданы минимальное и максиальное значения) используем доп параметр add_info
  значением которого выступает scope нашей базовой модели. Ретурн этого скопа получаем через базовую настройку каталога
  настройку add_info в которой указываем тип result_generator (подробнее смотри в описании самой настройки). 
  
  Чтобы параметры макс и мин цены не терялись, используем настройку 'not_in_add_info' => true. Так, этот фильтр не будет учитываться 
  при построении мин и макс значения.

```php
'prb' => [
                'name' => 'Цена от',
                'table_column' => 'value',
                'by_scope' => 'basePriceFilter',
                'operator' => '>=',
                'filter_type' => 'date',
                'add_info' => 'maxBasePrice',
				'not_in_add_info' => true,
            ],
            'pre' => [
                'name' => 'Цена до',
                'table_column' => 'value',
                'by_scope' => 'basePriceFilter',
                'operator' => '<=',
                'filter_type' => 'date',
                'add_info' => 'minBasePrice',
				'not_in_add_info' => true,
            ],
```  

```php
    public function scopeMaxBasePrice($query)
    {
        if (!$this->isJoined($query, 'prices')) {
            $query->leftJoin('prices', 'prices.product_id', '=', 'products.id')
                ->select('products.*');
        }

        return $query->max('prices.value');
    }

    public function scopeMinBasePrice($query)
    {
        if (!$this->isJoined($query, 'prices')) {
            $query->leftJoin('prices', 'prices.product_id', '=', 'products.id')
                ->select('products.*');
        }

        return $query->min('prices.value');
    }
``` 
 
 'sorts' - следующая настройка сущности, отвечает за то, какие сортировки будут 
 присутствовать на странице.
 
 пример настройки:
 
 ```php
        'sorts' => [
            [
                'name' => 'Дата создания',
                'alias' => 'ct',
                'by_column' => 'created_at'
            ],
            [
                'name' => 'Дата обновления',
                'alias' => 'ut',
                'by_column' => 'updated_at'
            ],
            [
                'name' => 'Название категории',
                'alias' => 'nm',
                'by_scope' => 'orderByCategory',
            ],
        ],
 ```
  
  ключей в сортировке не нужно
  
  для сортировок по базовой таблице используем параметр by_column и в него пишем название колонки
  для сортировок по связи пишем параметр by_scope и в него название функции сортировки, которая получится при использовании scope
  при этом в нашу модель добавляем соотв метод, к примеру в Product :
  
```php
public function scopeOrderByCategory($query, $order = 'desc')
    {
        $query->leftJoin('product_product_category', 'product_product_category.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'product_product_category.product_category_id')
            //we should always have select to not overwrite base model fields
            ->select('products.*')
            ->selectRaw('group_concat(product_categories.name, CHAR(13)) as aggregate')
            ->orderBy('aggregate', $order)
            ->groupBy('products.id');
    }
```
  
  в by_scope при этом передаем orderByCategory
  
```php
   'has_aggregation' => true,
```
  Настройка сущности, которую необходимо указать в случае наличия сортировок имеющих агрегацию в своем коде (при использовании scope).
  
  
 'default' - следующая настройка сущности, указывает нужно ли применять какие-либо
 фильтры или сортировки на страницах по-умолчанию.
 
 без дефолтных настроек массив имеет структуру:
 
```php
 'default' => [
     'sorts_const' => [],
     'sorts' => [],
     'filters' => [],
 ],
```
 'sorts_const' - сортировки, которые применяются всегда по умолчанию (к примеру, чтобы в каталоге
 товаров товары не в наличие всегда были в низу страницы). При том данные сортировки не выводятся
 в виджете сортировок, которые может изменить пользователь. Ключем ее значения выступает колонка в бд, а значением 
 поднастройки - порядок сортировки.
 
```php
'sorts_const' => [
                [
                    'order' => 'desc',
                    'source' => 'by_scope',
                    'value' => 'orderByCategory'
                ]
            ],
            'sorts' => [
                'order' => 'desc',
                'source' => 'by_scope',
                'value' => 'orderByCategory'
            ],
```
 
 'sorts' - та сортировка, которая будет задана по умолчанию на странице, когда на нее зайдет пользователь.
 
  
 'filters' - те фильтры, которые будут заданы по умолчанию на странице, когда на нее зайдет пользователь.
   при том у человека не будет возможности повлиять на данный фильтр. Пример - скрыть не опубликованные
   статьи со страницы блога. (добавлена поддержка 4-го параметра "by_scope", передаем в него название нашего скоупа и получаем 
   работающий дефолтный фильтр. При использовании by_scope остальные настройки в деф фильтре будут проигнорированы, но мы сам скоуп
   первым параметром мы получим настройки из этого фильтра)
   
```php
 'filters' => [
     'state' => [
         'table_column' => 'state',
         'operator' => '=',
         'value' => 1,
     ],
 ],
```
  
 'export_fields' - следующая настройка сущности после default, указывает какие поля должны
 участвовать в быстром експорте результатов страницы.
 
 Если нет необходимости выводить форму экспорта полей то значением 'export_fields' ставим пустой массив.
  
```php
  'export_fields' => [
            [
                'name' => 'Дата отправки',
                'column' => 'created_at',
            ],
            [
                'name' => 'Имя пользователя',
                'column' => 'name',
                'to_relation' => 'user'
            ],
        ],
```
  
  поднастройка 'name' - обозначает как будут названы колонки в файле экспорта
  
  поднастройка 'column' - обозначает к какому атрибуту модели мы обращаемся
  
  to_relation - поднастройка не обязательная, на случай если тянем данные из связи
  
 'bulk_operations' - следующая настройка сущности после export_fields, указывает какие массовые
 операции с сущностями возможны на странице.
 
 Если нет необходимости выводить возможности для массовых операций 'bulk_operations' ставим пустой массив.
   
```php
   'bulk_operations' => [
       'edit' => [
           'name' => 'Изменить',
           'check' => false,
           'type' => 'link',
           'gate' => 'host-service-change',
       ],
       'export' => [
           'name' => 'Экспортировать в EXEL',
           'check' => false,
           'type' => 'export',
           'alter' => false,
           'gate' => 'host-service-see',
       ],
       'set_disabled' => [
           'name' => 'Сделать не активными',
           'check' => true,
           'type' => 'db_query',
           'table' => 'host_services',
           'column' => 'state',
           'set_to' => 0,
           'gate' => 'host-service-change',
       ],
       'delete' => [
           'name' => 'Удалить',
           'check' => true,
           'type' => 'model',
           'model' => 'App\HostService',
           'method' => 'delete',
           'gate' => 'host-service-delete',
       ],
   ],
```
   
   ключем в массовых операциях выступает системное имя массовой операции.
   
   Поднастройки массовых операций:
   
   'name' - обязательный параметр, читабельное название операции, которое будет показано пользователю
   
   'check' - обязательный параметр, булево значение. Позволяет включать/выключать всплывающее окно, 
   уточняющее у пользователя действительно ли он хочет применить заданную массовую операцию.
   
   'gate' - обязательный параметр, название гейта, который проверяет может ли пользователь использовать данную массовую
   операцию. Если гейт не нужен - указываем просто false
   
   'type' - обязательный параметр, указывает каким образом обрабатывать данную массовую операцию. 
   Возможные значения данного параметра и поднастройки от него зависящие:
   
   'model' - действия с методами моделей, работает со следующими параметрами: model - полное имя класса модели,
   method - название метода, который необходимо вызвать, работает со следующими параметрами: alter - если задано значение false, то обработка 
   экспорта эксель в массовых операциях будет сделана стандартным экспортом каталога, если же передан массив с названием класса и метода, то
   данные для экспорта будут переданы в данный метод класса на обработку:
   
```php
      'bulk_operations' => [
          'export' => [
              'name' => 'Экспортировать в EXEL',
              'check' => false,
              'type' => 'export',
              'alter' => [
                  'class' => 'App\Tasks\Export',
                  'method' => 'fileCreate',
              ],
              'gate' => 'host-service-see',
          ],
```
   
   'export' - экспорт выбранных позиций в эксель. Специфических доп настроек нет. !! важно чтобы при этой настройке были заданы export_fields, 
   иначе ничего работать не будет
   
   'link' - открывает выбранные ссылки из позиций выведенных в каталоге, доп параметры не нужны. !! на ссылку, которая 
   должна открыться по клику нужно добавить класс с названием ключа операции. 
      
   'db_query' - выполнить апдейт записей в бд, работает со следующими параметрами: table - название таблицы, 
   к которой обратимся, column - название колонки в данной таблице, с которой будем работать,
   set_to - значение, которое должно быть записано в данную колонку.
 
 'additional_info' - следующая настройка сущности после bulk_operations, указывает откуда взять
 дополнительную информацию, которая может пригодиться для работы на страницах каталога.
 
 Если нет необходимости передавать доп инфо на страницу то значением 'additional_info' ставим пустой массив.

```php
 'additional_info' => [
        'selects' => [
            'type' => 'config',
            'file' => 'tasks.selects',
        ],
        'site_info' => [
             'type' => 'filter_item',
             'name' => 'site',
             'class' => 'App\Site',
        ],
        'rights' => [
             'type' => 'class_static',
             'name' => 'App\Tasks\TaskRights',
             'param' => null,
             'method' => 'getRights'
        ],
		'rights' => [
             'type' => 'function',
             'name' => 'my_function',
             'param' => null,
        ],
        'additionFieldData' => [
             'type' => 'class_static',
             'name' => 'App\AjaxCatalogAdditionalFields',
             'param' => 'for_page',
             'add_data' => 'catalogPage',
             'method' => 'getAdditionFieldData'
        ],
    ],
```
  
  Данные из этого набора будут переданы в переменную $add_info, которую мы получим в наши представления. Ключи занчений
   массива будут ключами подмассивов $add_info. 
   
   'add_data' => 'catalogPage' - передаст в указаный метод модель AjaxCatalogPage

   
   Возможные значения type: config, filterItem
   
   От значения настройки type зависят дополнительные параметры:
   * 'type' => 'config' - имеет только одну доп настройку 'file', в которую мы указываем из которого конфиг файла нам нужны данные.
   * 'type' => 'class_static' - в name передаем полное имя класса с которого нам нужны данные, в param передаем то, что попадет в метод класса при его вызове, в method 
   название статического метода, который будет вызван.
   * 'type' => 'filter_item' - выводит информацию о сущности с соответствующим значением фильтра (может быть использовано для обязательных фильтров). Значения поднастроек:
   'name' - системное имя фильтра; 'class' - название класса сущности модели метод find которой будет вызван для получения экземпляра сущности.
   * 'type' => 'function' - в name передаем название функции, в param передаем то, что попадет в нее при вызове.
   * 'type' => 'result_generator' - в name передаем название фильтра из которого получены данные, чтобы эти данные там были испльзуем соответствующие
   настройки в фильтрах
   
   также все настройки из секции additional_info поддерживают параметр 'to_js' => true, он позволяет передавать данные непосредственно в js.
  
   !! пока данный функционал отключен и не работает 'load_zones' - следующая настройка сущности после additional_info, указывает какие области страницы аякс каталога необходимо обновлять при инициации аякс обработки.
   По умолчанию выглядит следующим образом:
```php
    'load_zones' => [
           'breadcrumbs_load_zone' => true,
           'filters_load_zone' => true,
           'sorts_load_zone' => true,
           'curr_search_load_zone' => true,
           'items_load_zone' => true,
       ],
```
ключ в настройках - это id контейнера с информацией в нашем blade шаблоне. Его значение - булево, обозначает обновлять этот контейнер или же не стоит. Мы можем по своему усмотрению
добавлять/убирать данные зоны из настроек.
  


###Настраиваем blade шаблоны (пример настроенной страницы можно посмотреть в ajaxCatalogPage.blade.php):

Общее содержимое файла:

```php
@extends($jq ? 'ajaxCatalogElements::ajaxLayout' : $master_blade)
 
 @section('title', 'Страницы каталога')
 
 @section('content')
    <div id="catalog_page_ajax">
        <div id="breadcrumbs_load_zone">
            //хлебные крошки
        </div>
        <div id="filters_load_zone">
            //тут выводятся всефильтры
        </div>
        <div id="sorts_load_zone">
            //тут выводятся сортировки
        </div>
        <div id="curr_search_load_zone">
            //тут выводятся результаты поиска
        </div>
        <div id="items_load_zone">
            //позиции поиска и пагинация
        </div>        
    </div>
 @endsection
```

Теперь пара слов о содержимом страницы:

вся страница должна быть обернута в див с id "catalog_page_ajax", инициация аяксов будет идти по этому диву.

ID _load_zone разделяют страницу на области, которые обновляются отдельно друг от друга и при желании их обновление можно отключить в настройках фильтров или же задать 
свои зоны. Все обновляемое содержимое страницы должно находиться в пределах данных контейнеров.

внутри нам доступна переменная $itemsCount, которая отражает количество результатов выдачи соответствующее текущим фильтрам

```php
<h1>Страницы каталога товаров (найдено {{ $itemsCount }})</h1>
```
 
####вывод фильтров страницы:
 есть 2 вида фильтров страницы - просто ссылки и элементы форм страницы. Просто ссылки мы можем в целом располагать где угодно,
 элементы же форм должны содержаться внутри:
 
```php
 <form method="POST" action="{{ route('catalogAjaxField') }}" id="form_catalog" enctype="multipart/form-data">
 {{ csrf_field() }}
        
 //фильтры-поля страницы
        
 </form>
```
 
 Доступны следующие фильтры-поля формы:
 
 1. Текстовое поле для поиска
 
 используется с фильтрами, которые имеют настройки:
 
```php
  'operator' => 'like',
  'filter_type' => 'search',
  'num_of_values' => 'single',
  'widget' => false,
```
 
 html код поля:
```php
 <input class="ajax_filter" type="text" placeholder="Название статьи" data-type="text" name="name" 
 value="{{ empty($widgetsData['currTextValues']['name']) ? '' : $widgetsData['currTextValues']['name'] }}">
```

Имеет значение:
 * class="ajax_filter" - говорит что поле должно быть обработано на аяксе
 * data-type="text" - говорит каким типом аякс обработчика необходимо обработать поступающую информацию
 * name="name" - имя поля, должно соответстовать ключу фильтра из настроек
 * $widgetsData['currTextValues'] - переменная содержащая информацию о текущих значениях фильтров из юрл

Для добавления обычного автодополнения к текстовому полю:
 * убедитесь что к проекту подключен пакет https://www.npmjs.com/package/bootstrap-3-typeahead
 * добавьте текстовому полю класс autocompleteInput2
 * укажите дополнительными атрибутами из какой таблицы в бд и колонки брать варианты для автодополнения data-table="ajax_catalog_pages" data-column="name"
 * отключите родное автодополнение браузера autocomplete="off"
 
 html код текстового поля с автодополнением:

```php
 <input class="autocompleteInput2 form-control ajax_filter" type="text" placeholder="Название статьи"
  data-type="text" data-table="ajax_catalog_pages" data-column="name"  name="name"  autocomplete="off"
  value="{{ empty($widgetsData['currTextValues']['name']) ? '' : $widgetsData['currTextValues']['name'] }}">
```

Для преобразования виджета поиска в выбор из готовых значений с автодополнением (лучший виджет в случае
отсутствия необходимости в поиске по части слова):
 * убедитесь что к проекту подключен пакет https://select2.org/
 * добавьте класс autocompleteInput
 * укажите дополнительными атрибутами из какой таблицы в бд и колонки брать варианты для автодополнения data-table="ajax_catalog_pages" data-column="name"
 * отключите родное автодополнение браузера autocomplete="off"
 
 html код текстового поля с выбором через автодополнение:

```php
<select class="form-control autocompleteInput ajax_filter" name="name" data-type="text" data-table="ajax_catalog_pages" data-column="name">
    <option value="">Название статьи</option>
    @if(!empty($widgetsData['currTextValues']['name']))
        <option value="{{ $widgetsData['currTextValues']['name'] }}" selected>{{ $widgetsData['currTextValues']['name'] }}</option>
    @endif
</select>
```
 
2. Поля ОТ ДО

По сути они представляют из себя 2 поля поиска работающих совместно:

```php
<div class="col-sm-2">
    Наценка:
</div>
<div class="col-sm-2">
    <input type="text" class="form-control ajax_filter" name="apb" data-type="text" placeholder="От"
    value="{{ empty($widgetsData['currTextValues']['apb']) ? '' : $widgetsData['currTextValues']['apb'] }}">
</div>
<div class="col-sm-2">
    <input type="text" class="form-control ajax_filter" name="ape" data-type="text" placeholder="До"
    value="{{ empty($widgetsData['currTextValues']['ape']) ? '' : $widgetsData['currTextValues']['ape'] }}">
</div>
```

Нюанс есть только в полях с виджетом даты:
```php
<div class="col-sm-1">
    Дата изменения:
</div>
<div class="col-sm-2">
    <div class='input-group date ajaxDate2 ajax_filter_date'
     data-default="{{ empty($widgetsData['currTextValues']['uab']) ? '' : $widgetsData['currTextValues']['uab'] }}">
          <input type='text' class="form-control" placeholder="От" name="uab"
          value="{{ empty($widgetsData['currTextValues']['uab']) ? '' : $widgetsData['currTextValues']['uab'] }}"/>
          <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
          </span>
     </div>
</div>
<div class="col-sm-2">
    <div class='input-group date ajaxDate2 ajax_filter_date'
     data-default="{{ empty($widgetsData['currTextValues']['uae']) ? '' : $widgetsData['currTextValues']['uae'] }}">
         <input type='text' class="form-control" placeholder="До" name="uae"
         value="{{ empty($widgetsData['currTextValues']['uae']) ? '' : $widgetsData['currTextValues']['uae'] }}"/>
         <span class="input-group-addon">
             <span class="glyphicon glyphicon-calendar"></span>
         </span>
     </div>
</div>
```

На что обратить внимание:
 * убедитесь что к проекту подключен пакет "eonasdan-bootstrap-datetimepicker"
 * у дива-обертки поля даты должны быть классы ajaxDate2 ajax_filter_date
 * data-default содержит данные о дате заполненной в поле по умолчанию
 * span class="input-group-addon" - создает иконку по которой необхоидимо кликнуть для выбора даты
 * не забываем в name инпутов, а также в коды для показа значения по умолчанию подставлять ключ фильтра с которым работаем

3. Селект

Для создания фильтра-выбора из селекта используем следующую конструкцию:
```php
<select class="form-control ajax_filter" name="state" data-type="link">
    <option value="{{ $widgetsData['state']['default_url'] }}">Статус</option>
    @foreach($widgetsData['state']['options'] as $link)
         <option value="{{ $link['url'] }}" {{ $link['state'] ? 'selected' : '' }}>{{ $link['name'] }}</option>
    @endforeach
</select>
```                         
На что обратить внимание:
 * name="state" привязка обработки фильтра как и везде идет через name, в который пишется ключ фильтра
 * $widgetsData['state'] - в данном случае название вложенного массива равно ключу фильтра и содержит 
 все данные необходимые для построения фильтра
 * data-type="link" - указывает тип обработчика фильтра
 
 
4. Чекбоксы

Как таковые чекбоксы в фильтрах не нужны, по сути они представляют из себя обычные ссылки, потому настраиваются также как фильтры-ссылки о
которых пойдет речь ниже

пример кода чекбоксов:
```php
<div class="col-sm-3">
                    @foreach($widgetsData['called'] as $link)
                        <a href="{{ $link['url'] }}" class="btn btn-default btn-xs ajax_link"
                           title="{{ $link['name'] }}" style="margin-bottom: 5px;">
                            @if($link['state'])
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                            @else
                                <span class="glyphicon glyphicon-unchecked" aria-hidden="true"></span>
                            @endif
                            {{ $link['name'] }}
                        </a>
                    @endforeach
                </div>
``` 

5. vueElements 
С каталогом интегрированны виджеты библиотеки elements Vue.js. Чтобы все работало сначала убеждаемся что в скриптах подключена необходимая библиотека с нужными
нам элементами и далее просто вставляем наши виджеты используя как v-model ключи необходимых фильтров.

Пример вставки виджета для категорий (ключ фильтра - 'category') в фильтры каталога:
```php
<div class="category-filter">
                                <el-cascader
                                        placeholder="Категории"
                                        :options="options"
                                        filterable
                                        change-on-select
                                        clearable
                                        @change="catChange"
                                        v-model="category">
                                </el-cascader>
                            </div>
``` 

Как таковые чекбоксы в фильтрах не нужны, по сути они представляют из себя обычные ссылки, потому настраиваются также как фильтры-ссылки о
которых пойдет речь ниже

####настройки и разновидности фильтров-ссылок:
шаблон для фильтра-ссылок:
```php
Производитель
                    <ul class="list-group">
                        @foreach($widgetsData['manufacturer'] as $link)
                            <li class="list-group-item {{ $link['state'] ? 'active' : '' }}">
                                <span class="badge">{{ $link['count'] }}</span>
                                <a href="{{ $link['url'] }}" title="{{ $link['name'] }}" class="link">
                                    {{ $link['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
```

https://www.sitepoint.com/laravel-blade-recursive-partials/ - тут можно прочесть как собрать шаблон для фильтра со вложенностью, к примеру для категории

####создание формы поиска для каталога товаров
Поскольку поиск обычно на сайтах представлен на каждом по-своему, то описываю тут как создать и настроить 
свою форму с автодополнением и подружить ее с уже существующим каталогом:

1. создаем роут который будет принимать сабмит нашей формы и роут, который будет обрабатывать автодополнение

```php
Route::post('/product-search', 'ProductController@searchSubmit')->name('product.searchForm');
Route::post('/produt-search-autocomplete', 'ProductController@autocompleteSearch')->name('product.autocompleteSearch');
```

2. html формы

```php
<form method="POST" action="{{ route('product.searchForm') }}" enctype="multipart/form-data" >
    {{ csrf_field() }}

    <input id="productSearchInput" type="text" placeholder="@lang('product.site_search')" name="q"  autocomplete="off">
    <button type="submit">@lang('product.search')</button>

</form>
```

3. методы контроллера для обработки событий:
```php
/*
    *handler for search autocomplete fields
    */
        public function autocompleteSearch(Request $request)
    {

        $input = trim($request['q']);

        if (empty($input)) {
            return \Response::json([]);
        }

        $input = mb_strtolower($input);
        $input = Transliterator::transliterate($input, ' ');
        $input = explode(' ', $input);
        $input = array_filter($input);

        $result = new Product;

        foreach ($input as $word) {
            $result = $result->where('search', 'LIKE', '%' . $word . '%');
        }
        $result = $result->active()->paginate(5);

        $formatted = [];

        foreach ($result as $value) {
            $formatted[] = [
                'name' => $value->name,
                'link' => url($value->url()),
                'html' => '<div class="row"><div class="col-xs-2 col-sm-2 col-md-3 padding-right-zero"><img src="' . $value->getPictureUrl('thumb_s') . '"></div><div class="col-xs-10 col-sm-10 col-md-9"><p>' . $value->name . '</p><p>' . $value->getPrice() . ' ' . get_currency() . '</p></div></div>',
                'btn' => false
            ];
        }

        if (count($formatted) > 0) {
            $formatted[] = [
                'name' => __('product.all_results'),
                'link' => '/search/fraze-' . $request['q'],
                'html' => '<p>' . __('product.all_results') . '</p>',
                'btn' => true
            ];
        }

        return \Response::json($formatted);
    }

    /*
    *handler for search form submit
    */
    public function searchSubmit(Request $request)
    {

        $st = str_replace(array("\\", '/', '%'), ' ',  strip_tags($request['q']));

        return redirect(route('product.search.catalogFilters', 'fraze-' . $st));
    }
```

4. скрипт инициации автодополнения (используем http://easyautocomplete.com/ остальные либы творят чушь):
```php
window.easyAutocomplete = require('easy-autocomplete');

var options = {

    url: "produt-search-autocomplete",

    getValue: function(element) {
        return element.name;
    },

    ajaxSettings: {
        dataType: "json",
        method: "POST",
        data: {
            dataType: "json"
        }
    },

    preparePostData: function(data) {
        data.q = $("#productSearchInput").val();
        return data;
    },

    template: {
        type: "custom",
        method: function(value, item) {
            return item.html;
        }
    },

    list: {
        onClickEvent: function() {
            window.location.href = $("#productSearchInput").getSelectedItemData().link;
            $("#productSearchInput").val('');
        },
        onSelectItemEvent: function() {
            if($("#productSearchInput").getSelectedItemData().btn == true) {
                $("#productSearchInput").val('');
            }
        },
        onChooseEvent: function() {
            if($("#productSearchInput").getSelectedItemData().btn == true) {
                $("#productSearchInput").val('');
            }
        }
    },

    requestDelay: 200
};

5. Настройки фильтра поиска в каталоге:

```php
'fraze' => [
                'name' => 'Поиск',
                'table_column' => 'search',
                'operator' => 'search',
                'filter_type' => 'search',
                'curr_search' => false,
            ],
```

6. Вывод виджета "результаты поиска по запросу":

в блейд нашего каталога в нужном месте добавляем:

```php
<div class="row" id="search_result_load_zone">
    <p>@lang('product.search_results') "{{ $widgetsData['currTextValues']['fraze'] }}". @lang('product.find_products'):  <b>{{ $itemsCount }}</b></p>
</div>
```

#### вывод виджета цены

подключаем на проект https://github.com/IonDen/ion.rangeSlider

скрипт для обработки:

```php
if (document.getElementsByClassName('layout-slider').length > 0) {

        let minPrice = $('.inputformprice').find('input[name="prb"]').val();
        let maxPrice = $('.inputformprice').find('input[name="pre"]').val();
        let disableSlider = false;

        $("#Slider2").ionRangeSlider({
            type: "double",
            min: Math.floor(window.minPrice),
            max: Math.ceil(window.maxPrice),
            from: Math.floor(minPrice),
            to: Math.ceil(maxPrice),
            grid: true,
            step: 1,
            onChange: function (data) {
                $('.inputformprice').find('input[name="prb"]').val(data.from);
                $('.inputformprice').find('input[name="pre"]').val(data.to);
            },
            onFinish: function (data) {
                slider.update({
                    disable: true
                });

                //generate right url on server
                $.post('/f-ajax-field', {
                    name: "prb",
                    url: window.location.pathname,
                    text: $('.inputformprice').find('input[name="prb"]').val()
                }).done(function (data) {
                    $.post('/f-ajax-field', {
                        name: "pre",
                        url: data,
                        text: $('.inputformprice').find('input[name="pre"]').val()
                    }).done(function (data) {
                        //update page
                        updatePageOnAjax(data);
                    });
                });
            },
        });

        let slider = $("#Slider2").data("ionRangeSlider");

        $('body').on('keydown keyup', 'input[name="pre"], input[name="prb"]', function(e){
            if (parseInt($(this).val()) > parseInt($(this).attr('max'))
                && e.keyCode != 46 // delete
                && e.keyCode != 8 // backspace
            ) {
                e.preventDefault();
                $(this).val($(this).attr('max'));
            }

            slider.update({
                from: Math.floor($('.inputformprice').find('input[name="prb"]').val()),
                to: Math.ceil($('.inputformprice').find('input[name="pre"]').val()),
            });
        });

        $('body').on('change', 'input[name="pre"], input[name="prb"]', function(e){
            slider.update({
                disable: true
            });
        });

    }
```

код из темплейта:
```php
@if(($add_info['maxPrice'] - $add_info['minPrice']) > 5)
    <div class="facet">
        <div class="facet-title">@lang('ajaxCatalog.price'):<span
                    class="pull-right show-ajax">-</span></div>
        <div class="inputformprice form-inline">
            <div class="layout-slider">
                <input id="Slider2" type="slider" name="price"
                       value="{{ floor($add_info['minPrice']) }};{{ ceil($add_info['maxPrice']) }}"/>

            </div>
            <div class="form-group">
                <span>@lang('ajaxCatalog.from')</span>
                <input type="text" class="form-control ajax_filter" name="prb"
                       data-type="text"
                       placeholder=""
                       min="{{ floor($add_info['minPrice']) }}" max="{{ ceil($add_info['maxPrice']) }}"
                       value="{{ empty($widgetsData['currTextValues']['prb']) ? floor($add_info['minPrice']) : floor($widgetsData['currTextValues']['prb']) }}">
            </div>
            <div class="form-group">
                <span>@lang('ajaxCatalog.to')</span>

                <input type="text" class="form-control ajax_filter" name="pre"
                       data-type="text"
                       placeholder=""
                       min="{{ floor($add_info['minPrice']) }}" max="{{ ceil($add_info['maxPrice']) }}"
                       value="{{ empty($widgetsData['currTextValues']['pre']) ? ceil($add_info['maxPrice']) : ceil($widgetsData['currTextValues']['pre']) }}">
                <span>{{$currency}}</span>
            </div>
        </div>
    </div>
@endif
```



####вывод стандартных виджетов сортировок формы экспорта, текущего поиска
Тут все максимально просто - подключаем файлы из пакета в нужном месте нашего шаблона и имеем гововый к работе функционал:
```php
<div class="bottom-buffer btn-toolbar">

   @include('ajaxCatalogElements::exportForm')

   <div id="sorts_load_zone">
       @include('ajaxCatalogElements::sorts')
   </div>

</div>

<div id="curr_search_load_zone">
    @include('ajaxCatalogElements::currSearch')
</div>
```

если же необходимо что-то стилизовать, то можно посмотреть содержимое данных файлов и переменные ими подхватываемые и 
оформить виджеты на свое усмотрение.

###Имена роутов генерируемые в пакете:
После добавления новой сущности в конфиг файл для доступа к генерируемым страницам из представлений следует использовать
следующие имена роутов: route('АлиасРоута'). К примеру, настройка конфиг файла:
```php
'ajax-catalog-pages' => [
     //...
     'route_alias' => 'ajaxCatalogPage',
],
```
Вызов необходимого роута:
```php
route('ajaxCatalogPage')
```

Для корректной подсветки текущей активной страницы в ссылках на текущую страницу могут понадобиться названия
роутов каталога с фильтрами, их имена составляются по принципу: 'АлиасРоута.catalogFilters'. Т.е. проверка на активную
страницу для сущности с алиасом project будет выглядеть следующим образом:
```php
is_one_active(['pajaxCatalogPage', 'pajaxCatalogPage.catalogFilters'])
```

###Админ панель открытия страниц к индексации:
Админ панель для открытия каталожных страниц к индексации идет в пакете настроенной из коробки просто как
одна из сущностей, с которыми работает каталог, получить ее можно как
```php
route('admin.ajaxCatalogPage')
```

###Страницы каталога созданные через short_urls:
```php
route('алиас из сокращенных юрл')
```

####мультиязычность
для других языков каталожные страницы настраиваются как дополнительные каталоги сущностей

###список переменных доступных на странице
$seo - сео поля каталога
$filter_data - содержит информации о текущем фильтре(на странице категории можно увидеть что за id у этой категории) 
и информацию о settings этого фильтра

парамер $filter_data массив, через него можно передать какой-то параметр на страницу,    
'filter_data' => ['type' => 'App\CatalogPage', 'param' => '', 'foo'=>'bar'],

