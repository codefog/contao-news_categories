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
$GLOBALS['TL_LANG']['tl_news_category']['title']                      = ['Title', 'Please enter a category title.'];
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle']              = ['Frontend title', 'Here you can enter a category title that will be displayed in front end.'];
$GLOBALS['TL_LANG']['tl_news_category']['alias']                      = ['Category alias', 'The category alias is a unique reference to the category which can be called instead of its numeric ID.'];
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']                   = ['CSS class', 'Here you can enter a CSS class that will be added to the category in front end.'];
$GLOBALS['TL_LANG']['tl_news_category']['teaser']                     = ['Teaser', 'Assign a teaser for this category.'];
$GLOBALS['TL_LANG']['tl_news_category']['hideInList']                 = ['Hide in list/archive module', 'Do not display category in the news list/archive module (affects only the <em>news_</em> templates).'];
$GLOBALS['TL_LANG']['tl_news_category']['hideInReader']               = ['Hide in reader module', 'Do not display category in the news reader module (affects only the <em>news_</em> templates).'];
$GLOBALS['TL_LANG']['tl_news_category']['excludeInRelated']           = ['Exclude in related news list', 'Exclude the news of this category in the related list module.'];
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']                     = ['Redirect page', 'Here you can choose the page to which visitors will be redirected when clicking a category link in the news template.'];
$GLOBALS['TL_LANG']['tl_news_category']['archiveConfig']              = ['Archive settings for news in this category', 'Configure the redirects for news in this category based on the news archive.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_archive'] = ['News archive', 'Select a news archive.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_jumpTo']       = ['Category redirect page', 'Please select a page to which a visitor will be redirected when a category link is clicked in the news template.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_jumpTo']  = ['News redirect page', 'Please select a redirect page for news in this category and news archive.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_teaser']       = ['Teaser', 'Assign a teaser for this category and news from this archive.'];
$GLOBALS['TL_LANG']['tl_news_category']['published']                  = ['Publish category', 'Make the news category publicly visible on the website.'];

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']        = 'Title and alias';
$GLOBALS['TL_LANG']['tl_news_category']['teaser_legend']       = 'Teaser';
$GLOBALS['TL_LANG']['tl_news_category']['modules_legend']      = 'Modules settings';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend']     = 'Redirect settings';
$GLOBALS['TL_LANG']['tl_news_category']['news_archive_legend'] = 'Archive settings';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']      = 'Publish settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']        = ['New category', 'Create a new category'];
$GLOBALS['TL_LANG']['tl_news_category']['show']       = ['Category details', 'Show the details of category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['edit']       = ['Edit category', 'Edit category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['cut']        = ['Move category', 'Move category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copy']       = ['Duplicate category', 'Duplicate category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'] = ['Duplicate with subcategories', 'Duplicate category ID %s with its subcategories'];
$GLOBALS['TL_LANG']['tl_news_category']['delete']     = ['Delete category', 'Delete category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['toggle']     = ['Publish/unpublish category', 'Publish/unpublish category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteafter'] = ['Paste after', 'Paste after category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteinto']  = ['Paste into', 'Paste into category ID %s'];
