<?php

namespace App\Twig;

use App\Repository\CommentRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(private CommentRepository $commentRepository)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pending_comments_count', [$this, 'getPendingCommentsCount']),
            new TwigFunction('unread_comments_count', [$this, 'getUnreadCommentsCount']),
        ];
    }

    public function getPendingCommentsCount(): int
    {
        return $this->commentRepository->countPending();
    }

    public function getUnreadCommentsCount(): int
    {
        return $this->commentRepository->countUnread();
    }
}
