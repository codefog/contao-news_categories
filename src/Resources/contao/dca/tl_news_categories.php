<?php

/**
 * Table tl_news_categories
 */
$GLOBALS['TL_DCA']['tl_news_categories'] = [

    // Config
    'config' => [
        'dataContainer' => 'Table',
        'sql' => [
            'keys' => [
                'category_id' => 'index',
                'news_id' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'category_id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true],
        ],
        'news_id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true],
        ],
    ],
];
