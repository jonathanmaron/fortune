<?php
declare(strict_types=1);

namespace Application\Component\Console\Command\PurgeCommand;

use Application\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PurgeCommand extends AbstractCommand
{
    protected function configure(): self
    {
        $this->setName('purge');

        $this->setDescription('Purge the data directories');

        $this->setHelp('@todo: The <info>command</info> command. Example: <info>command</info>.');

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): self
    {
        $filesystem = new Filesystem();
        $fortune    = $this->getFortune();

        $paths = [
            $fortune->getFortunePath(),
            $fortune->getIndexPath(),
        ];

        $output->writeln('You are about to purge:');
        $output->writeln('');
        $output->writeln($paths);
        $output->writeln('');

        $helper   = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to continue? [Y|N] ', false);
        if (!$helper->ask($input, $output, $question)) {
            return $this;
        }
        $output->writeln('');

        array_walk($paths, function ($path) use ($filesystem, $output) {
            $format  = 'Purged %s';
            $message = sprintf($format, $path);
            $output->writeln($message);
            $filesystem->remove($path);
            $filesystem->mkdir($path);
        });

        $output->writeln('');
        $output->writeln('Now execute commands:');
        $output->writeln('');
        $output->writeln('fortune import --path=import/json');
        $output->writeln('fortune index');
        $output->writeln('');

        return $this;
    }
}
