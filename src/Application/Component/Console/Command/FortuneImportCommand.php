<?php

namespace Application\Component\Console\Command;

use Application\Exception\RuntimeException;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FortuneImportCommand extends AbstractCommand
{
    use LockableTrait;

    protected function configure()
    {
        $this->setName('fortune-import');

        $this->setDescription('Import fortune files.');

        $name        = 'input-dir';
        $shortcut    = null;
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Input directory.';
        $default     = null;

        $this->addOption($name, $shortcut, $mode, $description, $default);

        $name        = 'output-dir';
        $shortcut    = null;
        $mode        = InputOption::VALUE_REQUIRED;
        $description = 'Output directory.';
        $default     = null;

        $this->addOption($name, $shortcut, $mode, $description, $default);

        return $this;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $inputDir  = $input->getOption('input-dir');
        $outputDir = $input->getOption('output-dir');

        if (!$this->lock()) {
            $message = 'The script is already running in another process.';
            throw new RuntimeException($message);
        }

        return $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputDir  = $input->getOption('input-dir');
        $outputDir = $input->getOption('output-dir');

        $output->writeln($inputDir);
        $output->writeln($outputDir);

        return $this;
    }
}
