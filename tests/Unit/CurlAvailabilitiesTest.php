<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Pricehubble::class)]
#[CoversMethod(Pricehubble::class, '__construct')]
#[CoversMethod(Pricehubble::class, 'isCurlAvailable')]
#[Group('pricehubble')]
#[Group('pricehubble_unit')]
final class CurlAvailabilitiesTest extends TestCase
{
    use PHPMock;

    public function testIsCurlAvailable(): void
    {
        $pricehubble = new Pricehubble();
        $this->assertTrue($pricehubble->isCurlAvailable());
    }

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
     */
    #[DoesNotPerformAssertions]
    public function testCurlAvailable(): void
    {
        $pricehubbleMock = $this->createMock(Pricehubble::class);
        $pricehubbleMock->method('isCurlAvailable')->willReturn(true);
        $pricehubbleMock->__construct();
    }
}
