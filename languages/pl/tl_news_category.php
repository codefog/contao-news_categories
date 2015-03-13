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
$GLOBALS['TL_LANG']['tl_news_category']['title']         = array('Tytuł', 'Wprowadź tytuł kategorii.');
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'] = array('Tytuł na stronie', 'Tutaj możesz wprowadzić tytuł kategorii, który będzie widoczny na stronie.');
$GLOBALS['TL_LANG']['tl_news_category']['alias']         = array('Alias kategorii', 'Alias kategorii jest unikalnym odwołaniem do kategorii, do którego można się odwołać zamiast numerycznego ID.');
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']      = array('Klasa CSS', 'Tutaj możesz wprowadzić klasę CSS, która będzie dodana we front endzie.');
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']        = array('Strona przekierowania', 'Tutaj możesz wybrać stronę, na którą zostanie przeniesiony odwiedzający po kliknięciu na link kategorii w szablonie aktualności.');
$GLOBALS['TL_LANG']['tl_news_category']['published']     = array('Opublikuj kategorię', 'Opublikuj kategorię aktualności na stronie.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']    = 'Tytuł i alias';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend'] = 'Ustawienia przekierowania';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']  = 'Ustawienia publikacji';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']    = array('Nowa kategoria', 'Utwórz nową kategorię');
$GLOBALS['TL_LANG']['tl_news_category']['show']   = array('Szczegóły kategorii', 'Pokaż szczegóły kategorii ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['edit']   = array('Edytuj kategorię', 'Edytuj kategorię ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copy']   = array('Duplikuj kategorię', 'Duplikuj kategorię ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['delete'] = array('Usuń kategorię', 'Usuń kategorię ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['toggle'] = array('Publikuj/ukryj kategorię', 'Publikuj/ukryj kategorię ID %s');
