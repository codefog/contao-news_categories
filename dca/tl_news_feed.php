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
 * Extend the tl_news_feed palette
 */
$GLOBALS['TL_DCA']['tl_news_feed']['palettes']['default'] = str_replace('archives;', 'archives,categories,categories_show;', $GLOBALS['TL_DCA']['tl_news_feed']['palettes']['default']);

/**
 * Add field to tl_news_feed
 */
$GLOBALS['TL_DCA']['tl_news_feed']['fields']['categories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_feed']['categories'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'checkbox',
    'foreignKey'              => 'tl_news_category.title',
    'eval'                    => array('multiple'=>true),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_news_feed']['fields']['categories_show'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_feed']['categories_show'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'select',
    'options'                 => array('title', 'text_before', 'text_after'),
    'reference'               => &$GLOBALS['TL_LANG']['tl_news_feed']['categories_show'],
    'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>$GLOBALS['TL_LANG']['tl_news_feed']['categories_show']['empty']),
    'sql'                     => "varchar(16) NOT NULL default ''"
);
