<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use Antistatique\Pricehubble\Tests\Traits\TestPrivateTrait;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Pricehubble::class)]
#[CoversMethod(Pricehubble::class, 'attachRequestPayload')]
#[Group('pricehubble')]
#[Group('pricehubble_unit')]
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
