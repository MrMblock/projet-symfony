<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const REFERENCE_PREFIX = 'post-';

    public function load(ObjectManager $manager): void
    {
        $posts = [
            [
                'title' => 'Vagabond - Tome 4',
                'content' => 'Je sais pas qui est le mangaka mais il dessine super bien et pour l\'instant l\'histoire est super aussi donc jsp je mettrais peut-être à jour le post pour dire où j\'en suis (non)',
                'priority' => 2,
                'picture' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQHaIy1SYZwgmGrBhhpJDE6CyqTFpPBOINtlw&s',
                'category' => 0,
            ],
            [
                'title' => 'KAIJI: Ultimate Survivor',
                'content' => "De la frappe tout simplement, tout le monde doit voir cet anime ou lire le manga.\nFun fact: La plus part des jeux de squidgame sont des copies des jeux de kaiji\n\nPar contre dommage que le mangaka passent 100 chapitres sur des trucs qui devraient être rapides , genre une partie de dès",
                'priority' => 3,
                'picture' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSW9JTsWEZHMJaZ_WU4wQmXW55jbW0kAtcq1g&s',
                'category' => 0,
            ],
            [
                'title' => 'Nouveau Projet',
                'content' => 'En réalité ce blog est un projet dans le cadre du module Symfony de mon diplôme',
                'priority' => 100,
                'picture' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4AmLjWuFAso6_IHxN4BfTr9jg76w9FK3IGg&s',
                'category' => 1,
            ],
            [
                'title' => 'SI vous aviez pas remarqué je m\'appelle Gabriel',
                'content' => 'Je m\'appelle Gabriel (Saint-Louis)',
                'priority' => 0,
                'picture' => 'https://www.gabriel-saintlouis.com/assets/nujabes.png',
                'category' => 2,
            ],
            [
                'title' => 'Bravo Gabriel 20/20',
                'content' => 'Bravo Gabriel 20/20',
                'priority' => 101,
                'picture' => '/uploads/articles/celebration_20_20.png',
                'category' => 2,
                'author' => 'user-zakaria',
            ],
            [
                'title' => 'Jsp mais il me fallait un autre article pour tester la pagination',
                'content' => 'Shabat Shalom',
                'priority' => 0,
                'picture' => 'https://images.steamusercontent.com/ugc/527255702146701556/45AD8EEB2F6483DD7C378B7E51598094B969726C/?imw=5000&imh=5000&ima=fit&impolicy=Letterbox&imcolor=%23000000&letterbox=false',
                'category' => 2,
                'author' => 'user-zakaria',
            ],
        ];

        foreach ($posts as $i => $data) {
            $post = new Post();
            $post->setTitle($data['title']);
            $post->setContent($data['content']);
            $post->setPriority($data['priority']);
            $post->setPicture($data['picture']);
            $authorReference = $data['author'] ?? UserFixtures::ADMIN_REFERENCE;
            $post->setAuthor($this->getReference($authorReference, User::class));
            $post->setCategory($this->getReference(CategoryFixtures::REFERENCE_PREFIX . $data['category'], Category::class));
            $manager->persist($post);
            $this->addReference(self::REFERENCE_PREFIX . $i, $post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['post'];
    }
}
