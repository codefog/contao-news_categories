<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Widget\NewsCategoriesPickerWidget;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AjaxListener
{
    /**
     * On execute the post actions
     *
     * @param string        $action
     * @param DataContainer $dc
     */
    public function onExecutePostActions($action, DataContainer $dc)
    {
        if ($action === 'reloadNewsCategoriesWidget') {
            $this->reloadNewsCategoriesWidget($dc);
        }
    }

    /**
     * Reload the news categories widget
     *
     * @param DataContainer $dc
     */
    private function reloadNewsCategoriesWidget(DataContainer $dc)
    {
        $intId = Input::get('id');
        $strField = $dc->inputName = Input::post('name');

        // Handle the keys in "edit multiple" mode
        if (Input::get('act') === 'editAll') {
            $intId = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $strField);
            $strField = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $strField);
        }

        $dc->field = $strField;

        // The field does not exist
        if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField])) {
            System::log('Field "' . $strField . '" does not exist in DCA "' . $dc->table . '"', __METHOD__, TL_ERROR);
            throw new BadRequestHttpException('Bad request');
        }

        $row = null;
        $value = null;

        // Load the value
        if (Input::get('act') !== 'overrideAll' && $intId > 0 && Database::getInstance()->tableExists($dc->table)) {
            $row = Database::getInstance()->prepare("SELECT * FROM " . $dc->table . " WHERE id=?")
                ->execute($intId);

            // The record does not exist
            if ($row->numRows < 1) {
                System::log('A record with the ID "' . $intId . '" does not exist in table "' . $dc->table . '"', __METHOD__, TL_ERROR);
                throw new BadRequestHttpException('Bad request');
            }

            $value = $row->$strField;
            $dc->activeRecord = $row;
        }

        // Call the load_callback
        if (is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField]['load_callback'] as $callback) {
                if (is_array($callback)) {
                    $value = System::importStatic($callback[0])->{$callback[1]}($value, $dc);
                } elseif (is_callable($callback)) {
                    $value = $callback($value, $dc);
                }
            }
        }

        // Set the new value
        $value = Input::post('value', true);

        /** @var NewsCategoriesPickerWidget $strClass */
        $strClass = $GLOBALS['BE_FFL']['newsCategoriesPicker'];

        /** @var NewsCategoriesPickerWidget $objWidget */
        $objWidget = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$strField], $dc->inputName, $value, $strField, $dc->table, $dc));

        throw new ResponseException(new Response($objWidget->generate()));
    }
}
