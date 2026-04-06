<?php

namespace App\Service;

use App\Entity\Movement;
use App\Entity\Product;
use App\Repository\MovementRepository;
use Doctrine\ORM\EntityManagerInterface;

class MovementGenerator
{
    public function generate($productUnit, MovementRepository $movementRepository, EntityManagerInterface $entityManager)
    {
        $movement = new Movement();

        $movement->setProduct($productUnit->getProduct());
        $movement->setCompany($this->getUser()->getCompany());
        $movement->setType(1);
        $movement->setDeposit($productUnit->getDeposit());
        $movement->setUser($this->getUser());
        $entityManager->persist($movement);

        $entityManager->flush();
    }
}