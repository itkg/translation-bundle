<?php

namespace Itkg\TranslationBundle\Command;

use Itkg\TranslationBundle\Extractor\TranslationExtractor;
use Itkg\TranslationBundle\Writer\MessageCatalogueWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;

/**
 * Class TranslationConverterCommand
 */
class TranslationConverterCommand extends Command
{
    /**
     * @var TranslationExtractor
     */
    private $extractor;

    /**
     * @var MessageCatalogueWriter
     */
    private $writer;

    /**
     * @param TranslationExtractor   $extractor
     * @param MessageCatalogueWriter $writer
     * @param null|string            $name
     */
    public function __construct(TranslationExtractor $extractor, MessageCatalogueWriter $writer, $name = null)
    {
        $this->extractor = $extractor;
        $this->writer = $writer;

        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('itkg:translation:convert')
            ->setDescription('Translation convert command from an input format to another format')
            ->setHelp('You must specify a path using the --path option.')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Specify a path of files')
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'Specifiy a input translation format')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Specifiy an output translation format (default: xliff)')
            ->addOption('domain', null, InputOption::VALUE_OPTIONAL, 'All domains if not specified')
            ->addOption('output-path', null, InputOption::VALUE_OPTIONAL, 'Specify a path of output translations');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $outputFormat = $input->getOption('output') ?: 'xliff';

        $messageCatalogues = $this->extractor->extract(
            $input->getOption('path'),
            $input->getOption('input'),
            $input->getOption('domain')
        );

        $this->writer->write($messageCatalogues, $outputFormat, $input->getOption('output-path'));

        $output->writeln(
            sprintf(
                'Conversion from %s to %s finished. Translations are available in %s',
                $input->getOption('input'),
                $input->getOption('output'),
                $input->getOption('path')
            )
        );
    }
}
