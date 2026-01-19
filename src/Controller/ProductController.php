<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\ProductUnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[isGranted("ROLE_USER")]
#[Route('/produits/', 'app.dashboard.product.')]
final class ProductController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('dashboard/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('nouveau', name: 'create')]
    public function create(ValidatorInterface $validator, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="plus icon"></i> Ajouter',
            'submit_class' => 'ui black button',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé !');

            return $this->redirectToRoute('app.dashboard.product.index');
        }


        return $this->render('dashboard/product/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('{refInterne}', name: 'update')]
    public function update(#[MapEntity(mapping: ['refInterne' => 'refInterne'])] Product $product, ProductUnitRepository $productUnitRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="save icon"></i> Enregistrer',
            'submit_class' => 'ui black button',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié !');

            return $this->redirectToRoute('app.dashboard.product.index');
        }

        $productUnits = $productUnitRepository->findBy(['product' => $product], [
            'serialNumber' => 'ASC',
        ]);

        return $this->render('dashboard/product/update.html.twig', [
            'product' => $product,
            'form' => $form,
            'productUnits' => $productUnits,
        ]);
    }

    #[Route('supprimer/{refInterne}', name: 'delete')]
    public function delete(EntityManagerInterface $entityManager, Product $product): Response
    {
        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé !');

        return $this->redirectToRoute('app.dashboard.product.index');
    }

}
