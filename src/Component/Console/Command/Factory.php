<?php
declare(strict_types=1);

namespace App\Component\Console\Command;

use App\Fortune\Fortune;
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

        $fortune->setFortunePath(APP_PATH_FORTUNE);
        $fortune->setIndexPath(APP_PATH_INDEX);

        $command = new $requestedName();
        assert($command instanceof AbstractCommand);
        $command->setFortune($fortune);

        return $command;
    }
}
