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
 * Register the global save and delete callbacks
 */
$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = array('tl_news_categories', 'updateCategories');
$GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'][] = array('tl_news_categories', 'deleteCategories');

/**
 * Extend a tl_news palette
 */
$GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = str_replace('author;', 'author;{category_legend},categories;', $GLOBALS['TL_DCA']['tl_news']['palettes']['default']);

/**
 * Add a new field to tl_news
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['categories'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news']['categories'],
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'treePicker',
    'foreignKey'              => 'tl_news_category.title',
    'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'foreignTable'=>'tl_news_category', 'titleField'=>'title', 'searchField'=>'title', 'managerHref'=>'do=news&table=tl_news_category'),
    'sql'                     => "blob NULL"
);

class tl_news_categories extends Backend
{

    /**
     * Update the category relations
     * @param DataContainer
     */
    public function updateCategories(DataContainer $dc)
    {
        $this->deleteCategories($dc);
        $arrCategories = deserialize($dc->activeRecord->categories);

        if (is_array($arrCategories) && !empty($arrCategories)) {
            foreach ($arrCategories as $intCategory) {
                $this->Database->prepare("INSERT INTO tl_news_categories (category_id, news_id) VALUES (?, ?)")
                               ->execute($intCategory, $dc->id);
            }
        }
    }

    /**
     * Delete the category relations
     * @param DataContainer
     */
    public function deleteCategories(DataContainer $dc)
    {
        $this->Database->prepare("DELETE FROM tl_news_categories WHERE news_id=?")
                       ->execute($dc->id);
    }
}
