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
            ['Blackbelt', 29.90, true, 'images/products/blackbelt.jpeg'],
            ['BlueBelt', 29.90, false, 'images/products/bluebelt.jpeg'],
            ['Street', 34.50, false, 'images/products/street.jpeg'],
            ['Pokeball', 45.00, true, 'images/products/pokeball.jpeg'],
            ['PinkLady', 29.90, false, 'images/products/pinklady.jpeg'],
            ['Snow', 32.00, false, 'images/products/snow.jpeg'],
            ['Greyback', 28.50, false, 'images/products/greyback.jpeg'],
            ['BlueCloud', 45.00, false, 'images/products/bluecloud.jpeg'],
            ['BornInUsa', 59.90, true, 'images/products/borninusa.jpeg'],
            ['GreenSchool', 42.20, false, 'images/products/greenschool.jpeg'],
        ];

        foreach ($products as [$name, $price, $featured, $imagePath]) {
            $product = new Product();
            $product->setName($name);
            $product->setPrice($price);
            $product->setFeatured($featured);
            $product->setImagePath($imagePath);
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
