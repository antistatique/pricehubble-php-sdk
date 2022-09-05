<?php

namespace Antistatique\Pricehubble\Resource;

use Antistatique\Pricehubble\Pricehubble;

/**
 * The Pricehubble Points of Interest API class.
 *
 * @see https://docs.pricehubble.com/international/pois/
 */
final class PointsOfInterest extends AbstractResource
{
    /**
     * Returns points of interests such as schools, shops, etc. that match the specified search criteria.
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
    public function gather(array $args = [], int $timeout = Pricehubble::TIMEOUT)
    {
        return $this->getPricehubble()->makeRequest('post', Pricehubble::BASE_URL.'/pois', $args, $timeout);
    }
}
