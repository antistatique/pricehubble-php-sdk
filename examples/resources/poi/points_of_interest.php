<?php
/**
 * Example to return point of interests such as schools, shops, etc. that match
 * the specified search criteria.
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
Make the API call to return point of interests.
 ********************************/

$response = $pricehubble->pointsOfInterest()->gather([
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

?>

<?php echo renderResponse('Points of Interest on the coordinate for the search criteria', $response); ?>
