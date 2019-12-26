<?php

namespace ConVarnishTest\Service;

use ConVarnish\Service\VarnishService;
use ConVarnishTest\AbstractTest;
use ConVarnishTest\Bootstrap;
use Zend\Http\Client\Adapter\Test;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishServiceTest extends AbstractTest
{
    protected $adapter;

    /**
     *
     * @var VarnishService
     */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VarnishService([

        ]);
        $this->adapter = new Test();
        $this->service->getClient()->setAdapter($this->adapter);
    }

    public function testBan()
    {
        $result = $this->service->ban('example.com');
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }

    public function testBanUri()
    {
        $result = $this->service->banUri('example.com', '/');
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }

    public function testBanTags()
    {
        $result = $this->service->banTags('example.com', 'tag1,tag2,tag3');
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }

    public function testBanTagsArray()
    {
        $result = $this->service->banTags('example.com', ['tag1', 'tag2', 'tag3']);
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }
}
