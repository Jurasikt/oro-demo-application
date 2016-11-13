<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\IssueBundle\Entity\Manager\IssueManager;

class IssueManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var IssueManager
     */
    private $issueManager;

    public function setUp()
    {
        $this->entityManager = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $this->issueManager = new IssueManager($this->entityManager);
    }

    public function testGetChildWhenIssueNotFlash()
    {
        $issue = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');
        $issue->expects($this->once())->method('getId')
            ->willReturn(null);

        $this->assertSame([], $this->issueManager->getChild($issue));
    }

    public function testGetChildWhenIssueExist()
    {
        $issue = $this->getMock('Oro\Bundle\IssueBundle\Entity\Issue');
        $issue->expects($this->once())->method('getId')
            ->willReturn(1);

        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository->expects($this->once())->method('findBy')
            ->with(['parent' => $issue]);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with('OroIssueBundle:Issue')
            ->willReturn($objectRepository);

        $this->issueManager->getChild($issue);
    }
}
