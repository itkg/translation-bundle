<?php

namespace Itkg\TranslationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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
class TranslationConverterCommand extends ContainerAwareCommand
{
    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Finder      $finder
     * @param Filesystem  $filesystem
     * @param null|string $name
     */
    public function __construct(Finder $finder, Filesystem $filesystem, $name = null)
    {
        $this->finder = $finder;
        $this->filesystem = $filesystem;

        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

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
        $path = $input->getOption('path');
        $inputFormat = $input->getOption('input');
        $outputFormat = $input->getOption('output') ?: 'xliff';

        $catalogs = [];

        if (!$inputFormat) {
            throw new \InvalidArgumentException('You must specify a --input format option.');
        }

        if (!$path || !$this->filesystem->exists($path)) {
            throw new \InvalidArgumentException('You must specify a valid --path option.');
        }

        $dumper = $this->getDumper($outputFormat);
        $this->getTranslationWriter()->addDumper($outputFormat, $dumper);

        $files = $this->finder->files()->name('/[a-z]+\.[a-z]{2}\.'.$inputFormat.'/')->in($path);

        foreach ($files as $file) {
            list($domain, $language) = explode('.', $file->getFilename());
            if ($input->getOption('domain') && $domain !== $input->getOption('domain')) {
                continue;
            }
            $output->writeln(sprintf('Starts importing file %s', $file->getRealPath()));
            try {
                $msgCatalog = $this->getLoader($inputFormat)->load($file->getRealPath(), $language, $domain);

                $messages = $msgCatalog->all();

                if (!$messages) {
                    $output->writeln('No translations found in this file.');

                    continue;
                }

                if (isset($catalogs[$language])) {
                    $catalogs[$language]->addCatalogue($msgCatalog);
                } else {
                    $catalogs[$language] = $msgCatalog;
                }

                $output->writeln('Translation file saved.');
            } catch (\Exception $e) {
                $output->writeln(sprintf('An error has occured while trying to write translations: %s', $e->getMessage()));
            }
        }

        /** @var MessageCatalogue $catalog */
        foreach ($catalogs as $catalog) {
            $this->getTranslationWriter()->writeTranslations($catalog, $outputFormat, array(
                    'path' => $this->getTranslationPath($input->getOption('output-path')))
            );
        }


    }

    /**
     * Returns Symfony translation writer service
     *
     * @return TranslationWriter
     */
    protected function getTranslationWriter()
    {
        return $this->getContainer()->get('translation.writer');
    }

    /**
     * Returns Symfony requested format loader
     *
     * @param string $format
     *
     * @return \Symfony\Component\Translation\Loader\LoaderInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function getLoader($format)
    {
        $service = sprintf('translation.loader.%s', $format);

        if (!$this->getContainer()->has($service)) {
            throw new \InvalidArgumentException(sprintf('Unable to find Symfony Translation loader for format "%s"', $format));
        }

        return $this->getContainer()->get($service);
    }

    /**
     * Returns Symfony requested format dumper
     *
     * @param string $format
     *
     * @return \Symfony\Component\Translation\Dumper\DumperInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function getDumper($format)
    {
        $service = sprintf('translation.dumper.%s', $format);

        if (!$this->getContainer()->has($service)) {
            throw new \InvalidArgumentException(sprintf('Unable to find Symfony Translation dumper for format "%s"', $format));
        }

        return $this->getContainer()->get($service);
    }

    /**
     * Returns translation path
     *
     * @param string $outputPath
     *
     * @return string
     */
    protected function getTranslationPath($outputPath = null)
    {
        if ($outputPath) {
            return $outputPath;
        }

        return $this->getContainer()->get('kernel')->getRootDir() . '/Resources/translations';
    }
}
