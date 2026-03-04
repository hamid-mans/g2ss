<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductUnit;
use App\Form\GenerateUserType;
use App\Form\ProductType;
use App\Form\ProductUnitType;
use App\Form\SearchType;
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
    public function index(EntityManagerInterface $entityManager, Request $request, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
        ]);
        $form->handleRequest($request);

        $qb = $productRepository->createQueryBuilder('p')
            ->where('p.company = :company')
            ->setParameter('company', $this->getUser()->getCompany())
            ->orderBy('p.refInterne', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];

            $qb->andWhere('p.designation LIKE :search OR p.refInterne LIKE :search OR p.refSupplier LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $products = $qb->getQuery()->getResult();


        // Créer un produit

        $product = new Product();

        $formCreateProduct = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="ri ri-add-large-line"></i> Ajouter',
            'submit_class' => 'btn btn-primary',
        ]);
        $formCreateProduct->handleRequest($request);

        if ($formCreateProduct->isSubmitted() && $formCreateProduct->isValid()) {
            $product->setCompany($this->getUser()->getCompany());
            $product->setRefInterne(strtoupper($product->getRefInterne()));
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès !');

            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $product->getRefInterne()]);
        }

        return $this->render('dashboard/product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
            'formCreateProduct' => $formCreateProduct->createView(),
        ]);
    }

    #[Route('nouveau', name: 'create')]
    public function create(ValidatorInterface $validator, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="ri ri-add-large-line"></i> Ajouter',
            'submit_class' => 'btn btn-primary',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCompany($this->getUser()->getCompany());
            $product->setRefInterne(strtoupper($product->getRefInterne()));
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès !');

            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $product->getRefInterne()]);
        }

        return $this->render('dashboard/product/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('{refInterne}', name: 'update')]
    public function update(#[MapEntity(mapping: ['refInterne' => 'refInterne'])] Product $product, ProductUnitRepository $productUnitRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="ri ri-save-line"></i> Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $product->setRefInterne(strtoupper($product->getRefInterne()));
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié !');

            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $product->getRefInterne()]);
        }

        $productUnits = $productUnitRepository->findBy(['product' => $product], [
            'serialNumber' => 'ASC',
        ]);

        $sommeUnit = 0;

        foreach ($productUnits as $unit) {
            $sommeUnit += $unit->getBuyPrice();
        }

        $pmp = ($sommeUnit !== 0) ? $sommeUnit / count($productUnits) : 0;

        // Créer un numéro de série
        $productUnit = new ProductUnit();

        $formCreateUnit = $this->createForm(ProductUnitType::class, $productUnit, [
            'submit_label' => "<i class='ri ri-save-line'></i>Enregistrer",
            'submit_class' => "btn btn-primary",
            'company' => $product->getCompany(),
        ]);
        $formCreateUnit->handleRequest($request);

        if ($formCreateUnit->isSubmitted() && $formCreateUnit->isValid()) {
            $productUnit->setProduct($product);
            $entityManager->persist($productUnit);
            $entityManager->flush();

            $this->addFlash('success', 'Numéro de série créé !');

            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $productUnit->getProduct()->getRefInterne()]);
        }

        return $this->render('dashboard/product/update.html.twig', [
            'product' => $product,
            'form' => $form,
            'formCreateUnit' => $formCreateUnit->createView(),
            'productUnits' => $productUnits,
            'pmp' => number_format($pmp, 2),
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