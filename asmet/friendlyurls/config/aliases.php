<?php

/**
 * url формируется в том же порядке, как расположены настройки
 *
 * возможные type:
 * string - строка которую мы и задаем
 *  -content - что вписано, то и будет взято
 *
 *
 * db - таблица в db (алиас в таблице url частный случай данных из бд)
 *  -relation - вид связи обьекта с таблицой в базе данных
 *  -relation_param  - параметры при связи
 *  -table - таблица с которой будем работать
 *  -column - колонка таблицы с которой будем работать
 *  -translit - boolean, нужно или нет транслитить данные из хранилища
 *  -where - дополнительные условия для выбора из бд
 *      - table_column - колонка таблицы, для сравнения с obj_column
 *      - obj_column - колонка таблицы переданного объекта, для поска
 *
 *    ключи для relation_param при морфной связе
 *      - able - первая часть имени калонок с morph связью
 *      - _id - колонка таблицы переданного объекта, для поска по id
 *      - _type - колонка таблицы переданного объекта, для поска по типу
 *      -sub_table - таблица в которой из которой выберем sub_column
 *      -sub_column - колонка таблицы с которой будем работать
 *
 * alias - то, что пользователь дописал как желаемый алиас сущности
 */
return [
  'default' => [
    ['type' => 'alias'],
  ],
// системное имя сущности
  'blog' => [

    [
      'type'    => 'string',
      'content' => 'blog',
    ],

    [
      'type'           => 'db',
      'relation'       => 'many_to_many',
      'relation_param' => [
        'method' => 'categories'
      ],
      'table'          => 'category',
      'column'         => 'name',
      'translit'       => true,
    ],

    [
      'type' => 'alias',
    ],
  ],

  'category' => [
    [
      'type'    => 'string',
      'content' => 'blog/category',
    ],

    [
      'type'     => 'db',
      'table'    => 'categories',
      'column'   => 'category',
      'where'    => [
        'table_column' => 'id',
        'obj_column'   => 'parent_id',
      ],
      'translit' => true,
    ],

    [
      'type' => 'alias',
    ],
  ],
];


/**
 *      если type = string, значит дальше в массиве обязан быть content
 *      если type = db, - значит в массиве должны быть relation, relation_param, table, column.
 *                          необязательные параметры translit, where.
 *      если type = alias - этого достаточно
 *
 *
 *      пример relation_param при морфной связе - [
 *                           'able'  => 'categoryable',
 *                           '_type' => 'App\Article',
 *                           '_id'   => 'id',
 *                           'sub_table'      => 'categories',
                            'sub_column'     => 'category' ]
 *
 *      пример relation_param при связе многие ко многим - [
 *                                              'method' => 'tags' ]
 */