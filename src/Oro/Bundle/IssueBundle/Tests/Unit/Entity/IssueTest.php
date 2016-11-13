<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Oro\Bundle\IssueBundle\Entity\Issue;

class IssueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var Issue
     */
    protected $issue;

    public function setUp()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->issue = new Issue();
    }

    /**
     * @dataProvider getSetDataProvider
     *
     * @param $property
     * @param $value
     */
    public function testGettersSetters($property, $value)
    {
        $this->propertyAccessor->setValue($this->issue, $property, $value);
        $this->assertSame($this->propertyAccessor->getValue($this->issue, $property), $value);
    }

    public function testPreUpdate()
    {
        $this->issue->preUpdate();
        $this->assertNotNull($this->issue->getUpdatedAt());
    }

    public function testPrePersist()
    {
        $this->issue->prePersist();
        $this->assertNotNull($this->issue->getUpdatedAt());
        $this->assertNotNull($this->issue->getCreatedAt());
    }

    public function testSetUniqueCode()
    {
        $reflect = new \ReflectionClass($this->issue);
        $issueIdAccessible = $reflect->getProperty('id');
        $issueIdAccessible->setAccessible(true);
        $issueIdAccessible->setValue($this->issue, 123);

        $this->issue->setUniqueCode();
        $this->assertSame('ATT-123', $this->issue->getCode());
    }

    public function testGetStatus()
    {
        $workflowStep = $this->createMockWithoutConstructor('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep');
        $workflowStep
            ->expects($this->once())
            ->method('getLabel')
            ->willReturn('open');

        $this->assertNull($this->issue->getStatus());

        $this->issue->setWorkflowStep($workflowStep);
        $this->assertSame($this->issue->getStatus(), 'open');
    }

    public function getSetDataProvider()
    {
        return [
            ['issueType', Issue::ISSUE_TYPE_STORY],
            ['summary', 'summary'],
            ['code', 'code'],
            ['description', 'description'],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['reporter', $this->createMockWithoutConstructor('Oro\Bundle\UserBundle\Entity\User')],
            ['assignee', $this->createMockWithoutConstructor('Oro\Bundle\UserBundle\Entity\User')],
            ['issueResolution', $this->createMockWithoutConstructor('Oro\Bundle\IssueBundle\Entity\IssueResolution')],
            ['issuePriority', $this->createMockWithoutConstructor('Oro\Bundle\IssueBundle\Entity\IssuePriority')],
            ['parent', $this->createMockWithoutConstructor('Oro\Bundle\IssueBundle\Entity\Issue')],
            ['workflowItem', $this->createMockWithoutConstructor('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')],
            ['workflowStep', $this->createMockWithoutConstructor('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep')]
        ];
    }

    /**
     * @param $className
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockWithoutConstructor($className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
