<?php
declare(strict_types=1);

namespace App\Component\Console\Command\PurgeCommand;

use App\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PurgeCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->configureCommand();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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
            return 0;
        }
        $output->writeln('');

        array_walk($paths, function ($path) use ($filesystem, $output) {
            $format  = 'Purged %s';
            $message = sprintf($format, $path);
            $output->writeln($message);
            $filesystem->remove($path);
            $filesystem->mkdir($path);
        });

        $lns = [
            '',
            'Now execute:',
            '',
            'fortune import --path=import/json ; fortune index ; fortune',
            '',
        ];
        $output->writeln($lns);

        return 0;
    }
}
