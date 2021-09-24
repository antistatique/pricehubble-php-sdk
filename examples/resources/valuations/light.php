<?php
/**
 * Example to perform a simple valuation of the specified property.
 */
include_once '../base.php';

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
Make the API call to perform a simple valuation of the specified property.
 ********************************/

$response = $pricehubble->valuation()->light([
    'dealType' => 'sale',
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
        'livingArea' => 800,
        'propertyType' => [
            'code' => 'apartment',
        ],
    ],
    'countryCode' => 'CH',
]);

?>

<?php echo renderResponse('Simple valuation of the specified property', $response); ?>
