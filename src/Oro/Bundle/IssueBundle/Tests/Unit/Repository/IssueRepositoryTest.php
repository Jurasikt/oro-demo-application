<?php

namespace Oro\Bundle\IssueBundle\Tests\Unit\Repository;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Oro\Component\TestUtils\ORM\Mocks\EntityManagerMock;
use Oro\Component\TestUtils\ORM\OrmTestCase;

use Oro\Bundle\IssueBundle\Entity\Repository\IssueRepository;

class IssueRepositoryTest extends OrmTestCase
{
    /**
     * @var EntityManagerMock
     */
    protected $entityManager;

    public function setUp()
    {
        $metadataDriver = new AnnotationDriver(
            new AnnotationReader(),
            __DIR__ . '/../Fixtures/Entity'
        );

        $this->entityManager = $this->getTestEntityManager();
        $this->entityManager->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $this->entityManager->getConfiguration()->setEntityNamespaces(
            [
                'OroIssueBundle' => 'Oro\Bundle\IssueBundle\Tests\Unit\Fixtures\Entity'
            ]
        );
    }

    public function testGetAggregateIssuesStatusQB()
    {
        /** @var IssueRepository $repo */
        $repo = $this->entityManager->getRepository('OroIssueBundle:Issue');

        $this->assertSame(
            'SELECT COUNT(issue.id) as number, workflowStep.label '
            . 'FROM OroIssueBundle:Issue issue '
            . 'INNER JOIN OroWorkflowBundle:WorkflowStep workflowStep WITH workflowStep = issue.workflowStep '
            . 'GROUP BY workflowStep.label',
            $repo->getAggregateIssuesStatusQB()->getDQL()
        );
    }
}
