<?php
/**
 * Index file for the examples of the SDK.
 */
\error_reporting(E_ALL);

include_once 'templates/base.php';
?>

<?php if (!isWebRequest()) { ?>
  To view this example, run the following command from the root directory of this repository:

    php -S localhost:8000 -t examples/

  And then browse to "localhost:8000" in your web browser
<?php return; ?>
<?php } ?>

<?php echo pageHeader('Pricehubble API SDK Examples'); ?>

<h2>Resources</h2>

<ul>
    <li><a href="resources/valuations/light.php">Valuation Light Example</a></li>
  <li><a href="resources/valuations/full.php">Valuation Full Example</a></li>
  <li><a href="resources/poi/points_of_interest.php">Point of Interest Example</a></li>
</ul>

<?php echo pageFooter(); ?>
