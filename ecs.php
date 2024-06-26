<?php
declare(strict_types=1);

use Ctw\Qa\EasyCodingStandard\Config\ECSConfig\DefaultFileExtensions;
use Ctw\Qa\EasyCodingStandard\Config\ECSConfig\DefaultIndentation;
use Ctw\Qa\EasyCodingStandard\Config\ECSConfig\DefaultLineEnding;
use Ctw\Qa\EasyCodingStandard\Config\ECSConfig\DefaultRules;
use Ctw\Qa\EasyCodingStandard\Config\ECSConfig\DefaultRulesWithConfiguration;
use Ctw\Qa\EasyCodingStandard\Config\ECSConfig\DefaultSets;
use Ctw\Qa\EasyCodingStandard\Config\ECSConfig\DefaultSkip;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {

    $fileExtensions = new DefaultFileExtensions();
    $indentation    = new DefaultIndentation();
    $lineEnding     = new DefaultLineEnding();
    $rules          = new DefaultRules();
    $rulesConfig    = new DefaultRulesWithConfiguration();
    $sets           = new DefaultSets();
    $skip           = new DefaultSkip();

    $ecsConfig->fileExtensions($fileExtensions());

    // @phpstan-ignore-next-line
    $ecsConfig->indentation($indentation());

    $ecsConfig->lineEnding($lineEnding());

    $ecsConfig->paths(
        [
            sprintf('%s/bin', APP_PATH_ROOT),
            sprintf('%s/src', APP_PATH_ROOT),
            sprintf('%s/bootstrap.php', APP_PATH_ROOT),
            sprintf('%s/consts.php', APP_PATH_ROOT),
            sprintf('%s/ecs.php', APP_PATH_ROOT),
            sprintf('%s/rector.php', APP_PATH_ROOT),
        ]
    );

    $ecsConfig->sets($sets());

    $ecsConfig->rules($rules());

    $ecsConfig->rulesWithConfiguration($rulesConfig());

    $ecsConfig->skip($skip());
};
