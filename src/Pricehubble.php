<?php

namespace Antistatique\Pricehubble;

use Antistatique\Pricehubble\Resource\ResourceInterface;

/**
 * Super-simple, minimum abstraction Pricehubble API v1.x wrapper, in PHP.
 *
 * Pricehubble API: https://docs.pricehubble.com/
 * Every request should contain a valid access token.
 *
 * @see self::authenticate to obtain one.
 *
 * @psalm-api
 *
 * @method \Antistatique\Pricehubble\Resource\Valuation        valuation()
 * @method \Antistatique\Pricehubble\Resource\PointsOfInterest pointsOfInterest()
 * @method \Antistatique\Pricehubble\Resource\Dossier          dossier()
 */
class Pricehubble
{
    /**
     * The original URL for public & restricted API call.
     *
     * @var string
     */
    public const string BASE_URL = 'https://api.pricehubble.com/api/v1';

    /**
     * Default timeout limit for request in seconds.
     *
     * @var int
     */
    public const int TIMEOUT = 10;

    /**
     * Pricehubble FQD class to be automatically discovered.
     *
     * @var string
     */
    private const string FQN_CLASS = '\\Antistatique\\Pricehubble\\Resource\\';

    /**
     * SSL Verification.
     *
     * Read before disabling:
     * http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/
     *
     * @var bool
     */
    public bool $verifySsl = true;

    /**
     * The API auth token retrieved from credentials.
     *
     * @var string
     */
    private string $apiAuthToken = '';

    /**
     * The last request error description.
     *
     * @var string
     */
    private string $lastError = '';

    /**
     * The last request anatomy.
     *
     * @var array
     */
    private array $lastRequest = [];

    /**
     * The last response.
     *
     * @var array
     */
    private array $lastResponse = [];

    /**
     * Does the last request succeed or failed.
     *
     * @var bool
     */
    private bool $requestSuccessful = false;

    /**
     * Create a new instance.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        if (!$this->isCurlAvailable()) {
            throw new \RuntimeException("cURL support is required, but can't be found.");
        }

        $this->lastResponse = ['headers' => null, 'body' => null];
    }

    /**
     * Check if cURL is available.
     */
    public function isCurlAvailable(): bool
    {
        return function_exists('curl_init') || function_exists('curl_setopt');
    }

    /**
     * Proxies all Pricehubble API Class and Methods.
     *
     * @psalm-suppress PossiblyUnusedParam $arguments is required by PHP's __call signature
     */
    public function __call(string $name, array $arguments): ResourceInterface
    {
        $apiClass = ucfirst($name);
        $apiFQNClass = self::FQN_CLASS.$apiClass;

        if (false === class_exists($apiFQNClass)) {
            throw new \BadMethodCallException(sprintf('Undefined method %s', $name));
        }

        $resource = new $apiFQNClass($this);

        if (!$resource instanceof ResourceInterface) {
            throw new \BadMethodCallException(sprintf('API class %s is not a %s', $apiClass, ResourceInterface::class));
        }

        return $resource;
    }

    /**
     * Logs in w/ credentials & set token to be used in subsequent requests.
     *
     * @param string $username
     *                         The Pricehubble username authorized for restricted API calls
     * @param string $password
     *                         The Pricehubble password authorized for restricted API calls
     * @param int    $timeout
     *                         Timeout limit for request in seconds
     *
     * @throws \Exception
     */
    public function authenticate(string $username, string $password, int $timeout = self::TIMEOUT): self
    {
        $response = $this->makeRequest('post', 'https://api.pricehubble.com/auth/login/credentials', [
            'username' => $username,
            'password' => $password,
        ], $timeout);

        if (isset($response['access_token'])) {
            $this->setApiToken($response['access_token']);
        }

        return $this;
    }

    /**
     * Get the last error returned by either the network transport, or by the API.
     *
     * If something didn't work, this contain the string describing the problem.
     *
     * @return bool|string
     *                     The string describing the error, or FALSE when the
     *                     last request did not record one
     */
    public function getLastError(): bool|string
    {
        return $this->lastError ?: false;
    }

    /**
     * Get an array containing the HTTP headers and the body of the API request.
     *
     * @return array
     *               Assoc array
     */
    public function getLastRequest(): array
    {
        return $this->lastRequest;
    }

    /**
     * Get an array containing the HTTP headers and the body of the API response.
     *
     * @return array
     *               Assoc array with keys 'headers' and 'body'
     */
    public function getLastResponse(): array
    {
        return $this->lastResponse;
    }

    /**
     * Set the API token for restricted API calls.
     *
     * Logs in with the specified credentials and returns an access token which
     * can then be used in subsequent requests.
     * The token expires every 12 hours. Thereafter, it has to be acquired anew.
     *
     * @param string $token
     *                      The Pricehubble token authorized for restricted API calls
     */
    public function setApiToken(string $token): void
    {
        $this->apiAuthToken = $token;
    }

    /**
     * Get the API token for restricted API calls.
     *
     * @return string
     *                The Pricehubble token authorized for restricted API calls,
     *                or an empty string when no token has been set yet
     */
    public function getApiToken(): string
    {
        return $this->apiAuthToken;
    }

    /**
     * Was the last request successful?
     *
     * @return bool
     *              True for success, FALSE for failure
     */
    public function success(): bool
    {
        return $this->requestSuccessful;
    }

    /**
     * Encode the data and attach it to the request.
     *
     * @param \CurlHandle $curl
     *                          cURL session handle
     * @param array       $data
     *                          Assoc array of data to attach
     *
     * @throws \JsonException
     */
    protected function attachRequestPayload(\CurlHandle $curl, array $data): void
    {
        $encoded = json_encode($data, \JSON_THROW_ON_ERROR);
        $this->lastRequest['body'] = $encoded;
        curl_setopt($curl, \CURLOPT_POSTFIELDS, $encoded);
    }

    /**
     * Check if the response was successful or a failure.
     *
     * @param array       $response
     *                                       The response from the curl request
     * @param array|false $formattedResponse
     *                                       The response body payload from the curl request
     * @param int         $timeout
     *                                       The timeout supplied to the curl request
     *
     * @return bool
     *              If the request was successful
     *
     * @throws \Exception
     */
    protected function determineSuccess(array $response, $formattedResponse, int $timeout): bool
    {
        $status = $this->findHttpStatus($response, $formattedResponse);

        if ($status >= 200 && $status <= 299) {
            $this->requestSuccessful = true;

            return true;
        }

        if (isset($formattedResponse['message']) && isset($formattedResponse['message']['message']) && is_string($formattedResponse['message']['message'])) {
            $this->lastError = sprintf('%d %s', $status, $formattedResponse['message']['message']);

            throw new \Exception($this->lastError);
        }

        if (isset($formattedResponse['message']) && is_string($formattedResponse['message'])) {
            $this->lastError = sprintf('%d %s', $status, $formattedResponse['message']);

            throw new \Exception($this->lastError);
        }

        if (isset($formattedResponse['error'])) {
            $this->lastError = sprintf('%d %s: %s', $status, $formattedResponse['error'], $formattedResponse['error_description']);

            throw new \Exception($this->lastError);
        }

        if ($timeout > 0 && $response['headers'] && $response['headers']['total_time'] >= $timeout) {
            $this->lastError = sprintf('Request timed out after %f seconds.', $response['headers']['total_time']);

            throw new \Exception($this->lastError);
        }

        $this->lastError = 'Unknown error, call getLastResponse() to find out what happened.';

        throw new \Exception($this->lastError);
    }

    /**
     * Find the HTTP status code from the headers or API response body.
     *
     * @param array       $response
     *                                       The response from the curl request
     * @param array|false $formattedResponse
     *                                       The decoded response body payload from the curl request
     *
     * @return int
     *             HTTP status code
     */
    protected function findHttpStatus(array $response, $formattedResponse): int
    {
        if (!empty($response['headers']) && isset($response['headers']['http_code'])) {
            return (int) $response['headers']['http_code'];
        }

        if (!empty($response['body']) && isset($formattedResponse['code'])) {
            return (int) $formattedResponse['code'];
        }

        return 418;
    }

    /**
     * Decode the response and format any error messages for debugging.
     *
     * @param array $response
     *                        The response from the curl request
     *
     * @return array|false
     *                     A decoded array from JSON response
     *
     * @throws \JsonException
     */
    protected function formatResponse(array $response)
    {
        $this->lastResponse = $response;

        if (empty($response['body'])) {
            return false;
        }

        // Return the decoded response from JSON when reponse is a valid json.
        // Will return FALSE otherwise.
        return ($result = json_decode($response['body'], true, 512, \JSON_THROW_ON_ERROR)) ? $result : false;
    }

    /**
     * Get the HTTP headers as an array of header-name => header-value pairs.
     *
     * @param string $headersAsString
     *                                A string of headers to parse
     *
     * @return array
     *               The parsed headers
     */
    protected function getHeadersAsArray(string $headersAsString): array
    {
        $headers = [];

        foreach (explode(\PHP_EOL, $headersAsString) as $line) {
            // Http code.
            if (1 === preg_match('/HTTP\/[1-2]/', substr($line, 0, 7))) {
                continue;
            }

            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            list($key, $value) = explode(': ', $line);
            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Performs the underlying HTTP request. Not very exciting.
     *
     * @param string $http_verb
     *                          The HTTP verb to use: get, post, put, patch, delete
     * @param string $url
     *                          The API method to be called
     * @param array  $args
     *                          Assoc array of parameters to be passed
     * @param int    $timeout
     *                          Timeout limit for request in seconds
     *
     * @return array|bool
     *                    A decoded array of result or a boolean on unattended response
     *
     * @throws \Exception
     */
    public function makeRequest(string $http_verb, string $url, array $args = [], int $timeout = self::TIMEOUT)
    {
        $response = $this->prepareStateForRequest($http_verb, $url, $timeout);

        $httpHeader = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        // add Authorization token for any verb.
        $apiToken = $this->getApiToken();

        if ('' !== $apiToken) {
            $httpHeader[] = "Authorization: Bearer {$apiToken}";
        }

        if (isset($args['language'])) {
            $httpHeader[] = 'Accept-Language: '.$args['language'];
        }

        if ('put' === $http_verb) {
            $httpHeader[] = 'Allow: PUT, PATCH, POST';
        }

        $curl = curl_init();

        if (false === $curl) {
            $this->lastError = 'Unable to initialise a cURL session.';

            throw new \RuntimeException($this->lastError);
        }

        curl_setopt($curl, \CURLOPT_URL, $url);
        curl_setopt($curl, \CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($curl, \CURLOPT_USERAGENT, 'Antistatique/Pricehubble');
        curl_setopt($curl, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, \CURLOPT_VERBOSE, true);
        curl_setopt($curl, \CURLOPT_HEADER, true);
        curl_setopt($curl, \CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, \CURLOPT_SSL_VERIFYPEER, $this->verifySsl);
        curl_setopt($curl, \CURLOPT_ENCODING, '');
        curl_setopt($curl, \CURLINFO_HEADER_OUT, true);

        switch ($http_verb) {
            case 'post':
                curl_setopt($curl, \CURLOPT_POST, true);
                $this->attachRequestPayload($curl, $args);

                break;

            case 'get':
                $query = http_build_query($args, '', '&');
                curl_setopt($curl, \CURLOPT_URL, $url.'?'.$query);

                break;

            case 'delete':
                curl_setopt($curl, \CURLOPT_CUSTOMREQUEST, 'DELETE');

                break;

            case 'patch':
                curl_setopt($curl, \CURLOPT_CUSTOMREQUEST, 'PATCH');
                $this->attachRequestPayload($curl, $args);

                break;

            case 'put':
                curl_setopt($curl, \CURLOPT_CUSTOMREQUEST, 'PUT');
                $this->attachRequestPayload($curl, $args);

                break;
        }

        $response_content = curl_exec($curl);
        $response['headers'] = curl_getinfo($curl);
        $response = $this->setResponseState($response, $response_content, $curl);
        $formattedResponse = $this->formatResponse($response);

        unset($curl);

        if (false === $formattedResponse) {
            return false;
        }

        // Throws on any non-2xx status; the return value is always true here.
        $this->determineSuccess($response, $formattedResponse, $timeout);

        return $formattedResponse;
    }

    /**
     * Save the last request and last response meta and raw data.
     *
     * @param string $http_verb
     *                          The HTTP verb to use: get, post, put, patch, delete
     * @param string $url
     *                          The API URL to be called
     * @param int    $timeout
     *                          Timeout limit for request in seconds
     *
     * @return array
     *               The last request anatomy
     */
    protected function prepareStateForRequest(string $http_verb, string $url, int $timeout): array
    {
        $parts = parse_url($url);

        if (false === $parts) {
            $this->lastError = sprintf('Malformed URL: %s', $url);

            throw new \InvalidArgumentException($this->lastError);
        }

        $this->lastError = '';

        $this->requestSuccessful = false;

        $this->lastResponse = [
            // Array of details from curl_getinfo().
            'headers' => null,
            // Array of HTTP headers.
            'httpHeaders' => null,
            // Content of the response.
            'body' => null,
        ];

        $this->lastRequest = $parts + [
            'method' => $http_verb,
            'body' => '',
            'timeout' => $timeout,
        ];

        return $this->lastResponse;
    }

    /**
     * Do post-request formatting and setting state from the response.
     *
     * @param array       $response
     *                                      The response from the curl request
     * @param string|bool $response_content
     *                                      The body of the response from the curl request. Otherwise FALSE.
     * @param \CurlHandle $curl
     *                                      The curl session handle
     *
     * @return array
     *               The modified response
     *
     * @throws \Exception
     */
    protected function setResponseState(array $response, string|bool $response_content, \CurlHandle $curl): array
    {
        if (!is_string($response_content)) {
            $this->lastError = curl_error($curl);
            throw new \Exception($this->lastError);
        }
        $headerSize = $response['headers']['header_size'];

        $response['httpHeaders'] = $this->getHeadersAsArray(substr($response_content, 0, $headerSize));
        $response['body'] = substr($response_content, $headerSize);

        if (isset($response['headers']['request_header'])) {
            $this->lastRequest['headers'] = $response['headers']['request_header'];
        }

        return $response;
    }
}
