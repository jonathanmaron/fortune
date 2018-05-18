<?php

namespace Application\Component\Console\Command;

use Application\Fortune\Fortune;
use Interop\Container\ContainerInterface;

class CommandFactory
{
    public function __invoke(ContainerInterface $container = null, $requestedName = null, array $options = null)
    {
        $path = sprintf('%s/data', APPLICATION_ROOT);

        $fortune = new Fortune();
        $fortune->setFortunePath("{$path}/fortune");
        $fortune->setIndexPath("{$path}/index");

        $command = new $requestedName;
        $command->setFortune($fortune);

        return $command;
    }
}
