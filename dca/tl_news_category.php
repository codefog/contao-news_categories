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
 * Load tl_news_archive language file
 */
\System::loadLanguageFile('tl_news_archive');

/**
 * Table tl_news_category
 */
$GLOBALS['TL_DCA']['tl_news_category'] = array
(

    // Config
    'config' => array
    (
        'label'                       => $GLOBALS['TL_LANG']['tl_news_archive']['categories'][0],
        'dataContainer'               => 'Table',
        'enableVersioning'            => true,
        'onload_callback' => array
        (
            array('tl_news_category', 'checkPermission')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index',
                'alias' => 'index',
            )
        ),
        'backlink'                    => 'do=news'
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 5,
            'icon'                    => 'system/modules/news_categories/assets/icon.png',
            'paste_button_callback'   => array('tl_news_category', 'pasteCategory'),
            'panelLayout'             => 'search'
        ),
        'label' => array
        (
            'fields'                  => array('title', 'frontendTitle'),
            'format'                  => '%s <span style="padding-left:3px;color:#b3b3b3;">[%s]</span>',
            'label_callback'          => array('tl_news_category', 'generateLabel')
        ),
        'global_operations' => array
        (
            'toggleNodes' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['toggleAll'],
                'href'                => 'ptg=all',
                'class'               => 'header_toggle'
            ),
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_news_category']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.gif'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_news_category']['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'copyChilds' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_news_category']['copyChilds'],
                'href'                => 'act=paste&amp;mode=copy&amp;childs=1',
                'icon'                => 'copychilds.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'cut' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_news_category']['cut'],
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_news_category']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.gif',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_news_category']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.gif'
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_news_category']['toggle'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_news_category', 'toggleIcon')
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend},title,alias,frontendTitle,cssClass;{redirect_legend:hide},jumpTo;{publish_legend},published'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_news_category']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'frontendTitle' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_news_category']['frontendTitle'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'alias' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_news_category']['alias'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'alias', 'unique'=>true, 'spaceToUnderscore'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'save_callback' => array
            (
                array('tl_news_category', 'generateAlias')
            ),
            'sql'                     => "varbinary(128) NOT NULL default ''"
        ),
        'cssClass' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_news_category']['cssClass'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>128, 'tl_class'=>'w50'),
            'sql'                     => "varchar(128) NOT NULL default ''",
        ),
        'jumpTo' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_news_category']['jumpTo'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'eval'                    => array('fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default '0'",
            'relation'                => array('type'=>'hasOne', 'load'=>'eager', 'table'=>'tl_page')
        ),
        'published' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_news_category']['published'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'sql'                     => "char(1) NOT NULL default ''"
        )
    )
);

/**
 * Enable multilingual features
 */
if (\NewsCategories\NewsCategories::checkMultilingual()) {

    // Config
    $GLOBALS['TL_DCA']['tl_news_category']['config']['dataContainer'] = 'Multilingual';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['language'] = \NewsCategories\NewsCategories::getAvailableLanguages();
    $GLOBALS['TL_DCA']['tl_news_category']['config']['langColumn'] = 'language';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['langPid'] = 'lid';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['fallbackLang'] = \NewsCategories\NewsCategories::getFallbackLanguage();
    $GLOBALS['TL_DCA']['tl_news_category']['config']['sql']['keys']['language'] = 'index';
    $GLOBALS['TL_DCA']['tl_news_category']['config']['sql']['keys']['lid'] = 'index';

    // Fields
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['language']['sql'] = "varchar(2) NOT NULL default ''";
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['lid']['sql'] = "int(10) unsigned NOT NULL default '0'";
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['title']['eval']['translatableFor'] = '*';
    $GLOBALS['TL_DCA']['tl_news_category']['fields']['frontendTitle']['eval']['translatableFor'] = '*';
}

class tl_news_category extends Backend
{
	/**
	 * Check permissions
	 */
	public function checkPermission()
	{
		$this->import('BackendUser', 'User');

		if($this->User->isAdmin) {
			return;
		}

		if(!$this->User->newscategories) {
			$this->redirect('contao/main.php?act=error');
		}

		// Set root IDs
		if($this->User->newscategories_default == null &&  $this->User->newscategories_default == '') {
			$root = array(0);
		} else {
			$root = $this->User->newscategories_default;
		}

		$GLOBALS['TL_DCA']['tl_news_category']['list']['sorting']['root'] = $root;

		// Check permissions to add categories
		if(!$this->User->hasAccess('create', 'newscategoriesp'))
		{
			$GLOBALS['TL_DCA']['tl_news_category']['config']['closed'] = true;
			unset($GLOBALS['TL_DCA']['tl_news_category']['list']['operations']['copy']);
			unset($GLOBALS['TL_DCA']['tl_news_category']['list']['operations']['copyChilds']);
		}
		// Check permissions to delete categories
		if(!$this->User->hasAccess('delete', 'newscategoriesp'))
		{
			unset($GLOBALS['TL_DCA']['tl_news_category']['list']['operations']['delete']);
		}

		// get child records
		$objDb = \Database::getInstance();
		$arrAllowed = array();
		foreach($root as $pid)
		{
			$arrAllowed = array_merge($arrAllowed, $objDb->getChildRecords($pid, 'tl_news_category'));
		}

		// Check current action
		switch (Input::get('act')) {
			case 'create':
			case 'paste' :
				if(!$this->User->hasAccess('create', 'newscategoriesp'))
				{
					$this->log('Not enough permissions to ' . Input::get('act') . ' news category ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
					break;
				}

			case 'select':
				// Allow
				break;

			case 'edit':
				// Dynamically add the record to the user profile
				if (!in_array(Input::get('id'), $root))
				{
					$arrNew = $this->Session->get('new_records');

					if (is_array($arrNew['tl_news_category']) && in_array(Input::get('id'), $arrNew['tl_news_category']))
					{
						// Add permissions on user level
						if ($this->User->inherit == 'custom' || !$this->User->groups[0])
						{
							$objUser = $this->Database->prepare("SELECT newscategoriesp FROM tl_user WHERE id=?")
								->limit(1)
								->execute($this->User->id);

							$arrNewsCategoriesp = deserialize($objUser->newscategoriesp);

							if (is_array($arrNewsCategoriesp) && in_array('create', $arrNewsCategoriesp))
							{
								$arrNewsCategories = deserialize($objUser->newscategories);
								$arrNewsCategories[] = Input::get('id');

								$this->Database->prepare("UPDATE tl_user SET news=? WHERE id=?")
									->execute(serialize($arrNewsCategories), $this->User->id);
							}
						}

						// Add permissions on group level
						elseif ($this->User->groups[0] > 0)
						{
							$objGroup = $this->Database->prepare("SELECT newscategoriesp FROM tl_user_group WHERE id=?")
								->limit(1)
								->execute($this->User->groups[0]);

							$arrNewsCategoriesp = deserialize($objGroup->newp);

							if (is_array($arrNewsCategoriesp) && in_array('create', $arrNewsCategoriesp))
							{
								$arrNewsCategories = deserialize($objGroup->newscategories);
								$arrNewsCategories[] = Input::get('id');

								$this->Database->prepare("UPDATE tl_user_group SET news=? WHERE id=?")
									->execute(serialize($arrNewsCategories), $this->User->groups[0]);
							}
						}

						// Add new element to the user object
						$root[] = Input::get('id');
						$this->User->newscategories = $root;
					}
				}
			break;

			case 'copy':
				if(!$this->User->hasAccess('create', 'newscategoriesp'))
				{
					$this->log('Not enough permissions to ' . Input::get('act') . ' news category ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
					break;
				}
			case 'delete':
				if(!$this->User->hasAccess('delete', 'newscategoriesp'))
				{
					$this->log('Not enough permissions to ' . Input::get('act') . ' news category ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
					break;
				}
			case 'show':
				if (!in_array(Input::get('id'), $arrAllowed) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'newscategoriesp')))
				{
					$this->log(
						'Not enough permissions to ' . Input::get('act') . ' news category ID "' . Input::get('id') . '"',
						__METHOD__,
						TL_ERROR
					);
					$this->redirect('contao/main.php?act=error');
				}
				break;

			case 'editAll':
			case 'deleteAll':
				if(!$this->User->hasAccess('delete', 'newscategoriesp'))
				{
					$this->log('Not enough permissions to ' . Input::get('act') . ' news category ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
					break;
				}
			case 'overrideAll':
				$session = $this->Session->getData();
				if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'newscategoriesp')) {
					$session['CURRENT']['IDS'] = array();
				} else {
					$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
				}
				$this->Session->setData($session);
				break;

			default:
				if (strlen(Input::get('act'))) {
					$this->log('Not enough permissions to ' . Input::get('act') . ' news category ', __METHOD__, TL_ERROR);
					$this->redirect('contao/main.php?act=error');
				}
				break;
		}
	}

    /**
     * Return the paste category button
     * @param \DataContainer
     * @param array
     * @param string
     * @param boolean
     * @param array
     * @return string
     */
    public function pasteCategory(DataContainer $dc, $row, $table, $cr, $arrClipboard=null)
    {
		// Set root IDs
		if($this->User->newscategories_default == null &&  $this->User->newscategories_default == '')
		{
			$root = array(0);
		} else
		{
			$root = $this->User->newscategories_default;
		}

		// get child records
		$objDb = \Database::getInstance();
		$arrAllowed = array();
		foreach($root as $pid)
		{
			$arrAllowed = array_merge($arrAllowed, $objDb->getChildRecords($pid, 'tl_news_category'));
		}


		$disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id'])))) {
            $disablePA = true;
            $disablePI = true;
        }

        $return = '';

        // Return the buttons
        $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));
        $imagePasteInto = Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']));

		// disallow paste after root
		if($this->User->hasAccess('create', 'newscategoriesp') && !in_array($row['id'], $arrAllowed))
		{
			$disablePA = true;
		}

        if ($row['id'] > 0) {
            $return = $disablePA ? Image::getHtml('pasteafter_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
        }

        return $return.($disablePI ? Image::getHtml('pasteinto_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ');
    }

    /**
     * Add the correct indentation
     * @param array
     * @param string
     * @param object
     * @param string
     * @return string
     */
    public function generateLabel($arrRow, $strLabel, $objDca, $strAttributes)
    {
        return \Image::getHtml('iconPLAIN.gif', '', $strAttributes) . ' ' . $strLabel;
    }

    /**
     * Auto-generate the category alias if it has not been set yet
     * @param mixed
     * @param \DataContainer
     * @return string
     * @throws \Exception
     */
    public function generateAlias($varValue, DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if (!strlen($varValue)) {
            $autoAlias = true;
            $strTitle = $dc->activeRecord->title;

            // Use the frontend title if available
            if (strlen($dc->activeRecord->frontendTitle)) {
                $strTitle = $dc->activeRecord->frontendTitle;
            }

            $varValue = standardize($this->restoreBasicEntities($strTitle));
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_news_category WHERE alias=?")
                                   ->execute($varValue);

        // Check whether the category alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }

    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
    }

    /**
     * Publish/unpublish a category
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        $objVersions = new Versions('tl_news_category', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_news_category']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_news_category']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_news_category SET tstamp=". time() .", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
                       ->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_news_category.id='.$intId.'" has been created'.$this->getParentEntries('tl_news_category', $intId), __METHOD__, TL_GENERAL);
    }
}
