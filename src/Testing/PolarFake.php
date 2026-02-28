<?php

namespace Climactic\LaravelPolar\Testing;

use Climactic\LaravelPolar\LaravelPolar;
use PHPUnit\Framework\Assert as PHPUnit;

class PolarFake
{
    /**
     * Recorded method calls: ['method' => [[$args], [$args], ...]]
     *
     * @var array<string, list<list<mixed>>>
     */
    private array $calls = [];

    /**
     * Stub return values: ['method' => $value]
     *
     * @var array<string, mixed>
     */
    private array $stubs = [];

    /**
     * Install the fake by replacing the SDK with a recording proxy.
     */
    public static function install(): self
    {
        $fake = new self();

        // Create a mock Polar SDK — when faking, all calls are intercepted
        // by recordIfFaking() before reaching the SDK, so the mock is just
        // a placeholder to satisfy the type system.
        /** @var \Polar\Polar $mockSdk */
        $mockSdk = \Mockery::mock(\Polar\Polar::class);

        LaravelPolar::setSdk($mockSdk);

        return $fake;
    }

    /**
     * Tear down the fake and restore normal SDK behavior.
     */
    public function tearDown(): void
    {
        LaravelPolar::resetSdk();
    }

    /**
     * Configure a stub return value for a method.
     *
     * @return $this
     */
    public function stub(string $method, mixed $value): self
    {
        $this->stubs[$method] = $value;

        return $this;
    }

    /**
     * Record a method call.
     *
     * @param  list<mixed>  $args
     */
    public function recordCall(string $method, array $args): mixed
    {
        $this->calls[$method][] = $args;

        return $this->stubs[$method] ?? null;
    }

    /**
     * Assert a method was called at least once.
     */
    public function assertCalled(string $method): self
    {
        PHPUnit::assertTrue(
            isset($this->calls[$method]) && count($this->calls[$method]) > 0,
            "Expected [{$method}] to be called, but it was not."
        );

        return $this;
    }

    /**
     * Assert a method was not called.
     */
    public function assertNotCalled(string $method): self
    {
        PHPUnit::assertTrue(
            ! isset($this->calls[$method]) || count($this->calls[$method]) === 0,
            "Unexpected call to [{$method}]."
        );

        return $this;
    }

    /**
     * Assert a method was called with specific arguments.
     *
     * @param  callable(mixed...): bool  $callback
     */
    public function assertCalledWith(string $method, callable $callback): self
    {
        PHPUnit::assertTrue(
            isset($this->calls[$method]) && count($this->calls[$method]) > 0,
            "Expected [{$method}] to be called, but it was not."
        );

        $matched = false;
        foreach ($this->calls[$method] as $args) {
            if ($callback(...$args)) {
                $matched = true;
                break;
            }
        }

        PHPUnit::assertTrue($matched, "Expected [{$method}] to be called with matching arguments, but no call matched.");

        return $this;
    }

    /**
     * Assert a method was called exactly N times.
     */
    public function assertCalledTimes(string $method, int $times): self
    {
        $actual = isset($this->calls[$method]) ? count($this->calls[$method]) : 0;

        PHPUnit::assertSame(
            $times,
            $actual,
            "Expected [{$method}] to be called {$times} times, but was called {$actual} times."
        );

        return $this;
    }

    /**
     * Assert no methods were called at all.
     */
    public function assertNothingCalled(): self
    {
        $totalCalls = array_sum(array_map('count', $this->calls));

        PHPUnit::assertSame(0, $totalCalls, "Expected no methods to be called, but {$totalCalls} calls were recorded.");

        return $this;
    }
}
