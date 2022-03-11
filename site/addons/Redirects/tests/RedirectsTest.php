<?php

namespace Statamic\Addons\Redirects\tests;

use Illuminate\Foundation\Testing\TestCase;
use Statamic\Addons\Redirects\AutoRedirect;
use Statamic\Addons\Redirects\AutoRedirectsManager;
use Statamic\Addons\Redirects\ManualRedirect;
use Statamic\Addons\Redirects\ManualRedirectsManager;
use Statamic\Addons\Redirects\RedirectsLogger;
use Statamic\Addons\Redirects\RedirectsProcessor;
use Statamic\API\Config;
use Statamic\API\Entry;
use Statamic\API\Page;
use Statamic\API\Stache;
use Statamic\Config\Addons;
use Statamic\Config\Settings;

/**
 * @group redirects
 *
 * Functional tests for the redirects Addon.
 * Note: We cannot extend Statamic's TestCase, as we rely on the real event system.
 */
class RedirectsTest extends TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var AutoRedirectsManager
     */
    private $autoRedirectsManager;

    /**
     * @var ManualRedirectsManager
     */
    private $manualRedirectsManager;

    /**
     * @var RedirectsLogger
     */
    private $redirectsLogger;

    /**
     * @var \Statamic\Contracts\Data\Pages\Page[]
     */
    private $pages = [];

    /**
     * @var \Statamic\Contracts\Data\Entries\Entry[]
     */
    private $entries = [];

    public function setUp()
    {
        parent::setUp();

        $this->storagePath = __DIR__ . '/temp/';
        $this->redirectsLogger = new RedirectsLogger($this->storagePath);
        $this->autoRedirectsManager = new AutoRedirectsManager($this->storagePath . 'auto.yaml', $this->redirectsLogger);
        $this->manualRedirectsManager = new ManualRedirectsManager($this->storagePath . 'manual.yaml', $this->redirectsLogger);

        // Swap our services in Laravel's service container.
        $this->app->singleton(RedirectsLogger::class, function () {
            return $this->redirectsLogger;
        });

        $this->app->singleton(AutoRedirectsManager::class, function () {
            return $this->autoRedirectsManager;
        });

        $this->app->singleton(RedirectsProcessor::class, function () {
            return new RedirectsProcessor(
                $this->manualRedirectsManager,
                $this->autoRedirectsManager,
                $this->redirectsLogger,
                $this->getAddonConfig()
            );
        });

        // Create a second locale for testing multi language.
        $this->app[Settings::class]->set('system.locales.de', [
            'name' => 'German',
            'full' => 'de_DE',
            'url' => '/de',
        ]);

        $this->app[Settings::class]->save();
    }

    /**
     * @test
     * @dataProvider redirectUrlsDataProvider
     */
    public function it_should_redirect_based_on_auto_redirects($from, $to)
    {
        $redirect = (new AutoRedirect())
            ->setFromUrl($from)
            ->setToUrl($to)
            ->setContentId('1234');

        $this->autoRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get($from);

        $this->assertRedirectedTo($to);
        $this->assertEquals([$from => 1], $this->redirectsLogger->getAutoRedirects());
    }

    /**
     * @test
     * @dataProvider redirectUrlsDataProvider
     */
    public function it_should_redirect_based_on_manual_redirects($from, $to)
    {
        $redirect = (new ManualRedirect())
            ->setFrom($from)
            ->setTo($to);

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get($from);

        $this->assertRedirectedTo($to);
        $this->assertEquals([$from => 1], $this->redirectsLogger->getManualRedirects());
    }

    public function redirectUrlsDataProvider()
    {
        return [
            ['/from', '/to'],
            ['/de/from', '/de/to'],
            ['/to_external_url', 'https://gridonic.ch'],
        ];
    }

    /**
     * @test
     * @dataProvider placeholdersDataProvider
     */
    public function it_should_redirect_using_parameters($redirects, $requestUrl, $redirectedUrl)
    {
        foreach ($redirects as $fromUrl => $toUrl) {
            $redirect = (new ManualRedirect())
                ->setFrom($fromUrl)
                ->setTo($toUrl);

            $this->manualRedirectsManager->add($redirect);
        }

        $this->manualRedirectsManager->flush();

        $this->get($requestUrl);

        $this->assertRedirectedTo($redirectedUrl);
    }

    public function placeholdersDataProvider()
    {
        return [
            [
                [
                    '/foo' => '/unmatched',
                    '/news/{slug}' => '/unmatched/{slug}',
                    '/news/{year}/{month}' => '/unmatched/{year}/{month}',
                    '/news/{any}' => '/blog/{any}',
                ],
                '/news/2019/01/slug',
                '/blog/2019/01/slug',
            ],
            [
                [
                    '/news/{slug}' => '/blog/hardcoded-slug',
                    '/news/{any}' => '/blog/{any}',
                ],
                '/news/slug',
                '/blog/hardcoded-slug',
            ],
            [
                [
                    '/news/{any}' => '/blog/{any}',
                    '/news/{slug}' => '/blog/hardcoded-slug',
                ],
                '/news/slug',
                '/blog/slug',
            ],
            [
                [
                    '/foo/{any}' => '/unmatched/{any}',
                    '/news/{year}/{month}/{slug}' => '/blog/{month}/{year}/{slug}'
                ],
                '/news/2019/01/slug',
                '/blog/01/2019/slug',
            ],
        ];
    }

    /**
     * @test
     */
    public function it_should_redirect_to_the_url_of_existing_content()
    {
        $page = $this->createPage('/foo');

        $redirect = (new ManualRedirect())
            ->setFrom('/not-existing-source')
            ->setTo($page->id());

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get('/not-existing-source');

        $this->assertRedirectedTo($page->url());
    }

    /**
     * @test
     * @dataProvider timedActivationDataProvider
     */
    public function it_should_redirect_correctly_using_timed_activation($start, $end, $shouldRedirect)
    {
        $redirect = (new ManualRedirect())
            ->setFrom('/not-existing-source')
            ->setTo('/target')
            ->setStartDate($start ? new \DateTime(date('Y-m-d H:i:s', $start)) : null)
            ->setEndDate($end ? new \DateTime(date('Y-m-d H:i:s', $end)) : null);

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get('/not-existing-source');

        if ($shouldRedirect) {
            $this->assertRedirectedTo('/target');
            if ($start && $end) {
                $this->assertEquals(302, $this->response->getStatusCode());
            }
        } else {
            $this->assertResponseStatus(404);
        }
    }

    public function timedActivationDataProvider()
    {
        return [
            [time(), null, true],
            [null, strtotime('+1 minute'), true],
            [time(), strtotime('+1 minute'), true],
            [strtotime('-1 minute'), strtotime('+1 minute'), true],
            [strtotime('+1 hour'), null, false],
            [null, strtotime('-1 minute'), false],
            [strtotime('-1 minute'), strtotime('-1 minute'), false],
            [strtotime('+1 minute'), strtotime('+1 minute'), false],
        ];
    }

    /**
     * @test
     * @dataProvider queryStringsDataProvider
     */
    public function it_should_redirect_query_strings_to_the_target_url($shouldRetainQueryStrings, $queryStrings)
    {
        $redirect = (new ManualRedirect())
            ->setFrom('/not-existing-source')
            ->setTo('/target')
            ->setRetainQueryStrings($shouldRetainQueryStrings);

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get('/not-existing-source' . $queryStrings);

        $hasQueryStringsAtTargetUrl = strpos($this->response->getTargetUrl(), $queryStrings) !== false;

        if ($shouldRetainQueryStrings) {
            $this->assertTrue($hasQueryStringsAtTargetUrl);
        } else {
            $this->assertFalse($hasQueryStringsAtTargetUrl);
        }
    }

    public function queryStringsDataProvider()
    {
        return [
            [false, '?foo=bar'],
            [true, '?foo=bar'],
        ];
    }

    /**
     * @test
     * @dataProvider statusCodeDataProvider
     */
    public function it_should_redirect_using_a_correct_status_code($statusCode)
    {
        $redirect = (new ManualRedirect())
            ->setFrom('/not-existing-source')
            ->setTo('/target')
            ->setStatusCode($statusCode);

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get('/not-existing-source');

        $this->assertEquals($statusCode, $this->response->getStatusCode());
    }

    public function statusCodeDataProvider()
    {
        return [
            [301], [302],
        ];
    }

    /**
     * @test
     * @dataProvider localesDataProvider
     */
    public function it_should_redirect_correctly_based_on_locales($redirectLocale, $locale, $shouldRedirect)
    {
        $redirect = (new ManualRedirect())
            ->setFrom('/not-existing-source')
            ->setTo('/target')
            ->setLocale($redirectLocale);

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $currentLocale = site_locale();
        if ($locale) {
            site_locale($locale);
        }

        $this->get('/not-existing-source');

        if ($shouldRedirect) {
            $this->assertRedirectedTo('/target');
        } else {
            $this->assertResponseStatus(404);
        }

        site_locale($currentLocale);
    }

    public function localesDataProvider()
    {
        return [
            [null, null, true],
            [null, 'de', true],
            ['en', 'en', true],
            ['en', 'de', false],
            ['de', 'en', false],
        ];
    }

    /**
     * @test
     */
    public function it_should_create_redirects_when_the_slug_of_a_page_changes()
    {
        $parent = $this->createPage('/parent');
        $parent->in('de')->set('slug', 'vater');
        $parent->save();
        $child = $this->createPage('/parent/child');

        $parent->slug('parent-new');
        $parent->in('de')->set('slug', 'vater-neu');
        $parent->save();

        $autoRedirect = $this->autoRedirectsManager->get('/parent');
        $this->assertEquals('/parent-new', $autoRedirect->getToUrl());
        $this->assertEquals($parent->id(), $autoRedirect->getContentId());

        $this->markTestIncomplete('The Pages API does not recognize that the created parent page has a child, so the recursive creation of redirects does not work. Why? Probably a caching problem in the test environment.');

        $autoRedirect = $this->autoRedirectsManager->get('/de/vater');
        $this->assertEquals('/de/parent-neu', $autoRedirect->getToUrl());
        $this->assertEquals($parent->id(), $autoRedirect->getContentId());

        $autoRedirect = $this->autoRedirectsManager->get('/parent/child');
        $this->assertEquals('/parent-new/child', $autoRedirect->getToUrl());
        $this->assertEquals($child->id(), $autoRedirect->getContentId());
    }

    /**
     * @test
     */
    public function it_should_create_redirects_when_pages_move()
    {
        $parent1 = $this->createPage('/parent1');
        $child1 = $this->createPage('/parent1/child1');
        $child2 = $this->createPage('/parent1/child2');
        $this->createPage('/parent2');

        // Make parent1 a child of parent2.
        $parent1->uri('/parent2/parent1');
        $parent1->save();

        $autoRedirect = $this->autoRedirectsManager->get('/parent1');
        $this->assertEquals('/parent2/parent1', $autoRedirect->getToUrl());
        $this->assertEquals($parent1->id(), $autoRedirect->getContentId());

        $this->markTestIncomplete('The Pages API does not recognize that the created parent page has children, so the recursive creation of redirects does not work. Why? Probably a caching problem in the test environment.');

        $autoRedirect = $this->autoRedirectsManager->get('/parent1/child1');
        $this->assertEquals('/parent2/parent1/child1', $autoRedirect->getToUrl());
        $this->assertEquals($child1->id(), $autoRedirect->getContentId());
    }

    /**
     * @test
     */
    public function it_should_create_redirects_when_the_slug_of_an_entry_changes()
    {
        // TODO: We rely on the collection "things" of Statamic's default installation. Create our own here...
        $entry = $this->createEntry('entry');
        $entry->in('de')->set('slug', 'eintrag');
        $entry->save();

        $this->assertEquals([], $this->autoRedirectsManager->all());

        $entry->slug('entry-new');
        $entry->in('de')->set('slug', 'eintrag-neu');
        $entry->save();

        $autoRedirect = $this->autoRedirectsManager->get('/things/entry');
        $this->assertEquals('/things/entry-new', $autoRedirect->getToUrl());
        $this->assertEquals($entry->id(), $autoRedirect->getContentId());

        $autoRedirect = $this->autoRedirectsManager->get('/de/things/eintrag');
        $this->assertEquals('/de/things/eintrag-neu', $autoRedirect->getToUrl());
        $this->assertEquals($entry->id(), $autoRedirect->getContentId());
    }

    /**
     * @test
     */
    public function it_should_log_404_requests()
    {
        $this->get('/not-existing-source');

        $this->assertResponseStatus(404);

        $logs = $this->redirectsLogger->get404s();

        $this->assertEquals(['/not-existing-source' => 1], $logs);
    }

    /**
     * @test
     */
    public function it_should_not_log_manual_redirects_if_logging_is_disabled()
    {
        $this->disableRedirectsLogging();

        $redirect = (new ManualRedirect())
            ->setFrom('/from')
            ->setTo('/to');

        $this->manualRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get('/from');
        $this->assertRedirectedTo('/to');
        $this->assertEmpty($this->redirectsLogger->getManualRedirects());
    }

    /**
     * @test
     */
    public function it_should_not_log_auto_redirects_if_logging_is_disabled()
    {
        $this->disableRedirectsLogging();

        $redirect = (new AutoRedirect())
            ->setFromUrl('/from-auto')
            ->setToUrl('/to-auto')
            ->setContentId('1234');

        $this->autoRedirectsManager
            ->add($redirect)
            ->flush();

        $this->get('/from-auto');

        $this->assertRedirectedTo('/to-auto');
        $this->assertEmpty($this->redirectsLogger->getAutoRedirects());
    }

    public function createApplication()
    {
        $app = require statamic_path('/bootstrap') . '/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

    public function tearDown()
    {
        foreach ($this->pages as $page) {
            $page->delete();
        }

        foreach ($this->entries as $entry) {
            $entry->delete();
        }

        @unlink($this->storagePath . 'auto.yaml');
        @unlink($this->storagePath . 'manual.yaml');
        @unlink($this->storagePath . 'log_auto.yaml');
        @unlink($this->storagePath . 'log_manual.yaml');
        @unlink($this->storagePath . 'log_404.yaml');

        parent::tearDown();
    }

    private function getAddonConfig()
    {
        return [
            'access_roles' => [],
            'log_404_enable' => true,
            'log_redirects_enable' => true,
            'auto_redirect_enable' => true,
        ];
    }

    /**
     * @return \Statamic\Contracts\Data\Entries\Entry
     */
    private function createEntry($slug)
    {
        $entry = Entry::create($slug)
            ->collection('things')
            ->get()
            ->save();

        $this->entries[] = $entry;

        return $entry;
    }

    /**
     * @return \Statamic\Contracts\Data\Pages\Page
     */
    private function createPage($url)
    {
        $page = Page::create($url)
            ->published(true)
            ->get()
            ->save();

        $this->pages[] = $page;

        return $page;
    }

    private function disableRedirectsLogging()
    {
        $addonConfig = array_merge($this->getAddonConfig(), ['log_redirects_enable' => false]);

        $this->app->singleton(RedirectsProcessor::class, function () use ($addonConfig) {
            return new RedirectsProcessor(
                $this->manualRedirectsManager,
                $this->autoRedirectsManager,
                $this->redirectsLogger,
                $addonConfig
            );
        });
    }
}
