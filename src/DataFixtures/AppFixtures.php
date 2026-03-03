<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@stubborn.local');
        $admin->setName('Admin Stubborn');
        $admin->setShippingAddress('Piccadilly Circus, London W1J 0DA, Royaume-Uni');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_CLIENT']);
        $admin->setIsVerified(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $client = new User();
        $client->setEmail('client@stubborn.local');
        $client->setName('Client Stubborn');
        $client->setShippingAddress('Piccadilly Circus, London W1J 0DA, Royaume-Uni');
        $client->setRoles(['ROLE_CLIENT']);
        $client->setIsVerified(true);
        $client->setPassword($this->passwordHasher->hashPassword($client, 'client123'));
        $manager->persist($client);

        $products = [
            ['Blackbelt', 29.90, true],
            ['BlueBelt', 29.90, false],
            ['Street', 34.50, false],
            ['Pokeball', 45.00, true],
            ['PinkLady', 29.90, false],
            ['Snow', 32.00, false],
            ['Greyback', 28.50, false],
            ['BlueCloud', 45.00, false],
            ['BornInUsa', 59.90, true],
            ['GreenSchool', 42.20, false],
        ];

        foreach ($products as [$name, $price, $featured]) {
            $product = new Product();
            $product->setName($name);
            $product->setPrice($price);
            $product->setFeatured($featured);
            $product->setImagePath(null);
            $product->setStockXs(5);
            $product->setStockS(5);
            $product->setStockM(5);
            $product->setStockL(5);
            $product->setStockXl(5);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
