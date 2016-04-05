<?php

namespace Itkg\TranslationBundle;

use Itkg\TranslationBundle\Command\TranslationConverterCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class TranslationCommandTest
 */
class TranslationCommandTest extends WebTestCase
{
    private $command;
    private $commandTester;
    private $exportPath;
    private $importPath;

    protected function setUp()
    {
        $this->exportPath = __DIR__.'/../../export';
        $this->importPath =  __DIR__.'/../fixtures';

        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $extractor = static::$kernel->getContainer()->get('itkg_translation.extractor.translation');
        $extractor->setContainer(static::$kernel->getContainer());
        $writer = static::$kernel->getContainer()->get('itkg_translation.writer.message_catalogue');
        $writer->setContainer(static::$kernel->getContainer());
        $this->command     = new TranslationConverterCommand(
            $extractor,
            $writer
        );
        $application = new Application(static::$kernel);
        $this->command->setApplication($application);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExport()
    {
        $this->commandTester->execute(array(
            'command' => 'itkg_translation:translation:converter',
            '--input' => 'yml',
            '--output' => 'csv',
            '--path' => $this->importPath.'/data',
            '--output-path' => $this->exportPath
        ));

        $this->assertEquals(file_get_contents($this->exportPath.'/domain.en.csv'), file_get_contents($this->importPath.'/result/domain.en.csv'));
        $this->assertEquals(file_get_contents($this->exportPath.'/messages.fr.csv'), file_get_contents($this->importPath.'/result/messages.fr.csv'));
    }

    public function testExportSpecificDomain()
    {
        $this->commandTester->execute(array(
            'command'  => 'itkg_translation:translation:converter',
            '--input'  => 'yml',
            '--output' => 'csv',
            '--domain' => 'domain',
            '--path'   => $this->importPath.'/data',
            '--output-path' => $this->exportPath.'/domain'
        ));

        $this->assertCount(1, glob($this->exportPath.'/domain'));
        $this->assertEquals(file_get_contents($this->exportPath.'/domain/domain.en.csv'), file_get_contents($this->importPath.'/result/domain.en.csv'));
    }

    public function testImport()
    {
        $this->commandTester->execute(array(
            'command' => 'itkg_translation:translation:converter',
            '--input' => 'csv',
            '--output' => 'yml',
            '--path' => $this->importPath.'/data',
            '--output-path' => $this->exportPath
        ));

        $this->assertEquals(file_get_contents($this->exportPath.'/domain.en.yml'), file_get_contents($this->importPath.'/result/domain.en.yml'));
        $this->assertEquals(file_get_contents($this->exportPath.'/messages.fr.yml'), file_get_contents($this->importPath.'/result/messages.fr.yml'));
    }
}
