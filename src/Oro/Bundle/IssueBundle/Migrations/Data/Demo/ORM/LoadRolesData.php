<?php

namespace Oro\Bundle\IssueBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class LoadRolesData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\IssueBundle\Migrations\Data\ORM\LoadIssuePriority',
            'Oro\Bundle\IssueBundle\Migrations\Data\ORM\LoadIssueResolution'
        ];
    }

    /**
     * Load roles
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        $fileName = $this->container
            ->get('kernel')
            ->locateResource('@OroIssueBundle/Migrations/Data/Demo/ORM/Roles/roles.yml');

        $fileName  = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
        $rolesData = Yaml::parse(file_get_contents($fileName));

        foreach ($rolesData as $roleName => $roleConfigData) {
            if (isset($roleConfigData['bap_role'])) {
                $role = $manager->getRepository('OroUserBundle:Role')
                    ->findOneBy(['role' => $roleConfigData['bap_role']]);
            } else {
                $role = new Role($roleName);
            }

            $role->setLabel($roleConfigData['label']);
            $manager->persist($role);

            if ($aclManager->isAclEnabled()) {
                $sid = $aclManager->getSid($role);
                foreach ($roleConfigData['permissions'] as $permission => $acls) {
                    $this->processPermission($aclManager, $sid, $permission, $acls);
                }
            }
        }

        $aclManager->flush();
        $manager->flush();
    }

    /**
     * @param AclManager $aclManager
     * @param mixed $sid
     * @param string $permission
     * @param array $acls
     */
    protected function processPermission(
        AclManager $aclManager,
        SecurityIdentityInterface $sid,
        $permission,
        array $acls
    ) {
        $oid = $aclManager->getOid(str_replace('|', ':', $permission));

        $extension = $aclManager->getExtensionSelector()->select($oid);
        $maskBuilders = $extension->getAllMaskBuilders();

        foreach ($maskBuilders as $maskBuilder) {
            $mask = $maskBuilder->reset()->get();

            if (!empty($acls)) {
                foreach ($acls as $acl) {
                    if ($maskBuilder->hasMask('MASK_' . $acl)) {
                        $mask = $maskBuilder->add($acl)->get();
                    }
                }
            }

            $aclManager->setPermission($sid, $oid, $mask);
        }
    }
}
