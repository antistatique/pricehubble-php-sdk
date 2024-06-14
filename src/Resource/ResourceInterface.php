<?php

namespace Antistatique\Pricehubble\Resource;

use Antistatique\Pricehubble\Pricehubble;

/**
 * Pricehubble base API class.
 */
interface ResourceInterface
{
    /**
     * Get the API provider.
     *
     * @return Pricehubble
     *                     The Pricehubble base API instance
     */
    public function getPricehubble(): Pricehubble;
}
