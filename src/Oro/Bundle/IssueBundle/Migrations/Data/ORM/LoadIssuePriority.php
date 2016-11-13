<?php

namespace Oro\Bundle\IssueBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IssueBundle\Entity\IssuePriority;

class LoadIssuePriority extends AbstractFixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getDefaultIssuePriority() as $item) {
            $iPriority = new IssuePriority($item['id']);
            $iPriority->setLabel($item['label']);
            $iPriority->setOrder($item['order']);
            $manager->persist($iPriority);
        }
        $manager->flush();
    }

    protected function getDefaultIssuePriority()
    {
        return [
            [
                'id' => 'critical',
                'label' => 'Critical',
                'order' => 10
            ],
            [
                'id' => 'major',
                'label' => 'Major',
                'order' => 20
            ],
            [
                'id' => 'trivial',
                'label' => 'Trivial',
                'order' => 30
            ],
        ];
    }
}
