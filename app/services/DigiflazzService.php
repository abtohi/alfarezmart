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
        $this->mode = $settingModel->get('digiflazz_mode', 'development');
        
        if ($this->mode === 'production') {
            $this->apiKey = $settingModel->get('digiflazz_api_key_prod', '');
        } else {
            $this->apiKey = $settingModel->get('digiflazz_api_key_dev', '');
        }
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
        $bankUpper = strtoupper(trim($bank));

        $payload = [
            'username' => $this->username,
            'amount' => $amount,
            'Bank' => $bankUpper,
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
        $sign = md5($this->username . $this->apiKey . $customerNo);
        $payload = [
            'username' => $this->username,
            'customer_no' => $customerNo,
            'sign' => $sign
        ];
        
        if ($this->mode === 'development') {
            $payload['testing'] = true;
        }
        
        return $this->sendRequest('/inquiry-pln', $payload);
    }

    /**
     * Inquiry Postpaid Bill
     * @param string $sku
     * @param string $customerNo
     * @param string $refId
     * @param int|null $amount Denomination amount for e-money postpaid inquiry
     * @param int|null $year Tax year for PBB inquiry
     */
    public function inquiryPostpaid(string $sku, string $customerNo, string $refId, ?int $amount = null, ?int $year = null) {
        $sign = md5($this->username . $this->apiKey . $refId);
        $payload = [
            'commands' => 'inq-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign
        ];
        
        if ($amount !== null && $amount > 0) {
            $payload['amount'] = (int)$amount;
        }

        if ($year !== null && $year > 1900) {
            $payload['year'] = (int)$year;
        }
        
        if ($this->mode === 'development') {
            $payload['testing'] = true;
        }

        return $this->sendRequest('/transaction', $payload);
    }

    /**
     * Pay Postpaid Bill
     * @param string $sku
     * @param string $customerNo
     * @param string $refId
     * @param int|null $amount
     */
    public function payPostpaid(string $sku, string $customerNo, string $refId, ?int $amount = null) {
        $sign = md5($this->username . $this->apiKey . $refId);
        $payload = [
            'commands' => 'pay-pasca',
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign
        ];
        
        if ($amount !== null && $amount > 0) {
            $payload['amount'] = (int)$amount;
        }

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
                'message' => 'Username atau API Key Digiflazz belum dikonfigurasi di menu Pengaturan PPOB.'
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
        // Use a longer timeout for pricelist sync (thousands of products from Digiflazz)
        $isSync = strpos($endpoint, 'price-list') !== false;
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, $isSync ? 300 : 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

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
        
        // --- CATCHER / LOGGER FOR ALL DIGIFLAZZ REQUESTS ---
        $logData = date('Y-m-d H:i:s') . " | Endpoint: $endpoint\n";
        $logData .= "Request Payload:\n" . $jsonPayload . "\n";
        $logData .= "Response Code: " . $httpCode . "\n";
        $logData .= "Response Body:\n" . $response . "\n";
        if ($err) {
            $logData .= "cURL Error:\n" . $err . "\n";
        }
        $logData .= str_repeat("-", 50) . "\n\n";
        @file_put_contents(STORAGE_PATH . '/logs/digiflazz_debug.log', $logData, FILE_APPEND);
        // ----------------------------------------------------

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
