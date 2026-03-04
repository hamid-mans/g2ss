<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\ProductUnitRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
#[Route('/export/', name: 'app.export.')]
final class ExportController extends AbstractController
{
    #[Route('produits', name: 'products')]
    public function products(ProductRepository $productRepository): StreamedResponse
    {
        $products = $productRepository->findBy([
            'company' => $this->getUser()->getCompany()
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Catalogue Produits');

        $headers = ['Réf Interne', 'Désignation', 'Catégorie', 'Prix Vente', 'Stock (Unités)', 'Réf Fournisseur'];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', $header);
            $columnIndex++;
        }

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('F2F2F2');

        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValue('A' . $row, $product->getRefInterne());
            $sheet->setCellValue('B' . $row, $product->getDesignation());
            $sheet->setCellValue('C' . $row, $product->getCategory() ? $product->getCategory()->getLabel() : 'N/C');
            $sheet->setCellValue('D' . $row, $product->getSellPrice() . ' €');
            $sheet->setCellValue('E' . $row, count($product->getProductUnits()));
            $sheet->setCellValue('F' . $row, $product->getRefSupplier());
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->createExcelResponse($spreadsheet, 'export_produits');
    }

    #[Route('unites', name: 'units')]
    public function units(ProductUnitRepository $unitRepository): StreamedResponse
    {
        // On récupère les unités dont le produit appartient à la compagnie de l'user
        $units = $unitRepository->createQueryBuilder('u')
            ->join('u.product', 'p')
            ->where('p.company = :company')
            ->setParameter('company', $this->getUser()->getCompany())
            ->getQuery()
            ->getResult();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Liste des Unités');

        // Entêtes spécifiques aux unités
        $headers = ['Numéro de Série', 'Produit (Réf)', 'Désignation', 'Dépôt', 'Prix Achat', 'Description'];
        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($columnIndex . '1', $header);
            $columnIndex++;
        }

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('F2F2F2');

        $row = 2;
        foreach ($units as $unit) {
            $sheet->setCellValue('A' . $row, $unit->getSerialNumber());
            $sheet->setCellValue('B' . $row, $unit->getProduct()->getRefInterne());
            $sheet->setCellValue('C' . $row, $unit->getProduct()->getDesignation());
            $sheet->setCellValue('D' . $row, $unit->getDeposit() ? $unit->getDeposit()->getName() : 'N/C');
            $sheet->setCellValue('E' . $row, $unit->getBuyPrice() ? $unit->getBuyPrice() . ' €' : '0.00 €');
            $sheet->setCellValue('F' . $row, $unit->getDescription());
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->createExcelResponse($spreadsheet, 'export_unites_series');
    }

    /**
     * Méthode privée pour éviter la répétition de la création de la réponse Streamed
     */
    private function createExcelResponse(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $fileNameFull = $filename . '_' . date('d-m-Y') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileNameFull . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}