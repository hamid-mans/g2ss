<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Form\SearchType;
use App\Repository\DepositRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductUnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Knp\Component\Pager\PaginatorInterface;
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
    public function index(PaginatorInterface $paginator, ProductRepository $productRepository, Request $request): Response
    {
        $searchForm = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
        ]);
        $searchForm->handleRequest($request);

        $query = $productRepository->createQueryBuilder('p')
            ->orderBy('p.refInterne', 'ASC');

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();

            if (!empty($filters['search'])) {
                $query
                    ->orWhere('p.refInterne LIKE :refInterne')
                    ->orWhere('p.refSupplier LIKE :refSupplier')
                    ->orWhere('p.designation LIKE :designation')
                    ->setParameter('refInterne', '%' . $filters['search'] . '%')
                    ->setParameter('refSupplier', '%' . $filters['search'] . '%')
                    ->setParameter('designation', '%' . $filters['search'] . '%');
            }
        }

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('dashboard/product/index.html.twig', [
            'products' => $productRepository->findAll(),
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination
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

            $this->addFlash('success', 'Produit enregistré !');

            return $this->redirectToRoute('app.dashboard.product.index');
        }


        return $this->render('dashboard/product/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('{refInterne}', name: 'update')]
    public function update(#[MapEntity(mapping: ['refInterne' => 'refInterne'])] Product $product, DepositRepository $depositRepository, PaginatorInterface $paginator, ProductUnitRepository $productUnitRepository, Request $request, EntityManagerInterface $entityManager): Response
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

        $sommeUnit = 0;

        foreach ($productUnits as $unit) {
            $sommeUnit += $unit->getBuyPrice();
        }

        $pmp = $sommeUnit / (count($productUnits) > 0 ? count($productUnits) : 1);


        // STOCK

        $searchForm = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
        ]);
        $searchForm->handleRequest($request);

        $query = $productUnitRepository->createQueryBuilder('p')
            ->andWhere('p.product = :product')
            ->setParameter('product', $product)
            ->orderBy('p.serialNumber', 'ASC');

        $deposits = $depositRepository->findBy(['company' => $this->getUser()->getCompany()], ['name' => 'ASC']);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();

            if (!empty($filters['search'])) {
                $query
                    ->andWhere('p.serialNumber LIKE :serialNumber')
                    ->setParameter('serialNumber', '%' . $filters['search'] . '%');
            }
        }

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('dashboard/product/update.html.twig', [
            'product' => $product,
            'form' => $form,
            'productUnits' => $productUnits,
            'pmp' => $pmp,
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'deposits' => $deposits,
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
