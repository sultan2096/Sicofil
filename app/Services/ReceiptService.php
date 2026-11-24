<?php

namespace App\Services;

use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Service de génération de reçus PDF professionnels avec signature.
 */
class ReceiptService
{
    /**
     * Générer un reçu PDF pour un paiement.
     */
    public function generateReceipt(Paiement $paiement): string
    {
        $paiement->load(['unite.zone', 'unite.zone.commune', 'collecteur', 'collecteur.zone', 'commune']);

        $data = [
            'paiement' => $paiement,
            'numero_recu' => str_pad($paiement->id, 8, '0', STR_PAD_LEFT),
            'date_emission' => Carbon::now()->format('d/m/Y H:i'),
            'signature' => $this->getSignatureData($paiement),
        ];

        $pdf = Pdf::loadView('receipts.pdf', $data);

        // Sauvegarder le PDF dans storage/app/public/receipts
        $filename = "recu_{$paiement->id}_" . time() . '.pdf';
        $path = storage_path('app/public/receipts/' . $filename);

        // Créer le répertoire si nécessaire
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        return 'receipts/' . $filename;
    }

    /**
     * Obtenir les données de signature pour le reçu.
     */
    private function getSignatureData(Paiement $paiement): array
    {
        $commune = $paiement->commune ?? $paiement->unite?->zone?->commune;

        return [
            'signataire' => $commune->maire_nom ?? 'Le Maire',
            'titre' => 'Maire de ' . ($commune->nom ?? 'la Commune'),
            'date_signature' => Carbon::now()->format('d/m/Y'),
            'logo_path' => $commune->logo_path ?? null,
            'sceau' => $commune->sceau_path ?? null,
        ];
    }

    /**
     * Générer un reçu pour un paiement et retourner l'URL de téléchargement.
     */
    public function generateReceiptUrl(Paiement $paiement): string
    {
        $path = $this->generateReceipt($paiement);
        return asset('storage/' . $path);
    }

    /**
     * Télécharger directement un reçu PDF.
     */
    public function downloadReceipt(Paiement $paiement)
    {
        $path = storage_path('app/public/receipts/recu_' . $paiement->id . '_latest.pdf');

        // Si le reçu n'existe pas, le générer
        if (!file_exists($path)) {
            $relativePath = $this->generateReceipt($paiement);
            $path = storage_path('app/public/' . $relativePath);
        }

        return response()->download($path, "recu_paiement_{$paiement->id}.pdf");
    }
}
