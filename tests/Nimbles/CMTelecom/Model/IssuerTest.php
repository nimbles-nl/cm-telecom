<?php
/*
* (c) Nimbles b.v. <wessel@nimbles.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tests\Nimbles\CMTelecom\Model;

use Nimbles\CMTelecom\Model\Issuer;
use PHPUnit\Framework\TestCase;

class IssuerTest extends TestCase
{
    public function testIssuer()
    {
        $issuer = new Issuer('NLRABO16', 'Rabobank');

        $this->assertSame('NLRABO16', $issuer->getId());
        $this->assertSame('Rabobank', $issuer->getName());
    }
}
