<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @link    http://codefog.pl
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @author  Lionel Maccaud <https://github.com/lionel-m>
 * @license LGPL
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_news_category']['title']                      = ['Titre', 'Veuillez, s\'il vous plaît, saisir un titre pour cette catégorie.'];
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle']              = ['Titre front office', 'Ici, vous pouvez saisir un titre de catégorie qui sera affiché en front office.'];
$GLOBALS['TL_LANG']['tl_news_category']['alias']                      = ['Alias', 'L\'alias de la catégorie est une référence unique qui peut être utilisé à la place de son ID numérique.'];
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']                   = ['Classe CSS', 'Ici, vous pouvez saisir une classe CSS qui sera ajoutée à la catégorie dans le front office'];
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']                     = ['Page de redirection', 'Ici, vous pouvez choisir la page à laquelle les visiteurs seront redirigés lorsque vous cliquez sur un lien de catégorie dans le modèle d\'actualité.'];
$GLOBALS['TL_LANG']['tl_news_category']['jumpToNews']                 = ['Redéfinir les paramètres pour les nouvelles dans cette catégorie', 'Configurez les redirections pour les nouvelles dans cette catégorie.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_archive'] = ['Archives des actualités', 'Sélectionnez une archive de nouvelles.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_jumpTo']       = ['Page de redirection de catégorie', 'Sélectionnez une page sur laquelle un visiteur sera redirigé lorsqu\'un lien de catégorie est cliqué dans le modèle de nouvelles.'];
$GLOBALS['TL_LANG']['tl_news_category']['news_category_news_jumpTo']  = ['Page de redirection des nouvelles', 'Sélectionnez une page de redirection pour les nouvelles dans cette catégorie et les archives de nouvelles.'];
$GLOBALS['TL_LANG']['tl_news_category']['published']                  = ['Publier la catégorie', 'Rendre la catégorie visible sur le site internet.'];

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']    = 'Titre et alias';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend'] = 'Paramètres de redirection';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']  = 'Paramètres de publication';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']        = ['Nouvelle catégorie', 'Créer une nouvelle catégorie'];
$GLOBALS['TL_LANG']['tl_news_category']['show']       = ['Détails de la catégorie', 'Afficher les détails de la catégorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['edit']       = ['Éditer la catégorie', 'Éditer la catégorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['cut']        = ['Déplacer la catégorie', 'Déplacer la catégorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copy']       = ['Dupliquer la catégorie', 'Dupliquer la catégorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'] = ['Dupliquer avec les sous-catégories', 'Dupliquer la catégorie ID %s avec ses sous-catégories'];
$GLOBALS['TL_LANG']['tl_news_category']['delete']     = ['Supprimer la catégorie', 'Supprimer la catégorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['toggle']     = ['Publier/Dépublier cette catégorie', 'Publier/Dépublier la catégorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteafter'] = ['Coller après', 'Coller après la catégorie ID %s'];
$GLOBALS['TL_LANG']['tl_news_category']['pasteinto']  = ['Coller dedans', 'Coller dedans la catégorie ID %s'];
