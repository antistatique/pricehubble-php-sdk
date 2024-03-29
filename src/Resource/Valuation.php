<?php

namespace Antistatique\Pricehubble\Resource;

use Antistatique\Pricehubble\Pricehubble;

/**
 * The Pricehubble Valuation API class.
 *
 * @see https://docs.pricehubble.com/international/valuation/
 */
final class Valuation extends AbstractResource
{
    /**
     * Performs a full valuations for the specified real estate properties.
     *
     * @param array $args
     *                       Assoc array of arguments (usually your data)
     * @param int   $timeout
     *                       Timeout limit for request in seconds
     *
     * @return array|bool
     *                    A decoded array of result or a boolean on unattended response
     *
     * @throws \Exception
     */
    public function full(array $args = [], int $timeout = Pricehubble::TIMEOUT)
    {
        return $this->getPricehubble()->makeRequest('post', Pricehubble::BASE_URL.'/valuation/property_value', $args, $timeout);
    }

    /**
     * Performs a light valuations for the specified real estate properties.
     *
     * @param array $args
     *                       Assoc array of arguments (usually your data)
     * @param int   $timeout
     *                       Timeout limit for request in seconds
     *
     * @return array|bool
     *                    A decoded array of result or a boolean on unattended response
     *
     * @throws \Exception
     */
    public function light(array $args = [], int $timeout = Pricehubble::TIMEOUT)
    {
        return $this->getPricehubble()->makeRequest('post', Pricehubble::BASE_URL.'/valuation/property_value_light', $args, $timeout);
    }
}
