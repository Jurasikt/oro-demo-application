<?php

namespace Oro\Bundle\IssueBundle\Test\Unit\ImportExport\TemplateFixture;

use Oro\Bundle\IssueBundle\ImportExport\TemplateFixture\IssueFixture;

class IssueFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IssueFixture
     */
    protected $issueFixture;

    public function setUp()
    {
        $this->issueFixture = new IssueFixture();
    }

    public function testGetEntityClass()
    {
        $entityClass = $this->issueFixture->getEntityClass();
        $this->assertSame('Oro\Bundle\IssueBundle\Entity\Issue', $entityClass);
    }

    public function testFillEntityDataWhenKeyExist()
    {
        $issue = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');
        $issue->expects($this->once())->method('setSummary')->willReturn($issue);
        $issue->expects($this->once())->method('setReporter')->willReturn($issue);
        $issue->expects($this->once())->method('setIssuePriority')->willReturn($issue);
        $issue->expects($this->once())->method('setAssignee')->willReturn($issue);
        $issue->expects($this->once())->method('setIssueResolution')->willReturn($issue);
        $issue->expects($this->once())->method('setCode')->willReturn($issue);
        $issue->expects($this->once())->method('setIssueType')->willReturn($issue);

        $this->issueFixture->fillEntityData('default', $issue);
    }

    /**
     * @expectedException \LogicException
     */
    public function testFillEntityDataWhenKeyNotExist()
    {
        $issue = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');
        $this->issueFixture->fillEntityData('', $issue);
    }
}
