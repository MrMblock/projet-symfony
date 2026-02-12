<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $comments = [
            [
                'content' => 'T\'es trop fort Gabriel',
                'status' => 'valide',
                'author' => UserFixtures::USER_REFERENCE_PREFIX . '0',
                'post' => PostFixtures::REFERENCE_PREFIX . '2',
            ],
            [
                'content' => 'C\'est moi le meilleur Gabriel',
                'status' => 'en attente',
                'author' => UserFixtures::USER_REFERENCE_PREFIX . '1',
                'post' => PostFixtures::REFERENCE_PREFIX . '3',
            ],
        ];

        foreach ($comments as $data) {
            $comment = new Comment();
            $comment->setContent($data['content']);
            $comment->setStatus($data['status']);
            $comment->setAuthor($this->getReference($data['author'], User::class));
            $comment->setPost($this->getReference($data['post'], Post::class));
            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PostFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['comment'];
    }
}
