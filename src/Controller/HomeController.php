<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $postRepository->findAllOrderedByPriorityQuery();

        $posts = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/docs', name: 'app_docs')]
    public function docs(\Symfony\Component\HttpKernel\KernelInterface $kernel): Response
    {
        $readmePath = $kernel->getProjectDir() . '/README.md';
        
        if (!file_exists($readmePath)) {
            return $this->render('home/docs.html.twig', [
                'content' => '<div class="alert alert-warning">Documentation not found (README.md missing).</div>',
                'headings' => []
            ]);
        }

        $markdown = file_get_contents($readmePath);

        // 1. Configure the Environment
        $config = [
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => 'section',
                'fragment_prefix' => 'section',
                'symbol' => '#',
                'insert' => 'after',
            ],
        ];

        $environment = new \League\CommonMark\Environment\Environment($config);
        $environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
        $environment->addExtension(new \League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension());

        // 2. Instantiate Parser and Renderer
        $parser = new \League\CommonMark\Parser\MarkdownParser($environment);
        $renderer = new \League\CommonMark\Renderer\HtmlRenderer($environment);

        // 3. Parse and Render
        $document = $parser->parse($markdown);
        $htmlContent = $renderer->renderDocument($document);

        // 4. Walk the AST for Headings
        $headings = [];
        $slugger = new \Symfony\Component\String\Slugger\AsciiSlugger();
        
        foreach ($document->iterator() as $node) {
            if ($node instanceof \League\CommonMark\Extension\CommonMark\Node\Block\Heading) {
                $level = $node->getLevel();
                if ($level < 2 || $level > 3) {
                    continue;
                }

                $title = '';
                foreach ($node->children() as $child) {
                    if ($child instanceof \League\CommonMark\Node\Inline\Text) {
                        $title .= $child->getLiteral();
                    }
                }

                $id = 'section-' . strtolower($slugger->slug($title));

                $headings[] = [
                    'level' => $level,
                    'id' => $id,
                    'title' => $title,
                ];
            }
        }

        return $this->render('home/docs.html.twig', [
            'content' => $htmlContent,
            'headings' => $headings
        ]);
    }
}
