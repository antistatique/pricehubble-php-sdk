<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Antistatique\Pricehubble\Resource\Dossier
 *
 * @group pricehubble
 * @group pricehubble_unit
 */
class DossierTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreateReturnsExpected(): void
    {
        $response = json_decode(file_get_contents(__DIR__.'/../responses/dossier/create.json'), true, 512, JSON_THROW_ON_ERROR);

        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('makeRequest')
            ->willReturn($response);

        $pricehubble_mock->dossier()->create([
            'title' => 'My dossier',
            'description' => 'My description',
            'dealType' => 'sale',
            'property' => [
                'location' => [
                    'address' => [
                        'postCode' => '8037',
                        'city' => 'Zurich',
                        'street' => 'Nordstrasse',
                        'houseNumber' => '391',
                    ],
                    'coordinates' => [
                        'latitude' => 47.3968601,
                        'longitude' => 8.5153549,
                    ],
                ],
                'propertyType' => [
                    'code' => 'house',
                    'subcode' => 'house_detached',
                ],
                'buildingYear' => 1990,
                'livingArea' => 100.00,
                'landArea' => 900.00,
                'volume' => 900.00,
                'numberOfRooms' => 3,
                'numberOfBathrooms' => 1,
                'numberOfIndoorParkingSpaces' => 0,
                'numberOfOutdoorParkingSpaces' => 0,
                'hasPool' => true,
                'condition' => [
                    'bathrooms' => 'renovation_needed',
                    'kitchen' => 'renovation_needed',
                    'flooring' => 'well_maintained',
                    'windows' => 'new_or_recently_renovated',
                ],
                'quality' => [
                    'bathrooms' => 'simple',
                    'kitchen' => 'normal',
                    'flooring' => 'high_quality',
                    'windows' => 'luxury',
                ],
            ],
            'userDefinedFields' => [
                [
                    'label' => 'Extra garage',
                    'value' => 'Yes',
                ],
            ],
            'images' => [
                [
                    'filename' => '633390e8-0455-4520-87ba-3c5c8c234cb3.jpg',
                    'caption' => 'Front view',
                ],
            ],
            'logo' => 'b7219677-f4d5-4e99-9d7f-7cf1dee68900.png',
            'countryCode' => 'CH',
        ]);
    }
}
