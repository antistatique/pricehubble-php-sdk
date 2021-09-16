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
 * @method \Antistatique\Pricehubble\Resource\Valuation        valuation()
 * @method \Antistatique\Pricehubble\Resource\PointsOfInterest pointsOfInterest()
 */
class Pricehubble
{
    /**
     * The original URL for public & restricted API call.
     *
     * @var string
     */
    public const BASE_URL = 'https://api.pricehubble.com/api/v1';

    /**
     * Default timeout limit for request in seconds.
     *
     * @var int
     */
    public const TIMEOUT = 10;

    /**
     * Pricehubble FQD class to be automatically discovered.
     *
     * @var string
     */
    private const FQN_CLASS = '\\Antistatique\\Pricehubble\\Resource\\';

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
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \RuntimeException("cURL support is required, but can't be found.");
        }

        $this->lastResponse = ['headers' => null, 'body' => null];
    }

    /**
     * Proxies all Pricehubble API Class and Methods.
     */
    public function __call(string $name, array $arguments): ResourceInterface
    {
        try {
            $apiClass = ucfirst($name);
            $apiFQNClass = self::FQN_CLASS.$apiClass;

            if (false === class_exists($apiFQNClass)) {
                throw new \InvalidArgumentException(sprintf('Undefined API class %s', $apiClass));
            }

            return new $apiFQNClass($this);
        } catch (\InvalidArgumentException $e) {
            throw new \BadMethodCallException(sprintf('Undefined method %s', $name));
        }
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
    public function authenticate(string $username, string $password, int $timeout = self::TIMEOUT): void
    {
        $response = $this->makeRequest('post', 'https://api.pricehubble.com/auth/login/credentials', [
            'username' => $username,
            'password' => $password,
        ], $timeout);

        if (isset($response['access_token'])) {
            $this->setApiToken($response['access_token']);
        }
    }

    /**
     * Get the last error returned by either the network transport, or by the API.
     *
     * If something didn't work, this contain the string describing the problem.
     *
     * @return bool|string
     *                     Describing the error
     */
    public function getLastError()
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
     * @return string|null $token The Pricehubble token authorized for restricted API calls
     */
    public function getApiToken(): ?string
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
     * @param resource $curl
     *                       cURL session handle, used by reference
     * @param array    $data
     *                       Assoc array of data to attach
     *
     * @throws \JsonException
     */
    protected function attachRequestPayload(&$curl, array $data): void
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
     * @throws \Exception
     *
     * @return bool
     *              If the request was successful
     */
    protected function determineSuccess(array $response, $formattedResponse, int $timeout): bool
    {
        $status = $this->findHttpStatus($response, $formattedResponse);

        if ($status >= 200 && $status <= 299) {
            $this->requestSuccessful = true;

            return true;
        }

        if (isset($formattedResponse['message'])) {
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
     * @throws \JsonException
     *
     * @return array|false
     *                     A decoded array from JSON response
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
     * @throws \Exception
     *
     * @return array|bool
     *                    A decoded array of result or a boolean on unattended response
     */
    public function makeRequest(string $http_verb, string $url, array $args = [], int $timeout = self::TIMEOUT)
    {
        $response = $this->prepareStateForRequest($http_verb, $url, $timeout);

        $httpHeader = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        if (isset($args['language'])) {
            $httpHeader[] = 'Accept-Language: '.$args['language'];
        }

        if ('put' === $http_verb) {
            $httpHeader[] = 'Allow: PUT, PATCH, POST';
        }

        $curl = curl_init();
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

        // Set credentials for non GET verb.
        if ($this->apiAuthToken && \in_array($http_verb, [
                'post',
                'delete',
                'patch',
                'put',
            ], true)) {
            $url .= '?'.http_build_query(['access_token' => $this->apiAuthToken], '', '&');
            curl_setopt($curl, \CURLOPT_URL, $url);
        }

        switch ($http_verb) {
            case 'post':
                curl_setopt($curl, \CURLOPT_POST, true);
                $this->attachRequestPayload($curl, $args);

                break;

            case 'get':
                // Set credentials for GET verb.
                if ($this->apiAuthToken) {
                    $args += ['access_token' => $this->apiAuthToken];
                }

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

        /** @var string $response_content */
        $response_content = curl_exec($curl);
        $response['headers'] = curl_getinfo($curl);
        $response = $this->setResponseState($response, $response_content, $curl);
        $formattedResponse = $this->formatResponse($response);

        curl_close($curl);

        if (!$formattedResponse) {
            return false;
        }

        $isSuccess = $this->determineSuccess($response, $formattedResponse, $timeout);

        return \is_array($formattedResponse) ? $formattedResponse : $isSuccess;
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
     * @param resource    $curl
     *                                      The curl resource
     *
     * @throws \Exception
     *
     * @return array
     *               The modified response
     */
    protected function setResponseState(array $response, $response_content, $curl): array
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
