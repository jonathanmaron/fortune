<?php
declare(strict_types=1);

namespace Application\Component\Console\Command;

use Application\Fortune\Fortune;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

class Factory
{
    public function __invoke(
        ?ContainerInterface $container = null,
        ?string $requestedName = null,
        ?array $options = null
    ): Command {
        $path = sprintf('%s/data', APPLICATION_ROOT);

        $fortune = new Fortune();
        $fortune->setFortunePath("{$path}/fortune");
        $fortune->setIndexPath("{$path}/index");

        $command = new $requestedName;
        $command->setFortune($fortune);

        return $command;
    }
}
