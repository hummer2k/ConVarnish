<?php

namespace ConVarnishTest\Listener;

use ConVarnish\Listener\InjectTagsHeaderListener;
use ConVarnish\Service\VarnishService;
use ConVarnishTest\AbstractTest;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class InjectTagsHeaderListenerTest extends AbstractTest
{
    /**
     *
     * @var InjectTagsHeaderListener
     */
    protected $listener;

    public function setUp()
    {
        parent::setUp();
        $this->listener = new InjectTagsHeaderListener();
    }

    public function testAttach()
    {
        $em = new EventManager();
        $this->listener->attach($em);
        $this->assertCount(1, $em->getListeners(MvcEvent::EVENT_RENDER));
    }

    public function testInjectTagsHeader()
    {
        $tag = InjectTagsHeaderListener::OPTION_CACHE_TAGS;
        $event = new MvcEvent();

        $response = new Response();
        $event->setResponse($response);

        $layout = new ViewModel();

        $child1 = new ViewModel();
        $child1->setOption($tag, ['tag1', 'tag2']);

        $layout->addChild($child1);

        $child2 = new ViewModel();
        $child21 = new ViewModel();
        $child21->setOption($tag, ['tag3', null]);
        $child2->addChild($child21);

        $layout->addChild($child2);

        $child3 = new ViewModel();
        $child3->setOption('esi', ['ttl' => 120]);
        $child3->setOption($tag, 'tag4');

        $layout->addChild($child3);

        $event->setViewModel($layout);

        $this->listener->injectTagsHeader($event);

        $this->assertSame(
            ['tag1', 'tag2', 'tag3'],
            $this->listener->getCacheTags()
        );

        $headers = $response->getHeaders();
        $this->assertEquals(
            'tag1,tag2,tag3',
            $headers->get(VarnishService::VARNISH_HEADER_TAGS)->getFieldValue()
        );
    }
}
