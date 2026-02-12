<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\KernelInterface;

class ProjectController extends AbstractController
{
    #[Route('/project', name: 'app_project')]
    public function index(KernelInterface $kernel): Response
    {
        $projectDir = $kernel->getProjectDir();
        $readmePath = $projectDir . '/README.md';
        
        $content = 'README.md non trouve.';
        if (file_exists($readmePath)) {
            $content = file_get_contents($readmePath);
        }

        return $this->render('project/index.html.twig', [
            'content' => $content,
        ]);
    }
}
