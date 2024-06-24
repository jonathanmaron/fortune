<?php
declare(strict_types=1);

use Ctw\Qa\Rector\Config\RectorConfig\DefaultFileExtensions;
use Ctw\Qa\Rector\Config\RectorConfig\DefaultSets;
use Ctw\Qa\Rector\Config\RectorConfig\DefaultSkip;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {

    $fileExtensions = new DefaultFileExtensions();
    $sets           = new DefaultSets();
    $skip           = new DefaultSkip();

    $rectorConfig->fileExtensions($fileExtensions());

    $rectorConfig->sets($sets());

    $rectorConfig->paths(
        [
            sprintf('%s/bin', APP_PATH_ROOT),
            sprintf('%s/src', APP_PATH_ROOT),
            sprintf('%s/bootstrap.php', APP_PATH_ROOT),
            sprintf('%s/consts.php', APP_PATH_ROOT),
            sprintf('%s/ecs.php', APP_PATH_ROOT),
            sprintf('%s/rector.php', APP_PATH_ROOT),
        ]
    );

    $rectorConfig->skip($skip());
};
