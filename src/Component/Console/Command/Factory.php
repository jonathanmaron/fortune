<?php
declare(strict_types=1);

namespace Application\Component\Console\Command;

use Application\Fortune\Fortune;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

class Factory
{
    public function __invoke(
        ContainerInterface $container = null,
        string $requestedName = '',
        array $options = []
    ): Command {

        $fortune = new Fortune();

        $path = sprintf('%s/data', APPLICATION_ROOT);

        $fortunePath = sprintf('%s/fortune', $path);
        $fortune->setFortunePath($fortunePath);

        $indexPath = sprintf('%s/index', $path);
        $fortune->setIndexPath($indexPath);

        $command = new $requestedName;
        assert($command instanceof AbstractCommand);
        $command->setFortune($fortune);

        return $command;
    }
}
