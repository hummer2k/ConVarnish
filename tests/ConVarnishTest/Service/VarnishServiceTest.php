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

    public function setUp()
    {
        parent::setUp();
        $this->service = Bootstrap::getServiceManager()
            ->get('ConVarnish\Service\VarnishService');
        $this->adapter = new Test();
        $this->service->getClient()->setAdapter($this->adapter);
    }

    public function testPurge()
    {
        $result = $this->service->purge('example.com');
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }

    public function testPurgeUri()
    {
        $result = $this->service->purgeUri('example.com', '/');
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }

    public function testPurgeTags()
    {
        $result = $this->service->purgeTags('example.com', 'tag1,tag2,tag3');
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }

    public function testPurgeTagsArray()
    {
        $result = $this->service->purgeTags('example.com', ['tag1', 'tag2', 'tag3']);
        $this->assertContainsOnlyInstancesOf('Zend\Http\Response', $result);
    }
}
