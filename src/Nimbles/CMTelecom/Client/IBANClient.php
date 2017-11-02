<?php
/*
* (c) Nimbles b.v. <wessel@nimbles.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Nimbles\CMTelecom\Client;

use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Nimbles\CMTelecom\Exception\IBANTransactionException;
use Nimbles\CMTelecom\Exception\IssuerConnectionException;
use Nimbles\CMTelecom\Model\IBANTransaction;
use Nimbles\CMTelecom\Model\Issuer;

/**
 * Class IBANClient
 */
class IBANClient
{
    /** @var HttpClient */
    private $httpClient;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $url;

    /** @var string */
    private $applicationName;

    /**
     * @param HttpClient $httpClient
     * @param string          $apiKey
     * @param string          $url
     * @param string          $applicationName
     */
    public function __construct(HttpClient $httpClient, string $apiKey, string $url, string $applicationName)
    {
        $this->httpClient             = $httpClient;
        $this->apiKey                 = $apiKey;
        $this->url                    = $url;
        $this->applicationName        = $applicationName;
    }

    /**
     * @return Issuer[]
     *
     * @throws IssuerConnectionException
     */
    public function getIssuers() : array
    {
        $uri = sprintf('%s/directory', rtrim($this->url, '/'));

        $request = new Request('POST', $uri, [
            'User-Agent' => $this->applicationName,
            'Content-Type' => 'application/json'
        ], json_encode(['merchant_token' => $this->apiKey]));

        $response = $this->httpClient->sendRequest($request);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() !== 200) {
            throw new IssuerConnectionException($responseData);
        }

        if ( ! isset($responseData[0]['issuers'])) {
            throw new IssuerConnectionException('Unable to parse issuers');
        }

        return array_map(function($issuerData) {
            return new Issuer($issuerData['issuer_id'], $issuerData['issuer_name']);
        }, $responseData[0]['issuers']);
    }

    /**
     * @param Issuer $issuer
     * @param string $redirectUrl
     *
     * @return IBANTransaction
     *
     * @throws IBANTransactionException
     */
    public function getIBANTransaction(Issuer $issuer, string $redirectUrl) : IBANTransaction
    {
        $uri = sprintf('%s/transaction', rtrim($this->url, '/'));

        $token = md5(time() . rand(1, 1000) . $issuer->getId() . $issuer->getName());

        $requestData = [
            'merchant_token'      => $this->apiKey,
            'identity'            => true,
            'name'                => true,
            'issuer_id'           => $issuer->getId(),
            'entrance_code'       => $token,
            'merchant_return_url' => $redirectUrl,
        ];

        $request = new Request('POST', $uri, [
            'User-Agent' => $this->applicationName,
            'Content-Type' => 'application/json'
        ], json_encode($requestData));

        $response = $this->httpClient->sendRequest($request);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() !== 200) {
            throw new IBANTransactionException($responseData);
        }

        return new IBANTransaction(
            $responseData['transaction_id'],
            $responseData['merchant_reference'],
            $token,
            $responseData['issuer_authentication_url']
        );
    }

    /**
     * @param IBANTransaction $IBANTransaction
     *
     * @return array
     *
     * @throws IBANTransactionException
     */
    public function getTransactionInfo(IBANTransaction $IBANTransaction) : array
    {
        $uri = sprintf('%s/status', rtrim($this->url, '/'));

        $requestData = [
            'merchant_token'     => $this->apiKey,
            'transaction_id'     => $IBANTransaction->getTransactionId(),
            'merchant_reference' => $IBANTransaction->getMerchantReference(),
        ];

        $request = new Request('POST', $uri, [
            'User-Agent' => $this->applicationName,
            'Content-Type' => 'application/json'
        ], json_encode($requestData));

        $response = $this->httpClient->sendRequest($request);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() !== 200) {
            throw new IBANTransactionException($responseData);
        }

        return $responseData;
    }
}
