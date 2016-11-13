<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\IssueBundle\DependencyInjection\OroIssueExtension;

class OroIssueExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroIssueExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->extension = new OroIssueExtension();
    }

    public function testLoad()
    {
        $this->extension->load(array(), $this->container);
    }
}
