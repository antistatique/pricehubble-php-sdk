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
     * @covers ::authenticate
     */
    public function testAuthenticateOnSuccessSetApiToken(): void
    {
        $mock = $this->getMockBuilder(Pricehubble::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['makeRequest', 'setApiToken'])
            ->getMockForAbstractClass();

        $mock->expects(self::once())
            ->method('makeRequest')
            ->with('post', 'https://api.pricehubble.com/auth/login/credentials', [
                'username' => 'user',
                'password' => 'password',
            ], 10)
            ->willReturn(['access_token' => 'token']);
        $mock->expects(self::once())
            ->method('setApiToken')
            ->with('token');

        $mock->authenticate('user', 'password');
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateOnError(): void
    {
        $mock = $this->getMockBuilder(Pricehubble::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['makeRequest', 'setApiToken'])
            ->getMockForAbstractClass();

        $mock->expects(self::once())
            ->method('makeRequest')
            ->with('post', 'https://api.pricehubble.com/auth/login/credentials', [
                'username' => 'user',
                'password' => 'password',
            ], 10)
            ->willReturn(false);
        $mock->expects(self::never())
            ->method('setApiToken')
            ->with('token');

        $mock->authenticate('user', 'password');
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
     * @covers ::determineSuccess
     */
    public function testDetermineSuccess401MissingToken()
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/exceptions/401-missing-token.json'), true, 512, JSON_THROW_ON_ERROR);

        $pricehubble_mock = new Pricehubble();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('401 invalid_request: The access token is missing');
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess',[
            $response, [
              'error_description' => 'The access token is missing',
              'error' => 'invalid_request',
            ], 0,
        ]);
    }

    /**
     * @covers ::determineSuccess
     */
    public function testDetermineSuccess403MissingProperty()
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/exceptions/403-missing-property.json'), true, 512, JSON_THROW_ON_ERROR);

        $pricehubble_mock = new Pricehubble();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("403 'dossierId', 'simulationId' or 'buildingId' is a required property");
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            $response, [
              'message' => "'dossierId', 'simulationId' or 'buildingId' is a required property",
          ], 0,
        ]);
    }

    /**
     * @covers ::determineSuccess
     */
    public function testDetermineSuccess403Forbidden()
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/exceptions/403-forbidden.json'), true, 512, JSON_THROW_ON_ERROR);

        $pricehubble_mock = new Pricehubble();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("403 Forbidden");
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            $response, ['message' => 'Forbidden'], 0,
        ]);
    }

    /**
     * @covers ::determineSuccess
     *
     * Cover the scenario of inconsistency on Pricehubble API with nested message.
     */
    public function testDetermineSuccess403ForbiddenNested()
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/exceptions/403-forbidden-nested.json'), true, 512, JSON_THROW_ON_ERROR);

        $pricehubble_mock = new Pricehubble();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("403 Forbidden");
        $this->callPrivateMethod($pricehubble_mock, 'determineSuccess', [
            $response, ['message' => ['message' => 'Forbidden']], 0,
        ]);
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

    /**
     * @covers ::makeRequest
     */
    public function testMakeRequestMalformedResponse(): void
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['prepareStateForRequest', 'setResponseState', 'formatResponse', 'determineSuccess'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('prepareStateForRequest')
            ->with('verb', 'https://example.org', 10);

        $pricehubble_mock->expects($this->once())
            ->method('setResponseState')
            ->with($this->isType('array'), $this->isType('string'), $this->anything());

        $pricehubble_mock->expects($this->once())
            ->method('formatResponse')
            ->with($this->isType('array'));

        $pricehubble_mock->expects($this->never())
            ->method('determineSuccess');

        $curl_exec_mock = $this->getFunctionMock('Antistatique\Pricehubble', 'curl_exec');
        $curl_exec_mock->expects($this->once())->willReturn('');

        $this->callPrivateMethod($pricehubble_mock, 'makeRequest', [
            'verb', 'https://example.org',
        ]);
    }

    /**
     * @covers ::makeRequest
     */
    public function testMakeRequestGet(): void
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['getApiToken', 'prepareStateForRequest', 'setResponseState', 'formatResponse', 'determineSuccess'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('prepareStateForRequest')
            ->with('get', 'https://example.org', 10);

        $pricehubble_mock->expects($this->exactly(3))
            ->method('getApiToken')
            ->willReturn('api-token');

        $pricehubble_mock->expects($this->once())
            ->method('setResponseState')
            ->with($this->isType('array'), $this->isType('string'), $this->anything())
        ;

        $pricehubble_mock->expects($this->once())
            ->method('formatResponse')
            ->with($this->isType('array'))
            ->willReturn(['foo' => 'bar']);

        $pricehubble_mock->expects($this->once())
            ->method('determineSuccess')
            ->with($this->isType('array'), $this->isType('array'), $this->isType('integer'))
            ->willReturn(true);

        $curl_exec_mock = $this->getFunctionMock('Antistatique\Pricehubble', 'curl_exec');
        $curl_exec_mock->expects($this->once())->willReturn('body');

        $this->callPrivateMethod($pricehubble_mock, 'makeRequest', [
            'get', 'https://example.org',
        ]);
    }

    /**
     * @covers ::makeRequest
     *
     * @dataProvider providerHttpVerbs
     */
    public function testMakeRequestByVerbs(string $verb): void
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['getApiToken', 'prepareStateForRequest', 'setResponseState', 'formatResponse', 'determineSuccess'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('prepareStateForRequest')
            ->with($verb, 'https://example.org', 10);

        $pricehubble_mock->expects($this->exactly(1))
            ->method('getApiToken')
            ->willReturn('api-token');

        $pricehubble_mock->expects($this->once())
            ->method('setResponseState')
            ->with($this->isType('array'), $this->isType('string'), $this->anything())
        ;

        $pricehubble_mock->expects($this->once())
            ->method('formatResponse')
            ->with($this->isType('array'))
            ->willReturn(['foo' => 'bar']);

        $pricehubble_mock->expects($this->once())
            ->method('determineSuccess')
            ->with($this->isType('array'), $this->isType('array'), $this->isType('integer'))
            ->willReturn(true);

        $curl_exec_mock = $this->getFunctionMock('Antistatique\Pricehubble', 'curl_exec');
        $curl_exec_mock->expects($this->once())->willReturn('body');

        $this->callPrivateMethod($pricehubble_mock, 'makeRequest', [
            $verb, 'https://example.org',
        ]);
    }

    /**
     * Provider of :testMakeRequestByVerbs.
     *
     * @return iterable Variation of HTTP Verbs
     */
    public function providerHttpVerbs(): iterable
    {
        yield ['post'];
        yield ['delete'];
        yield ['patch'];
        yield ['put'];
    }
}
