<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\ProductUnitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted('ROLE_USER')]
#[Route('/dashboard', 'app.dashboard.')]
final class DashboardController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductRepository $productRepository, ProductUnitRepository $productUnitRepository): Response
    {
        $allProducts = $productRepository->findBy([
            'company' => $this->getUser()->getCompany()
        ]);
        $costProducts = 0;
        foreach($productUnitRepository->findAll() as $unit) {
            if($unit->getProduct()->getCompany() === $this->getUser()->getCompany()) {
                $costProducts += $unit->getBuyPrice();
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'sumProducts' => count($allProducts),
            'costProducts' => $costProducts,
        ]);
    }
}

