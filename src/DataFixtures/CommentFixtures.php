<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var \Faker\Generator
     */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $parents = $this->loadParents($manager);
        $this->loadChildren($manager, $parents);
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            ProductFixtures::class
        ];
    }

    private function loadParents(ObjectManager $manager, int $limit = 1000): array
    {
        $parents = [];

        for ($i = 0; $i < $limit; $i++) {
            $comment = new Comment();

            $createdAt = $this->faker->dateTimeThisYear;
            $updatedAt = $this->faker->boolean ? $this->faker->dateTimeBetween($createdAt) : $createdAt;

            $comment
                ->setContent($this->faker->text)
                ->setProduct($this->getReference('product_' . random_int(0, 199)))
                ->setUser($this->getReference('user_' . random_int(0, 49)))
                ->setUpdatedAt($updatedAt)
                ->setCreatedAt($createdAt);

            $parents[] = $comment;

            $manager->persist($comment);
        }

        $manager->flush();

        return $parents;
    }

    private function loadChildren(ObjectManager $manager, array $parents, int $limit = 1000)
    {
        for ($i = 0; $i < $limit; $i++) {
            $comment = new Comment();
            $parent = $parents[array_rand($parents)];

            $createdAt = $this->faker->dateTimeThisYear;
            $updatedAt = $this->faker->boolean ? $this->faker->dateTimeBetween($createdAt) : $createdAt;

            $comment
                ->setContent($this->faker->text)
                ->setParent($parent)
                ->setProduct($parent->getProduct())
                ->setUser($this->getReference('user_' . random_int(0, 49)))
                ->setUpdatedAt($updatedAt)
                ->setCreatedAt($createdAt);

            $manager->persist($comment);
        }

        $manager->flush();
    }
}
