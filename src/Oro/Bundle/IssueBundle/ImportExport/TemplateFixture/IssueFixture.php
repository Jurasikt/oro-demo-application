<?php

namespace Oro\Bundle\IssueBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Entity\IssuePriority;
use Oro\Bundle\IssueBundle\Entity\IssueResolution;

class IssueFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Oro\Bundle\IssueBundle\Entity\Issue';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('default');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new Issue();
    }

    /**
     * @param string  $key
     * @param Issue $entity
     */
    public function fillEntityData($key, $entity)
    {
        switch ($key) {
            case 'default':
                $entity
                    ->setSummary('Solutions of the Einstein field equations.')
                    ->setReporter($this->getUser('Planck'))
                    ->setAssignee($this->getUser('Kruskal'))
                    ->setIssuePriority(new IssuePriority('major'))
                    ->setIssueResolution(new IssueResolution('done'))
                    ->setIssueType(Issue::ISSUE_TYPE_STORY)
                    ->setCode('OTO-100');
                return;
        }
        parent::fillEntityData($key, $entity);
    }

    protected function getUser($name)
    {
        $user = new User;
        $user->setUsername($name);
        return $user;
    }
}
