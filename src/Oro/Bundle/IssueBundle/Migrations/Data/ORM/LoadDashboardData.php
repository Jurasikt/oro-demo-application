<?php

namespace Oro\Bundle\IssueBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;

class LoadDashboardData extends AbstractDashboardFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DashboardBundle\Migrations\Data\ORM\LoadDashboardData',
            'Oro\Bundle\IssueBundle\Migrations\Data\ORM\LoadIssuePriority',
            'Oro\Bundle\IssueBundle\Migrations\Data\ORM\LoadIssueResolution'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $mainDashboard = $this->findAdminDashboardModel($manager, 'main');

        if ($mainDashboard) {
            $mainDashboard->addWidget(
                $this->createWidgetModel('issue_status', [0, 10])
            );

            $mainDashboard->addWidget(
                $this->createWidgetModel('issue_grid', [10, 10])
            );

            $manager->flush();
        }
    }
}
