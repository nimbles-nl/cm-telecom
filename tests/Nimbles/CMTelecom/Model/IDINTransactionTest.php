<?php
/*
* (c) Nimbles b.v. <wessel@nimbles.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tests\Nimbles\CMTelecom\Model;

use Nimbles\CMTelecom\Model\IDINTransaction;
use PHPUnit\Framework\TestCase;

/**
 * Class IDINTransactionTest
 */
class IDINTransactionTest extends TestCase
{
    public function testIBANTransaction()
    {
        $IDINTransaction = new IDINTransaction('transaction-id', 'merchent-ref', 'entrance-code', 'https://www.redirect.to.bank');
        $this->assertSame('transaction-id', $IDINTransaction->getTransactionId());
        $this->assertSame('merchent-ref', $IDINTransaction->getMerchantReference());
        $this->assertSame('entrance-code', $IDINTransaction->getEntranceCode());
        $this->assertSame('https://www.redirect.to.bank', $IDINTransaction->getAuthenticationUrl());
    }
}
