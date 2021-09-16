<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use Antistatique\Pricehubble\Resource\AbstractResource;
use Antistatique\Pricehubble\Tests\Traits\TestPrivateTrait;
use BadMethodCallException;
use Exception;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Antistatique\Pricehubble\Pricehubble
 *
 * @group pricehubble
 * @group pricehubble_unit
 *
 * @internal
 */
final class PricehubbleTest extends TestCase
{
    use TestPrivateTrait;
    use PHPMock;

    /**
     * The Pricehubble base API instance.
     *
     * @var \Antistatique\Pricehubble\Pricehubble
     */
    private Pricehubble $pricehubble;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pricehubble = new Pricehubble();
    }

    /**
     * @covers ::__construct
     * @covers ::success
     * @covers ::getLastError
     * @covers ::getLastResponse
     * @covers ::getLastRequest
     * @covers ::setResponseState
     * @covers ::getHeadersAsArray
     * @covers ::prepareStateForRequest
     */
    public function testInstantiation(): void
    {
        $pricehubble = new Pricehubble();
        self::assertFalse($pricehubble->success());
        self::assertFalse($pricehubble->getLastError());
        self::assertSame([
            'headers' => null,
            'body' => null,
        ], $pricehubble->getLastResponse());
        self::assertSame([], $pricehubble->getLastRequest());
    }

    /**
     * @covers ::formatResponse
     * @covers ::getLastResponse
     */
    public function testFormatResponseJson(): void
    {
        $response['body'] = '{"access_token": "74126eab0a9048d993bda4b1b55ae074", "expires_in": 43200}';
        $result = $this->callPrivateMethod($this->pricehubble, 'formatResponse', [$response]);

        self::assertIsArray($result);
        self::assertIsArray($this->pricehubble->getLastResponse());
        self::assertSame([
            'access_token' => '74126eab0a9048d993bda4b1b55ae074',
            'expires_in' => 43200,
        ], $result);
    }

    /**
     * @covers ::formatResponse
     */
    public function testFormatResponseEmptyBody()
    {
        $result = $this->callPrivateMethod($this->pricehubble, 'formatResponse', [[]]);
        self::assertFalse($result);
        self::assertEmpty($this->pricehubble->getLastResponse());
    }

    /**
     * @covers ::__call
     */
    public function testMagicCallReturnsExpected(): void
    {
        $valuation = $this->pricehubble->valuation();
        self::assertInstanceOf(AbstractResource::class, $valuation);
    }

    /**
     * @covers ::__call
     */
    public function testMagicCallReturnsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Undefined method foo');
        $this->pricehubble->foo();
    }

    /**
     * @covers ::setApiToken
     * @covers ::getApiToken
     */
    public function testSetApiToken(): void
    {
        self::assertEmpty($this->pricehubble->getApiToken());
        $this->pricehubble->setApiToken('api-token');
        self::assertEquals('api-token', $this->pricehubble->getApiToken());
    }

    /**
     * @covers ::setResponseState
     */
    public function testSetResponseState()
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/partials/complete.json'), true, 512, JSON_THROW_ON_ERROR);
        $response_content = file_get_contents(__DIR__.'/../responses/partials/body.txt');

        self::assertArrayHasKey('httpHeaders', $response);
        self::assertArrayHasKey('body', $response);
        self::assertEmpty($response['httpHeaders']);
        self::assertEmpty($response['body']);

        $response = $this->callPrivateMethod($this->pricehubble, 'setResponseState', [
            $response, $response_content, null,
        ]);

        self::assertArrayHasKey('httpHeaders', $response);
        self::assertArrayHasKey('body', $response);
        self::assertSame([
            'Date' => 'Mon, 30 Sep 2019 14:09:23 GMT',
            'Server' => 'Apache',
            'Access-Control-Allow-Origin' => '*',
            'Content-Encoding' => 'gzip',
            'Connection' => 'close',
            'Transfer-Encoding' => 'chunked',
            'Content-Type' => 'application/json',
        ], $response['httpHeaders']);
        self::assertSame('
{"access_token": "74126eab0a9048d993bda4b1b55ae074", "expires_in": 43200}
', $response['body']);
    }

    /**
     * @covers ::setResponseState
     */
    public function testSetResponseStateError()
    {
        $curl_error_mock = $this->getFunctionMock('Antistatique\Pricehubble', 'curl_error');
        $curl_error_mock->expects($this->once())
            ->willReturn('Something went wrong.');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something went wrong.');
        $this->callPrivateMethod($this->pricehubble, 'setResponseState', [
            [], false, null,
        ]);
    }

    /**
     * @covers ::getHeadersAsArray
     */
    public function testGetHeadersAsArray()
    {
        $headers_string = '
HTTP/1.1 200 OK
Date: Mon, 30 Sep 2019 14:09:23 GMT
Server: Apache
Access-Control-Allow-Origin: *
Content-Encoding: gzip
Connection: close
Transfer-Encoding: chunked
Content-Type: application/json';
        $headers = $this->callPrivateMethod($this->pricehubble, 'getHeadersAsArray', [$headers_string]);

        self::assertSame([
            'Date' => 'Mon, 30 Sep 2019 14:09:23 GMT',
            'Server' => 'Apache',
            'Access-Control-Allow-Origin' => '*',
            'Content-Encoding' => 'gzip',
            'Connection' => 'close',
            'Transfer-Encoding' => 'chunked',
            'Content-Type' => 'application/json',
        ], $headers);
    }

    /**
     * @covers ::makeRequest
     */
    public function testMakeRequestPost()
    {
        $headers_json = json_decode((file_get_contents(__DIR__.'/../responses/partials/headers.json')), true);
        $body_json = file_get_contents(__DIR__.'/../responses/partials/body.json');
        $body_txt = file_get_contents(__DIR__.'/../responses/partials/body.txt');

        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['prepareStateForRequest', 'setResponseState', 'formatResponse', 'determineSuccess'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('prepareStateForRequest')
            ->with('post', 'https://api.pricehubble.com/auth/login/credentials', 10)
            ->willReturn([
                'headers' => null,
                'httpHeaders' => null,
                'body' => null,
            ]);

        $response = [
            'body' => $body_json,
            'headers' => $headers_json,
            'httpHeaders' => [
                'Date' => 'Mon, 30 Sep 2019 14:09:23 GMT',
                'Server' => 'Apache',
                'Access-Control-Allow-Origin' => '*',
                'Content-Encoding' => 'gzip',
                'Connection' => 'close',
                'Transfer-Encoding' => 'chunked',
                'Content-Type' => 'application/json',
            ],
        ];
        $pricehubble_mock->expects($this->once())
            ->method('setResponseState')
            ->with($this->isType('array'), $this->isType('string'), $this->anything())
            ->willReturn($response);

        $pricehubble_mock->expects($this->once())
            ->method('formatResponse')
            ->with($this->isType('array'))
            ->willReturn(json_decode($response['body'], true));

        $pricehubble_mock->expects($this->once())
            ->method('determineSuccess')
            ->with($this->isType('array'), $this->isType('array'), $this->isType('integer'))
            ->willReturn(true);

        $curl_exec_mock = $this->getFunctionMock('Antistatique\Pricehubble', 'curl_exec');
        $curl_exec_mock->expects($this->any())->willReturn($body_txt);

        $curl_getinfo_mock = $this->getFunctionMock('Antistatique\Pricehubble', 'curl_getinfo');
        $curl_getinfo_mock->expects($this->any())->willReturn($response['headers']);

        $result = $this->callPrivateMethod($pricehubble_mock, 'makeRequest', [
            'post', 'https://api.pricehubble.com/auth/login/credentials',
            ['foo' => 'bar'],
            10,
        ]);
        self::assertSame([
            'access_token' => '74126eab0a9048d993bda4b1b55ae074',
            'expires_in' => 43200,
        ], $result);
    }

    /**
     * @covers ::findHttpStatus
     *
     * @dataProvider providerHttpStatus
     */
    public function testFindHttpStatus($response, $formatted_response, $expected_code)
    {
        $code = $this->callPrivateMethod($this->pricehubble, 'findHttpStatus', [
            $response,
            $formatted_response,
        ]);
        self::assertEquals($expected_code, $code);
    }

    /**
     * Dataprovider of :testFindHttpStatus.
     *
     * @return array
     *               Variation of HTTP Status response
     */
    public function providerHttpStatus()
    {
        return [
            [
                ['headers' => ['http_code' => 400]],
                null,
                400,
            ],
            [
                ['headers' => null],
                ['code' => 300],
                418,
            ],
            [
                ['headers' => null, 'body' => ''],
                ['code' => 300],
                418,
            ],
            [
                ['headers' => ['http_code' => 400]],
                ['code' => 300],
                400,
            ],
            [
                ['body' => 'lorem'],
                ['code' => 318],
                318,
            ],
            [
                ['body' => ''],
                ['code' => 318],
                418,
            ],
            [
                [],
                null,
                418,
            ],
            [
                ['headers' => []],
                [],
                418,
            ],
        ];
    }

    /**
     * @covers ::determineSuccess
     *
     * @dataProvider providerStatus200
     */
    public function testDetermineSuccessStatus200($code)
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['findHttpStatus'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('findHttpStatus')
            ->willReturn($code);

        self::assertFalse($pricehubble_mock->success());
        $result = $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            [], false, 0,
        ]);
        self::assertTrue($result);
        self::assertTrue($pricehubble_mock->success());
    }

    /**
     * Dataprovider of :testDetermineSuccessStatus200.
     *
     * @return array
     *               Variation of HTTP Status response
     */
    public function providerStatus200()
    {
        return [
            [
                200,
            ],
            [
                250,
            ],
            [
                299,
            ],
        ];
    }

    /**
     * @covers ::determineSuccess
     */
    public function testDetermineSuccessErrorMessage()
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['findHttpStatus'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('findHttpStatus')
            ->willReturn(400);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('400 Unsupported country code.');
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            [], ['message' => 'Unsupported country code.'], 0,
        ]);

        self::assertEquals('ERROR 400: Unsupported country code.', $pricehubble_mock->getLastError());
    }

    /**
     * @covers ::determineSuccess
     */
    public function testDetermineSuccessErrorMessageDescription()
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['findHttpStatus'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('findHttpStatus')
            ->willReturn(401);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('401 invalid_token: The access token is invalid or has expired');
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            [], ['error_description' => 'The access token is invalid or has expired', 'error' => 'invalid_token'], 0,
        ]);

        self::assertEquals('ERROR 401: invalid_token: The access token is invalid or has expired.', $pricehubble_mock->getLastError());
    }

    /**
     * @covers ::determineSuccess
     */
    public function testDetermineSuccessTimeout()
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['findHttpStatus'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('findHttpStatus')
            ->willReturn(100);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request timed out after 20.000000 seconds.');
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            ['headers' => ['total_time' => 20]], null, 5,
        ]);
    }

    /**
     * @covers ::determineSuccess
     */
    public function testDetermineSuccessUnknown()
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['findHttpStatus'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('findHttpStatus')
            ->willReturn(100);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error, call getLastResponse() to find out what happened.');
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            ['headers' => ['total_time' => 20]], null, 35,
        ]);

        self::assertEquals('Unknown error, call getLastResponse() to find out what happened.', $pricehubble_mock->getLastError());
    }

    /**
     * @covers ::prepareStateForRequest
     */
    public function testPrepareStateForRequest()
    {
        $this->callPrivateMethod($this->pricehubble, 'prepareStateForRequest', [
            'post', 'https://api.pricehubble.com/api/v1/pois', 23,
        ]);

        self::assertFalse($this->pricehubble->success());
        self::assertFalse($this->pricehubble->getLastError());
        self::assertSame([
            'headers' => null,
            'httpHeaders' => null,
            'body' => null,
        ], $this->pricehubble->getLastResponse());
        self::assertSame([
            'scheme' => 'https',
            'host' => 'api.pricehubble.com',
            'path' => '/api/v1/pois',
            'method' => 'post',
            'body' => '',
            'timeout' => 23,
        ], $this->pricehubble->getLastRequest());
    }
}
