<?php

namespace Application\Component\Console\Command;

use Application\Fortune\Fortune;
use Interop\Container\ContainerInterface;
use Riimu\Kit\PHPEncoder\PHPEncoder;

class CommandFactory
{
    public function __invoke(ContainerInterface $container = null, $requestedName = null, array $options = null)
    {
        $fortune = new Fortune();
        $fortune->setPath(APPLICATION_ROOT . '/data');

        $phpEncoder = new PHPEncoder();
        $phpEncoder->setOption('array.inline', false);
        $phpEncoder->setOption('array.short', true);
        $phpEncoder->setOption('array.omit', true);
        $phpEncoder->setOption('object.format', 'export');
        $phpEncoder->setOption('string.utf8', true);
        $phpEncoder->setOption('whitespace', true);

        $command = new $requestedName;
        $command->setFortune($fortune);
        $command->setPhpEncoder($phpEncoder);

        return $command;
    }
}
