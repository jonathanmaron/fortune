<?php
declare(strict_types=1);

namespace App\Component\Console\Command\PurgeCommand;

use App\Component\Filesystem\Filesystem;
use Override;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PurgeCommand extends AbstractCommand
{
    #[Override]
    protected function configure(): void
    {
        $this->configureCommand();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fortune = $this->getFortune();

        $paths = [$fortune->getFortunePath(), $fortune->getIndexPath()];

        $output->writeln('You are about to purge:');
        $output->writeln('');
        $output->writeln($paths);
        $output->writeln('');

        $helper = $this->getHelper('question');
        assert($helper instanceof QuestionHelper);
        $question = new ConfirmationQuestion('Do you want to continue? [Y|N] ', false);
        $answer   = $helper->ask($input, $output, $question);
        assert(is_bool($answer));
        if (!$answer) {
            return 0;
        }
        $output->writeln('');

        array_walk($paths, function ($path) use ($output) {
            $filesystem = new Filesystem();
            $format     = 'Purged %s';
            $message    = sprintf($format, $path);
            $output->writeln($message);
            $filesystem->remove($path);
            $filesystem->mkdir($path);
        });

        $path = sprintf('%s/import/json', dirname($fortune->getFortunePath(), 2));
        $lns  = ['', 'Now execute:', '', sprintf('fortune import --path=%s ; fortune index ; fortune', $path), ''];
        $output->writeln($lns);

        return 0;
    }
}
