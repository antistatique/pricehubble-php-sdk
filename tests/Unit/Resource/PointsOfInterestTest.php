<?php

namespace Antistatique\Pricehubble\Tests\Unit\Resource;

use Antistatique\Pricehubble\Pricehubble;
use Antistatique\Pricehubble\Resource\AbstractResource;
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
     * @covers \Antistatique\Pricehubble\Pricehubble::__call
     */
    public function testCallReturnsExpected(): void
    {
        $pricehubble = new Pricehubble();
        $resource = $pricehubble->pointsOfInterest();
        self::assertInstanceOf(AbstractResource::class, $resource);
    }

    /**
     * @covers ::gather
     */
    public function testGatherReturnsExpected(): void
    {
        $response = json_decode(file_get_contents(__DIR__.'/../../responses/pois.json'), true, 512, JSON_THROW_ON_ERROR);

        $pricehubble_mock = $this->getMockBuilder(Pricehubble::class)
            ->onlyMethods(['makeRequest'])
            ->getMock();

        $pricehubble_mock->expects($this->once())
            ->method('makeRequest')
            ->willReturn($response);

        $pointOfInterests = $pricehubble_mock->pointsOfInterest()->gather([
            'coordinates' => [
                // Rue de Genève 90B, 1004 Lausanne.
                'latitude' => 46.525469979127884,
                'longitude' => 6.612476627529015,
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

        self::assertEquals([
            'totalItems' => 124,
            'items' => [
                0 => [
                    'category' => 'leisure',
                    'distance' => 89,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5251541137695,
                            'longitude' => 6.61354732513428,
                        ],
                    ],
                    'name' => 'Laser Game',
                    'subcategory' => 'sport',
                ],
                1 => [
                    'category' => 'leisure',
                    'distance' => 118,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5265121459961,
                            'longitude' => 6.6127724647522,
                        ],
                    ],
                    'name' => 'Av. de Morges 60, Lausanne',
                    'subcategory' => 'playground',
                ],
                2 => [
                    'category' => 'education',
                    'distance' => 137,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5266304016113,
                            'longitude' => 6.61183977127075,
                        ],
                    ],
                    'name' => 'Jardins de Prélaz',
                    'subcategory' => 'kindergarten',
                ],
                3 => [
                    'category' => 'leisure',
                    'distance' => 143,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5267601013184,
                            'longitude' => 6.61249494552612,
                        ],
                    ],
                    'name' => 'Av. de Morges 60a, Lausanne',
                    'subcategory' => 'playground',
                ],
                4 => [
                    'category' => 'leisure',
                    'distance' => 169,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.526481628418,
                            'longitude' => 6.61412763595581,
                        ],
                    ],
                    'subcategory' => 'sport',
                ],
                5 => [
                    'category' => 'leisure',
                    'distance' => 245,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5275077819824,
                            'longitude' => 6.6112322807312,
                        ],
                    ],
                    'name' => 'Av. de Sévery 3, Lausanne',
                    'subcategory' => 'playground',
                ],
                6 => [
                    'category' => 'leisure',
                    'distance' => 251,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5276718139648,
                            'longitude' => 6.61325788497925,
                        ],
                    ],
                    'name' => 'Parc des Vignes-d\'Argent',
                    'subcategory' => 'park',
                ],
                7 => [
                    'category' => 'education',
                    'distance' => 256,
                    'location' => [
                        'address' => [
                            'city' => 'Lausanne',
                            'houseNumber' => '26-28',
                            'postCode' => '1004',
                            'street' => 'Avenue de Provence',
                        ],
                        'coordinates' => [
                            'latitude' => 46.5234680175781,
                            'longitude' => 6.6108078956604,
                        ],
                    ],
                    'name' => 'Gymnase Provence',
                    'subcategory' => 'university',
                ],
                8 => [
                    'category' => 'education',
                    'distance' => 261,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5267715454102,
                            'longitude' => 6.61532020568848,
                        ],
                    ],
                    'name' => 'Collège de Prélaz',
                    'subcategory' => 'primary_school',
                ],
                9 => [
                    'category' => 'education',
                    'distance' => 261,
                    'location' => [
                        'coordinates' => [
                            'latitude' => 46.5267715454102,
                            'longitude' => 6.61532020568848,
                        ],
                    ],
                    'name' => 'Collège de Prélaz',
                    'subcategory' => 'kindergarten',
                ],
            ],
        ], $pointOfInterests);
    }
}
