<?php
/*
* (c) Nimbles b.v. <wessel@nimbles.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Nimbles\CMTelecom\Client;

use GuzzleHttp\ClientInterface;
use Nimbles\CMTelecom\Exception\IDINTransactionException;
use Nimbles\CMTelecom\Exception\IssuerConnectionException;
use Nimbles\CMTelecom\Exception\UserInfoException;
use Nimbles\CMTelecom\Model\IDINTransaction;
use Nimbles\CMTelecom\Model\Issuer;

/**
 * Class IDINClient
 */
class IDINClient
{
    /** @var ClientInterface */
    private $httpClient;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $url;

    /** @var string */
    private $applicationName;

    /**
     * @param ClientInterface $httpClient
     * @param string          $apiKey
     * @param string          $url
     * @param string          $applicationName
     */
    public function __construct(ClientInterface $httpClient, string $apiKey, string $url, string $applicationName)
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
        $url = sprintf('%s/directory', rtrim($this->url, '/'));

        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'merchant_token' => $this->apiKey,
            ],
            'headers' => [
                'User-Agent' => $this->applicationName,
            ],
        ]);

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
     * @return IDINTransaction
     *
     * @throws IDINTransactionException
     */
    public function getIDINTransaction(Issuer $issuer, string $redirectUrl) : IDINTransaction
    {
        $url = sprintf('%s/transaction', rtrim($this->url, '/'));

        $token = md5(time() . rand(1, 1000) . $issuer->getId() . $issuer->getName());

        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'merchant_token'      => $this->apiKey,
                'identity'            => true,
                'name'                => true,
                'gender'              => true,
                'address'             => true,
                'date_of_birth'       => true,
                '18y_or_older'        => true,
                'email_address'       => false,
                'telephone_number'    => false,
                'issuer_id'           => $issuer->getId(),
                'entrance_code'       => $token,
                'merchant_return_url' => $redirectUrl,
                'language'            => 'nl',
            ],
            'headers' => [
                'User-Agent' => $this->applicationName,
            ],
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() !== 200) {
            throw new IDINTransactionException($responseData);
        }

        return new IDINTransaction(
            $responseData['transaction_id'],
            $responseData['merchant_reference'],
            $token,
            $responseData['issuer_authentication_url']
        );
    }

    /**
     * @param IDINTransaction $IDINTransaction
     *
     * @return array
     *
     * @throws UserInfoException
     */
    public function getUserInfo(IDINTransaction $IDINTransaction) : array
    {
        $url = sprintf('%s/status', rtrim($this->url, '/'));

        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'merchant_token'     => $this->apiKey,
                'transaction_id'     => $IDINTransaction->getTransactionId(),
                'merchant_reference' => $IDINTransaction->getMerchantReference(),
            ],
            'headers' => [
                'User-Agent' => $this->applicationName,
            ],
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() !== 200) {
            throw new UserInfoException($responseData);
        }

        return $responseData;
    }
}
