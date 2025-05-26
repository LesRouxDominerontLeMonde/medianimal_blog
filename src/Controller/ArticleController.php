<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleForm;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
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
            ->where('a.publishedAt IS NOT NULL')
            ->andWhere('a.publishedAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        if (
            $article->getPublishedAt() === null ||
            $article->getPublishedAt() > new \DateTimeImmutable()
        ) {
            throw $this->createNotFoundException();
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }
}
