<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Oro\Bundle\IssueBundle\Entity\IssueResolution;

class IssueResolutionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssueResolution
     */
    protected $issueResolution;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function setUp()
    {
        $this->issueResolution = new IssueResolution('test');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @dataProvider getSetDataProvider
     */
    public function testGettersSetters($property, $value)
    {
        $this->propertyAccessor->setValue($this->issueResolution, $property, $value);
        $this->assertSame($this->propertyAccessor->getValue($this->issueResolution, $property), $value);
    }

    public function testToString()
    {
        $this->issueResolution->setLabel('label');
        $this->assertSame((string) $this->issueResolution, 'label');
    }

    public function getSetDataProvider()
    {
        return [
            ['label', 'label'],
            ['order', 1]
        ];
    }
}
