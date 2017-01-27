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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_news_category']['title']             = array('Title', 'Please enter a category title.');
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle']     = array('Frontend title', 'Here you can enter a category title that will be displayed in front end.');
$GLOBALS['TL_LANG']['tl_news_category']['alias']             = array('Category alias', 'The category alias is a unique reference to the category which can be called instead of its numeric ID.');
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']          = array('CSS class', 'Here you can enter a CSS class that will be added to the category in front end.');
$GLOBALS['TL_LANG']['tl_news_category']['hideInList']        = array('Hide in list/archive module', 'Do not display category in the news list/archive module (affects only the <em>news_</em> templates).');
$GLOBALS['TL_LANG']['tl_news_category']['hideInReader']      = array('Hide in reader module', 'Do not display category in the news reader module (affects only the <em>news_</em> templates).');
$GLOBALS['TL_LANG']['tl_news_category']['excludeInRelated']  = array('Exclude in related news list', 'Exclude the news of this category in the related list module.');
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']            = array('Redirect page', 'Here you can choose the page to which visitors will be redirected when clicking a category link in the news template.');
$GLOBALS['TL_LANG']['tl_news_category']['published']         = array('Publish category', 'Make the news category publicly visible on the website.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']    = 'Title and alias';
$GLOBALS['TL_LANG']['tl_news_category']['modules_legend']  = 'Modules settings';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend'] = 'Redirect settings';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']  = 'Publish settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']        = array('New category', 'Create a new category');
$GLOBALS['TL_LANG']['tl_news_category']['show']       = array('Category details', 'Show the details of category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['edit']       = array('Edit category', 'Edit category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['cut']        = array('Move category', 'Move category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copy']       = array('Duplicate category', 'Duplicate category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'] = array('Duplicate with subcategories', 'Duplicate category ID %s with its subcategories');
$GLOBALS['TL_LANG']['tl_news_category']['delete']     = array('Delete category', 'Delete category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['toggle']     = array('Publish/unpublish category', 'Publish/unpublish category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['pasteafter'] = array('Paste after', 'Paste after category ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['pasteinto']  = array('Paste into', 'Paste into category ID %s');
