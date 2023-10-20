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


     /**
     * Just change these when you are in a go Live //production
     * Payements with mobile money in scandbox
     * No interfaces but you can add view for it
     * Post request Api
     * @param TestRequest $request
     * @return RedirectResponse
     */
    public function buy(TestRequest $request)
    {
        //get amount
        $amount = $request->validated();
        //important function that generates X-Coretionnal-id
        function generateRandomCorrelationId() {
            // You can customize your correlation ID generation here
            return 'CORR-' . uniqid();
        }
        //verry important

        //

            //insert here your params

        //
        // Replace the following information with your real access and application data
        $customerKey = 'your customer key';
        $customerSecret = 'your customer secret';
        $accessToken =  'your access token';
        // API URL
        //https://devapi.mvola.mg/mvola/mm/transactions/type/merchantpay/1.0.0/
        // scandbox api
        $credentials = base64_encode($customerKey . ':' . $customerSecret);
        $correlationId = 'X-CorrelationId: ' . generateRandomCorrelationId();
        //$url = 'https://devapi.mvola.mg/token';
        $apiUrl = 'https://devapi.mvola.mg/$accessToken'; // Replace with the actual token endpoint
        // You can customize the reference format
        $originalTransactionReference = 'TX' . uniqid();
        // Data you want to send in the POST request (in JSON format for example)
        $postData = json_encode([
            'amount' => $amount, // Replace with the transaction amount
            'currency' => 'Ar', // Replace with currency code
            'descriptionText' => 'description', // Replace with description
            'requestDate' => '2023-10-13T12:00:00.000Z', // Replace with transaction date
            'debitParty' => '0343500003', // Replace with the customer's phone number
            'creditParty' => '0343500004', // Replace with the merchant's phone number
            'metadata' => [
                'partnerName' => 'You partner name', // Replace with partner name
                'requestingOrganisation' => 'Transaction Reference', // Replace with the transaction reference
                'originalTransactionReference' => $originalTransactionReference, // Random transaction references
                'fc' => 'USD', // Replace with foreign currency
                'amountFc' => 100.50, // Replace with amount based on foreign currency
            ]
        ]);
        //Initialisation of curl
        $curl = curl_init($apiUrl);
        // Configuring cURL options
        $headers = [
            'Authorization: Bearer ' . $accessToken, // Use the token in the Bearer header
            //correlationId, instanciation in the amount variable
            $correlationId,
            //languages Fr or Mg
            'UserLanguage: MG',
            //Types
            'Content-Type: application/json', // Specify the content type in JSON
            //callback url if success | change if you need to change it
            'X-Callback-URL: http://example.com/',
            //appliaction cache
            'Cache-Control: no-cache'
        ];
        // Set cURL to return the response as a string instead of directly outputting it.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Configure cURL to perform a POST request.
        curl_setopt($curl, CURLOPT_POST, 1);
        // Set the data to be sent in the POST request. The data is stored in the $postData variable.
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        // Set the HTTP headers for the request, which are defined in the $headers array.
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        // curl response
        $response = curl_exec($curl);
        //If no response
        if ($response === false) {
            die('Erreur cURL : ' . curl_error($curl));
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        //If it's finished then redirect to a callBack url || it doesn't work in the scandbox
        //but if you need to verify these if success or not get the response code
        // Check if the HTTP request was successful (usually 200 OK)
        if ($httpCode === 200) {
            return redirect()->route('index',)->with('success', 'Payement successful');
        } else {
            //if error
            return redirect()->route('index')->with('error', 'Failure');
        }
    }

}
