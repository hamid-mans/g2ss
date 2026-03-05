<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\User;
use App\Form\BrandType;
use App\Form\CategoryType;
use App\Form\CompanyType;
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

#[IsGranted("ROLE_ADMIN")]
#[Route('/parametres', 'app.dashboard.settings.')]
final class SettingsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UserPasswordHasherInterface $hasher, UserRepository $userRepository, CompanyRepository $companyRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        // MODIFIER LA SOCIÉTÉ

        $company = $companyRepository->find($this->getUser()->getCompany());
        $formCompany = $this->createForm(CompanyType::class, $company, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $formCompany->handleRequest($request);

        if ($formCompany->isSubmitted() && $formCompany->isValid()) {
            $company = $formCompany->getData();
            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash('success', 'Société enregistrée !');

            return $this->redirectToRoute('app.dashboard.settings.index');
        }



        // CRÉER UN UTILISATEUR

        $user = new User();
        $formCreateUser = $this->createForm(GenerateUserType::class, $user, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'company' => $this->getUser()->getCompany(),
            'submit_class' => 'btn btn-primary',
        ]);
        $formCreateUser->handleRequest($request);

        if ($formCreateUser->isSubmitted() && $formCreateUser->isValid()) {
            $email = $formCreateUser->get('email')->getData();
            $password = $formCreateUser->get('plainPassword')->getData();

            if(count($userRepository->findBy(['company' => $this->getUser()->getCompany()])) >= 5) {
                $this->addFlash('error', "Nombre d'utilisateurs sur cette licence atteint. Veuillez vous rapprocher de votre revendeur.");
            } else {
                if(empty($password)) {
                    $this->addFlash('error', "Le mot de passe est obligatoire.");
                } else {
                    if(empty($email)) {
                        $this->addFlash('error', "L'email est obligatoire.");
                    } else {
                        $user->setPassword($hasher->hashPassword($user, $password));
                        $user->setCompany($this->getUser()->getCompany());
                        $user->setRoles(['ROLE_USER']);
                        $entityManager->persist($user);
                        $entityManager->flush();

                        $this->addFlash('success', 'Utilisateur créé !');
                    }
                }
            }

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'users']);
        }



        // CRÉER UNE CATÉGORIE

        $category = new Category();
        $formCreateCategory = $this->createForm(CategoryType::class, $category, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $formCreateCategory->handleRequest($request);

        if ($formCreateCategory->isSubmitted() && $formCreateCategory->isValid()) {
            $category = $formCreateCategory->getData();
            $category->setCompany($this->getUser()->getCompany());
            $category->setLabel(strtoupper($category->getLabel()));
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }



        // CRÉER UNE MARQUE

        $brand = new Brand();
        $formCreateBrand = $this->createForm(BrandType::class, $brand, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $formCreateBrand->handleRequest($request);

        if ($formCreateBrand->isSubmitted() && $formCreateBrand->isValid()) {
            $brand = $formCreateBrand->getData();
            $brand->setCompany($this->getUser()->getCompany());
            $brand->setLabel(strtoupper($brand->getLabel()));
            $entityManager->persist($brand);
            $entityManager->flush();

            $this->addFlash('success', 'Marque créée !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }



        return $this->render('dashboard/settings/index.html.twig', [
            'formCompany' => $formCompany->createView(),
            'formCreateUser' => $formCreateUser->createView(),
            'formCreateCategory' => $formCreateCategory->createView(),
            'formCreateBrand' => $formCreateBrand->createView(),
        ]);
    }

    #[Route('/utilisateur/{id}', name: 'update.user')]
    public function updateUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GenerateUserType::class, $user, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
            'company' => $this->getUser()->getCompany(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'users']);
        }

        return $this->render('dashboard/settings/user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/supprimer/{id}', 'delete.user')]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if($user !== null) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimée !');
        } else {
            $this->addFlash('error', "L'utilisateur n'existe pas !");
        }

        return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'users']);
    }


    #[Route('/categorie/{id}', name: 'update.category')]
    public function updateCategory(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setLabel(strtoupper($category->getLabel()));
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie modifié !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }

        return $this->render('dashboard/settings/category/update.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/categorie/supprimer/{id}', 'delete.category')]
    public function deleteCategory(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if($category !== null) {
            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée !');
        } else {
            $this->addFlash('error', "La catégorie n'existe pas !");
        }

        return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
    }


    #[Route('/marque/{id}', name: 'update.brand')]
    public function updateBrand(Request $request, Brand $brand, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BrandType::class, $brand, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($brand);
            $entityManager->flush();

            $this->addFlash('success', 'Marque modifié !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }

        return $this->render('dashboard/settings/brand/update.html.twig', [
            'brand' => $brand,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/marque/supprimer/{id}', 'delete.brand')]
    public function deleteBrand(Request $request, Brand $brand, EntityManagerInterface $entityManager): Response
    {
        if($brand !== null) {
            $entityManager->remove($brand);
            $entityManager->flush();

            $this->addFlash('success', 'Marque supprimée !');
        } else {
            $this->addFlash('error', "La marque n'existe pas !");
        }

        return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
    }
}
