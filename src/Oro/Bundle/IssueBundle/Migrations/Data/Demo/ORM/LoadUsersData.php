<?php

namespace Oro\Bundle\IssueBundle\Migrations\Data\Demo\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;

class LoadUsersData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    const COUNT = 5;

    const USER_PASSWORD = '123456';

    /** @var ContainerInterface */
    protected $container;

    /** @var UserManager */
    protected $userManager;

    /** @var Role[] */
    protected $role;

    /** @var  EntityManager */
    protected $em;

    /**
     * @var Organization[]
     */
    protected $organization;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\IssueBundle\Migrations\Data\Demo\ORM\LoadRolesData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager)
    {
        $this->em = $manager;
        $this->role = $this->em->getRepository('OroUserBundle:Role')->findAll();
        $this->organization = $this->em->getRepository('OroOrganizationBundle:Organization')->findAll();
        $this->userManager  = $this->container->get('oro_user.manager');
    }

    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadUsers();
    }

    /**
     * Load users
     *
     * @return void
     */
    public function loadUsers()
    {
        for ($i = 0; $i < self::COUNT; ++$i) {
            $firstName = $this->generateFirstName();
            $lastName = $this->generateLastName();
            $birthday = $this->generateBirthday();
            $username = $this->generateUsername($i);
            $email = $this->generateEmail($i);

            $user = $this->createUser(
                $username,
                $email,
                $firstName,
                $lastName,
                $birthday
            );

            $this->userManager->updatePassword($user);

            $this->em->persist($user);
        }
        $this->em->flush();
    }

    /**
     * Creates a user
     *
     * @param  string    $username
     * @param  string    $email
     * @param  string    $firstName
     * @param  string    $lastName
     * @param  \DateTime $birthday
     *
     * @return User
     */
    private function createUser($username, $email, $firstName, $lastName, $birthday)
    {
        /** @var $user User */
        $user = $this->userManager->createUser();

        $user->setEmail($email);
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setBirthday($birthday);
        $this->setUserBusinessOwner($user);
        $user->addRole($this->getRandomRole());
        $user->setPlainPassword(self::USER_PASSWORD);

        return $user;
    }

    /**
     * Set Owner BusinessUnit & Organization
     *
     * @param User $user
     */
    private function setUserBusinessOwner(User $user)
    {
        $organization = $this->getRandomOrganization();
        $unit = $this->getRandomUnit($organization);

        $user->setOrganization($organization);
        $user->addOrganization($organization);
        $user->addBusinessUnit($unit);
        $user->setOwner($unit);
    }

    /**
     * Get random role
     *
     * @return Role
     */
    private function getRandomRole()
    {
        return $this->role[array_rand($this->role)];
    }

    /**
     * @return Organization
     */
    private function getRandomOrganization()
    {
        return $this->organization[array_rand($this->organization)];
    }

    /**
     * @param Organization $organization
     * @return mixed
     */
    private function getRandomUnit(Organization $organization)
    {
        $units = $organization->getBusinessUnits()->toArray();
        return $units[array_rand($units)];
    }

    /**
     * Generates a username
     * @param $userId
     * @return string
     */
    private function generateUsername($userId)
    {
        return sprintf("user%s", $userId);
    }

    /**
     * Generates an email
     *
     * @param $userId
     * @return string
     */
    private function generateEmail($userId)
    {
        return sprintf("user%s@oro.com", $userId);
    }

    /**
     * Generate a first name
     *
     * @return string
     */
    private function generateFirstName()
    {
        $firstNamesDictionary = ['Mark', 'James', 'David', 'Robert', 'Tim', 'Tom'];

        return $firstNamesDictionary[array_rand($firstNamesDictionary)];
    }

    /**
     * Generates a last name
     *
     * @return string
     */
    private function generateLastName()
    {
        $lastNamesDictionary = ['Plank', 'Friedman', 'Einstein', 'Newton', 'Smith'];

        return $lastNamesDictionary[array_rand($lastNamesDictionary)];
    }

    /**
     * Generates a date of birth
     *
     * @return \DateTime
     */
    private function generateBirthday()
    {
        // Convert to timetamps
        $min = strtotime('1930-01-01');
        $max = strtotime('2000-01-01');

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return new \DateTime(date('Y-m-d', $val), new \DateTimeZone('UTC'));
    }
}
