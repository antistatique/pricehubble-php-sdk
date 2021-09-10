<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Antistatique\Pricehubble\Resource\PointsOfInterest
 *
 * @group pricehubble
 * @group pricehubble_unit
 */
class PointsOfInterestTest extends TestCase
{
    /**
     * @covers ::gather
     */
    public function testGatherReturnsExpected(): void
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/pois.json'), true, 512, JSON_THROW_ON_ERROR);

        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('makeRequest')
            ->willReturn($response);

        $pricehubble_mock->pointsOfInterest()->gather([
            'coordinates' => [
                'latitude' => 47.3968601,
                'longitude' => 8.5153549,
            ],
            'radius' => 1000,
            'category' => [
                'education',
                'leisure',
            ],
            'subcategory' => [
                'kindergarten',
                'playground',
            ],
            'offset' => 0,
            'limit' => 10,
            'countryCode' => 'CH',
        ]);
    }
}
