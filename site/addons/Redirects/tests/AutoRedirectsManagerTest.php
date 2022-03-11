<?php

use Statamic\Addons\Redirects\AutoRedirect;
use Statamic\Addons\Redirects\AutoRedirectsManager;
use Statamic\Addons\Redirects\RedirectsLogger;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Testing\TestCase;

/**
 * @group redirects
 * @coversDefaultClass \Statamic\Addons\Redirects\AutoRedirectsManager
 */
class AutoRedirectsManagerTest extends TestCase
{
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var AutoRedirectsManager
     */
    private $autoRedirectsManager;

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
        $this->storagePath = __DIR__ . '/temp/auto.yaml';
        $this->autoRedirectsManager = new AutoRedirectsManager($this->storagePath, $this->redirectsLogger);
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
        $redirect = (new AutoRedirect())
            ->setFromUrl('/from')
            ->setToUrl('/to')
            ->setContentId('12345');

        $this->autoRedirectsManager
            ->add($redirect)
            ->flush();

        $this->assertFileExists($this->storagePath);

        $redirects = $this->getRedirectsFromYamlFile();
        $redirect = $this->autoRedirectsManager->get('/from');

        $this->assertArrayHasKey('/from', $redirects);
        $this->assertEquals('/to', $redirects['/from']['to']);
        $this->assertEquals('12345', $redirects['/from']['content_id']);
        $this->assertInstanceOf(AutoRedirect::class, $redirect);
        $this->assertEquals('/from', $redirect->getFromUrl());
        $this->assertEquals('/to', $redirect->getToUrl());
        $this->assertEquals('12345', $redirect->getContentId());
        $this->assertEquals([$redirect], $this->autoRedirectsManager->all());
        $this->assertTrue($this->autoRedirectsManager->exists('/from'));

        $this->autoRedirectsManager
            ->remove('/from')
            ->flush();

        $redirects = $this->getRedirectsFromYamlFile();

        $this->assertEmpty($redirects);
        $this->assertEmpty($this->autoRedirectsManager->all());
        $this->assertNull($this->autoRedirectsManager->get('/from'));
        $this->assertFalse($this->autoRedirectsManager->exists('/from'));
    }

    /**
     * @test
     */
    public function it_should_optimize_chained_redirects()
    {
        $redirect1 = (new AutoRedirect())
            ->setFromUrl('/foo')
            ->setToUrl('/bar')
            ->setContentId('12345');

        $redirect2 = (new AutoRedirect())
            ->setFromUrl('/bar')
            ->setToUrl('/foo2')
            ->setContentId('9876');

        $this->autoRedirectsManager
            ->add($redirect1)
            ->add($redirect2)
            ->flush();

        $redirect = $this->autoRedirectsManager->get('/foo');

        // /foo should now directly redirect to /foo2.
        $this->assertEquals('/foo2', $redirect->getToUrl());
    }

    /**
     * @test
     */
    public function it_should_remove_redirects_where_the_source_url_equals_a_new_target_url()
    {
        $redirect1 = (new AutoRedirect())
            ->setFromUrl('/foo')
            ->setToUrl('/bar')
            ->setContentId('12345');

        $redirect2 = (new AutoRedirect())
            ->setFromUrl('/bar2')
            ->setToUrl('/foo')
            ->setContentId('9876');

        $this->autoRedirectsManager
            ->add($redirect1)
            ->add($redirect2)
            ->flush();

        // /foo now points to a content, so we should remove redirect1.
        $this->assertCount(1, $this->autoRedirectsManager->all());
        $this->assertNull($this->autoRedirectsManager->get('/foo'));
    }

    /**
     * @test
     * @covers ::removeRedirectsOfContentId
     */
    public function it_should_remove_all_redirects_from_a_given_content_id()
    {
        $redirect1 = (new AutoRedirect())
            ->setFromUrl('/foo')
            ->setToUrl('/bar')
            ->setContentId('123');

        $redirect2 = (new AutoRedirect())
            ->setFromUrl('/foo2')
            ->setToUrl('/bar2')
            ->setContentId('123');

        $redirect3 = (new AutoRedirect())
            ->setFromUrl('/foo3')
            ->setToUrl('/bar3')
            ->setContentId('999');

        $this->autoRedirectsManager
            ->add($redirect1)
            ->add($redirect2)
            ->add($redirect3)
            ->flush();

        $this->autoRedirectsManager->removeRedirectsOfContentId('123');

        $this->assertCount(1, $this->autoRedirectsManager->all());
        $this->assertNull($this->autoRedirectsManager->get('/foo'));
        $this->assertNull($this->autoRedirectsManager->get('/foo2'));
        $this->assertInstanceOf(AutoRedirect::class, $this->autoRedirectsManager->get('/foo3'));
    }

    /**
     * @test
     * @covers ::remove
     */
    public function it_should_remove_logs_when_removing_a_redirect()
    {
        $redirect = (new AutoRedirect())
            ->setFromUrl('/foo')
            ->setToUrl('/bar')
            ->setContentId('123');

        $this->autoRedirectsManager
            ->add($redirect)
            ->flush();

        $this->redirectsLogger
            ->expects($this->once())
            ->method('removeAutoRedirect')
            ->with('/foo');

        $this->autoRedirectsManager->remove('/foo');
    }

    /**
     * @test
     */
    public function it_should_not_save_a_redirect_if_the_source_equals_the_target()
    {
        $redirect = (new AutoRedirect())
            ->setFromUrl('/foo')
            ->setToUrl('/foo')
            ->setContentId('123');

        $this->autoRedirectsManager
            ->add($redirect)
            ->flush();

        $this->assertEmpty($this->autoRedirectsManager->all());
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
