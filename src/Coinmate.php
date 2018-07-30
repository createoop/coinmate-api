<?php namespace CoinmateApi;

class Coinmate
{
    private $requestor = null;

    private $publicKey = null;

    private $privateKey = null;

    private $clientId = null;

    private $url = 'https://coinmate.io/api';

    private $nonce = null;

    private $signature = null;

    function __construct($publicKey = null, $privateKey = null, $clientId = null)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->clientId = $clientId;
        $this->requestor = new Requestor();
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setCredentials($publicKey, $privateKey, $clientId)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->clientId = $clientId;
    }

    private function  generateSignature()
    {
        // Generate unique number
        $this->nonce = str_replace('.', '', microtime(true));

        // Generate request signature
        $tmp = $this->nonce.$this->clientId.$this->publicKey;
        $tmp = hash_hmac('sha256', $tmp, $this->privateKey);

        // Set signature
        $this->signature = strtoupper($tmp);
    }

    private function splitWithCredentials(array $request = [])
    {
        $request['clientId'] = $this->clientId;
        $request['publicKey'] = $this->publicKey;

        $this->generateSignature();

        $request['nonce'] = $this->nonce;
        $request['signature'] = $this->signature;

        return $request;
    }

    /**
     * Get specified currency pair rates.
     *
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getTicker($from = null, $to = null)
    {
        return $this->requestor->sendRequest("{$this->url}/ticker", "get", [
            'currencyPair' => "{$from}_{$to}"
        ]);
    }

    /**
     * Get last transactions.
     *
     * @param  integer  $minutes
     * @return array
     */
    public function transactions($minutes = 10)
    {
        return $this->requestor->sendRequest("{$this->url}/transactions", "get", [
            'minutesIntoHistory' => $minutes
        ]);
    }

    /**
     * Account balances.
     *
     * @return array
     */
    public function balances()
    {
        return $this->requestor->sendRequest("{$this->url}/balances", "post", $this->splitWithCredentials());
    }

    /**
     * Get account transaction history.
     *
     * @param array $request
     * @return array
     */
    public function getTransactionHistory(array $request = [])
    {
        $request = $this->splitWithCredentials($request);
        return $this->requestor->sendRequest("{$this->url}/transactionHistory", "post", $request);
    }

    /**
     * Order history.
     *
     * @param  string  $currencyPair
     * @param  integer  $limit
     * @return array
     */
    public function orderHistory($currencyPair, $limit = 5)
    {
        $request = $this->splitWithCredentials([
            'currencyPair' => $currencyPair,
            'limit' => $limit
        ]);
        return $this->requestor->sendRequest("{$this->url}/orderHistory", "post", $request);
    }
}