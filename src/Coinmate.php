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

    /**
     * Set Coinmate API URL
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set credentials for Coinmate User
     *
     * @param string $publicKey
     * @param string $privateKey
     * @param string $clientId
     * @return $this
     */
    public function setCredentials($publicKey, $privateKey, $clientId)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->clientId = $clientId;
        return $this;
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

    /**
     * Implode request array with user credentials
     *
     * @param array $request
     * @return array
     */
    private function implodeWithCredentials(array $request = [])
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
        return $this->requestor->sendRequest("{$this->url}/balances", "post", $this->implodeWithCredentials());
    }

    /**
     * Get account transaction history.
     *
     * @param array $request
     * @return array
     */
    public function getTransactionHistory(array $request = [])
    {
        $request = $this->implodeWithCredentials($request);
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
        $request = $this->implodeWithCredentials([
            'currencyPair' => $currencyPair,
            'limit' => $limit
        ]);
        return $this->requestor->sendRequest("{$this->url}/orderHistory", "post", $request);
    }

    /**
     * List of open orders.
     *
     * @param  string  $currencyPair
     * @return array
     */
    public function openOrders($currencyPair)
    {
        $request = $this->implodeWithCredentials([
            'currencyPair' => $currencyPair,
        ]);
        return $this->requestor->sendRequest("{$this->url}/openOrders", "post", $request);
    }

    /**
     * Cancel order.
     *
     * @param  string  $orderId
     * @return array
     */
    public function cancelOrder($orderId)
    {
        $request = $this->implodeWithCredentials([
            'orderId' => $orderId,
        ]);
        return $this->requestor->sendRequest("{$this->url}/cancelOrder", "post", $request);
    }

    /**
     * Cancel order with info.
     *
     * @param  string  $orderId
     * @return array
     */
    public function cancelOrderWithInfo($orderId)
    {
        $request = $this->implodeWithCredentials([
            'orderId' => $orderId,
        ]);
        return $this->requestor->sendRequest("{$this->url}/cancelOrderWithInfo", "post", $request);
    }

    /**
     * Creates new order for buying of type limit order.
     *
     * @param float $amount
     * @param float $price
     * @param string $currencyPair
     */
    public function buyLimitOrder($amount, $price, $currencyPair)
    {
        $request = $this->implodeWithCredentials([
            'amount' => $amount,
            'price' => $price,
            'currencyPair' => $currencyPair
        ]);
        return $this->requestor->sendRequest("{$this->url}/buyLimit", "post", $request);
    }

    /**
     * Creates new order for selling of type limit order
     *
     * @param float $amount
     * @param float $price
     * @param string $currencyPair
     */
    public function sellLimitOrder($amount, $price, $currencyPair)
    {
        $request = $this->implodeWithCredentials([
            'amount' => $amount,
            'price' => $price,
            'currencyPair' => $currencyPair
        ]);
        return $this->requestor->sendRequest("{$this->url}/sellLimit", "post", $request);
    }
}