<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', 'app.home.')]
class HomeController extends AbstractController
{
    #[Route('/', 'app.home.index')]
    public function index(): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute('app.security.login');
        }

        return $this->render('home.html.twig');
    }
}
