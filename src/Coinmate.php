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
        $this->requestor = new Requestor();
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->clientId = $clientId;
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

    private function splitWithCredentials(array $request)
    {
        $request['clientId'] = $this->clientId;
        $request['publicKey'] = $this->publicKey;
        $request['nonce'] = $this->nonce;

        $this->generateSignature();

        $request['signature'] = $this->signature;

        return $request;
    }

    public function getTicker($from = null, $to = null)
    {
        return $this->requestor->sendRequest("{$this->url}/ticker", "get", [
            'currencyPair' => "{$from}_{$to}"
        ]);
    }

    public function transactions($minutes = 10)
    {
        return $this->requestor->sendRequest("{$this->url}/transactions", "get", [
            'minutesIntoHistory' => $minutes
        ]);
    }

    public function balances()
    {

    }
}