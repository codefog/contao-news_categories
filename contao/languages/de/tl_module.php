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
$GLOBALS['TL_LANG']['tl_module']['news_categories'] = ['Nachrichten-Kategorien', 'Bitte wähle die Nachrichten-Kategorien.'];
$GLOBALS['TL_LANG']['tl_module']['news_customCategories'] = ['Auswählbare Kategorien', 'Kategorien, deren Auswahl möglich sein soll.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterCategories'] = ['Nach Kategorien filtern', 'Filtert die Nachrichten-Liste nach Kategorien.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterCategoriesCumulative'] = ['Nach Kategorien filtern (kumulativ)', 'Filtert die Nachrichtenliste kumulativ nach Kategorien. Priorität gegenüber der vorhergehenden Checkbox ("Nach Kategorien filtern").'];
$GLOBALS['TL_LANG']['tl_module']['news_relatedCategories'] = ['Nach verwandten Kategorien filtern', 'Nutzt die Kategorien des aktuellen Nachrichtenbeitrags um die Nachrichtenliste zu filtern. Hinweis: dieses Modul muss sich auf der selben Seite befinden wie das Nachrichtenleser-Modul.'];
$GLOBALS['TL_LANG']['tl_module']['news_relatedCategoriesOrder'] = ['Sortierung der Beiträge verwandter Kategorien', 'Hier kann die Sortierung der verwandten Nachrichtenbeiträge ausgewählt werden.'];
$GLOBALS['TL_LANG']['tl_module']['news_includeSubcategories'] = ['Unterkategorien miteinbeziehen', 'Berücksichtigt Unterkategorien bei der Filterung wenn eine übergeordnete Kategorie aktiv ist.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterCategoriesUnion'] = ['Kategorie Filterung über Vereinigung (nur kumulativ)', 'Filtert Nachrichtenbeiträge und Kategorien vereinigt (x OR y) statt der Schnittmenge (x AND y).'];
$GLOBALS['TL_LANG']['tl_module']['news_enableCanonicalUrls'] = ['Canonical URL einfügen', 'Fügt bei aktiver Kategorieauswahl einen Canonical-Tag im Head der Webseite ein.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterDefault'] = ['Standard-Filter', 'Hier kann der Standard-Filter für die Nachrichtenliste gewählt werden.'];
$GLOBALS['TL_LANG']['tl_module']['news_filterPreserve'] = ['Standard-Filter aktivieren', 'Die Standard-Filtereinstellungen gelten auch dann, wenn eine aktive Kategorie ausgewählt wurde.'];
$GLOBALS['TL_LANG']['tl_module']['news_resetCategories'] = ['Kategorie-Filter zurücksetzen', 'Fügt einen Link hinzu, um die Filter zurückzusetzen.'];
$GLOBALS['TL_LANG']['tl_module']['news_showEmptyCategories'] = ['Leere Kategorien anzeigen', 'Zeigt Kategorien auch dann an, wenn die Kategorie keine Nachrichtenbeiträge enthält.'];
$GLOBALS['TL_LANG']['tl_module']['news_forceCategoryUrl'] = ['Kategorie-URL erzwingen', 'Nutzt die Zielseite der Kategorie (falls gesetzt) statt die reguläre Filter-URL.'];
$GLOBALS['TL_LANG']['tl_module']['news_categoriesRoot'] = ['Referenz-Kategorie (Root)', 'Hier kann die Referenzkategorie ausgewählt werden. Diese wird als Startpunkt benutzt (ähnlich zum Navigationsmodul).'];
$GLOBALS['TL_LANG']['tl_module']['news_categoryFilterPage'] = ['Kategorie-Zielseite', 'Hier kann die Zielseite für die Kategorie-Filterung festgelegt werden. Dies überschreibt die Kategorie-Zielseite.'];
$GLOBALS['TL_LANG']['tl_module']['news_categoryImgSize'] = ['Bildgröße für Nachrichten-Kategorien', &$GLOBALS['TL_LANG']['tl_module']['imgSize'][1]];

/*
 * Reference.
 */
$GLOBALS['TL_LANG']['tl_module']['news_relatedCategoriesOrderRef'] = [
    'default' => 'Standardsortierung',
    'best_match' => 'Beste Übereinstimmung',
];
