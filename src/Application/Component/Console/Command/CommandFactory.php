<?php

namespace Application\Component\Console\Command;

use Application\Fortune\Fortune;
use Interop\Container\ContainerInterface;

class CommandFactory
{
    public function __invoke(ContainerInterface $container = null, $requestedName = null, array $options = null)
    {
        $fortune = new Fortune();
        $fortune->setPath(APPLICATION_ROOT . '/data');

        $command = new $requestedName;
        $command->setFortune($fortune);

        return $command;
    }
}
