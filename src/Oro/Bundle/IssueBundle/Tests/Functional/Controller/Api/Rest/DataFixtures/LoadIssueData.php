<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional\Controller\Api\Rest\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\IssueBundle\Entity\Issue;

class LoadIssueData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $owner = $manager->getRepository('OroUserBundle:User')->findOneBy(['username' => 'admin']);

        if (null === $owner) {
            return;
        }

        /** @var \Oro\Bundle\IssueBundle\Entity\IssuePriority $issuePriority */
        $issuePriority = $manager->find('OroIssueBundle:IssuePriority', 'major');
        $issue = new Issue();
        $issue->setReporter($owner)
            ->setIssueType(Issue::ISSUE_TYPE_STORY)
            ->setSummary('ACL test')
            ->setIssuePriority($issuePriority);

        $manager->persist($issue);
        $manager->flush();
    }
}
