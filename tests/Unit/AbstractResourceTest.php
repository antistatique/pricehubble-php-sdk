<?php

namespace Antistatique\Pricehubble\Tests\Unit;

use Antistatique\Pricehubble\Pricehubble;
use Antistatique\Pricehubble\Resource\AbstractResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AbstractResource::class)]
#[CoversMethod(AbstractResource::class, '__construct')]
#[CoversMethod(AbstractResource::class, 'setPricehubble')]
#[CoversMethod(AbstractResource::class, 'getPricehubble')]
#[Group('pricehubble')]
#[Group('pricehubble_unit')]
final class AbstractResourceTest extends TestCase
{
    public function testConstructorArgumentCount(): void
    {
        $this->expectException(\ArgumentCountError::class);
        new class extends AbstractResource {};
    }

    public function testConstructor(): void
    {
        $pricehubble = new Pricehubble();
        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setPricehubble'])
            ->getMock();

        // Set expectations for constructor calls.
        $mock->expects($this->once())
            ->method('setPricehubble')
            ->with($pricehubble);

        // Now call the constructor
        $reflectedClass = new \ReflectionClass(AbstractResource::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $pricehubble);
    }

    public function testSetPricehubbleReturnsExpected(): void
    {
        $pricehubble = new Pricehubble();
        $testResource = new class(new Pricehubble()) extends AbstractResource {};

        $testResource->setPricehubble($pricehubble);
        $result = $testResource->getPricehubble();
        self::assertSame($result, $pricehubble);
    }

    public function testGetPricehubbleReturnsExpected(): void
    {
        $pricehubble = new Pricehubble();
        $testResourceClass = new class($pricehubble) extends AbstractResource {};
        $result = $testResourceClass->getPricehubble();
        self::assertSame($result, $pricehubble);
    }
}
