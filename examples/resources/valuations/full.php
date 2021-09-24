<?php
/**
 * Example to perform valuations for the specified real estate properties.
 */
include_once "../base.php";

/********************************
Create the Pricehubble object
 ********************************/
$pricehubble = getPricehubble();
$envs = getEnvVariables();

/********************************
Authenticate the following calls.
 ********************************/
$pricehubble->authenticate($envs['PRICEHUBBLE_USERNAME'], $envs['PRICEHUBBLE_PASS']);

/********************************
Make the API call to perform valuations for the specified real estate properties.
 ********************************/
$response = $pricehubble->valuation()->full([
    'dealType' => 'sale',
    'valuationInputs' => [
        [
            'property' => [
                'location' => [
                    'address' => [
                        'postCode' => '8037',
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
]);

?>

<?= renderResponse('Valuations for the specified real estate properties', $response); ?>
