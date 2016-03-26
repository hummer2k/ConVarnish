<?php

namespace ConVarnishTest\Options;

use ConVarnish\Options\VarnishOptions;
use ConVarnishTest\AbstractTest;

/**
 * @package ConVarnish
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class VarnishOptionsTest extends AbstractTest
{
    public function testOptions()
    {
        $options = new VarnishOptions();
        $options->setCacheEnabled(true);
        $this->assertTrue($options->isCacheEnabled());

        $options->setCacheableRoutes(['test/route' => 60]);
        $this->assertEquals(['test/route' => 60], $options->getCacheableRoutes());

        $options->setPolicy(VarnishOptions::POLICY_ALLOW);
        $this->assertEquals(VarnishOptions::POLICY_ALLOW, $options->getPolicy());

        $options->setDebug(true);
        $this->assertTrue($options->getDebug());

        $options->setDefaultTtl(3600);
        $this->assertEquals(3600, $options->getDefaultTtl());

        $options->setServers(['server1' => ['host' => '127.0.0.1']]);
        $this->assertEquals(['server1' => ['host' => '127.0.0.1']], $options->getServers());
    }
}
