<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Order;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setFirstname("Henry".$i);
            $user->setLastname("dupuis".$i);
            $user->setEmail("henry.dupuis".$i."@toto.mail");
            $user->setLogin("foobar".$i);
            $password = $this->hasher->hashPassword($user, 'toto');
            $user->setPassword($password);
            $manager->persist($user);
        }

        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName("Flamby".$i);
            $product->setDescription("dessert".$i);
            $product->setPhoto("https://platetrecette.com/wp-content/uploads/2020/12/Flamby-leger-maison-WW.jpg");
            $product->setPrice("2.99");
            $manager->persist($product);
        }

        for ($i = 0; $i < 20; $i++) {
            $order = new Order();
            $order->setUserId($user);
            $order->setTotalPrice("2.56");
            $order->setCreationDate(new \DateTime());
            $order->addProduct($product);
            $order->setIsCompleted(false);
            $manager->persist($order);
        }
    
        $manager->flush();
    }
}