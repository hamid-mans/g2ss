<?php

namespace App\Controller;

use App\Entity\Deposit;
use App\Entity\Movement;
use App\Entity\Product;
use App\Form\MovementsType;
use App\Repository\DepositRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function PHPUnit\Framework\throwException;

#[isGranted("ROLE_USER")]
#[Route('/mouvements', name: 'app.dashboard.movements.')]
final class MovementController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('dashboard/movement/index.html.twig');
    }

    #[Route('/entree/{product}/{deposit}', name: 'entry')]
    public function entry(DepositRepository $depositRepository, Product $product, Deposit $deposit, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($product->getCompany() == $this->getUser()->getCompany() && $this->getUser()->getDeposits()->contains($deposit))
        {
            $movement = new Movement();

            $ghostProduct = new Product();
            $ghostProduct->setRefInterne($product->getRefInterne());

            $form = $this->createForm(MovementsType::class, $ghostProduct);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                foreach ($ghostProduct->getProductUnits() as $unit) {

                    $unit->setProduct($product);

                    if (!$unit->getDeposit()) {
                        $unit->setDeposit($deposit);
                    }

                    $entityManager->persist($unit);
                }

                $movement->setProduct($product);
                $movement->setDeposit($deposit);
                $movement->setUser($this->getUser());
                $movement->setCompany($this->getUser()->getCompany());
                $movement->setType('1');

                $entityManager->persist($movement);
                $entityManager->flush();

                $this->addFlash('success', 'Mouvement effectué !');

                return $this->redirectToRoute('app.dashboard.product.update', [
                    'refInterne' => $product->getRefInterne(),
                    'tab' => 'serial',
                    'depositTab' => $deposit->getId()
                ]);
            }

            return $this->render('dashboard/movement/entry.html.twig', [
                'product' => $product,
                'form' => $form->createView(),
                'deposit' => $deposit,
            ]);
        } else throw $this->createNotFoundException("Impossible d'effectuer cette action");
    }
}
