<?php

/*
 * News Categories bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2023, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

/*
 * Fields.
 */
$GLOBALS['TL_LANG']['tl_news_category']['title'] = ['Title', 'Please enter the category title.'];
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'] = ['Frontend title', 'Here you can enter the category title that will be displayed in front end.'];
$GLOBALS['TL_LANG']['tl_news_category']['alias'] = ['Category alias', 'The category alias is a unique reference to the category which can be called instead of its numeric ID.'];
$GLOBALS['TL_LANG']['tl_news_category']['cssClass'] = ['CSS class', 'Here you can enter the CSS class that will be added to the category in front end.'];
$GLOBALS['TL_LANG']['tl_news_category']['description'] = ['Description', 'Here you can enter the category description.'];
$GLOBALS['TL_LANG']['tl_news_category']['image'] = ['Image', 'Here you can choose the category image.'];
$GLOBALS['TL_LANG']['tl_news_category']['hideInList'] = ['Hide in list/archive module', 'Do not display category in the news list/archive module (affects only the <em>news_</em> templates).'];
$GLOBALS['TL_LANG']['tl_news_category']['hideInReader'] = ['Hide in reader module', 'Do not display category in the news reader module (affects only the <em>news_</em> templates).'];
$GLOBALS['TL_LANG']['tl_news_category']['excludeInRelated'] = ['Exclude in related news list', 'Exclude the news of this category in the related list module.'];
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo'] = ['Redirect page', 'Here you can choose the page to which visitors will be redirected when clicking a category link in the news template or categories list module (configurable). This setting is inherited!'];
$GLOBALS['TL_LANG']['tl_news_category']['published'] = ['Publish category', 'Make the news category publicly visible on the website.'];

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend'] = 'Title and alias';
$GLOBALS['TL_LANG']['tl_news_category']['details_legend'] = 'Category details';
$GLOBALS['TL_LANG']['tl_news_category']['modules_legend'] = 'Modules settings';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend'] = 'Redirect settings';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend'] = 'Publish settings';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new'] = ['New category', 'Create a new category'];
$GLOBALS['TL_LANG']['tl_news_category']['show'] = ['Category details', 'Show the details of category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['edit'] = ['Edit category', 'Edit category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['cut'] = ['Move category', 'Move category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copy'] = ['Duplicate category', 'Duplicate category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'] = ['Duplicate with subcategories', 'Duplicate category ID %s with its subcategories'];
$GLOBALS['TL_LANG']['tl_news_category']['delete'] = ['Delete category', 'Delete category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['toggle'] = ['Publish/unpublish category', 'Publish/unpublish category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteafter'] = ['Paste after', 'Paste after category ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteinto'] = ['Paste into', 'Paste into category ID %s'];
