# WebSpider

This repository wraps Guzzle and some Symfony components providing an easy way for spidering websites.

## Requirements

- PHP >=5.5
- Guzzle >= 6.0
- Doctrine ORM >= 2.2
- Symfony Components >= 2.7

## Installation

Add `fievel/webspider` as a require dependency in your `composer.json` file:

    composer require fievel/webspider

## Usage

Extend class `WebSpiderAbstract` as needed implementing these methods:

getDataFromResponse: used to extract data from response, default behaviour treats body as plain text;

    protected function getDataFromResponse(ResponseInterface $response)
    {
        return (string) $response->getBody();
    }

parseData: used to extract data information, it's possible to initialize Symfony `DomCrawler` if needed;

    protected function parseData($data)
    {
        $this->crawler->addHtmlContent($data);

        $node = $this->crawler->filter('input');

        $value = null;
        if ($node->count() > 0) {
            $value = $node->first()->attr('value');
        }

        return $value;
    }

handleException: used to handle Guzzle exceptions;

    protected function handleException(\Exception $e)
    {
        return null;
    }

The only remaining thing to do is launch the spider created, in order to do that you can use the `SpiderManager` service.

    $manager = $this->container->get('fievel_web_spider.manager.spider');
    $manager->setLogger($this->logger);

    $response = null;
    try {
        $response = $manager->runSpider([
            AppBundle\Spiders\CustomSpider::class,  // Spider class created
            'http://localhost/test-spider',         // URL to spidering
            'post',                                 // Http method supported by Guzzle
            ['cookies' => true],                    // Custom config supported by Guzzle Client
            [                                       // Custom options supported by Guzzle Client
                RequestOptions::FORM_PARAMS => [
                    'full_name' => 'John Doe'
                ]
            ]
        ]);
    } catch(\Exception $e) {
    }

## Features

It's possible to share a storage between subsequent spiders call.

    $storage = new SpiderStorage();
    $storage->add($sharedData);

    $response = $manager->runSpider([
        AppBundle\Spiders\CustomSpider::class,  // Spider class created
        'http://localhost/test-spider',         // URL to spidering
        'post',                                 // Http method supported by Guzzle
        ['cookies' => true],                    // Custom config supported by Guzzle Client
        [                                       // Custom options supported by Guzzle Client
            RequestOptions::FORM_PARAMS => [
                'full_name' => 'John Doe'
            ]
        ],
        $storage                                // Shared storage
    ]);

It's even possible to create queues and leave the entire execution to the manager.

    $queue = new SpiderCallQueue();

    $queue->enqueue(
        AppBundle\Spiders\FirstPageSpider::class,
        'http://localhost/test-spider',
        'post',
        ['cookies' => true],
        [
            RequestOptions::FORM_PARAMS => [
                'full_name' => 'John Doe'
            ]
        ]
    );
    $queue->enqueue(
        AppBundle\Spiders\SecondPageSpider::class,
        'http://localhost/test-spider',
        'get',
        ['cookies' => true],
        []
    );

    $response = $manager->runSpiderQueue($queue);

Last but not least, the `SpiderManager` will handle retries on failure using a custom `GuzzleMiddleware`.

## Proxy

## Links

- [Guzzle Documentation](http://docs.guzzlephp.org/en/latest/overview.html)
- [Symfony DomCrawler Documentation](http://symfony.com/doc/current/components/dom_crawler.html)
