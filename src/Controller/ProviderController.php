<?php

namespace App\Controller;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[isGranted("ROLE_USER")]
#[Route('/fournisseurs/', 'app.dashboard.provider.')]
final class ProviderController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(Request $request, EntityManagerInterface $entityManager, ProviderRepository $providerRepository): Response
    {
        $provider = new Provider();

        $createProviderForm = $this->createForm(ProviderType::class, $provider, [
            'submit_class' => 'btn btn-primary',
            'submit_label' => "<i class='ri ri-add-line'></i> Ajouter"
        ]);
        $createProviderForm->handleRequest($request);

        if ($createProviderForm->isSubmitted() && $createProviderForm->isValid()) {
            $provider->setAddress1(strtoupper($provider->getAddress1()));
            $provider->setAddress2(strtoupper($provider->getAddress2()));

            $provider->setAddress1Liv($provider->getAddress1());
            $provider->setAddress2Liv($provider->getAddress2());
            $provider->setCityLiv($provider->getCity());
            $provider->setCopLiv($provider->getCop());
            $provider->setName(strtoupper($provider->getName()));
            $provider->setCity(strtoupper($provider->getCity()));
            $provider->setCompany($this->getUser()->getCompany());

            $entityManager->persist($provider);
            $entityManager->flush();

            $this->addFlash('success', 'Fournisseur créé !');

            return $this->redirectToRoute('app.dashboard.provider.index');
        }

        return $this->render('dashboard/provider/index.html.twig', [
            'providers' => $providerRepository->findBy(['company' => $this->getUser()->getCompany()]),
            'createProviderForm' => $createProviderForm->createView(),
        ]);
    }

    #[Route('{id}', 'update')]
    public function update(Request $request, Provider $provider, EntityManagerInterface $entityManager): Response
    {
        $formUpdateProvider = $this->createForm(ProviderType::class, $provider, [
            'submit_class' => 'btn btn-primary',
            'submit_label' => "<i class='ri ri-save-line'></i> Enregistrer"
        ]);
        $formUpdateProvider->handleRequest($request);

        if ($formUpdateProvider->isSubmitted() && $formUpdateProvider->isValid()) {
            $provider->setAddress1(strtoupper($provider->getAddress1()));
            $provider->setAddress2(strtoupper($provider->getAddress2()));

            $provider->setName(strtoupper($provider->getName()));
            $provider->setCity(strtoupper($provider->getCity()));

            $entityManager->persist($provider);
            $entityManager->flush();

            $this->addFlash('success', 'Fournisseur modifié !');

            return $this->redirectToRoute('app.dashboard.provider.update', ['id' => $provider->getId()]);
        }

        return $this->render('dashboard/provider/update.html.twig', [
            'provider' => $provider,
            'form' => $formUpdateProvider->createView(),
        ]);
    }

    #[Route('supprimer/{id}', 'delete')]
    public function delete(Request $request, Provider $provider, EntityManagerInterface $entityManager): Response
    {
        if($provider->getCompany() == $this->getUser()->getCompany()) {
            $entityManager->remove($provider);
            $entityManager->flush();

            $this->addFlash('success', 'Fournisseur supprimé !');

            return $this->redirectToRoute('app.dashboard.provider.index');
        }

        throw $this->createNotFoundException();
    }
}
