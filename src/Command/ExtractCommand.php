<?php

namespace Panlatent\Translator\Command;

use Panlatent\Translator\Extractor;
use Panlatent\Translator\Formatter\GettextPo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('extract', 'Extract translations from files')]
class ExtractCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('files', InputArgument::IS_ARRAY, 'Source files')
            ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'Base path')
            ->addOption('keywords', 'k', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Keywords', ['Yii::t', 'Craft::t'])
            ->addOption('default', 'd', InputOption::VALUE_REQUIRED, 'Default category', 'site')
            ->addOption('ignore', 'i', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Ignore categories', [])
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extractor = new Extractor(
            basePath: $input->getOption('base'),
            keywords: $input->getOption('keywords'),
        );
        $extractor->setDefaultCategory($input->getOption('default'));
        $extractor->setIgnoreCategories($input->getOption('ignore'));

        $files = $input->getArgument('files');
        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext === 'twig' || $ext === 'html') {
                $extractor->fromTwigFile($file);
            } elseif ($ext === 'php') {
                $extractor->fromPhpFile($file);
            }
        }

        $formatter = new GettextPo();
        $dst = $input->getOption('output');
        if (!empty($dst)) {
            file_put_contents($dst, $formatter->format($extractor->extract()));
        } else {
            $output->write($formatter->format($extractor->extract()));
        }

        return Command::SUCCESS;
    }
}