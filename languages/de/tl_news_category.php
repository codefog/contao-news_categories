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
$GLOBALS['TL_LANG']['tl_news_category']['title']                      = ['Titel', 'Bitte Kategorie-Titel eingeben.'];
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle']              = ['Frontend Titel', 'Titel der im Frontend angezeigt wird.'];
$GLOBALS['TL_LANG']['tl_news_category']['alias']                      = ['Nachrichten-Kategorie-Alias', 'Der Kategorie-Alias ist eine eindeutige Referenz, die anstelle der numerischen ID aufgerufen werden kann.'];
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']                   = ['CSS-Klasse', 'Hier kann eine CSS-Klasse vergeben werden, welche zur Kategorie im Front-End hinzugefügt wird.'];
$GLOBALS['TL_LANG']['tl_news_category']['teaser']                     = ['Teasertext', 'Vergeben Sie einen Teasertext für diese Kategorie.'];
$GLOBALS['TL_LANG']['tl_news_category']['hideInList']                 = ['Im Listen/Archiv-Modul verstekcen', 'Zeigt diese Kategorie nicht im Nachrichtenliste oder -archiv Modul an (wirkt sich nur auf die <em>news_</em> Templates aus).'];
$GLOBALS['TL_LANG']['tl_news_category']['hideInReader']               = ['Im Leser-Modul verstecken', 'Zeigt diese Kategorie nicht im Nachrichtenleser-Modul an (wirkt sich nur auf die <em>news_</em> Templates aus).'];
$GLOBALS['TL_LANG']['tl_news_category']['excludeInRelated']           = ['Aus ähnlicher Nachrichtenliste ausschließen', 'Schließt die Nachrichten dieser Kategorie in der Nachrichten-Liste der ähnlichen Nachrichten aus.'];
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']                     = ['Weiterleitungsseite', 'Hier kann eine Seite ausgewählt werden, auf diese ein Besucher weitergeleitet wird wenn ein Kategorielink im Nachrichtentemplate angeklickt wird.'];
$GLOBALS['TL_LANG']['tl_news_category']['archiveConfig']              = ['Archiv-Einstellungen für Nachrichten dieser Kategorie', 'Konfigurieren Sie die Weiterleitungen für Kategorielink und Nachrichten dieser Kategorie basierend auf dem Nachrichtenarchiv.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_archive'] = ['Nachrichtenarchiv', 'Wählen Sie ein Nachrichtenarchiv aus.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_jumpTo']       = ['Kategorie-Weiterleitung', 'Hier kann eine Seite ausgewählt werden, auf diese ein Besucher weitergeleitet wird wenn ein Kategorielink im Nachrichtentemplate angeklickt wird.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_jumpTo']  = ['Nachrichten-Weiterleitung', 'Wählen Sie hier eine Weiterleitungsseite für Nachrichten dieser Kategorie und Nachrichtenarchiv.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_teaser']       = ['Teasertext', 'Vergeben Sie einen Teasertext für diese Kategorie und Nachrichten dieses Archivs.'];
$GLOBALS['TL_LANG']['tl_news_category']['published']                  = ['Kategorie veröffentlichen', 'Nachrichten-Kategorie veröffentlichen.'];

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']        = 'Titel und Alias';
$GLOBALS['TL_LANG']['tl_news_category']['teaser_legend']       = 'Teaser';
$GLOBALS['TL_LANG']['tl_news_category']['modules_legend']      = 'Modul-Einstellungen';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend']     = 'Weiterleitungs-Einstellungen';
$GLOBALS['TL_LANG']['tl_news_category']['news_archive_legend'] = 'Archiv-Einstellungen';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']      = 'Veröffentlichung';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']        = ['Nachrichten-Kategorie', 'Nachrichten-Kategorie erstellen'];
$GLOBALS['TL_LANG']['tl_news_category']['show']       = ['Details der Kategorie', 'Zeigt die Details der Kategorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['edit']       = ['Kategorie bearbeiten', 'Kategorie ID %s bearbeiten'];
$GLOBALS['TL_LANG']['tl_news_category']['cut']        = ['Kategorie verschieben', 'Kategorie ID %s verschieben'];
$GLOBALS['TL_LANG']['tl_news_category']['copy']       = ['Kategorie kopieren', 'Kopiert die Kategorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'] = ['Unterkategorien kopieren', 'Kopiert die Kategorie ID %s mit den Unterkatehgorien'];
$GLOBALS['TL_LANG']['tl_news_category']['delete']     = ['Kategorie löschen', 'Löscht die Kategorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['toggle']     = ['Kategorie veröffentlichen/unveröffentlichen', 'Kategorie ID %s veröffentlichen/unveröffentlichen'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteafter'] = ['Einfügen nach', 'Nach Kategorie ID %s einfügen'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteinto']  = ['Einfügen in', 'In Kategorie ID %s einfügen'];
