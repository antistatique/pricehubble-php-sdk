<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Antistatique\Pricehubble\Resource\Valuation
 *
 * @group pricehubble
 * @group pricehubble_unit
 */
class PricehubbleValuationTest extends TestCase
{
    /**
     * @covers ::full
     */
    public function testFullReturnsExpected(): void
    {
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('makeRequest')
            ->willReturn(json_decode('{"valuations":[[{"salePrice":374000,"salePriceRange":{"lower":329000,"upper":418000},"confidence":"medium","coordinates":{"latitude":47.3968601,"longitude":8.5153549},"currency":"CHF","scores":{"location":0.551}},{"salePrice":374000,"salePriceRange":{"lower":329000,"upper":418000},"confidence":"medium","currency":"CHF","scores":{"location":0.551}}]]}', true, 512, JSON_THROW_ON_ERROR));

        $pricehubble_mock->valuation()->full();
    }
}
