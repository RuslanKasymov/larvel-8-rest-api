<?php

namespace Tests;

use Apoplavs\Support\AutoDoc\Services\SwaggerService;
use Apoplavs\Support\AutoDoc\Tests\AutoDocTestCaseTrait;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use AutoDocTestCaseTrait;

    protected $jwt;
    protected $auth;
    protected $testNow = '2018-11-11 11:11:11';

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Schema::dropAllTables();
        Carbon::setTestNow(Carbon::parse($this->testNow));
        $this->artisan('cache:clear');
        $this->artisan('migrate');

        $this->auth = app(JWTAuth::class);
        $this->docService = app(SwaggerService::class);
    }

    public function actingAs(Authenticatable $user, $driver = null)
    {
        $this->jwt = $this->auth->fromUser($user);

        return $this;
    }

    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $headers = [
            'Authorization' => empty($this->jwt) ? null : "Bearer {$this->jwt}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $server = array_merge(
            $this->transformHeadersToServerVars($headers),
            $server
        );

        $response = parent::call($method, $uri, $parameters, $cookies,
            $files, $server, $content);

        return $response;
    }

    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->loadEnvironmentFrom('.env.testing');
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function tearDown(): void
    {
        $currentTestCount = $this->getTestResultObject()->count();
        $allTestCount = $this->getTestResultObject()->topTestSuite()->count();

        if (($currentTestCount == $allTestCount) && (!$this->hasFailed())) {
            $this->docService->saveProductionData();
        }

        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        parent::tearDown();
    }

    public function getFixturePath($fn)
    {
        $class = get_class($this);
        $explodedClass = explode('\\', $class);
        $className = Arr::last($explodedClass);

        return base_path("tests/fixtures/{$className}/{$fn}");
    }

    public function getFixture($fn, $failIfNotExists = true)
    {
        $path = $this->getFixturePath($fn);

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        if ($failIfNotExists) {
            $this->fail($fn . ' fixture does not exist');
        }

        return '';
    }

    public function getJsonFixture($fn, $assoc = true)
    {
        return json_decode($this->getFixture($fn), $assoc);
    }

    public function assertEqualsFixture($fixture, $data)
    {
        $this->assertEquals($this->getJsonFixture($fixture), $data);
    }
}
