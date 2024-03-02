<?php

return [
    /**
     * -home, blog... - названия страниц(на свой выбор)
     *
     * -setting - настройки общие для всех страниц
     *      -markup - bool, микроразметка
     *      -separator - разделитель хлебных крошек
     *
     * -type
     *      - static для статических страниц (просто сформированы в роуте)
     *      - dynamic для динамических страниц, таких как Статьи или Категории(страницы сущностей)
     *
     * -name - имя крошки(при dynamic типе название свойства от куда взять имя для крошки)
     *
     * - alias - bool, если присутствует связь с пакетом FriendlyURLs
     *     либо
     * -route_name - имя роута
     *      либо
     *  url - url страницы
     *
     * alias_method - дополнительные параметр в который нужно передавать имя метода модели котоырй должен возвращать
     * ее юрл
     *
     *
     * -parent - в случае если есть родитель ссылка на ближайший статический родитель
     * -parent_type - тип динамического родителя
     *      relation -  родитель через отношения
     *          method - метод по-которому можно достучаться до родителя
     *      table_column - родитель через колонку в собственной таблице
     *                      (значения этой колонки должны соответствовать id родителя)
     *          column - имя колонки
     *
     *          self - название страницы, для реверсивного вызова
     *
     */
  'setting' => [
    'separator' => '&raquo;',
    'markup'    => false,
    'bootstrap' => false,
  ],

    /**
     * примеры
     */

  'home' => [
    'type'       => 'static',
    'name'       => 'Главная страница',
    'route_name' => 'home',
  ],

  'blog' => [
    'type'       => 'static',
    'name'       => 'Блог',
    'route_name' => 'blog',
    'parent'     => 'home',
  ],

  'categories' => [
    'type'   => 'static',
    'name'   => 'Категории',
    'url'    => '/category',
    'parent' => 'blog',
  ],

  'category' => [
    'type'   => 'dynamic',
    'name'   => 'name',
    'alias'  => true,
    'parent' => 'categories',
  ],

  'post' => [
    'type'        => 'dynamic',
    'name'        => 'name',
    'alias'       => true,
    'parent'      => 'category',
    'parent_type' => 'relation',
    'method'      => 'category'
  ],

];