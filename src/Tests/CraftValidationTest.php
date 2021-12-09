<?php
declare(strict_types=1);

namespace App\Tests;

use App\Command\CraftPluginCommand;
use PHPUnit\Framework\TestCase;

class CraftValidationTest extends TestCase
{

    protected CraftPluginCommand $craft;

    protected function setUp(): void
    {
        $this->craft = new CraftPluginCommand();
    }


    public function testValidateLimitOptionSuccessfully(): void
    {
        $limit = 50;
        $this->assertTrue($this->craft->validateLimitOption($limit));
    }

    /**
     * @throws \Exception
     */
    public function testLimitOptionWithOutOfRangeNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $limit = 101;
        $this->craft->validateLimitOption($limit);
    }

    /**
     * @throws \Exception
     */
    public function testLimitOptionWithOutOfRangeLikeZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $limit = 0;

        $this->craft->validateLimitOption($limit);
    }

    /**
     * @throws \Exception
     */
    public function testLimitOptionWithStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $limit = 'random_string';
        $this->craft->validateLimitOption($limit);
    }


    public function testOrderOptionWithDescOrder(): void
    {
        $order = 'desc';
        $result = $this->craft->validateOrderOption($order);
        $this->assertTrue($result);
    }


    public function testOrderOptionWithAscOrder(): void
    {
        $order = 'asc';
        $result = $this->craft->validateOrderOption($order);
        $this->assertTrue($result);
    }

    /**
     * @throws \Exception
     */
    public function testOrderOptionWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $order = 'invalid_order';
        $this->craft->validateOrderOption($order);
    }


    public function testOrderByOptionWithValidValue(): void
    {
        $orderBy = 'downloads';
        $result = $this->craft->validateOrderByOption($orderBy);
        $this->assertTrue($result);
    }


    /**
     * @throws \Exception
     */
    public function testOrderByOptionWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $order = 'invalid_order_by';
        $this->craft->validateOrderByOption($order);
    }
}