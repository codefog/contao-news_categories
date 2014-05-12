<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

/**
 * Table tl_news_categories
 */
$GLOBALS['TL_DCA']['tl_news_categories'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'sql' => array
        (
            'keys' => array
            (
                'category_id' => 'index',
                'news_id' => 'index'
            )
        )
    ),

    // Fields
    'fields' => array
    (
        'category_id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'news_id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        )
    )
);
