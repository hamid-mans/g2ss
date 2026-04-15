<?php

namespace App\Controller;

use App\Entity\Movement;
use App\Entity\Product;
use App\Entity\ProductUnit;
use App\Entity\User;
use App\Form\MovementSearchType;
use App\Form\ProductType;
use App\Form\ProductUnitSearchType;
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
use Knp\Component\Pager\PaginatorInterface;

#[IsGranted("ROLE_USER")]
#[Route('/produits/', name: 'app.dashboard.product.')]
final class ProductController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(
        EntityManagerInterface $entityManager,
        Request $request,
        ProductRepository $productRepository,
        PaginatorInterface $paginator
    ): Response {
        $form = $this->createForm(SearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        $qb = $productRepository->createQueryBuilder('p')
            ->where('p.company = :company')
            ->setParameter('company', $this->getUser()->getCompany())
            ->orderBy('p.refInterne', 'ASC');

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->get('search')->getData();
            if ($search) {
                $qb->andWhere('p.designation LIKE :search OR p.refInterne LIKE :search OR p.refSupplier LIKE :search')
                    ->setParameter('search', '%' . $search . '%');
            }
        }

        $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1), 15);

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
            'products' => $pagination,
            'form' => $form->createView(),
            'formCreateProduct' => $formCreateProduct->createView(),
        ], new Response(null, $formCreateProduct->isSubmitted() ? 422 : 200));
    }

    #[Route('{refInterne}', name: 'update')]
    public function update(
        #[MapEntity(mapping: ['refInterne' => 'refInterne'])] Product $product,
        PaginatorInterface $paginator,
        MovementRepository $movementRepository,
        ProductUnitRepository $productUnitRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($product->getCompany() !== $this->getUser()->getCompany()) {
            throw $this->createNotFoundException("Ce produit n'existe pas !");
        }

        $form = $this->createForm(ProductType::class, $product, [
            'submit_label' => '<i class="ri ri-save-line"></i> Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setRefInterne(strtoupper($product->getRefInterne()));
            $entityManager->flush();
            $this->addFlash('success', 'Produit modifié !');
            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $product->getRefInterne()]);
        }

        // --- STOCK (Unités) ---
        $searchForm = $this->createForm(ProductUnitSearchType::class, null, [
            'company_deposits' => $this->getUser()->getCompany()->getDeposits(),
            'method' => 'GET'
        ]);
        $searchForm->handleRequest($request);

        $qbUnits = $productUnitRepository->createQueryBuilder('u')
            ->where('u.product = :product')
            ->andWhere('u.deleted = false')
            ->setParameter('product', $product)
            ->orderBy('u.serialNumber', 'ASC');

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            if ($data['q']) {
                $qbUnits->andWhere('u.serialNumber LIKE :q')->setParameter('q', '%' . $data['q'] . '%');
            }
            if ($data['deposit']) {
                $qbUnits->andWhere('u.deposit = :deposit')->setParameter('deposit', $data['deposit']);
            }
        }

        $countUnitsWithFilter = count($qbUnits->getQuery()->getResult());
        $paginationUnits = $paginator->paginate($qbUnits, $request->query->getInt('page', 1), 10);

        // --- PMP ---
        $allUnits = $productUnitRepository->findBy(['product' => $product, 'deleted' => false]);
        $sommeUnit = array_reduce($allUnits, fn($carry, $u) => $carry + $u->getBuyPrice(), 0);
        $pmp = count($allUnits) > 0 ? $sommeUnit / count($allUnits) : 0;

        $movementSearchForm = $this->createForm(MovementSearchType::class, null, [
            'company_users' => $entityManager->getRepository(User::class)->findBy(['company' => $this->getUser()->getCompany()]),
        ]);
        $movementSearchForm->handleRequest($request);

        $qbMovements = $movementRepository->createQueryBuilder('m')
            ->where('m.product = :product')
            ->setParameter('product', $product);

        if ($movementSearchForm->isSubmitted() && $movementSearchForm->isValid()) {
            $data = $movementSearchForm->getData();

            if ($data['type'] !== null) {
                $qbMovements->andWhere('m.type = :type')->setParameter('type', $data['type']);
            }
            if ($data['user']) {
                $qbMovements->andWhere('m.user = :user')->setParameter('user', $data['user']);
            }
            if ($data['dateFrom']) {
                $qbMovements->andWhere('m.createdAt >= :from')->setParameter('from', $data['dateFrom']);
            }
            if ($data['dateTo']) {
                // On ajoute 23:59:59 pour inclure toute la journée de fin
                $qbMovements->andWhere('m.createdAt <= :to')->setParameter('to', $data['dateTo']->format('Y-m-d 23:59:59'));
            }
        }

        $qbMovements->orderBy('m.createdAt', 'DESC');

        $countMovementsWithFilter = count($qbMovements->getQuery()->getResult());

        $paginationMovements = $paginator->paginate(
            $qbMovements,
            $request->query->getInt('page_mov', 1),
            10,
            ['pageParameterName' => 'page_mov']
        );

        // --- MODALS FORMS ---
        $productUnit = new ProductUnit();
        $formCreateUnit = $this->createForm(ProductUnitType::class, $productUnit, [
            'submit_label' => "Ajouter",
            'submit_class' => "btn btn-primary",
            'company' => $product->getCompany(),
        ]);
        $formCreateUnit->handleRequest($request);

        if ($formCreateUnit->isSubmitted() && $formCreateUnit->isValid()) {
            $productUnit->setProduct($product);
            $entityManager->persist($productUnit);

            $movement = new Movement();
            $movement->setProduct($product);
            $movement->setCompany($this->getUser()->getCompany());
            $movement->setType(1);
            $movement->setDeposit($productUnit->getDeposit());
            $movement->setUser($this->getUser());
            $movement->addProductUnit($productUnit);

            $entityManager->persist($movement);
            $entityManager->flush();

            $this->addFlash('success', 'Entrée effectuée !');
            return $this->redirectToRoute('app.dashboard.product.update', ['refInterne' => $product->getRefInterne(), 'tab' => 'serial']);
        }

        $formSearchDeposit = $this->createForm(SearchDepositType::class, null, [
            'user' => $this->getUser(),
            'company' => $this->getUser()->getCompany(),
        ]);
        $formSearchDeposit->handleRequest($request);

        if ($formSearchDeposit->isSubmitted() && $formSearchDeposit->isValid()) {
            $deposit = $formSearchDeposit->get('deposits')->getData();
            $action = $request->request->get('action');
            $route = ($action === 'entry') ? 'app.dashboard.movements.entry' : 'app.dashboard.movements.exit';

            return $this->redirectToRoute($route, [

                'product' => $product->getId(),

                'deposit' => $deposit->getId()

            ]);

        }

        return $this->render('dashboard/product/update.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
            'formCreateUnit' => $formCreateUnit->createView(),
            'formSearchDeposit' => $formSearchDeposit->createView(),
            'productUnits' => $paginationUnits,
            'pmp' => number_format($pmp, 2),
            'movements' => $paginationMovements,
            'searchForm' => $searchForm->createView(),
            'countUnitsWithFilter' => $countUnitsWithFilter,
            'countMovementsWithFilter' => $countMovementsWithFilter,
            'movementSearchForm' => $movementSearchForm->createView(),
        ]);
    }

    #[Route('supprimer/{refInterne}', name: 'delete')]
    public function delete(ProductRepository $productRepository, EntityManagerInterface $entityManager, string $refInterne): Response
    {
        $product = $productRepository->findOneBy(['refInterne' => $refInterne, 'company' => $this->getUser()->getCompany()]);
        if (!$product) throw $this->createNotFoundException();

        $entityManager->remove($product);
        $entityManager->flush();
        $this->addFlash('success', 'Produit supprimé !');
        return $this->redirectToRoute('app.dashboard.product.index');
    }
}