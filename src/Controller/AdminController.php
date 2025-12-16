<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Deposit;
use App\Entity\User;
use App\Form\CompanyType;
use App\Form\DepositType;
use App\Form\GenerateUserType;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration', 'app.dashboard.')]
class AdminController extends AbstractController
{
    #[isGranted('ROLE_SA')]
    #[Route('/', name: 'index')]
    public function index(CompanyRepository $companyRepository): Response
    {
        return $this->render('dashboard/dashboard.html.twig', [
            'companies' => $companyRepository->findAll(),
        ]);
    }

    #[isGranted('ROLE_SA')]
    #[Route('/nouvelle-societe', name: 'create_company')]
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
    #[Route('/modifier-societe/{id}', name: 'update_company')]
    public function updateCompany(UserRepository $userRepository, EntityManagerInterface $entityManager, ?Company $company, Request $request): Response
    {
        if($company)
        {
            $users = $userRepository->findBy(['company' => $company], ['lastname' => 'ASC']);

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
                'users' => $users,
            ]);
        }
        else
        {
            return $this->redirectToRoute('app.dashboard.index');
        }
    }

    #[isGranted('ROLE_SA')]
    #[Route('/supprimer-societe/{id}', name: 'delete_company')]
    public function deleteCompany(?Company $company, EntityManagerInterface $entityManager): Response
    {
        if($company)
        {
            $entityManager->remove($company);
            $entityManager->flush();

            $this->addFlash('success', 'Société supprimée avec succès');

            return $this->redirectToRoute('app.dashboard.index');
        }
        else
        {
            return $this->redirectToRoute('app.dashboard.index');
        }
    }




    // ##### DEPOSITS #####

    #[isGranted('ROLE_SA')]
    #[Route('/nouveau-depot/{id}', 'create.deposit')]
    public function createDeposit(?Company $company, EntityManagerInterface $entityManager, Request $request): Response
    {
        if($company)
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
        else
        {
            return $this->redirectToRoute('app.dashboard.index');
        }
    }

    #[isGranted('ROLE_SA')]
    #[Route('/modifier-depot/{id}', 'update.deposit')]
    public function updateDeposit(UserRepository $userRepository, ?Deposit $deposit, EntityManagerInterface $entityManager, Request $request): Response
    {
        if($deposit)
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
                'deposit' => $deposit
            ]);
        }
        else
        {
            return $this->redirectToRoute('app.dashboard.index');
        }
    }

    #[isGranted('ROLE_SA')]
    #[Route('/supprimer-depot/{id}', name: 'delete_deposit')]
    public function deleteDeposit(?Deposit $deposit, EntityManagerInterface $entityManager): Response
    {
        if($deposit)
        {
            $companyId = $deposit->getCompany()->getId();
            $entityManager->remove($deposit);
            $entityManager->flush();

            $this->addFlash('success', 'Dépôt supprimée avec succès');

            return $this->redirectToRoute('app.dashboard.update_company', ['id' => $companyId]);
        }
        else
        {
            return $this->redirectToRoute('app.dashboard.index');
        }
    }

    // #### UTILISATEURS ####

    #[isGranted('ROLE_SA')]
    #[Route('/generer-utilisateur/{id}', name: 'create_user')]
    public function createUser(UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager, Company $company, Request $request): Response
    {
        $user = new User();
        $userForm = $this->createForm(GenerateUserType::class, $user, [
            'submit_label' => '<i class="plus icon"></i>Enregistrer',
            'submit_class' => 'ui basic blue button',
            'company' => $company,
        ]);
        $userForm->handleRequest($request);


        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setCompany($company);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($hasher->hashPassword($user, $userForm->get('plainPassword')->getData()));

            foreach ($userForm->getData()->getDeposits() as $deposit)
            {
                $user->addDeposit($deposit);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "L'utilisateur a bien été créé");

            return $this->redirectToRoute('app.dashboard.update_company', ['id' => $company->getId()]);
        }

        return $this->render('dashboard/user/create.html.twig', [
            'id' => $company->getId(),
            'userForm' => $userForm->createView(),
        ]);
    }

    #[isGranted('ROLE_SA')]
    #[Route('/modifier-utilisateur/{id}', name: 'update_user')]
    public function updateUser(UserRepository $userRepository, ?User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if($user)
        {
            $userForm = $this->createForm(GenerateUserType::class, $user, [
                'submit_label' => '<i class="save icon"></i>Mettre à jour',
                'submit_class' => 'ui basic green button'
            ]);
            $userForm->handleRequest($request);

            if ($userForm->isSubmitted() && $userForm->isValid()) {

            }

            return $this->render('dashboard/user/update.html.twig', [
                'userForm' => $userForm->createView(),
                'user' => $user
            ]);
        }
        else
        {
            return $this->redirectToRoute('app.dashboard.index');
        }
    }

    #[isGranted('ROLE_SA')]
    #[Route('/supprimer-utilisateur/{id}', name: 'delete_user')]
    public function deleteUser(UserRepository $userRepository, ?User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if($user)
        {
            $companyId = $user->getCompany()->getId();

            $entityManager->remove($user);
            $entityManager->flush();

            return $this->redirectToRoute('app.dashboard.update_company', ['id' => $companyId]);
        }
        else
        {
            return $this->redirectToRoute('app.dashboard.index');
        }
    }

}
