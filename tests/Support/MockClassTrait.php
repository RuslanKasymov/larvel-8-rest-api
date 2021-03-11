<?php

namespace Tests\Support;

use Illuminate\Support\Arr;
use Mockery\MockInterface;

trait MockClassTrait
{
    /**
     * Mock selected class. Call chain should looks like:
     *
     * [
     *     [
     *         'method' => 'yourMethod',
     *         'result' => 'result_fixture.json'
     *     ]
     * ]
     *
     * @param string $class
     * @param array $callChain
     */
    public function mockClass(string $class, array $callChain)
    {
        $mock = \Mockery::mock($class);
        $mock->shouldAllowMockingProtectedMethods();

        foreach ($callChain as $call) {
            $mock->shouldReceive($call['method'])->andReturn($call['result']);
        }

        $this->app->instance($class, $mock);
    }

    /**
     * Mock selected class. Call chain should looks like:
     *
     * [
     *     [
     *         'method' => 'yourMethod',
     *         'result' => 'result_fixture.json'
     *     ]
     * ]
     *
     * @param string $class
     * @param array $callChain
     * @param array $constructorArgs
     */
    public function mockClassPartial(string $class, array $callChain, array $constructorArgs=[])
    {
        $mock = \Mockery::mock($class, $constructorArgs);
        $mock->shouldAllowMockingProtectedMethods()->makePartial();

        foreach ($callChain as $call) {
            $mock->shouldReceive($call['method'])->andReturn($call['result']);
        }
        $this->app->instance($class, $mock);
    }
}
