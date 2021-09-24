<?php

include_once \dirname(__DIR__) . '/../vendor/autoload.php';

if (!\class_exists('Symfony\Component\Dotenv\Dotenv')) {
  throw new \RuntimeException('You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
}

$env =  \dirname(__DIR__) . '/.env';

if (file_exists($env)) {
  $dotenv = new Symfony\Component\Dotenv\Dotenv($env);
  $dotenv->load($env);
} else {
  throw new \RuntimeException('You need to define a .env file.');
}

function getEnvVariables() {
  return [
    'PRICEHUBBLE_USERNAME' => getenv('PRICEHUBBLE_USERNAME'),
    'PRICEHUBBLE_PASS' => getenv('PRICEHUBBLE_PASS')
  ];
}

/* Ad hoc functions to make the examples marginally prettier.*/
function isWebRequest()
{
  return isset($_SERVER['HTTP_USER_AGENT']);
}

function pageHeader($title)
{
  $ret = "<!doctype html>
  <html>
  <head>
    <title>" . $title . "</title>
    <link href='/styles/style.css' rel='stylesheet' type='text/css' />
  </head>
  <body>\n";
  if ($_SERVER['PHP_SELF'] != "/index.php") {
    $ret .= "<p><a href='/index.php'>Back</a></p>";
  }
  $ret .= "<header><h1>" . $title . "</h1></header>";

 // Start the session (for storing access tokens and things)
  if (!headers_sent()) {
    session_start();
  }

  return $ret;
}

function pageFooter($file = null)
{
  $ret = "";
  if ($file) {
    $ret .= "<h3>Code:</h3>";
    $ret .= "<pre class='code'>";
    $ret .= htmlspecialchars(file_get_contents($file));
    $ret .= "</pre>";
  }
  $ret .= "</html>";

  return $ret;
}

function pageContent($response) {
  echo "<pre class='code'>", var_dump($response), "</pre>";
}

function renderResponse($title, $response) {
  echo pageHeader($title);
  echo pageContent($response);
  echo pageFooter();
}
