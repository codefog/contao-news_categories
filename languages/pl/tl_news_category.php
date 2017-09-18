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
$GLOBALS['TL_LANG']['tl_news_category']['title']                      = ['Tytuł', 'Wprowadź tytuł kategorii.'];
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle']              = ['Tytuł na stronie', 'Tutaj możesz wprowadzić tytuł kategorii, który będzie widoczny na stronie.'];
$GLOBALS['TL_LANG']['tl_news_category']['alias']                      = ['Alias kategorii', 'Alias kategorii jest unikalnym odwołaniem do kategorii, do którego można się odwołać zamiast numerycznego ID.'];
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']                   = ['Klasa CSS', 'Tutaj możesz wprowadzić klasę CSS, która będzie dodana we front endzie.'];
$GLOBALS['TL_LANG']['tl_news_category']['teaser']                     = ['Zajawka', 'Przypisz zajawka do tej kategorii.'];
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']                     = ['Strona przekierowania', 'Tutaj możesz wybrać stronę, na którą zostanie przeniesiony odwiedzający po kliknięciu na link kategorii w szablonie aktualności.'];
$GLOBALS['TL_LANG']['tl_news_category']['archiveConfig']              = ['Ustawienia archiwum wiadomości w tej kategorii', 'Skonfiguruj przekierowania dla wiadomości w tej kategorii na podstawie archiwum wiadomości.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_archive'] = ['Archiwum wiadomości', 'Wybierz archiwum wiadomości.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_jumpTo']       = ['Strona przekierowania kategorii', 'Wybierz stronę, do której użytkownik zostanie przekierowany po kliknięciu linku kategorii w szablonie wiadomości.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_jumpTo']  = ['Strona przekierowania wiadomości', 'Proszę wybrać stronę przekierowania wiadomości w tej kategorii i archiwum wiadomości.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_teaser']       = ['Zajawka', 'Przypisz zajawka z tej kategorii i nowości z tego archiwum.'];
$GLOBALS['TL_LANG']['tl_news_category']['published']                  = ['Opublikuj kategorię', 'Opublikuj kategorię aktualności na stronie.'];

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']        = 'Tytuł i alias';
$GLOBALS['TL_LANG']['tl_news_category']['teaser_legend']       = 'Zajawka';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend']     = 'Ustawienia przekierowania';
$GLOBALS['TL_LANG']['tl_news_category']['news_archive_legend'] = 'Ustawienia archiwum';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']      = 'Ustawienia publikacji';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']    = ['Nowa kategoria', 'Utwórz nową kategorię'];
$GLOBALS['TL_LANG']['tl_news_category']['show']   = ['Szczegóły kategorii', 'Pokaż szczegóły kategorii ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['edit']   = ['Edytuj kategorię', 'Edytuj kategorię ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copy']   = ['Duplikuj kategorię', 'Duplikuj kategorię ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['delete'] = ['Usuń kategorię', 'Usuń kategorię ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['toggle'] = ['Publikuj/ukryj kategorię', 'Publikuj/ukryj kategorię ID %s'];
