<?php

namespace Oro\Bundle\IssueBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\IssueBundle\Entity\IssuePriority;
use Oro\Bundle\IssueBundle\Tests\Unit\Fixtures\Entity\IssueResolution;
use Oro\Bundle\UserBundle\Entity\User;

class LoadIssueData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const DEFAULT_ADMIN_USERNAME = 'admin';
    const COUNT_FIXTURES = 50;
    const SKIP_TRANSITION_PARAM = 0.25;

    /** @var  array */
    protected $summaries;

    /** @var  array */
    protected $issueType;

    /** @var  EntityManager */
    protected $em;

    /** @var  User[] */
    protected $users;

    /** @var  IssuePriority[] */
    protected $issuePriorities;

    /** @var  IssueResolution[] */
    protected $issueResolutions;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\IssueBundle\Migrations\Data\ORM\LoadIssuePriority',
            'Oro\Bundle\IssueBundle\Migrations\Data\ORM\LoadIssueResolution',
            'Oro\Bundle\IssueBundle\Migrations\Data\Demo\ORM\LoadUsersData',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadIssue();
        $this->setNewIssueStatus();
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager)
    {
        $this->em = $manager;
        $this->users = $manager->getRepository('OroUserBundle:User')->findAll();
        $this->issuePriorities = $manager->getRepository('OroIssueBundle:IssuePriority')->findAll();
        $this->issueResolutions = $manager->getRepository('OroIssueBundle:IssueResolution')->findAll();

        $this->issueType = [
            Issue::ISSUE_TYPE_SUBTASK,
            Issue::ISSUE_TYPE_STORY,
            Issue::ISSUE_TYPE_TASK,
            Issue::ISSUE_TYPE_BUG
        ];

        $this->summaries = [
            'Lorem ipsum dolor sit amet',
            'Consectetur adipisicing elit',
            'Ut enim ad minim veniam',
            'Duis aute irure dolor',
            'Excepteur sint occaecat',
            'Sunt in culpa qui officia',
            'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia',
        ];
    }

    /**
     * Load issue fixtures
     */
    private function loadIssue()
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $issueCollection = [];
        $dataSet = $this->getIssuesFixtures($this->em);
        krsort($dataSet);

        foreach ($dataSet as $id => $issueFixtures) {
            $issue = new Issue();
            foreach ($issueFixtures as $property => $value) {
                ($property != 'parent') ? $propertyAccessor->setValue($issue, $property, $value) :
                    $propertyAccessor->setValue($issue, $property, $issueCollection[$value]);
            }
            $issueCollection[$id] = $issue;
            $this->em->persist($issue);
        }
        $this->em->flush();
    }

    /**
     * Generate random issue status
     */
    private function setNewIssueStatus()
    {
        $workflowManager = $this->container->get('oro_workflow.manager');
        $issues = $this->em->getRepository('OroIssueBundle:Issue')->findAll();
        foreach ($issues as $issue) {
            if ($this->skipTransition()) {
                continue;
            }
            $transition = $workflowManager->getTransitionsByWorkflowItem($issue->getWorkflowItem());
            $workflowManager->transit(
                $issue->getWorkflowItem(),
                $this->getRandomItem($transition)
            );
        }
    }

    /**
     * @return bool
     */
    private function skipTransition()
    {
        return rand()/getrandmax() < self::SKIP_TRANSITION_PARAM;
    }

    /**
     * @param ObjectManager $manager
     * @return array
     */
    private function getIssuesFixtures(ObjectManager $manager)
    {
        $user = $manager->getRepository('OroUserBundle:User')
            ->findOneBy(['username' => self::DEFAULT_ADMIN_USERNAME]);

        $dataSet = $this->getRandomIssueFixtures(self::COUNT_FIXTURES);
        $dataSet[self::COUNT_FIXTURES] = [
            'summary'          => 'Create REST API Controller',
            'reporter'         => $user,
            'assignee'         => $user,
            'issueType'        => Issue::ISSUE_TYPE_STORY,
            'issuePriority'    => $manager->find('OroIssueBundle:IssuePriority', 'major'),
            'issueResolution'  => $manager->find('OroIssueBundle:IssueResolution', 'unresolved'),
            'description'      => 'User must be able to do CRUD operations on entity using API',
            'organization'     => $user->getOrganization(),
        ];

        return $dataSet;
    }

    /**
     * @param int $countFixtures
     * @return array
     */
    private function getRandomIssueFixtures($countFixtures)
    {
        $dataFixtures = [];
        $storyPool = [$countFixtures];

        while ($countFixtures--) {
            $reporter = $this->getRandomItem($this->users);
            $dataFixtures[$countFixtures] = [
                'summary' => $this->getRandomItem($this->summaries),
                'reporter' => $reporter,
                'issueResolution' => $this->getRandomItem($this->issueResolutions),
                'issueType' => $this->getRandomItem($this->issueType),
                'issuePriority' => $this->getRandomItem($this->issuePriorities),
                'description' => $this->getRandomDescriptions(),
                'organization' => $reporter->getOrganization(),
            ];

            switch ($dataFixtures[$countFixtures]['issueType']) {
                case Issue::ISSUE_TYPE_SUBTASK:
                    $dataFixtures[$countFixtures]['parent'] = $storyPool[array_rand($storyPool)];
                    break;
                case Issue::ISSUE_TYPE_STORY:
                    $storyPool[] = $countFixtures;
                    break;
            }
        }

        return $dataFixtures;
    }

    /**
     * @param $elements
     * @return mixed
     */
    private function getRandomItem($elements)
    {
        if ($elements instanceof ArrayCollection) {
            $elements = $elements->toArray();
        }

        return $elements[array_rand($elements)];
    }

    /**
     * @return string
     */
    private function getRandomDescriptions()
    {
        return 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod'
            . 'tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,'
            . 'quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo'
            . 'consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse'
            . 'cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non'
            . 'proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
    }
}
