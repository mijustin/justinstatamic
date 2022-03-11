<?php

use Statamic\Addons\Redirects\ManualRedirect;
use Statamic\Addons\Redirects\ManualRedirectsManager;
use Statamic\Addons\Redirects\RedirectsLogger;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Testing\TestCase;

/**
 * @group redirects
 * @coversDefaultClass \Statamic\Addons\Redirects\ManualRedirectsManager
 */
class ManualRedirectsManagerTest extends TestCase
{
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var ManualRedirectsManager
     */
    private $manualRedirectsManager;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectsLogger;

    public function setUp()
    {
        parent::setUp();

        $this->redirectsLogger = $this->getMockBuilder(RedirectsLogger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storagePath = __DIR__ . '/temp/manual.yaml';
        $this->manualRedirectsManager = new ManualRedirectsManager($this->storagePath, $this->redirectsLogger);
    }

    /**
     * @test
     * @covers ::add
     * @covers ::remove
     * @covers ::flush
     * @covers ::get
     * @covers ::exists
     * @covers ::all
     */
    public function it_should_store_and_return_redirects_correctly()
    {
        $redirect = (new ManualRedirect())
            ->setFrom('/from')
            ->setTo('/to')
            ->setStartDate(new DateTime('2019-01-01'))
            ->setEndDate(new DateTime('2019-12-31'))
            ->setLocale('en')
            ->setRetainQueryStrings(true);

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->assertFileExists($this->storagePath);

        $redirects = $this->getRedirectsFromYamlFile();
        $redirect = $this->manualRedirectsManager->get('/from');

        $this->assertArrayHasKey('/from', $redirects);
        $this->assertArraySubset($redirects['/from'], $redirect->toArray());
        $this->assertInstanceOf(ManualRedirect::class, $redirect);
        $this->assertEquals([$redirect], $this->manualRedirectsManager->all());
        $this->assertTrue($this->manualRedirectsManager->exists('/from'));

        $this->manualRedirectsManager
            ->remove('/from')
            ->flush();

        $redirects = $this->getRedirectsFromYamlFile();

        $this->assertEmpty($redirects);
        $this->assertEmpty($this->manualRedirectsManager->all());
        $this->assertNull($this->manualRedirectsManager->get('/from'));
        $this->assertFalse($this->manualRedirectsManager->exists('/from'));
    }

    /**
     * @test
     * @covers ::remove
     */
    public function it_should_remove_logs_when_removing_a_redirect()
    {
        $redirect = (new ManualRedirect())
            ->setFrom('/foo')
            ->setTo('/bar');

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->redirectsLogger
            ->expects($this->once())
            ->method('removeManualRedirect')
            ->with('/foo');

        $this->manualRedirectsManager->remove('/foo');
    }

    /**
     * @test
     * @covers ::add
     * @covers ::setPosition
     */
    public function it_should_add_the_redirect_at_the_correct_position()
    {
        $redirect1 = (new ManualRedirect())
            ->setFrom('/foo')
            ->setTo('/bar');

        $redirect2 = (new ManualRedirect())
            ->setFrom('/foo2')
            ->setTo('/bar2');

        $redirect3 = (new ManualRedirect())
            ->setFrom('/foo3')
            ->setTo('/bar3');

        $this->manualRedirectsManager
            ->add($redirect1)
            ->add($redirect2, 0)
            ->add($redirect3, 1)
            ->flush();

        $routes = array_keys($this->getRedirectsFromYamlFile());

        $this->assertEquals(['/foo2', '/foo3', '/foo'], $routes);

        $this->manualRedirectsManager
            ->setPosition('/foo3', 2)
            ->flush();

        $routes = array_keys($this->getRedirectsFromYamlFile());

        $this->assertEquals(['/foo2', '/foo', '/foo3'], $routes);
    }

    /**
     * @test
     */
    public function it_should_not_save_a_redirect_if_the_source_equals_the_target()
    {
        $redirect = (new ManualRedirect())
            ->setFrom('/foo')
            ->setTo('/foo');

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->assertEmpty($this->manualRedirectsManager->all());
    }

    public function tearDown()
    {
        parent::tearDown();

        @unlink($this->storagePath);
    }

    private function getRedirectsFromYamlFile()
    {
        return YAML::parse(File::get($this->storagePath));
    }
}
