<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    public const REFERENCE_PREFIX = 'category-';

    public function load(ObjectManager $manager): void
    {
        $categories = [
            ['name' => 'Manga', 'description' => 'Tout ce qui est relatif aux mangas/animes, mes avis surtout', 'color' => '#ed333b'],
            ['name' => 'Projets', 'description' => 'Mes nouveaux projets', 'color' => '#c061cb'],
            ['name' => 'Autres', 'description' => null, 'color' => '#000000'],
        ];

        foreach ($categories as $i => $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setDescription($data['description']);
            $category->setColor($data['color']);
            $manager->persist($category);
            $this->addReference(self::REFERENCE_PREFIX . $i, $category);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['category'];
    }
}
