<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestRequest;
use App\Models\Clien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use MVolaphp\Exception;
use MVolaphp\Money;
use MVolaphp\Objects\KeyValue;
use MVolaphp\Objects\PayIn;
use MVolaphp\Objects\Phone;
use MVolaphp\Telma as MVola;

class TestController extends Controller
{
    public function index(): View
    {
        return view('index');
    }

    //il y a encore une erreur
    public function buy(TestRequest $request): RedirectResponse
{
    try {
        // Récupérer le client numéro 1
        $client = Clien::findOrFail(1);

        // Instantiation de MVola
        $credentials = array(
            // Identifiant client
            'client_id'         => $client->id,
            // Secret client
            'client_secret'     => 'ElmVrpbVtpsECW2wCK6SowK84SYa',
            // Numéro du marchand
            'merchant_number'   => '0343500003',
            // Activer le mode production (true)
            'production'        => true,
            // Nom de la société
            'partner_name'      => "fusiongift",
            // Langue (par exemple 'MG' pour le malgache)
            'lang'              => 'MG'
        );

        // Répertoire pour le cache
        $cache = __DIR__.'/cache';

        // Instanciation de MVola
        $mvola = new MVola($credentials, $cache);

        // Détails de paiement
        $payDetails = new PayIn();

        // Montant (1000 ar ou ariary)
        $money = new Money('MGA', $request->validated());
        $payDetails->amount = $money;


        // Utilisateur pour récupérer le montant
        $debit = new KeyValue();
        $debit->addPairObject(new Phone("0343500004"));
        $payDetails->debitParty = $debit;


        // Description du paiement
        $payDetails->descriptionText = "Test paiement";

        $meta = new KeyValue();
        $meta->add('partnerName', "fusiongift");

        // Informations métadonnées
        $payDetails->metadata = $meta;

        // Définir l'URL de rappel
        $mvola->setCallbackUrl("https://example.com/mycallback");
        $convertion = implode(',', $mvola->payIn($payDetails));
        // Effectuer un paiement
        $response = $convertion;
        dd($response);

        // Convertir la réponse en JSON
        $responseJson = json_encode($response);

        // Afficher la réponse en tant que chaîne de caractères
        dd($responseJson);

        // Si nécessaire, décoder la réponse en tant que tableau PHP
        // $responseArray = json_decode($responseJson, true);
        // dd($responseArray);

        return redirect()->route('index');
    } catch (Exception $e) {
        // Gérer l'exception ici
        // Vous pouvez afficher l'erreur ou la journaliser
        // Exemple : Log::error($e->getMessage());
        return redirect()->route('index')->with('error', 'Une erreur est survenue lors du paiement.');
    }
}

}
