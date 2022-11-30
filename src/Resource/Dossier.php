<?php

namespace Antistatique\Pricehubble\Resource;

use Antistatique\Pricehubble\Pricehubble;

/**
 * The Pricehubble Dossier API class.
 *
 * @see https://docs.pricehubble.com/international/dossier_creation/
 * @see https://docs.pricehubble.com/international/dossier_read/
 * @see https://docs.pricehubble.com/international/dossier_deletion/
 * @see https://docs.pricehubble.com/international/dossier_images/
 * @see https://docs.pricehubble.com/international/dossier_logos/
 * @see https://docs.pricehubble.com/international/dossier_search/
 * @see https://docs.pricehubble.com/international/dossier_sharing/
 * @see https://docs.pricehubble.com/international/dossier_update/
 */
final class Dossier extends AbstractResource
{
    /**
     * Creates a new dossier. The dossier can then be shared using the Dossier Sharing endpoint.
     *
     * @param array $args
     *                       Assoc array of arguments (usually your data)
     * @param int   $timeout
     *                       Timeout limit for request in seconds
     *
     * @throws \Exception
     *
     * @return array|bool
     *                    A decoded array of result or a boolean on unattended response
     */
    public function create(array $args = [], int $timeout = Pricehubble::TIMEOUT)
    {
        return $this->getPricehubble()->makeRequest('post', Pricehubble::BASE_URL.'/dossiers', $args, $timeout);
    }
}
