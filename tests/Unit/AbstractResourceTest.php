<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use Antistatique\Pricehubble\Resource\AbstractResource;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass \Antistatique\Pricehubble\Resource\AbstractResource
 *
 * @group pricehubble
 * @group pricehubble_unit
 *
 * @internal
 */
final class AbstractResourceTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstructorArgumentCount(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new class() extends AbstractResource {};
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $pricehubble = new Pricehubble();
        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setPricehubble'])
            ->getMockForAbstractClass();

        // Set expectations for constructor calls.
        $mock->expects($this->once())
            ->method('setPricehubble')
            ->with($pricehubble);

        // Now call the constructor
        $reflectedClass = new ReflectionClass(AbstractResource::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $pricehubble);
    }

    /**
     * @covers ::setPricehubble
     */
    public function testSetPricehubbleReturnsExpected(): void
    {
        $pricehubble = new Pricehubble();

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $mock->setPricehubble($pricehubble);
        $result = $mock->getPricehubble();
        self::assertSame($result, $pricehubble);
    }

    /**
     * @covers ::getPricehubble
     */
    public function testGetPricehubbleReturnsExpected(): void
    {
        $pricehubble = new Pricehubble();
        $testResourceClass = new class($pricehubble) extends AbstractResource {};
        $result = $testResourceClass->getPricehubble();
        self::assertSame($result, $pricehubble);
    }
}
