<?php

namespace App\Controller;

use App\Entity\Movement;
use App\Entity\Product;
use App\Entity\ProductUnit;
use App\Form\ProductUnitType;
use App\Repository\ProductRepository;
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

        if($productUnit && $productUnit->getProduct()->getCompany() == $this->getUser()->getCompany()){
            $form = $this->createForm(ProductUnitType::class, $productUnit, [
                'submit_label' => "<i class='ri ri-save-line'></i>Enregistrer",
                'submit_class' => "btn btn-primary",
                'company' => $productUnit->getProduct()->getCompany(),
                'user' => $this->getUser(),
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
        } else {
            throw $this->createNotFoundException("Cette produit n'existe pas !");
        }
    }

    #[Route('ajouter/{refInterne}', name: 'create')]
    public function create(String $refInterne, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBy(['refInterne' => $refInterne]);

        if($product && $product->getCompany() == $this->getUser()->getCompany()){
            $productUnit = new ProductUnit();
            $product = null;

            $form = $this->createForm(ProductUnitType::class, $productUnit, [
                'submit_label' => "<i class='ri ri-save-line'></i>Enregistrer",
                'submit_class' => "btn btn-primary",
                'company' => $this->getUser()->getCompany(),
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $product = $productRepository->findBy([
                    'refInterne' => $refInterne,
                    'company' => $this->getUser()->getCompany()
                ]);
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
        } else { throw $this->createNotFoundException("Cette produit n'existe pas !"); }
    }

    #[Route('supprimer/{id}', name: 'delete')]
    public function delete(int $id, ProductUnitRepository $productUnitRepository, EntityManagerInterface $entityManager): Response
    {
        $productUnit = $productUnitRepository->findOneBy([
            'id' => $id
        ]);
        $id = $productUnit->getDeposit()->getId();

        if($productUnit && $productUnit->getProduct()->getCompany() == $this->getUser()->getCompany()){
            $product = $productUnit->getProduct();
            $productRef = $productUnit->getProduct()->getRefInterne();

            $movement = new Movement();
            $movement->setType('0');
            $movement->setUser($this->getUser());
            $movement->setCompany($this->getUser()->getCompany());
            $movement->setProduct($product);
            $movement->setDeposit($productUnit->getDeposit());
            $movement->addProductUnit($productUnit);
            $entityManager->persist($movement);

            $productUnit->setDeleted(true);
            $entityManager->flush();

            $this->addFlash('success', 'Numéro de série supprimé !');

            return $this->redirectToRoute('app.dashboard.product.update', [
                'tab' => 'serial',
                'depositTab' => $id,
                'refInterne' => $productRef
            ]);
        }

        throw $this->createNotFoundException("Cette produit n'existe pas !");
    }
}
