<?php

namespace Oro\Bundle\IssueBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IssueBundle\Entity\IssueResolution;

class LoadIssueResolution extends AbstractFixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getDefaultIssueResolution() as $item) {
            $iPriority = new IssueResolution($item['id']);
            $iPriority->setLabel($item['label']);
            $iPriority->setOrder($item['order']);
            $manager->persist($iPriority);
        }
        $manager->flush();
    }

    protected function getDefaultIssueResolution()
    {
        return [
            [
                'id' => 'unresolved',
                'label' => 'Unresolved',
                'order' => 10
            ],
            [
                'id' => 'fixed',
                'label' => 'Fixed',
                'order' => 20
            ],
            [
                'id' => 'incomplete',
                'label' => 'Incomplete',
                'order' => 30
            ],
            [
                'id' => 'done',
                'label' => 'Done',
                'order' => 40
            ],
            [
                'id' => 'rejected',
                'label' => 'Rejected',
                'order' => 50
            ],
            [
                'id' => 'declined',
                'label' => 'Declined',
                'order' => 60
            ],
        ];
    }
}
