<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Deposit;
use App\Form\CompanyType;
use App\Form\DepositType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
class DashboardController extends AbstractController
{
    #[isGranted('ROLE_SA')]
    #[Route('/', name: 'app.dashboard.index')]
    public function index(CompanyRepository $companyRepository): Response
    {
        return $this->render('dashboard/dashboard.html.twig', [
            'companies' => $companyRepository->findAll(),
        ]);
    }

    #[isGranted('ROLE_SA')]
    #[Route('/nouvelle-societe', name: 'app.dashboard.create_company')]
    public function createCompany(EntityManagerInterface $entityManager, Request $request): Response
    {
        $company = new Company();
        $createForm = $this->createForm(CompanyType::class, $company, [
            'submit_label' => '<i class="plus icon"></i>Enregistrer',
            'submit_class' => 'ui basic blue button'
        ]);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $company->setName(strtoupper($company->getName()));
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'La société a bien été créée');

            return $this->redirectToRoute('app.dashboard.index');
        }

        return $this->render('dashboard/company/create.html.twig', [
            'companyForm' => $createForm->createView(),
        ]);
    }

    #[isGranted('ROLE_SA')]
    #[Route('/modifier/{id}', name: 'app.dashboard.update_company')]
    public function updateCompany(EntityManagerInterface $entityManager, Company $company, Request $request): Response
    {
        $updateForm = $this->createForm(CompanyType::class, $company, [
            'submit_label' => '<i class="save icon"></i>Mettre à jour',
            'submit_class' => 'ui basic green button'
        ]);
        $updateForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $company->setName(strtoupper($company->getName()));
            $entityManager->flush();

            $this->addFlash('success', 'La société a bien été mise à jour');

            return $this->redirectToRoute('app.dashboard.index');
        }

        return $this->render('dashboard/company/update.html.twig', [
            'company' => $company,
            'companyForm' => $updateForm->createView(),
            'deposits' => $company->getDeposits(),
        ]);
    }

    #[isGranted('ROLE_SA')]
    #[Route('/supprimer/{id}', name: 'app.dashboard.delete_company')]
    public function deleteCompany(Company $company, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($company);
        $entityManager->flush();

        $this->addFlash('success', 'Société supprimée avec succès');

        return $this->redirectToRoute('app.dashboard.index');
    }




    // ##### DEPOSITS #####

    #[isGranted('ROLE_SA')]
    #[Route('/nouveau-depot/{id}', 'app.dashboard.create.deposit')]
    public function createDeposit(Company $company, EntityManagerInterface $entityManager, Request $request): Response
    {
        $deposit = new Deposit();

        $depositForm = $this->createForm(DepositType::class, $deposit, [
            'submit_label' => '<i class="plus icon"></i>Enregistrer',
            'submit_class' => 'ui basic blue button'
        ]);
        $depositForm->handleRequest($request);

        if ($depositForm->isSubmitted() && $depositForm->isValid()) {
            $deposit->setCompany($company);
            $deposit->setName(strtoupper($deposit->getName()));
            $entityManager->persist($deposit);

            $entityManager->flush();

            $this->addFlash('success', 'Le dépôt a été ajouté avec succès');

            return $this->redirectToRoute('app.dashboard.update_company', ['id' => $deposit->getCompany()->getId()]);
        }
        return $this->render('dashboard/deposit/create.html.twig', [
            'company' => $company,
            'depositForm' => $depositForm->createView(),
        ]);
    }

    #[isGranted('ROLE_SA')]
    #[Route('/modifier-depot/{id}', 'app.dashboard.update.deposit')]
    public function updateDeposit(Deposit $deposit, EntityManagerInterface $entityManager, Request $request): Response
    {

        $depositForm = $this->createForm(DepositType::class, $deposit, [
            'submit_label' => '<i class="save icon"></i>Mettre à jour',
            'submit_class' => 'ui basic green button'
        ]);
        $depositForm->handleRequest($request);

        if ($depositForm->isSubmitted() && $depositForm->isValid()) {
            $deposit->setName(strtoupper($deposit->getName()));
            $entityManager->persist($deposit);

            $entityManager->flush();

            $this->addFlash('success', 'Le dépôt a été modifié avec succès');

            return $this->redirectToRoute('app.dashboard.update_company', ['id' => $deposit->getCompany()->getId()]);
        }
        return $this->render('dashboard/deposit/update.html.twig', [
            'depositForm' => $depositForm->createView(),
            'deposit' => $deposit,
        ]);
    }

    #[isGranted('ROLE_SA')]
    #[Route('/supprimer-depot/{id}', name: 'app.dashboard.delete_deposit')]
    public function deleteDeposit(Deposit $deposit, EntityManagerInterface $entityManager): Response
    {
        $companyId = $deposit->getCompany()->getId();
        $entityManager->remove($deposit);
        $entityManager->flush();

        $this->addFlash('success', 'Dépôt supprimée avec succès');

        return $this->redirectToRoute('app.dashboard.update_company', ['id' => $companyId]);
    }

}
