<?php

use Statamic\Addons\Redirects\RedirectsLogger;
use Statamic\Addons\Redirects\RedirectsLogParseException;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Testing\TestCase;

/**
 * @group redirects
 * @coversDefaultClass \Statamic\Addons\Redirects\RedirectsLogger
 */
class RedirectsLoggerTest extends TestCase
{
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var RedirectsLogger
     */
    private $redirectsLogger;

    public function setUp()
    {
        parent::setUp();

        $this->storagePath = __DIR__ . '/temp/';
        $this->redirectsLogger = new RedirectsLogger($this->storagePath);
    }

    /**
     * @test
     * @covers ::log404
     * @covers ::logAutoRedirect
     * @covers ::logManualRedirect
     * @covers ::flush
     * @covers ::remove404
     */
    public function it_should_store_and_compute_log_counts_correctly()
    {
        $this->redirectsLogger
            ->log404('/404')
            ->logManualRedirect('/manual')
            ->logAutoRedirect('/auto')
            ->flush();

        $this->assertEquals(['/404' => 1], $this->redirectsLogger->get404s());
        $this->assertEquals(['/manual' => 1], $this->redirectsLogger->getManualRedirects());
        $this->assertEquals(['/auto' => 1], $this->redirectsLogger->getAutoRedirects());

        $this->assertEquals(['/404' => 1], $this->parse404LogsFromYaml());
        $this->assertEquals(['/manual' => 1], $this->parseManualLogsFromYaml());
        $this->assertEquals(['/auto' => 1], $this->parseAutoLogsFromYaml());

        $this->redirectsLogger
            ->log404('/404')
            ->flush();

        $this->assertEquals(['/404' => 2], $this->redirectsLogger->get404s());
        $this->assertEquals(['/404' => 2], $this->parse404LogsFromYaml());

        $this->redirectsLogger
            ->remove404('/404')
            ->flush();

        $this->assertEmpty($this->redirectsLogger->get404s());
        $this->assertEmpty($this->parse404LogsFromYaml());
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_parsing_manual_log_file_fails()
    {
        $this->writeInvalidYaml($this->storagePath . 'log_manual.yaml');

        $this->expectException(RedirectsLogParseException::class);

        $this->redirectsLogger->logManualRedirect('/foo');
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_parsing_auto_log_file_fails()
    {
        $this->writeInvalidYaml($this->storagePath . 'log_auto.yaml');

        $this->expectException(RedirectsLogParseException::class);

        $this->redirectsLogger->logAutoRedirect('/foo');
    }

    /**
     * @test
     */
    public function it_should_throw_exception_if_parsing_404_log_file_fails()
    {
        $this->writeInvalidYaml($this->storagePath . 'log_404.yaml');

        $this->expectException(RedirectsLogParseException::class);

        $this->redirectsLogger->log404('/foo');
    }

    public function tearDown()
    {
        parent::tearDown();

        foreach (['404', 'manual', 'auto'] as $what) {
            @unlink($this->storagePath . sprintf('log_%s.yaml', $what));
        }
    }

    private function writeInvalidYaml($file) {
        $invalidYaml = "/valid-entry: 1\n/invalid-entry-not-having-count";
        File::put($file, $invalidYaml);
    }

    private function parse404LogsFromYaml() {
        return YAML::parse(File::get($this->storagePath . 'log_404.yaml'));
    }

    private function parseManualLogsFromYaml() {
        return YAML::parse(File::get($this->storagePath . 'log_manual.yaml'));
    }

    private function parseAutoLogsFromYaml() {
        return YAML::parse(File::get($this->storagePath . 'log_auto.yaml'));
    }
}
