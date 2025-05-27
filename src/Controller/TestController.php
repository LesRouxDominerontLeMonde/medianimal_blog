<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test-trumbowyg', name: 'test_trumbowyg')]
    public function testTrumbowyg(): Response
    {
        return $this->render('test_trumbowyg.html.twig');
    }
} 