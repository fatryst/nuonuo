<?php

namespace Fatryst\NuoNuo;

use Cache;
use Fatryst\NuoNuo\Exception\NuoNuoException;
use GuzzleHttp\Client;
use Log;

class NuoNuo
{
    const API_URL = [
        '2.0' => [
            'auth_url'            => 'https://open.nuonuo.com/accessToken',
            'invoice_url'         => 'https://sdk.nuonuo.com/open/v1/servies',
            'sandbox_invoice_url' => 'https://sandbox.nuonuocs.cn/open/v1/services',
        ],
    ];
    const API_NAME = [
        '2.0' => [
            'invoice'              => 'nuonuo.electronInvoice.RequestBillingNew',
            'query_invoice_result' => 'nuonuo.ElectronInvoice.QueryInvoiceResult',
            'invoice_retry'        => 'nuonuo.ElectronInvoice.reInvoice',
        ],
    ];
    /**
     * @var string
     */
    private $invoice_query_url;
    /**
     * @var mixed
     */
    private $access_token;
    /**
     * @var string
     */
    private $auth_url;
    /**
     * @var mixed
     */
    private $appKey;
    /**
     * @var mixed
     */
    private $appSecret;
    /**
     * @var mixed
     */
    private $version;
    /**
     * @var mixed
     */
    private $sandbox;
    /**
     * @var mixed
     */
    private $taxNum;
    /**
     * @var Client
     */
    private $client;

    /**
     * @throws NuoNuoException
     */
    public function __construct($config = null)
    {
        $this->client = new Client(['timeout' => 5]);
        $this->parseConfig($config);
        $this->getAccessToken();
    }

    /**
     * @throws NuoNuoException
     */
    private function parseConfig($config)
    {
        if (!$config) {
            $config = config('nuonuo');
        }
        if (array_key_exists('appKey', $config)) {
            $this->appKey = $config['appKey'];
        } else {
            throw new NuoNuoException('appKey 不能为空');
        }
        if (array_key_exists('appSecret', $config)) {
            $this->appSecret = $config['appSecret'];
        } else {
            throw new NuoNuoException('appSecret 不能为空');
        }
        if (array_key_exists('version', $config)) {
            if (!array_key_exists($config['version'], self::API_URL)) {
                throw new NuoNuoException('config not found');
            }
            $this->version = $config['version'];
        } else {
            $this->version = '2.0';
        }
        if (array_key_exists('sandbox', $config)) {
            $this->sandbox = $config['sandbox'];
        } else {
            $this->sandbox = false;
        }
        if (array_key_exists('taxNum', $config)) {
            $this->taxNum = $config['taxNum'];
        } else {
            $this->taxNum = null;
        }
        $this->setUrl();
    }

    private function setUrl()
    {
        $this->auth_url = self::API_URL[$this->version]['auth_url'];

        if ($this->sandbox) {
            $this->invoice_query_url = self::API_URL[$this->version]['sandbox_invoice_url'];
        } else {
            $this->invoice_query_url = self::API_URL[$this->version]['invoice_url'];
        }
    }

    /**
     * @throws NuoNuoException
     */
    private function getAccessToken()
    {
        $access_token = Cache::get('nuonuo_access_token');
        if ($access_token) {
            $this->access_token = $access_token;
        } else {
            $this->setAccessToken();
        }
    }

    /**
     * @throws NuoNuoException
     */
    private function setAccessToken()
    {
        $headers = [
            'Content-type' => 'application/x-www-form-urlencoded;charset=UTF-8',
            'Accept'       => 'application/json',
        ];
        $params = [
            'client_id'     => $this->appKey,
            'client_secret' => $this->appSecret,
            'grant_type'    => 'client_credentials',
        ];
        $res = $this->client->post($this->auth_url, [
            'form_params' => $params,
            'headers'     => $headers,
        ]);
        $data = $this->parseResponse($res);
        if ($data['access_token']) {
            Cache::set('nuonuo_access_token', $data['access_token'], 86400);
            $this->access_token = $data['access_token'];
        }
    }

    /**
     * @throws NuoNuoException
     */
    private function parseResponse($res)
    {
        $data = $res->getBody()->getContents();
        if ($res->getStatusCode() != 200) {
            throw new NuoNuoException($data);
        } else {
            return json_decode($data, true);
        }
    }

    private function makeSign($path, $senid, $nonce, $body, $timestamp)
    {
        $pieces = explode('/', $path);
        $signStr = "a={$pieces[3]}&l={$pieces[2]}&p={$pieces[1]}&k={$this->appKey}&i={$senid}&n={$nonce}&t={$timestamp}&f={$body}";

        return base64_encode(hash_hmac('sha1', $signStr, $this->appSecret, true));
    }

    /**
     * @throws NuoNuoException
     */
    private function query($content, $method)
    {
        $senid = strtoupper(str_replace('-', '', \Ramsey\Uuid\Uuid::uuid1()->toString()));
        $nonce = rand(10000000, 99999999);
        $timestamp = time();
        $urlInfo = parse_url($this->invoice_query_url);
        if ($urlInfo === false) {
            throw new NuoNuoException('url解析失败');
        }
        $sign = $this->makeSign($urlInfo['path'], $senid, $nonce, json_encode($content), $timestamp);
        $headers = [
            'Content-Type'  => 'application/json',
            'X-Nuonuo-Sign' => $sign,
            'accessToken'   => $this->access_token,
            'userTax'       => $this->taxNum,
            'method'        => $method,
        ];
        $query_url = "$this->invoice_query_url?senid=$senid&nonce=$nonce&timestamp=$timestamp&appkey=$this->appKey";
        Log::info('开票信息：', $content);
        $res = $this->client->post($query_url, [
            'headers' => $headers,
            'json'    => $content,
        ]);

        return $this->parseResponse($res);
    }

    /**
     * @throws NuoNuoException
     */
    public function invoiceOrder(
        $buyerName,
        $salerTel,
        $salerAddress,
        $orderNo,
        $invoiceDate,
        $clerk,
        $buyerPhone,
        $email,
        $invoiceType,
        $goodsName,
        $withTaxFlag,
        $price,
        $num,
        $unit,
        $taxRate,
        $taxIncludeAmount,
        $tax = '',
        $buyerTaxNum = '',
        $buyerTel = '',
        $buyerAddress = '',
        $buyerAccount = '',
        $salerAccount = '',
        $pushMode = '1'
    ) {
        $content = [
            'order' => [
                'buyerName'     => $buyerName,
                'buyerTaxNum'   => $buyerTaxNum,
                'buyerTel'      => $buyerTel,
                'buyerAddress'  => $buyerAddress,
                'buyerAccount'  => $buyerAccount,
                'salerTaxNum'   => $this->taxNum,
                'salerTel'      => $salerTel,
                'salerAddress'  => $salerAddress,
                'salerAccount'  => $salerAccount,
                'orderNo'       => $orderNo,
                'invoiceDate'   => $invoiceDate,
                'clerk'         => $clerk,
                'pushMode'      => $pushMode,
                'buyerPhone'    => $buyerPhone,
                'email'         => $email,
                'invoiceType'   => $invoiceType,
                'invoiceDetail' => [
                    'goodsName'   => $goodsName,
                    'withTaxFlag' => $withTaxFlag,
                    'price'       => $price,
                    'num'         => $num,
                    'unit'        => $unit,
                    'tax'         => $tax,
                    'taxRate'     => $taxRate,
                ],
            ],
        ];

        return $this->query($content, self::API_NAME[$this->version]['invoice']);
    }

    public function queryInvoiceResult($orderNo)
    {
        $content = ['orderNos' => $orderNo];

        return $this->query($content, self::API_NAME[$this->version]['query_invoice_result']);
    }

    public function invoiceRetry($orderNo)
    {
        $content = [
            'orderNo' => $orderNo,
        ];
        return $this->query($content, self::API_NAME[$this->version]['invoice_retry']);
    }
}
