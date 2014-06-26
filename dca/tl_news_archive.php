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
 * Register the global callbacks
 */
$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = array('tl_news_archive_categories', 'checkPermission');

/**
 * Add a global operation to tl_news_archive
 */
array_insert($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations'], 1, array
(
    'categories' => array
    (
        'label'               => &$GLOBALS['TL_LANG']['tl_news_archive']['categories'],
        'href'                => 'table=tl_news_category',
        'icon'                => 'system/modules/news_categories/assets/icon.png',
        'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="c"'
    )
));

class tl_news_archive_categories extends Backend
{

    /**
     * Check the permission
     */
    public function checkPermission()
    {
        $this->import('BackendUser', 'User');

        if (!$this->User->isAdmin && !$this->User->newscategories) {
            unset($GLOBALS['TL_DCA']['tl_news_archive']['list']['global_operations']['categories']);
        }
    }
}
