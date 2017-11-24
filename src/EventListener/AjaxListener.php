<?php

/*
 * News Categories Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2017, Codefog
 * @author     Codefog <https://codefog.pl>
 * @license    MIT
 */

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Widget\NewsCategoriesPickerWidget;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AjaxListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AjaxListener constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * On execute the post actions.
     *
     * @param string        $action
     * @param DataContainer $dc
     */
    public function onExecutePostActions($action, DataContainer $dc)
    {
        if ('reloadNewsCategoriesWidget' === $action) {
            $this->reloadNewsCategoriesWidget($dc);
        }
    }

    /**
     * Reload the news categories widget.
     *
     * @param DataContainer $dc
     */
    private function reloadNewsCategoriesWidget(DataContainer $dc)
    {
        /**
         * @var Database
         * @var Input    $input
         */
        $db = $this->framework->createInstance(Database::class);
        $input = $this->framework->getAdapter(Input::class);

        $id = $input->get('id');
        $field = $dc->inputName = $input->post('name');

        // Handle the keys in "edit multiple" mode
        if ('editAll' === $input->get('act')) {
            $id = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $field);
            $field = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $field);
        }

        $dc->field = $field;

        // The field does not exist
        if (!isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$field])) {
            $this->logger->log(
                LogLevel::ERROR,
                sprintf('Field "%s" does not exist in DCA "%s"', $field, $dc->table),
                ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
            );

            throw new BadRequestHttpException('Bad request');
        }

        $row = null;
        $value = null;

        // Load the value
        if ('overrideAll' !== $input->get('act') && $id > 0 && $db->tableExists($dc->table)) {
            $row = $db->prepare('SELECT * FROM '.$dc->table.' WHERE id=?')->execute($id);

            // The record does not exist
            if ($row->numRows < 1) {
                $this->logger->log(
                    LogLevel::ERROR,
                    sprintf('A record with the ID "%s" does not exist in table "%s"', $id, $dc->table),
                    ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
                );

                throw new BadRequestHttpException('Bad request');
            }

            $value = $row->$field;
            $dc->activeRecord = $row;
        }

        // Call the load_callback
        if (is_array($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['load_callback'])) {
            /** @var System $systemAdapter */
            $systemAdapter = $this->framework->getAdapter(System::class);

            foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['load_callback'] as $callback) {
                if (is_array($callback)) {
                    $value = $systemAdapter->importStatic($callback[0])->{$callback[1]}($value, $dc);
                } elseif (is_callable($callback)) {
                    $value = $callback($value, $dc);
                }
            }
        }

        // Set the new value
        $value = $input->post('value', true);

        // Convert the selected values
        if ($value) {
            /** @var StringUtil $stringUtilAdapter */
            $stringUtilAdapter = $this->framework->getAdapter(StringUtil::class);
            $value = $stringUtilAdapter->trimsplit("\t", $value);
            $value = serialize($value);
        }

        /** @var NewsCategoriesPickerWidget $strClass */
        $strClass = $GLOBALS['BE_FFL']['newsCategoriesPicker'];

        /** @var NewsCategoriesPickerWidget $objWidget */
        $objWidget = new $strClass($strClass::getAttributesFromDca($GLOBALS['TL_DCA'][$dc->table]['fields'][$field], $dc->inputName, $value, $field, $dc->table, $dc));

        throw new ResponseException(new Response($objWidget->generate()));
    }
}
