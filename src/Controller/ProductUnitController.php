<?php

namespace App\Controller;

use App\Entity\ProductUnit;
use App\Form\ProductUnitType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_USER")]
#[Route('/produits/numeros-de-series/', 'app.dashboard.product_unit.')]
final class ProductUnitController extends AbstractController
{
    #[Route('nouveau/{productId}', name: 'create')]
    public function create(int $productId, ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $productUnit = new ProductUnit();
        $user = $this->getUser();

        $product = $productRepository->find($productId);

        $form = $this->createForm(ProductUnitType::class, $productUnit, [
            'submit_label' => "<i class='save icon'></i>Enregistrer",
            'submit_class' => 'ui black button',
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $deposit = $productUnit->getDeposit();

            if ($deposit->getCompany() !== $user->getCompany()) {
                throw $this->createAccessDeniedException('Accès interdit au dépôt.');
            }

            $productUnit->setDeposit($deposit);
            $productUnit->setProduct($product);

            $entityManager->persist($productUnit);
            $entityManager->flush();

            $this->addFlash('success', 'Numéro de série enregistré !');

            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $product->getRefInterne()]);
        }

        return $this->render('dashboard/product/product_unit/create.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    #[Route('{productUnitId}', name: 'update')]
    public function update(int $productUnitId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $productUnit = $entityManager->find(ProductUnit::class, $productUnitId);

        $form = $this->createForm(ProductUnitType::class, $productUnit, [
            'submit_label' => "<i class='ui save icon'></i>Enregistrer",
            'submit_class' => 'ui black button',
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deposit = $productUnit->getDeposit();

            if ($deposit->getCompany() !== $user->getCompany()) {
                throw $this->createAccessDeniedException('Accès interdit au dépôt.');
            }

            $productUnit->setDeposit($deposit);
            $entityManager->persist($productUnit);
            $entityManager->flush();

            $this->addFlash('success', 'Numéro de série modifié !');

            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $productUnit->getProduct()->getRefInterne()]);
        }

        return $this->render('dashboard/product/product_unit/update.html.twig', [
            'form' => $form->createView(),
            'productUnit' => $productUnit
        ]);
    }

    #[Route('supprimer/{productUnitId}', name: 'delete')]
    public function delete(ProductUnit $productUnitId, Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = $productUnitId->getProduct();

        $entityManager->remove($productUnitId);
        $entityManager->flush();

        $this->addFlash('success', 'Le numéro de série a bien été supprimé !');

        return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $product->getRefInterne()]);
    }
}
