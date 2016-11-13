<?php

namespace Oro\Bundle\IssueBundle\Tests\Functional\Controller\Api\Rest\DataFixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\UserBundle\Entity\User;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('oro_user.manager');
        $role = $userManager->getStorageManager()
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(['role' => User::ROLE_ANONYMOUS]);

        $user = $userManager->createUser();

        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $api = new UserApi();
        $api->setApiKey(ApiKeys::USER_PASSWORD)
            ->setOrganization($organization)
            ->setUser($user);

        $user
            ->setUsername(ApiKeys::USER_NAME)
            ->setPlainPassword(ApiKeys::USER_PASSWORD)
            ->setFirstName('Simple')
            ->setLastName('User')
            ->addRole($role)
            ->setEmail('simple@example.com')
            ->setOrganization($organization)
            ->setOrganizations(new ArrayCollection([$organization]))
            ->addApiKey($api)
            ->setSalt('');

        $userManager->updateUser($user);
    }
}
