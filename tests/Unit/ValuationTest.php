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
class ValuationTest extends TestCase
{
    /**
     * @covers ::full
     */
    public function testFullReturnsExpected(): void
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/valuation-full.json'), true, 512, JSON_THROW_ON_ERROR);

        $fullParams = [
          'dealType' => 'sale',
          'valuationInputs' => [
            [
              'property' => [
                'location' => [
                  'address' => [
                    'postCode' => 8037,
                    'city' => 'Zürich',
                    'street' => 'Nordstrasse',
                    'houseNumber' => '391',
                  ],
                ],
                'buildingYear' => 1850,
                'livingArea' => 130,
                'propertyType' => [
                  'code' => 'apartment',
                  'subcode' => 'apartment_normal',
                ],
                'numberOfRooms' => 5,
                'gardenArea' => 25,
                'balconyArea' => 5,
                'numberOfIndoorParkingSpaces' => 1,
                'numberOfOutdoorParkingSpaces' => 2,
                'numberOfBathrooms' => 2,
                'renovationYear' => 2019,
              ],
            ],
          ],
          'countryCode' => 'CH',
        ];
        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('makeRequest')
            ->with('post', 'https://api.pricehubble.com/api/v1/valuation/property_value', $fullParams)
            ->willReturn($response);

        $pricehubble_mock->valuation()->full($fullParams);
    }

    /**
     * @covers ::light
     */
    public function testLightReturnsExpected(): void
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/valuation-light.json'), true, 512, JSON_THROW_ON_ERROR);

        $lightParams = [
            'dealType' => 'sale',
            'valuationInputs' => [
                [
                    'property' => [
                        'location' => [
                            'address' => [
                                'postCode' => 8037,
                                'city' => 'Zürich',
                                'street' => 'Nordstrasse',
                                'houseNumber' => '391',
                            ],
                        ],
                        'buildingYear' => 1850,
                        'livingArea' => 130,
                        'propertyType' => [
                            'code' => 'apartment',
                        ],
                    ],
                ],
            ],
            'countryCode' => 'CH',
        ];

        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('makeRequest')
            ->with('post', 'https://api.pricehubble.com/api/v1/valuation/property_value_light', $lightParams)
            ->willReturn($response);

        $pricehubble_mock->valuation()->light($lightParams);
    }
}
