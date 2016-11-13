<?php

namespace Oro\Bundle\IssueBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\IssueBundle\Entity\Issue;

class IssueRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function getAggregateIssuesStatusQB()
    {
         $qb = $this->getEntityManager()->createQueryBuilder();

         return $qb->select('COUNT(issue.id) as number', 'workflowStep.label')
            ->from('OroIssueBundle:Issue', 'issue')
            ->join('OroWorkflowBundle:WorkflowStep', 'workflowStep', 'WITH', 'workflowStep = issue.workflowStep')
            ->groupBy('workflowStep.label');
    }
}
