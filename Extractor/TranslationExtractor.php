<?php

namespace Itkg\TranslationBundle\Extractor;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class TranslationExtractor
 */
class TranslationExtractor extends ContainerAware
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MessageCatalogue[]
     */
    private $messageCatalogues = [];

    /**
     * @param Finder     $finder
     * @param Filesystem $filesystem
     */
    public function __construct(Finder $finder, Filesystem $filesystem)
    {
        $this->finder = $finder;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string      $path
     * @param string      $format
     * @param null|string $domain
     *
     * @return MessageCatalogue[]
     */
    public function extract($path, $format, $domain = null)
    {
        $this->messageCatalogues = [];
        if (!$this->filesystem->exists($path)) {
            throw new \RuntimeException(sprintf('Path %s does not exist', $path));
        }
        $files = $this->finder->files()->name('/[a-z]+\.[a-z]{2}\.'.$format.'/')->in($path);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            list($translationDomain, $language) = explode('.', $file->getFilename());
            if ($domain && $domain !== $translationDomain) {
                continue;
            }
            $this->addMessageCatalogue($this->getLoader($format)->load($file->getRealPath(), $language, $translationDomain), $language);
        }
        return $this->messageCatalogues;
    }

    /**
     * @param MessageCatalogue $messageCatalogue
     * @param string           $language
     */
    private function addMessageCatalogue(MessageCatalogue $messageCatalogue, $language)
    {
        if (!$messageCatalogue->all()) {
            return;
        }

        if (isset($this->messageCatalogues[$language])) {
            $this->messageCatalogues[$language]->addCatalogue($messageCatalogue);
        } else {
            $this->messageCatalogues[$language] = $messageCatalogue;
        }
    }

    /**
     * @param string $format
     *
     * @return LoaderInterface
     */
    private function getLoader($format)
    {
        $service = sprintf('translation.loader.%s', $format);

        if (!$this->container->has($service)) {
            throw new \InvalidArgumentException(sprintf('Unable to find Symfony Translation loader for format "%s"', $format));
        }

        return $this->container->get($service);
    }
}
