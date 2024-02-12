<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2024, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

use Contao\Config;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\System;
use Terminal42\DcMultilingualBundle\Driver;

System::loadLanguageFile('tl_news_archive');

/*
 * Table tl_news_category
 */
$GLOBALS['TL_DCA']['tl_news_category'] = [
    // Config
    'config' => [
        'label' => $GLOBALS['TL_LANG']['tl_news_archive']['categories'][0],
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'backlink' => 'do=news',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'alias' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_TREE,
            'rootPaste' => true,
            'icon' => 'bundles/codefognewscategories/icon.png',
            'panelLayout' => 'filter;search',
        ],
        'label' => [
            'fields' => ['title', 'frontendTitle'],
            'format' => '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>',
        ],
        'global_operations' => [
            'toggleNodes' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href' => 'ptg=all',
                'class' => 'header_toggle',
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{title_legend},title,alias,frontendTitle,cssClass;{details_legend:hide},description,image;{modules_legend:hide},hideInList,hideInReader,excludeInRelated;{redirect_legend:hide},jumpTo;{publish_legend},published',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'pid' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'sorting' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['title'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'default' => ''],
        ],
        'frontendTitle' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['alias'],
            'search' => true,
            'inputType' => 'text',
            'eval' => [
                'rgxp' => 'alias',
                'doNotCopy'=>true,
                'alwaysSave' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
            ],
            'sql' => ['type' => 'binary', 'length' => 128, 'default' => ''],
        ],
        'cssClass' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['cssClass'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'string', 'default' => ''],
        ],
        'description' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['description'],
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['rte' => 'tinyMCE', 'helpwizard' => true, 'tl_class' => 'clr'],
            'explanation' => 'insertTags',
            'sql' => ['type' => 'text', 'notnull' => false],
        ],
        'image' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['image'],
            'inputType' => 'fileTree',
            'eval' => [
                'files' => true,
                'filesOnly' => true,
                'fieldType' => 'radio',
                'extensions' => Config::get('validImageTypes'),
                'tl_class' => 'clr',
            ],
            'sql' => ['type' => 'binary', 'length' => 16, 'notnull' => false],
        ],
        'hideInList' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['hideInList'],
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => 0],
        ],
        'hideInReader' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['hideInReader'],
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => 0],
        ],
        'excludeInRelated' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['excludeInRelated'],
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'sql' => ['type' => 'boolean', 'default' => 0],
        ],
        'jumpTo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['jumpTo'],
            'inputType' => 'pageTree',
            'eval' => ['fieldType' => 'radio'],
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_news_category']['published'],
            'filter' => true,
            'toggle' => true,
            'inputType' => 'checkbox',
            'sql' => ['type' => 'boolean', 'default' => 0],
        ],
    ],
];

/*
 * Enable multilingual features
 */
if (class_exists(Driver::class)) {
    // Config
    $GLOBALS['TL_DCA']['tl_news_category']['config']['dataContainer'] = Driver::class;
    $GLOBALS['TL_DCA']['tl_news_category']['config']['langColumn'] = 'language';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['langPid'] = 'lid';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['sql']['keys']['language,lid'] = 'index';

    // Fields
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['language']['sql'] = ['type' => 'string', 'length' => 5, 'default' => ''];
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['lid']['sql'] = ['type' => 'integer', 'unsigned' => true, 'default' => 0];
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['title']['eval']['translatableFor'] = '*';
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['frontendTitle']['eval']['translatableFor'] = '*';
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['description']['eval']['translatableFor'] = '*';

    $GLOBALS['TL_DCA']['tl_news_category']['fields']['alias']['eval']['translatableFor'] = '*';
    unset($GLOBALS['TL_DCA']['tl_news_category']['fields']['alias']['eval']['spaceToUnderscore'], $GLOBALS['TL_DCA']['tl_news_category']['fields']['alias']['eval']['unique']);
}
