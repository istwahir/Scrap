<?php
require_once __DIR__ . '/../config.php';

class MpesaAPI {
    private $consumerKey;
    private $consumerSecret;
    private $passkey;
    private $shortcode;
    private $environment;
    private $accessToken;
    
    // Base URLs for Safaricom API
    private $sandboxBaseURL = 'https://sandbox.safaricom.co.ke';
    private $productionBaseURL = 'https://api.safaricom.co.ke';
    
    public function __construct() {
        $this->consumerKey = MPESA_CONSUMER_KEY;
        $this->consumerSecret = MPESA_CONSUMER_SECRET;
        $this->passkey = MPESA_PASSKEY;
        $this->shortcode = MPESA_SHORTCODE;
        $this->environment = MPESA_ENV;
        
        // Get access token on initialization
        $this->accessToken = $this->generateAccessToken();
    }
    
    /**
     * Generate OAuth access token
     * @return string Access token
     */
    private function generateAccessToken() {
        $credentials = base64_encode(
            $this->consumerKey . ':' . $this->consumerSecret
        );
        
        $url = $this->getBaseURL() . '/oauth/v1/generate?grant_type=client_credentials';
        
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $credentials
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false
        ]);
        
        $response = curl_exec($curl);
        
        if ($response === false) {
            throw new Exception('Failed to generate access token: ' . curl_error($curl));
        }
        
        $result = json_decode($response);
        
        if (!isset($result->access_token)) {
            throw new Exception('Invalid access token response');
        }
        
        return $result->access_token;
    }
    
    /**
     * Get base URL based on environment
     * @return string Base URL
     */
    private function getBaseURL() {
        return $this->environment === 'production' 
            ? $this->productionBaseURL 
            : $this->sandboxBaseURL;
    }
    
    /**
     * Initiate STK Push transaction
     * @param string $phone Customer phone number (254XXXXXXXXX)
     * @param float $amount Amount to charge
     * @param string $reference Payment reference
     * @param string $description Transaction description
     * @return array Response from M-Pesa
     */
    public function initiateSTKPush($phone, $amount, $reference, $description) {
        $timestamp = date('YmdHis');
        $password = base64_encode(
            $this->shortcode . 
            $this->passkey . 
            $timestamp
        );
        
        $url = $this->getBaseURL() . '/mpesa/stkpush/v1/processrequest';
        
        $curl = curl_init($url);
        
        $data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => ceil($amount),
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => MPESA_CALLBACK_URL,
            'AccountReference' => $reference,
            'TransactionDesc' => $description
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($curl);
        
        if ($response === false) {
            throw new Exception('STK push request failed: ' . curl_error($curl));
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Query STK Push transaction status
     * @param string $checkoutRequestID The CheckoutRequestID from STK push
     * @return array Transaction status
     */
    public function queryTransaction($checkoutRequestID) {
        $timestamp = date('YmdHis');
        $password = base64_encode(
            $this->shortcode . 
            $this->passkey . 
            $timestamp
        );
        
        $url = $this->getBaseURL() . '/mpesa/stkpushquery/v1/query';
        
        $data = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestID
        ];
        
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($curl);
        
        if ($response === false) {
            throw new Exception('Transaction query failed: ' . curl_error($curl));
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Process callback data
     * @param string $callbackData Raw callback data
     * @return array Processed callback data
     */
    public function processCallback($callbackData) {
        $data = json_decode($callbackData, true);
        
        if (!isset($data['Body']['stkCallback'])) {
            throw new Exception('Invalid callback data');
        }
        
        $stkCallback = $data['Body']['stkCallback'];
        
        return [
            'ResultCode' => $stkCallback['ResultCode'],
            'ResultDesc' => $stkCallback['ResultDesc'],
            'MerchantRequestID' => $stkCallback['MerchantRequestID'],
            'CheckoutRequestID' => $stkCallback['CheckoutRequestID'],
            'Amount' => $stkCallback['CallbackMetadata']['Item'][0]['Value'] ?? null,
            'MpesaReceiptNumber' => $stkCallback['CallbackMetadata']['Item'][1]['Value'] ?? null,
            'TransactionDate' => $stkCallback['CallbackMetadata']['Item'][2]['Value'] ?? null,
            'PhoneNumber' => $stkCallback['CallbackMetadata']['Item'][3]['Value'] ?? null
        ];
    }
}
