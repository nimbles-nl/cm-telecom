<?php
/*
* (c) Nimbles b.v. <wessel@nimbles.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Nimbles\CMTelecom\Model;

/**
 * Class IDINTransaction
 */
class IDINTransaction
{
    /** @var string */
    private $transactionId;

    /** @var string */
    private $merchantReference;

    /** @var string */
    private $entranceCode;

    /** @var string */
    private $authenticationUrl;

    /**
     * @param string      $transactionId
     * @param string      $merchantReference
     * @param string      $entranceCode
     * @param string|null $authenticationUrl
     */
    public function __construct(string $transactionId, string $merchantReference, string $entranceCode, string $authenticationUrl = null)
    {
        $this->transactionId     = $transactionId;
        $this->merchantReference = $merchantReference;
        $this->entranceCode      = $entranceCode;
        $this->authenticationUrl = $authenticationUrl;
    }

    /**
     * @return string
     */
    public function getTransactionId() : string
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getMerchantReference() : string
    {
        return $this->merchantReference;
    }

    /**
     * @return string
     */
    public function getEntranceCode() : string
    {
        return $this->entranceCode;
    }

    /**
     * @return string|null
     */
    public function getAuthenticationUrl()
    {
        return $this->authenticationUrl;
    }
}
