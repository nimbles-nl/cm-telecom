<?php
/*
* (c) Nimbles b.v. <wessel@nimbles.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tests\Nimbles\CMTelecom\Model;

use Nimbles\CMTelecom\Model\IBANTransaction;
use PHPUnit\Framework\TestCase;

/**
 * Class IBANTransactionTest
 */
class IBANTransactionTest extends TestCase
{
    public function testIBANTransaction()
    {
        $IBANTransaction = new IBANTransaction('transaction-id', 'merchent-ref', 'entrance-code', 'https://www.redirect.to.bank');
        $this->assertSame('transaction-id', $IBANTransaction->getTransactionId());
        $this->assertSame('merchent-ref', $IBANTransaction->getMerchantReference());
        $this->assertSame('entrance-code', $IBANTransaction->getEntranceCode());
        $this->assertSame('https://www.redirect.to.bank', $IBANTransaction->getAuthenticationUrl());
    }
}
