<?php
/**
 * Digiflazz API Service
 * Handles all communication with Digiflazz API
 */

class DigiflazzService {
    private string $username;
    private string $apiKey;
    private string $mode;
    private string $baseUrl = 'https://api.digiflazz.com/v1';

    public function __construct() {
        $settingModel = new SettingModel();
        $this->username = $settingModel->get('digiflazz_username', '');
        $this->apiKey = $settingModel->get('digiflazz_api_key', '');
        $this->mode = $settingModel->get('digiflazz_mode', 'development');
    }

    /**
     * Check Digiflazz Deposit Balance
     */
    public function getBalance() {
        $sign = md5($this->username . $this->apiKey . "depo");
        $payload = [
            'cmd' => 'deposit',
            'username' => $this->username,
            'sign' => $sign
        ];
        return $this->sendRequest('/cek-saldo', $payload);
    }

    /**
     * Request Deposit Ticket
     */
    public function createDeposit(float $amount, string $bank, string $ownerName) {
        $sign = md5($this->username . $this->apiKey . "deposit");
        $payload = [
            'username' => $this->username,
            'amount' => $amount,
            'Bank' => $bank,
            'owner_name' => $ownerName,
            'sign' => $sign
        ];
        return $this->sendRequest('/deposit', $payload);
    }


    /**
     * Get Price List (prepaid / pasca)
     */
    public function getPriceList(string $type = 'prepaid') {
        $sign = md5($this->username . $this->apiKey . "pricelist");
        $payload = [
            'cmd' => $type,
            'username' => $this->username,
            'sign' => $sign
        ];
        return $this->sendRequest('/price-list', $payload);
    }

    /**
     * Create Prepaid Transaction
     */
    public function createTransaction(string $sku, string $customerNo, string $refId) {
        $sign = md5($this->username . $this->apiKey . $refId);
        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign
        ];
        
        // For development/testing mode, we can append testing=true if needed by Digiflazz
        if ($this->mode === 'development') {
            $payload['testing'] = true;
        }

        return $this->sendRequest('/transaction', $payload);
    }

    /**
     * Check status of an existing prepaid transaction.
     * Digiflazz returns current status when the same ref_id is re-submitted.
     * Use this to poll for pending transactions when webhook hasn't fired.
     */
    public function checkTransaction(string $sku, string $customerNo, string $refId) {
        $sign = md5($this->username . $this->apiKey . $refId);
        $payload = [
            'username'       => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no'    => $customerNo,
            'ref_id'         => $refId,
            'sign'           => $sign
        ];
        return $this->sendRequest('/transaction', $payload);
    }

    /**
     * Inquiry PLN Prepaid Customer Name
     */
    public function inquiryPLN(string $customerNo) {
        $refId = uniqid('PLNINQ');
        $sign = md5($this->username . $this->apiKey . $refId);
        $payload = [
            'commands' => 'pln-subscribe',
            'buyer_sku_code' => 'pln',
            'username' => $this->username,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign
        ];
        
        if ($this->mode === 'development') {
            $payload['testing'] = true;
        }

        return $this->sendRequest('/transaction', $payload);
    }

    /**
     * Inquiry Postpaid Bill
     */
    public function inquiryPostpaid(string $sku, string $customerNo, string $refId) {
        $sign = md5($this->username . $this->apiKey . $refId);
        $payload = [
            'commands' => 'inq-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign
        ];
        
        if ($this->mode === 'development') {
            $payload['testing'] = true;
        }

        return $this->sendRequest('/transaction', $payload);
    }

    /**
     * Pay Postpaid Bill
     */
    public function payPostpaid(string $sku, string $customerNo, string $refId) {
        $sign = md5($this->username . $this->apiKey . $refId);
        $payload = [
            'commands' => 'pay-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign
        ];
        
        if ($this->mode === 'development') {
            $payload['testing'] = true;
        }

        return $this->sendRequest('/transaction', $payload);
    }

    /**
     * Send HTTP POST request to Digiflazz API
     */
    private function sendRequest(string $endpoint, array $payload) {
        if (empty($this->username) || empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'Digiflazz API credentials are not configured in .env'
            ];
        }

        $url = $this->baseUrl . $endpoint;
        $jsonPayload = json_encode($payload);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Disable SSL verification for local dev if needed, but better to keep it true in prod
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        // curl_close is deprecated in PHP 8.0+

        if ($err) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $err
            ];
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Failed to parse JSON response from Digiflazz',
                'raw' => $response
            ];
        }

        // Digiflazz wraps everything in 'data' object usually
        return [
            'success' => true,
            'http_code' => $httpCode,
            'data' => $decodedResponse['data'] ?? $decodedResponse
        ];
    }
}
