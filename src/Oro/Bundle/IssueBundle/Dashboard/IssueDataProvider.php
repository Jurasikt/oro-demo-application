<?php

namespace  Oro\Bundle\IssueBundle\Dashboard;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IssueBundle\Entity\Repository\IssueRepository;

class IssueDataProvider
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function getIssueCountGroupByStatus()
    {
        return $this->getIssueRepository()
            ->getAggregateIssuesStatusQB()
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return IssueRepository
     */
    private function getIssueRepository()
    {
        return $this->registry->getRepository('OroIssueBundle:Issue');
    }
}
