<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use Antistatique\Pricehubble\Resource\AbstractResource;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Antistatique\Pricehubble\Resource\Dossier
 *
 * @group pricehubble
 * @group pricehubble_unit
 */
class DossierTest extends TestCase
{
    /**
     * @covers \Antistatique\Pricehubble\Pricehubble::__call
     */
    public function testCallReturnsExpected(): void
    {
        $pricehubble = new Pricehubble();
        $resource = $pricehubble->dossier();
        self::assertInstanceOf(AbstractResource::class, $resource);
    }

}
