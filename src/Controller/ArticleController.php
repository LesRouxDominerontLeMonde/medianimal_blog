<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/article')]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->createQueryBuilder('a')
            ->where('a.isPublished = :published')
            ->andWhere('a.publishedAt IS NOT NULL')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('published', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/blog', name: 'app_blog', methods: ['GET'])]
    public function blog(Request $request, ArticleRepository $articleRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 6; // Nombre d'articles par page

        $queryBuilder = $articleRepository->createQueryBuilder('a')
            ->where('a.isPublished = :published')
            ->andWhere('a.publishedAt IS NOT NULL')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('published', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($queryBuilder);
        $totalArticles = count($paginator);
        $totalPages = ceil($totalArticles / $limit);

        return $this->render('blog/index.html.twig', [
            'articles' => $paginator,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalArticles' => $totalArticles,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, ArticleRepository $articleRepository): Response
    {
        // Récupération manuelle pour éviter l'erreur EntityValueResolver
        $article = $articleRepository->find($id);
        
        // Vérification si l'article existe
        if (!$article) {
            throw $this->createNotFoundException('Cet article n\'existe pas.');
        }
        
        // Vérification si l'article est visible
        if (!$article->isVisible()) {
            throw $this->createNotFoundException('Cet article n\'est pas disponible.');
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
