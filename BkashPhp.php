<?php

/**
 * Bkash Payment Gateway
 *
 * @author Pranay Chakraborty
 * @link https://github.com.com/pranaycb
 */

namespace App\Libraries;

use Exception;
use Config\Services;

class BkashPhp
{
    /**
     * Bkash config
     * @var array
     */
    private array $bkash_config;

    /**
     * Bkash request base url
     * @var string
     */
    private string $base_url;


    public function __construct()
    {
        /**
         * Load cookie helper
         */
        helper('cookie');
    }


    /**
     * Set configuration
     * @param array $config
     */
    public function setConfig(array $config)
    {

        if (!array_key_exists('environment', $config)) {

            throw new Exception('Enviroment parameter is required');
        }

        switch ($config['environment']) {

            case 'sandbox':
                $this->base_url = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/';
                break;

            case 'production':
                $this->base_url = 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/';
                break;

            default:
                throw new Exception('Environment ' . $config['environment'] . ' is not allowed. Allowed environments are: sandbox, production');
        }

        unset($config['environment']);

        $this->bkash_config = $config;
    }

    /**
     * @param object $response
     * @param string|int $code
     * @throws Exception
     */
    private function getResponse($response, $code)
    {
        if ($response->statusCode === '0000' && $code === 200) {

            return $response;
        }

        throw new Exception($response->statusCode . ': ' . $response->statusMessage);
    }

    /**
     * Grant token
     * @return string
     * @throws Exception
     */
    public function _getToken()
    {

        $client = Services::curlrequest();

        try {

            $tokenResponse = $client->post($this->base_url . 'token/grant', [
                'json' => [
                    'app_key' => $this->bkash_config['app_key'],
                    'app_secret' => $this->bkash_config['app_secret'],
                ],
                'headers' => [
                    'content-type' => 'application/json',
                    'password' => $this->bkash_config['password'],
                    'username' => $this->bkash_config['username']
                ],
            ]);

            if ($tokenResponse->getStatusCode() !== 200) {

                throw new Exception('Failed to generate token. Check credentials');
            }

            $contentsObject = json_decode($tokenResponse->getBody());

            if ($contentsObject->statusCode !== '0000') {

                throw new Exception("Code: {$contentsObject->statusCode}; {$contentsObject->statusMessage}");
            }

            set_cookie('id_token', $contentsObject->id_token, '0');

            return $contentsObject->id_token;
        }

        //Error
        catch (Exception $e) {

            throw new Exception($e->getMessage());
        }
    }


    /**
     * Create payment
     * @param array $paymentData
     * @return object
     * @throws Exception
     */
    public function createPayment(array $paymentData)
    {
        $authToken = $this->_getToken();

        $client = Services::curlrequest();

        try {

            $response = $client->post($this->base_url . 'create', [
                'json' => $paymentData,
                'headers' => [
                    'authorization' => $authToken,
                    'x-app-key' => $this->bkash_config['app_key'],
                    'content-type' => 'application/json'
                ],
            ]);

            $statusCode = $response->getStatusCode();

            $response = json_decode($response->getBody());

            return $this->getResponse($response, $statusCode);
        }

        //Error
        catch (Exception $e) {

            throw new Exception($e->getMessage());
        }
    }


    /**
     * Execute payment
     * @param string $paymentId
     * @return object
     * @throws Exception
     */
    public function executePayment(string $paymentId)
    {
        $authToken = get_cookie('id_token');

        $client = Services::curlrequest();

        try {

            $response = $client->post($this->base_url . 'execute', [
                'json' => [
                    'paymentID' => $paymentId
                ],
                'headers' => [
                    'authorization' => $authToken,
                    'x-app-key' => $this->bkash_config['app_key'],
                    'content-type' => 'application/json'
                ],
            ]);

            $statusCode = $response->getStatusCode();

            $response = json_decode($response->getBody());

            return $this->getResponse($response, $statusCode);
        }

        //Error
        catch (Exception $e) {

            throw new Exception($e->getMessage());
        }
    }


    /**
     * Query payment
     * @param array $trxId
     * @return object
     * @throws Exception
     */
    public function queryPayment(string $trxId)
    {
        $authToken = get_cookie('id_token');

        $client = Services::curlrequest();

        try {

            $response = $client->get($this->base_url . 'general/searchTransaction', [
                'json' => [
                    'trxID' => $trxId
                ],
                'headers' => [
                    'authorization' => $authToken,
                    'x-app-key' => $this->bkash_config['app_key'],
                    'content-type' => 'application/json'
                ],
            ]);

            $statusCode = $response->getStatusCode();

            $response = json_decode($response->getBody());

            return $this->getResponse($response, $statusCode);
        }

        //Error
        catch (Exception $e) {

            throw new Exception($e->getMessage());
        }
    }
}
