<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
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
final class CurlAvailabilitiesTest extends TestCase
{
    use PHPMock;

    /**
     * @covers ::isCurlAvailable
     */
    public function testIsCurlAvailable(): void
    {
        $pricehubble = new Pricehubble();
        $this->assertTrue($pricehubble->isCurlAvailable());
    }

    /**
     * @covers ::__construct
     * @covers ::isCurlAvailable
     */
    public function testcurlNotAvailable(): void
    {
        $pricehubbleMock = $this->createMock(Pricehubble::class);
        $pricehubbleMock->method('isCurlAvailable')->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('cURL support is required, but can\'t be found.');
        $pricehubbleMock->__construct();

        $pricehubbleMock->method('isCurlAvailable')->willReturn(true);
    }

    /**
     * @covers ::__construct
     * @covers ::isCurlAvailable
     *
     * @doesNotPerformAssertions
     */
    public function testCurlAvailable(): void
    {
        $pricehubbleMock = $this->createMock(Pricehubble::class);
        $pricehubbleMock->method('isCurlAvailable')->willReturn(true);
        $pricehubbleMock->__construct();
    }
}
