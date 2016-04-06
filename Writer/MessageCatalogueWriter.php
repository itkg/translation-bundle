<?php

namespace Itkg\TranslationBundle\Writer;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;

/**
 * Class MessageCatalogWriter
 */
class MessageCatalogueWriter extends ContainerAware
{
    /**
     * @var TranslationWriter
     */
    private $translationWriter;

    /**
     * @param TranslationWriter $translationWriter
     */
    public function __construct(TranslationWriter $translationWriter)
    {
        $this->translationWriter = $translationWriter;
    }

    /**
     * @param array       $messageCatalogues
     * @param string      $format
     * @param null|string $path
     */
    public function write(array $messageCatalogues, $format, $path = null)
    {
        $this->translationWriter->addDumper($format, $this->getDumper($format));

        /** @var MessageCatalogue $messageCatalogue */
        foreach ($messageCatalogues as $messageCatalogue) {
            $this->translationWriter->writeTranslations($messageCatalogue, $format, array(
                'path' => $this->getTranslationPath($path))
            );
        }
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
    private function getDumper($format)
    {
        $service = sprintf('translation.dumper.%s', $format);

        if (!$this->container->has($service)) {
            throw new \InvalidArgumentException(sprintf('Unable to find Symfony Translation dumper for format "%s"', $format));
        }

        return $this->container->get($service);
    }

    /**
     * Returns translation path
     *
     * @param string $outputPath
     *
     * @return string
     */
    private function getTranslationPath($outputPath = null)
    {
        if ($outputPath) {
            return $outputPath;
        }

        return $this->container->get('kernel')->getRootDir() . '/Resources/translations';
    }
}
