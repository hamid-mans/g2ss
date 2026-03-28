<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Deposit;
use App\Entity\TVA;
use App\Entity\User;
use App\Form\BrandType;
use App\Form\CategoryType;
use App\Form\ColorType;
use App\Form\CompanyType;
use App\Form\CompanyUnitsType;
use App\Form\DepositType;
use App\Form\GenerateUserType;
use App\Form\TVAType;
use App\Repository\CompanyRepository;
use App\Repository\DepositRepository;
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
    public function index(DepositRepository $depositRepository, UserPasswordHasherInterface $hasher, UserRepository $userRepository, CompanyRepository $companyRepository, Request $request, EntityManagerInterface $entityManager): Response
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

        // MODIFIER LES UNITES DE LA SOCIETE

        $formComanyUnits = $this->createForm(CompanyUnitsType::class, $company, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $formComanyUnits->handleRequest($request);
        if ($formComanyUnits->isSubmitted() && $formComanyUnits->isValid()) {
            $companyUnits = $formComanyUnits->getData();
            $entityManager->persist($companyUnits);
            $entityManager->flush();

            $this->addFlash('success', 'Unités enregistrées !');

            return $this->redirectToRoute('app.dashboard.settings.index', [ 'tab' => 'products' ]);
        }



        // CRÉER UN DÉPÔT

        $deposit = new Deposit();
        $formCreateDeposit = $this->createForm(DepositType::class, $deposit, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $formCreateDeposit->handleRequest($request);

        if ($formCreateDeposit->isSubmitted() && $formCreateDeposit->isValid()) {
            if(count($depositRepository->findBy(['company' => $this->getUser()->getCompany()])) <= 10){
                $deposit = $formCreateDeposit->getData();
                $deposit->setCompany($this->getUser()->getCompany());
                $entityManager->persist($deposit);
                $entityManager->flush();

                $this->addFlash('success', 'Dépôt créé !');

                return $this->redirectToRoute('app.dashboard.settings.index', [
                    'tab' => 'deposits',
                ]);
            } else {
                $this->addFlash('error', 'Nombre maximum de dépôts atteint sur cette licence. Contactez votre revendeur');

                return $this->redirectToRoute('app.dashboard.settings.index', [
                    'tab' => 'deposits',
                ]);
            }
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


        // CRÉER UNE COULEUR

        $color = new Color();
        $formCreateColor = $this->createForm(ColorType::class, $color, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $formCreateColor->handleRequest($request);

        if ($formCreateColor->isSubmitted() && $formCreateColor->isValid()) {
            $color = $formCreateColor->getData();
            $color->setCompany($this->getUser()->getCompany());
            $color->setLabel(strtoupper($color->getLabel()));
            $entityManager->persist($color);
            $entityManager->flush();

            $this->addFlash('success', 'Couleur créée !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }


        // CRÉER UN TAUX DE TVA

        $tva = new TVA();
        $formCreateTVA = $this->createForm(TVAType::class, $tva, [
            'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
            'submit_class' => 'btn btn-primary',
        ]);
        $formCreateTVA->handleRequest($request);

        if ($formCreateTVA->isSubmitted() && $formCreateTVA->isValid()) {
            $tva->setCompany($this->getUser()->getCompany());

            $entityManager->persist($tva);
            $entityManager->flush();

            $this->addFlash('success', 'Taux de TVA créée !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }



        return $this->render('dashboard/settings/index.html.twig', [
            'formCompany' => $formCompany->createView(),
            'formCreateUser' => $formCreateUser->createView(),
            'formCreateCategory' => $formCreateCategory->createView(),
            'formCreateBrand' => $formCreateBrand->createView(),
            'formCreateDeposit' => $formCreateDeposit->createView(),
            'formCreateColor' => $formCreateColor->createView(),
            'formComanyUnits' => $formComanyUnits->createView(),
            'formCreateTVA' => $formCreateTVA->createView(),
        ]);
    }

    #[Route('/utilisateur/{id}', name: 'update.user')]
    public function updateUser(UserPasswordHasherInterface $hasher, Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if($user->getCompany() === $this->getUser()->getCompany()) {
            $isSuperAdmin = in_array('ROLE_SA', $user->getRoles());

            $form = $this->createForm(GenerateUserType::class, $user, [
                'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
                'submit_class' => 'btn btn-primary',
                'company' => $this->getUser()->getCompany(),
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newRoles = $user->getRoles();
                if ($isSuperAdmin) {
                    $newRoles[] = 'ROLE_SA';
                }

                $user->setRoles(array_unique($newRoles));

                if($form->get('plainPassword')->getData()) {
                    $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
                }
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

        throw $this->createNotFoundException("Le utilisateur n'existe pas !");
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
        if($category->getCompany() === $this->getUser()->getCompany()) {
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

        throw $this->createNotFoundException("La catégorie n'existe pas !");
    }

    #[Route('/categorie/supprimer/{id}', 'delete.category')]
    public function deleteCategory(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if($category && $category->getCompany() === $this->getUser()->getCompany()) {
            $entityManager->remove($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie supprimée !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }

        throw $this->createNotFoundException("La catégorie n'existe pas");
    }


    #[Route('/marque/{id}', name: 'update.brand')]
    public function updateBrand(Request $request, Brand $brand, EntityManagerInterface $entityManager): Response
    {
        if($brand && $brand->getCompany() === $this->getUser()->getCompany()) {
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

        throw $this->createNotFoundException("La marque n'existe pas !");
    }

    #[Route('/marque/supprimer/{id}', 'delete.brand')]
    public function deleteBrand(Request $request, Brand $brand, EntityManagerInterface $entityManager): Response
    {
        if($brand && $brand->getCompany() === $this->getUser()->getCompany()) {
            $entityManager->remove($brand);
            $entityManager->flush();

            $this->addFlash('success', 'Marque supprimée !');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }

        throw $this->createNotFoundException("La marque n'existe pas !");
    }


    #[Route('/depot/{id}', name: 'update.deposit')]
    public function updateDeposit(Deposit $deposit, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($deposit && $deposit->getCompany() === $this->getUser()->getCompany()) {
            $form = $this->createForm(DepositType::class, $deposit, [
                'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
                'submit_class' => 'btn btn-primary',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($deposit);
                $entityManager->flush();

                $this->addFlash('success', 'Dépôt modifié !');

                return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'deposits']);
            }

            return $this->render('dashboard/settings/deposit/update.html.twig', [
                'deposit' => $deposit,
                'form' => $form->createView(),
            ]);
        }

        throw $this->createNotFoundException("La depot n'existe pas !");
    }

    #[Route('/depot/supprimer/{id}', 'delete.deposit')]
    public function deleteDeposit(Request $request, Deposit $deposit, EntityManagerInterface $entityManager): Response
    {
        if($deposit && $deposit->getCompany() === $this->getUser()->getCompany()) {
            $entityManager->remove($deposit);
            $entityManager->flush();

            $this->addFlash('success', 'Dépôt supprimé');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'deposits']);
        }

        throw $this->createNotFoundException("Le depot n'existe pas !");
    }

    #[Route('/couleur/{id}', name: 'update.color')]
    public function updateColor(Color $color, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($color && $color->getCompany() === $this->getUser()->getCompany()) {
            $form = $this->createForm(ColorType::class, $color, [
                'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
                'submit_class' => 'btn btn-primary',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($color);
                $entityManager->flush();

                $this->addFlash('success', 'Couleur modifié !');

                return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
            }

            return $this->render('dashboard/settings/color/update.html.twig', [
                'color' => $color,
                'form' => $form->createView(),
            ]);
        }

        throw $this->createNotFoundException("La couleur n'existe pas !");
    }

    #[Route('/couleur/supprimer/{id}', 'delete.color')]
    public function deleteColor(Request $request, Color $color, EntityManagerInterface $entityManager): Response
    {
        if($color && $color->getCompany() === $this->getUser()->getCompany()) {
            $entityManager->remove($color);
            $entityManager->flush();

            $this->addFlash('success', 'Couleur supprimée');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }

        throw $this->createNotFoundException("La couleur n'existe pas !");
    }

    #[Route('/tva/{id}', name: 'update.tva')]
    public function updateTva(Tva $tva, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($tva && $tva->getCompany() === $this->getUser()->getCompany()) {
            $form = $this->createForm(TvaType::class, $tva, [
                'submit_label' => '<i class="ri ri-save-line"></i>Enregistrer',
                'submit_class' => 'btn btn-primary',
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($tva);
                $entityManager->flush();

                $this->addFlash('success', 'Taux de TVA modifié !');

                return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
            }

            return $this->render('dashboard/settings/tva/update.html.twig', [
                'tva' => $tva,
                'form' => $form->createView(),
            ]);
        }

        throw $this->createNotFoundException("La tva n'existe pas !");
    }

    #[Route('/tva/supprimer/{id}', 'delete.tva')]
    public function deleteTva(Request $request, TVA $tva, EntityManagerInterface $entityManager): Response
    {
        if($tva && $tva->getCompany() === $this->getUser()->getCompany()) {
            $entityManager->remove($tva);
            $entityManager->flush();

            $this->addFlash('success', 'Taux de TVA supprimée');

            return $this->redirectToRoute('app.dashboard.settings.index', ['tab' => 'products']);
        }

        throw $this->createNotFoundException("La tva n'existe pas !");
    }
}
