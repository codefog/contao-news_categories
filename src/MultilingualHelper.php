<?php

namespace Codefog\NewsCategoriesBundle\Model;

use Contao\System;

class MultilingualHelper
{
    /**
     * Return true if the multilingual features are active
     *
     * @return bool
     */
    public static function isActive()
    {
        return array_key_exists('Terminal42DcMultilingualBundle', System::getContainer()->getParameter('kernel.bundles'));
    }
}
