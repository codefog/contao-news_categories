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
$GLOBALS['TL_LANG']['tl_news_category']['title']         = array('Titre', 'Veuillez, s\'il vous plaît, saisir un titre pour cette catégorie.');
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'] = array('Titre front office', 'Ici, vous pouvez saisir un titre de catégorie qui sera affiché en front office.');
$GLOBALS['TL_LANG']['tl_news_category']['alias']         = array('Alias', 'L\'alias de la catégorie est une référence unique qui peut être utilisé à la place de son ID numérique.');
$GLOBALS['TL_LANG']['tl_news_category']['cssClass']      = array('Classe CSS', 'Ici, vous pouvez saisir une classe CSS qui sera ajoutée à la catégorie dans le front office');
$GLOBALS['TL_LANG']['tl_news_category']['jumpTo']        = array('Page de redirection', 'Ici, vous pouvez choisir la page à laquelle les visiteurs seront redirigés lorsque vous cliquez sur un lien de catégorie dans le modèle d\'actualité.');
$GLOBALS['TL_LANG']['tl_news_category']['published']     = array('Publier la catégorie', 'Rendre la catégorie visible sur le site internet.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']    = 'Titre et alias';
$GLOBALS['TL_LANG']['tl_news_category']['redirect_legend'] = 'Paramètres de redirection';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend']  = 'Paramètres de publication';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']        = array('Nouvelle catégorie', 'Créer une nouvelle catégorie');
$GLOBALS['TL_LANG']['tl_news_category']['show']       = array('Détails de la catégorie', 'Afficher les détails de la catégorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['edit']       = array('Éditer la catégorie', 'Éditer la catégorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['cut']        = array('Déplacer la catégorie', 'Déplacer la catégorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copy']       = array('Dupliquer la catégorie', 'Dupliquer la catégorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'] = array('Dupliquer avec les sous-catégories', 'Dupliquer la catégorie ID %s avec ses sous-catégories');
$GLOBALS['TL_LANG']['tl_news_category']['delete']     = array('Supprimer la catégorie', 'Supprimer la catégorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['toggle']     = array('Publier/Dépublier cette catégorie', 'Publier/Dépublier la catégorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['pasteafter'] = array('Coller après', 'Coller après la catégorie ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['pasteinto']  = array('Coller dedans', 'Coller dedans la catégorie ID %s');
