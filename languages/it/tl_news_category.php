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
 * @author  Marco Damian <info@zod.it>
 * @license LGPL
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_news_category']['title']     = array('Titolo', 'Nome della categoria per esteso.');
$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'] = array('Titolo in Frontend', 'Puoi scegliere un titolo diverso da far apparire in front end.');
$GLOBALS['TL_LANG']['tl_news_category']['alias']     = array('Alias', 'Testo univoco per il link, che può essere usato al posto dell\'ID.');
$GLOBALS['TL_LANG']['tl_news_category']['published'] = array('Pubblica categoria', 'Rende attiva la categoria nel sito web.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_category']['title_legend']   = 'Titolo e alias';
$GLOBALS['TL_LANG']['tl_news_category']['publish_legend'] = 'Parametri di pubblicazione';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_news_category']['new']    = array('Nuova categoria', 'Crea una nuova categoria di news');
$GLOBALS['TL_LANG']['tl_news_category']['show']   = array('Dettagli categoria', 'Mostra i dettagli della categoria ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['edit']   = array('Modifica categoria', 'Modifica la categoria ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['copy']   = array('Duplica categoria', 'Duplica la categoria ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['delete'] = array('Elimina categoria', 'Elimina la categoria ID %s');
$GLOBALS['TL_LANG']['tl_news_category']['toggle'] = array('Pubblicazione categoria', 'Attiva/disattiva la categoria ID %s');
