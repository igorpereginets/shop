<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture implements DependentFixtureInterface
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
        for ($i = 0; $i < 200; $i++) {
            $product = new Product();

            $product
                ->setName($this->faker->text(40))
                ->setDescription($this->faker->text)
                ->setPrice($this->faker->randomFloat(2, 5, 300))
                ->setActive($this->faker->boolean)
                ->setPosition(random_int(1, 10000))
                ->setPublishedAt($product->isActive() ? $this->faker->dateTimeThisYear : null)
                ->setCategory($this->getReference('category_' . random_int(0, 99)));

            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class
        ];
    }
}
