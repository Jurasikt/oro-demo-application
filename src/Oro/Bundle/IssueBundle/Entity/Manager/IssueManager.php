<?php

namespace Oro\Bundle\IssueBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

use Oro\Bundle\IssueBundle\Entity\Issue;

class IssueManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function getChild(Issue $issue)
    {
        if (null === $issue->getId()) {
            return [];
        }

        return $this->getIssueRepository()->findBy(['parent' => $issue]);
    }

    private function getIssueRepository()
    {
        return $this->entityManager->getRepository('OroIssueBundle:Issue');
    }
}
