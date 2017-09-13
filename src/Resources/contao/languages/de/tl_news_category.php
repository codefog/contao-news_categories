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
$GLOBALS['TL_LANG']['tl_news_category']['title']             = array('Titel', 'Bitte Kategorie-Titel eingeben.');
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle']     = array('Frontend Titel', 'Titel der im Frontend angezeigt wird.');
$GLOBALS['TL_LANG']['tl_news_category']['alias']             = array('Nachrichten-Kategorie-Alias', 'Der Kategorie-Alias ist eine eindeutige Referenz, die anstelle der numerischen ID aufgerufen werden kann.');
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']          = array('CSS-Klasse', 'Hier kann eine CSS-Klasse vergeben werden, welche zur Kategorie im Front-End hinzugefügt wird.');
$GLOBALS['TL_LANG']['tl_news_category']['hideInList']        = array('Im Listen/Archiv-Modul verstekcen', 'Zeigt diese Kategorie nicht im Nachrichtenliste oder -archiv Modul an (wirkt sich nur auf die <em>news_</em> Templates aus).');
$GLOBALS['TL_LANG']['tl_news_category']['hideInReader']      = array('Im Leser-Modul verstecken', 'Zeigt diese Kategorie nicht im Nachrichtenleser-Modul an (wirkt sich nur auf die <em>news_</em> Templates aus).');
$GLOBALS['TL_LANG']['tl_news_category']['excludeInRelated']  = array('Aus ähnlicher Nachrichtenliste ausschließen', 'Schließt die Nachrichten dieser Kategorie in der Nachrichten-Liste der ähnlichen Nachrichten aus.');
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']            = array('Weiterleitungsseite', 'Hier kann eine Seite ausgewählt werden, auf diese ein Besucher weitergeleitet wird wenn ein Kategorielink im Nachrichtentemplate angeklickt wird.');
$GLOBALS['TL_LANG']['tl_news_category']['published']         = array('Kategorie veröffentlichen', 'Nachrichten-Kategorie veröffentlichen.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']    = 'Titel und Alias';
$GLOBALS['TL_LANG']['tl_news_category']['modules_legend']  = 'Modul-Einstellungen';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend'] = 'Weiterleitungs-Einstellungen';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']  = 'Veröffentlichung';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']        = array('Nachrichten-Kategorie', 'Nachrichten-Kategorie erstellen');
$GLOBALS['TL_LANG']['tl_news_category']['show']       = array('Details der Kategorie', 'Zeigt die Details der Kategorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['edit']       = array('Kategorie bearbeiten', 'Kategorie ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_news_category']['cut']        = array('Kategorie verschieben', 'Kategorie ID %s verschieben');
$GLOBALS['TL_LANG']['tl_news_category']['copy']       = array('Kategorie kopieren', 'Kopiert die Kategorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'] = array('Unterkategorien kopieren', 'Kopiert die Kategorie ID %s mit den Unterkatehgorien');
$GLOBALS['TL_LANG']['tl_news_category']['delete']     = array('Kategorie löschen', 'Löscht die Kategorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['toggle']     = array('Kategorie veröffentlichen/unveröffentlichen', 'Kategorie ID %s veröffentlichen/unveröffentlichen');
$GLOBALS['TL_LANG']['tl_news_category']['pasteafter'] = array('Einfügen nach', 'Nach Kategorie ID %s einfügen');
$GLOBALS['TL_LANG']['tl_news_category']['pasteinto']  = array('Einfügen in', 'In Kategorie ID %s einfügen');
