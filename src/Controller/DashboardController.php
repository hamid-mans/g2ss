<?php

namespace App\Controller;

use App\Form\NotesType;
use App\Repository\CategoryRepository;
use App\Repository\DepositRepository;
use App\Repository\MovementRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductUnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[isGranted('ROLE_USER')]
#[Route('/', 'app.dashboard.')]
final class DashboardController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(DepositRepository $depositRepository, EntityManagerInterface $entityManager, Request $request, MovementRepository $movementRepository, CategoryRepository $categoryRepository, ChartBuilderInterface $chartBuilder, ProductRepository $productRepository, ProductUnitRepository $productUnitRepository): Response
    {
        $deposits = $depositRepository->findBy([
            'company' => $this->getUser()->getCompany()
        ]);
        $allProducts = $productUnitRepository->findBy([
            'deposit' => $deposits,
            'deleted' => false
        ]);

        $costProducts = 0;
        foreach($productUnitRepository->findBy(['deleted' => false, 'deposit' => $deposits]) as $unit) {
            if($unit->getProduct()->getCompany() === $this->getUser()->getCompany()) {
                $costProducts += $unit->getBuyPrice();
            }
        }

        $categories = [];
        $unitsCategories = [];
        $buyPriceCategories = [];

        foreach($categoryRepository->findBy(['company' => $this->getUser()->getCompany()]) as $category) {
            $categories[] = $category->getLabel();

            $totalUnitsForCategory = 0;
            $totalBuyPriceForCategory = 0;

            foreach($category->getProducts() as $product) {
                foreach($product->getProductUnits() as $productUnit) {
                    if (!$productUnit->isDeleted()) {
                        $totalUnitsForCategory++;
                        $totalBuyPriceForCategory += $productUnit->getBuyPrice();
                    }
                }
            }

            $unitsCategories[] = $totalUnitsForCategory;
            $buyPriceCategories[] = $totalBuyPriceForCategory;
        }

        $otherProducts = $productRepository->findBy([
            'company' => $this->getUser()->getCompany(),
            'category' => null
        ]);

        if (count($otherProducts) > 0) {
            $categories[] = "Autres";
            $otherUnits = 0;
            $otherPrice = 0;

            foreach($otherProducts as $product) {
                foreach($product->getProductUnits() as $productUnit) {
                    if (!$productUnit->isDeleted()) {
                        $otherUnits++;
                        $otherPrice += $productUnit->getBuyPrice();
                    }
                }
            }
            $unitsCategories[] = $otherUnits;
            $buyPriceCategories[] = $otherPrice;
        }

        $company = $this->getUser()->getCompany();

        $notesForm = $this->createForm(NotesType::class, $company);
        $notesForm->handleRequest($request);

        if ($notesForm->isSubmitted() && $notesForm->isValid()) {
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'Notes modifiées !');

            return $this->redirectToRoute('app.dashboard.index');
        }

        return $this->render('dashboard/index.html.twig', [
            'sumProducts' => count($allProducts),
            'costProducts' => $costProducts,
            'unitsCategories' => $unitsCategories,
            'buyPriceCategories' => $buyPriceCategories,
            'categories' => $categories,
            'movements' => $movementRepository->findBy(['company' => $this->getUser()->getCompany()], ['createdAt' => 'DESC'], 5),
            'notesForm' => $notesForm->createView(),
        ]);
    }
}

