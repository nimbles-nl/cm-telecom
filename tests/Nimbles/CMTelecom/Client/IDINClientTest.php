<?php
/*
* (c) Nimbles b.v. <wessel@nimbles.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Tests\Nimbles\CMTelecom\Client;

use GuzzleHttp\ClientInterface;
use Nimbles\CMTelecom\Client\IDINClient;
use Nimbles\CMTelecom\Model\IDINTransaction;
use Nimbles\CMTelecom\Model\Issuer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class IDINClientTest
 */
class IDINClientTest extends TestCase
{
    /** @var IDINClient */
    private $client;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface */
    private $httpClient;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ResponseInterface */
    private $response;

    /** @var \PHPUnit_Framework_MockObject_MockObject|StreamInterface */
    private $stream;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Issuer */
    private $issuer;

    /** @var \PHPUnit_Framework_MockObject_MockObject|IDINTransaction */
    private $transaction;

    public function setUp()
    {
        $this->httpClient   = $this->createHttpClientMock();
        $this->response     = $this->createResponseInterfaceMock();
        $this->stream       = $this->createStreamInterfaceMock();
        $this->issuer       = $this->createIssuerMock();
        $this->transaction  = $this->createIDINTransaction();

        $this->client = new IDINClient($this->httpClient, 'secret-token', 'https://test.cm-telecom.nl', 'MyApp');
    }
    
    public function testGetIssuers()
    {
        $this->httpClient->expects($this->once())->method('request')
            ->with('POST', 'https://test.cm-telecom.nl/directory', [
                'json' => [
                    'merchant_token' => 'secret-token',
                ],
                'headers' => [
                    'user_agent' => 'MyApp',
                ],
            ])
        ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->stream);
        
        $this->stream->expects($this->once())
            ->method('getContents')
            ->willReturn(file_get_contents(__DIR__ . '/issuers.json'));

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->assertCount(7, $this->client->getIssuers());
    }

    /**
     * @expectedException \Nimbles\CMTelecom\Exception\IssuerConnectionException
     */
    public function testGetIssuersStatusCodeNot200Exception()
    {
        $this->httpClient->expects($this->once())->method('request')
            ->with('POST', 'https://test.cm-telecom.nl/directory', [
                'json' => [
                    'merchant_token' => 'secret-token',
                ],
                'headers' => [
                    'user_agent' => 'MyApp',
                ],
            ])
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->stream);
        
        $this->stream->expects($this->once())
            ->method('getContents');

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(502);

        $this->client->getIssuers();
    }

    public function testGetIDINTransaction()
    {
        $this->httpClient->expects($this->once())->method('request')
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->stream);

        $this->stream->expects($this->once())
            ->method('getContents')
            ->willReturn(file_get_contents(__DIR__ . '/transaction.json'));

        $this->issuer->expects($this->once())
            ->method('getName');

        $this->issuer->expects($this->exactly(2))
            ->method('getId');

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->assertInstanceOf(IDINTransaction::class, $this->client->getIDINTransaction($this->issuer, 'https://www.myapp.com/redirect'));
    }

    /**
     * @expectedException \Nimbles\CMTelecom\Exception\IDINTransactionException
     */
    public function testGetIDINTransactionException()
    {
        $this->httpClient->expects($this->once())->method('request')
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->stream);

        $this->stream->expects($this->once())
            ->method('getContents');
        $this->issuer->expects($this->once())
            ->method('getName');

        $this->issuer->expects($this->exactly(2))
            ->method('getId');

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(502);

        $this->client->getIDINTransaction($this->issuer, 'https://www.myapp.com/redirect');
    }

    public function testGetUserInfo()
    {
        $this->transaction->expects($this->once())
            ->method('getTransactionId')
            ->willReturn('transaction-id');

        $this->transaction->expects($this->once())
            ->method('getMerchantReference')
            ->willReturn('transaction-reference');

        $this->httpClient->expects($this->once())->method('request')
            ->with('POST', 'https://test.cm-telecom.nl/status', [
                'json' => [
                    'merchant_token'     => 'secret-token',
                    'transaction_id'     => 'transaction-id',
                    'merchant_reference' => 'transaction-reference',
                ],
                'headers' => [
                    'user_agent' => 'MyApp',
                ],
            ])
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->stream);

        $this->stream->expects($this->once())
            ->method('getContents')
            ->willReturn(file_get_contents(__DIR__ . '/status.json'));

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        
        $this->assertTrue(is_array($this->client->getUserInfo($this->transaction)));
    }

    /**
     * @expectedException \Nimbles\CMTelecom\Exception\UserInfoException
     */
    public function testGetUserInfoException()
    {
        $this->transaction->expects($this->once())
            ->method('getTransactionId')
            ->willReturn('transaction-id');

        $this->transaction->expects($this->once())
            ->method('getMerchantReference')
            ->willReturn('transaction-reference');

        $this->httpClient->expects($this->once())->method('request')
            ->with('POST', 'https://test.cm-telecom.nl/status', [
                'json' => [
                    'merchant_token'     => 'secret-token',
                    'transaction_id'     => 'transaction-id',
                    'merchant_reference' => 'transaction-reference',
                ],
                'headers' => [
                    'user_agent' => 'MyApp',
                ],
            ])
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getBody')
            ->willReturn($this->stream);

        $this->stream->expects($this->once())
            ->method('getContents');

        $this->response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(502);

        $this->client->getUserInfo($this->transaction);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    private function createHttpClientMock()
    {
        return $this->getMockBuilder(ClientInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    private function createResponseInterfaceMock()
    {
        return $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StreamInterface
     */
    private function createStreamInterfaceMock()
    {
        return $this->getMockBuilder(StreamInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Issuer
     */
    private function createIssuerMock()
    {
        return $this->getMockBuilder(Issuer::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IDINTransaction
     */
    private function createIDINTransaction()
    {
        return $this->getMockBuilder(IDINTransaction::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
