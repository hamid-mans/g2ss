<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductUnit;
use App\Form\ProductUnitType;
use App\Repository\ProductUnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_USER")]
#[Route('/produits/numero-de-serie/', 'app.dashboard.product_unit.')]
final class ProductUnitController extends AbstractController
{
    #[Route('{id}', name: 'update')]
    public function update(Request $request, EntityManagerInterface $entityManager): Response
    {
        $productUnit = $entityManager->getRepository(ProductUnit::class)->find($request->get('id'));

        $form = $this->createForm(ProductUnitType::class, $productUnit, [
            'submit_label' => "<i class='ri ri-save-line'></i>Enregistrer",
            'submit_class' => "btn btn-primary",
            'company' => $productUnit->getProduct()->getCompany(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($productUnit);
            $entityManager->flush();

            $this->addFlash('success', 'Numéro de série modifié !');

            return $this->redirectToRoute('app.dashboard.product.update', [
                'tab' => 'serial',
                'refInterne' => $productUnit->getProduct()->getRefInterne()
            ]);
        }

        return $this->render('dashboard/product/product_unit/update.html.twig', [
            'form' => $form->createView(),
            'productUnit' => $productUnit,
        ]);
    }

    #[Route('ajouter/{refInterne}', name: 'create')]
    public function create(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $productUnit = new ProductUnit();

        $form = $this->createForm(ProductUnitType::class, $productUnit, [
            'submit_label' => "<i class='ri ri-save-line'></i>Enregistrer",
            'submit_class' => "btn btn-primary",
            'company' => $product->getCompany(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productUnit->setProduct($product);
            $entityManager->persist($productUnit);
            $entityManager->flush();

            $this->addFlash('success', 'Numéro de série créé !');

            return $this->redirectToRoute('app.dashboard.product.update', ['tab' => 'serial', 'refInterne' => $productUnit->getProduct()->getRefInterne()]);
        }

        return $this->render('dashboard/product/product_unit/create.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('supprimer/{id}', name: 'delete')]
    public function delete(int $id, ProductUnitRepository $productUnitRepository, EntityManagerInterface $entityManager): Response
    {
        $productUnit = $productUnitRepository->find($id);
        $productRef = $productUnit->getProduct()->getRefInterne();

        if($productRef){
            $entityManager->remove($productUnit);
            $entityManager->flush();

            $this->addFlash('success', 'Numéro de série supprimé !');
        }

        return $this->redirectToRoute('app.dashboard.product.update', [
            'tab' => 'serial',
            'refInterne' => $productRef
        ]);
    }
}
