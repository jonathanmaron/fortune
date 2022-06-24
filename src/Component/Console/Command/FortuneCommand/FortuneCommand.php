<?php
declare(strict_types=1);

namespace App\Component\Console\Command\FortuneCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FortuneCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->configureCommand();
        $this->configureWordwrap();
        $this->configureLength();
        $this->configureWait();
        $this->configureAuthor();
        $this->configureShort();
        $this->configureLong();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->initializeWordwrap($input);
        $this->initializeLength($input);
        $this->initializeWait($input);
        $this->initializeAuthor($input);
        $this->initializeShort($input);
        $this->initializeLong($input);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fortune = $this->getFortune();
        $length  = $this->getLength();
        $author  = $this->getAuthor();

        if ($this->getShort()) {
            return $this->render($output, $fortune->getRandomShortFortune());
        }

        if ($this->getLong()) {
            return $this->render($output, $fortune->getRandomLongFortune());
        }

        if ($length > 0) {
            return $this->render($output, $fortune->getRandomFortuneByLength($length));
        }

        if (strlen($author) > 0) {
            return $this->render($output, $fortune->getRandomFortuneByAuthor($author));
        }

        return $this->render($output, $fortune->getRandomFortune());
    }
}
