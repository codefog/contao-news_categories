<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $year = date('Y');
    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header' => <<<EOF
News Categories bundle for Contao Open Source CMS.

@copyright  Copyright (c) $year, Codefog
@author     Codefog <https://codefog.pl>
@license    MIT
EOF
,
    ]);
};
