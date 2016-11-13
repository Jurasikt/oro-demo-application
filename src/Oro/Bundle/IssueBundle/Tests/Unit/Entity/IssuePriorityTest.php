<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Oro\Bundle\IssueBundle\Entity\IssuePriority;

class IssuePriorityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssuePriority
     */
    protected $issuePriority;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    public function setUp()
    {
        $this->issuePriority = new IssuePriority('test');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @dataProvider getSetDataProvider
     */
    public function testGettersSetters($property, $value)
    {
        $this->propertyAccessor->setValue($this->issuePriority, $property, $value);
        $this->assertSame($this->propertyAccessor->getValue($this->issuePriority, $property), $value);
    }

    public function testToString()
    {
        $this->issuePriority->setLabel('label');
        $this->assertSame((string) $this->issuePriority, 'label');
    }

    public function getSetDataProvider()
    {
        return [
            ['label', 'label'],
            ['order', 12]
        ];
    }
}
