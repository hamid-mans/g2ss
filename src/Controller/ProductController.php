<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Movement;
use App\Entity\Product;
use App\Entity\ProductUnit;
use App\Form\GenerateUserType;
use App\Form\ProductType;
use App\Form\ProductUnitType;
use App\Form\Search\SearchDepositType;
use App\Form\SearchType;
use App\Repository\MovementRepository;
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
            $productsExist = $productRepository->findBy(['company' => $this->getUser()->getCompany(), 'refInterne' => $formCreateProduct->get('refInterne')->getData()]);
            if(count($productsExist) > 0) {
                $this->addFlash('error', 'Cette référence interne existe déjà.');

                return $this->redirectToRoute('app.dashboard.product.index');
            }

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
        ], new Response(null, $formCreateProduct->isSubmitted() ? 422 : 200));
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
    public function update(#[MapEntity(mapping: ['refInterne' => 'refInterne'])] Product $product, MovementRepository $movementRepository, ProductUnitRepository $productUnitRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($product->getCompany() == $this->getUser()->getCompany()) {
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
                'submit_label' => "<i class='ri ri-add-line'></i>Ajouter",
                'submit_class' => "btn btn-primary",
                'company' => $product->getCompany(),
            ]);
            $formCreateUnit->handleRequest($request);

            if ($formCreateUnit->isSubmitted() && $formCreateUnit->isValid()) {
                $productUnit->setProduct($product);
                $entityManager->persist($productUnit);
                $entityManager->flush();

                $this->addFlash('success', 'Numéro de série créé !');

                $movement = new Movement();
                $movement->setProduct($product);
                $movement->setCompany($this->getUser()->getCompany());
                $movement->setType(1);
                $movement->setDeposit($productUnit->getDeposit());
                $movement->setUser($this->getUser());
                $movement->addProductUnit($productUnit);
                $entityManager->persist($movement);
                $entityManager->flush();

                return $this->redirectToRoute('app.dashboard.product.update', [
                    'tab' => 'serial',
                    'refInterne' => $productUnit->getProduct()->getRefInterne()
                ]);
            }


            // Mouvements

            $formSearchDeposit = $this->createForm(SearchDepositType::class, null, [
                'user' => $this->getUser(),
                'company' => $this->getUser()->getCompany(),
            ]);
            $formSearchDeposit->handleRequest($request);

            if ($formSearchDeposit->isSubmitted() && $formSearchDeposit->isValid()) {
                $deposit = $formSearchDeposit->get('deposits')->getData();
                $action = $request->request->get('action');

                if ($action === 'entry') {
                    return $this->redirectToRoute('app.dashboard.movements.entry', [
                        'product' => $product->getId(),
                        'deposit' => $deposit->getId()
                    ]);
                } else {
                    return $this->redirectToRoute('app.dashboard.movements.exit', [
                        'product' => $product->getId(),
                        'deposit' => $deposit->getId()
                    ]);
                }
            }

            return $this->render('dashboard/product/update.html.twig', [
                'product' => $product,
                'form' => $form,
                'formCreateUnit' => $formCreateUnit->createView(),
                'formSearchDeposit' => $formSearchDeposit->createView(),
                'productUnits' => $productUnits,
                'pmp' => number_format($pmp, 2),
                'movements' => $movementRepository->findBy([
                    'product' => $product
                ])
            ]);
        } else {
            throw $this->createNotFoundException("Cette produit n'existe pas !");
        }
    }

    #[Route('supprimer/{refInterne}', name: 'delete')]
    public function delete(ProductRepository $productRepository, EntityManagerInterface $entityManager, String $refInterne): Response
    {
        $product = $productRepository->findOneBy([
            'refInterne' => $refInterne,
            'company' => $this->getUser()->getCompany(),
        ]);

        if($product === null) {
            throw $this->createNotFoundException("Cette produit n'existe pas !");
        } else {
            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit supprimé !');

            return $this->redirectToRoute('app.dashboard.product.index');
        }
    }

}