<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Utils\MDTokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    const ADMINS = [
        [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'username' => 'admin',
            'password' => '123Pass',
            'is_active' => true,
            'roles' => ['ROLE_ADMIN']
        ],
        [
            'name' => 'SuperAdmin',
            'email' => 'superadmin@admin.com',
            'username' => 'superadmin',
            'password' => '123Pass',
            'is_active' => true,
            'roles' => ['ROLE_SUPERADMIN']
        ],
    ];

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;
    /**
     * @var \Faker\Generator
     */
    private $faker;
    /**
     * @var MDTokenGenerator
     */
    private $tokenGenerator;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder, MDTokenGenerator $tokenGenerator)
    {
        $this->faker = Factory::create();
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function load(ObjectManager $manager)
    {
        foreach (self::ADMINS as $admin) {
            $user = new User();

            $user
                ->setName($admin['name'])
                ->setEmail($admin['email'])
                ->setUsername($admin['username'])
                ->setPassword($this->userPasswordEncoder->encodePassword($user, $admin['password']))
                ->setActive($admin['is_active'])
                ->setRoles($admin['roles']);

            $manager->persist($user);
        }

        for ($i = 0; $i < 50; $i++) {
            $user = new User();

            $user
                ->setName($this->faker->name)
                ->setEmail($this->faker->email)
                ->setUsername($this->faker->userName)
                ->setPassword($this->userPasswordEncoder->encodePassword($user, '123pass'))
                ->setActive($this->faker->boolean)
                ->setBirthday($this->faker->dateTimeThisCentury)
                ->setRoles(['ROLE_USER']);

            if (!$user->isActive()) {
                $user->setConfirmationToken($this->tokenGenerator->generate());
            }

            $this->setReference('user_' . $i, $user);
            $manager->persist($user);
        }


        $manager->flush();
    }
}
