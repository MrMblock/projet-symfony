<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const ADMIN_REFERENCE = 'user-admin';
    public const USER_REFERENCE_PREFIX = 'user-';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $usersData = [
            [
                'email' => 'gabriel.saintlouis99@gmail.com',
                'firstName' => 'Gabriel',
                'lastName' => 'Saint-Louis',
                'roles' => ['ROLE_ADMIN'],
                'profilePicture' => 'gojo-698de4d7cd7dc.webp',
                'password' => 'admin123',
                'reference' => self::ADMIN_REFERENCE,
            ],
            [
                'email' => 'zakariaeddouh@gmail.com',
                'firstName' => 'Zakaria',
                'lastName' => 'Eddouh',
                'roles' => ['ROLE_ADMIN'],
                'profilePicture' => 'zakaria.jpg',
                'password' => 'admin123',
                'reference' => 'user-zakaria',
            ],
            [
                'email' => 'joelnutsugan@gmail.com',
                'firstName' => 'Joël',
                'lastName' => 'Nutsugan',
                'roles' => [],
                'profilePicture' => 'joel-698de78e347f1.jpg',
                'password' => 'user123',
                'reference' => self::USER_REFERENCE_PREFIX . '0',
            ],
            [
                'email' => 'gabrielmartin@gmail.com',
                'firstName' => 'Gabriel',
                'lastName' => 'Martin',
                'roles' => [],
                'profilePicture' => 'gabriel-698de7fb675c3.jpg',
                'password' => 'user123',
                'reference' => self::USER_REFERENCE_PREFIX . '1',
            ],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setRoles($data['roles']);
            $user->setProfilePicture($data['profilePicture']);
            $user->setProfilePicture($data['profilePicture']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            
            $slug = strtolower($data['firstName'] . '-' . $data['lastName']);
            $user->setSlug($slug);

            $manager->persist($user);
            $this->addReference($data['reference'], $user);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['user'];
    }
}
