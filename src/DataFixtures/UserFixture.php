<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create a single user
        UserFactory::createOne();

        // Flush the ObjectManager to persist the data
        $manager->flush();
    }
}
