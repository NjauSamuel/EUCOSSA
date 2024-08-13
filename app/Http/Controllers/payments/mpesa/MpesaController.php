<?php

namespace App\Http\Controllers\payments\mpesa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MpesaController extends Controller
{
    public function getAccessToken(){
        $url = env('MPESA_ENV') === '0'
        ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' 
        : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init($url);
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf8'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_USERPWD => env('MPESA_CONSUMER_KEY').':'.env('MPESA_CONSUMER_SECRET'),
                CURLOPT_SSL_VERIFYPEER => false, // Disables SSL certificate verification
                CURLOPT_SSL_VERIFYHOST => false, // Disables SSL host verification
            )
        );
        $response = curl_exec($curl);

        // Check for errors
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            dd("cURL Error #: " . curl_errno($curl) . " - " . $error_msg);
        }

        curl_close($curl);
        
        $responseData = json_decode($response, true); // Decode the JSON response into an array

        //return view('welcome', ['responseData' => $responseData]);
        //dd($responseData['access_token']);
        return $responseData['access_token'];
    }

    public function makeHttp($url, $body){

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer '. $this->getAccessToken()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, \json_encode($body));
       
        $curl_response = curl_exec($curl);
        \curl_close($curl);

        return $curl_response;
    }
}
