<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
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
        $parents = [];

        for ($i = 0; $i < 100; $i++) {
            $category = new Category();

            $category
                ->setName($this->faker->text(40))
                ->setPosition(random_int(1, 10000))
                ->setActive($this->faker->boolean);

            if ($i > 15) {
                $category->setParent($parents[array_rand($parents)]);
            } else {
                $parents[] = $category;
            }

            $manager->persist($category);
        }

        $manager->flush();

    }
}
