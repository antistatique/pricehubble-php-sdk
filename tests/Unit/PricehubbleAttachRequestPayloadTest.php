<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use Antistatique\Pricehubble\Tests\Traits\TestPrivateTrait;
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
final class PricehubbleAttachRequestPayloadTest extends TestCase
{
    use TestPrivateTrait;
    use PHPMock;

    /**
     * The Pricehubble base API instance.
     *
     * @var Pricehubble
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
     * @covers ::attachRequestPayload
     */
    public function testAttachRequestPayload()
    {
        self::assertSame([], $this->pricehubble->getLastRequest());

        $curl = curl_init();
        $curl_setopt_mock = $this->getFunctionMock('Antistatique\Pricehubble', 'curl_setopt');
        $curl_setopt_mock->expects($this->once())
            ->with($curl, CURLOPT_POSTFIELDS, '{"name":"john","age":30,"car":null}');

        $this->callPrivateMethod($this->pricehubble, 'attachRequestPayload', [
            &$curl, ['name' => 'john', 'age' => 30, 'car' => null],
        ]);
        self::assertSame(['body' => '{"name":"john","age":30,"car":null}'], $this->pricehubble->getLastRequest());
    }
}
