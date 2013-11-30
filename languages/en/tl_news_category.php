<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog Ltd
 *
 * @package news_categories
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog Ltd <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_news_category']['title']         = array('Title', 'Please enter a category title.');
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'] = array('Frontend title', 'Here you can enter a category title that will be displayed in front end.');
$GLOBALS['TL_LANG']['tl_news_category']['alias']         = array('Category alias', 'The category alias is a unique reference to the category which can be called instead of its numeric ID.');
$GLOBALS['TL_LANG']['tl_news_category']['published']     = array('Publish category', 'Make the news category publicly visible on the website.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']   = 'Title and alias';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend'] = 'Publish settings';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']    = array('New category', 'Create a new category');
$GLOBALS['TL_LANG']['tl_news_category']['show']   = array('Category details', 'Show the details of category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['edit']   = array('Edit category', 'Edit category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copy']   = array('Duplicate category', 'Duplicate category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['delete'] = array('Delete category', 'Delete category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['toggle'] = array('Publish/unpublish category', 'Publish/unpublish category ID %s');
