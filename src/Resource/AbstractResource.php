<?php

namespace Antistatique\Pricehubble\Resource;

use Antistatique\Pricehubble\Pricehubble;

/**
 * Pricehubble base API class.
 */
abstract class AbstractResource implements ResourceInterface
{
    /**
     * The Pricehubble base API instance.
     *
     * @var \Antistatique\Pricehubble\Pricehubble
     */
    private Pricehubble $pricehubble;

    /**
     * Construct a new AbstractApi object.
     *
     * @param \Antistatique\Pricehubble\Pricehubble $pricehubble
     *                                                           The Pricehubble base API class
     */
    public function __construct(Pricehubble $pricehubble)
    {
        $this->pricehubble = $pricehubble;
    }

    /**
     * {@inheritdoc}
     */
    public function getPricehubble(): Pricehubble
    {
        return $this->pricehubble;
    }
}
