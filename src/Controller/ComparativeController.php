<?php

namespace App\Controller;

use App\Entity\ProductProviderComparative;
use App\Repository\ProductProviderComparativeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comparatifs/', name: 'app.dashboard.comparative.')]
#[isGranted('ROLE_USER')]
final class ComparativeController extends AbstractController
{
    #[Route('{id}', name: 'delete')]
    public function delete(
        EntityManagerInterface $entityManager,
        ProductProviderComparativeRepository $productProviderComparativeRepository,
        ProductProviderComparative $productProviderComparative
    ): Response
    {
        if($productProviderComparative->getCompany() == $this->getUser()->getCompany()){
            $productRef = $productProviderComparative->getProduct()->getRefInterne();
            $entityManager->remove($productProviderComparative);
            $entityManager->flush();

            $this->addFlash('success', 'Tarif fournisseur supprimé !');

            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $productRef, 'tab' => 'prices']);
        }
    }
}
