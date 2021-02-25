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
use Tymon\JWTAuth\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use AutoDocTestCaseTrait;

    protected $jwt;
    protected $auth;
    protected $testNow = '2018-11-11 11:11:11';
    protected static $tables;
    protected $postgisTables = [
        'tiger.addrfeat',
        'tiger.edges',
        'tiger.faces',
        'topology.topology',
        'tiger.place_lookup',
        'topology.layer',
        'tiger.geocode_settings',
        'tiger.geocode_settings_default',
        'tiger.direction_lookup',
        'tiger.secondary_unit_lookup',
        'tiger.state_lookup',
        'tiger.street_type_lookup',
        'tiger.county_lookup',
        'tiger.countysub_lookup',
        'tiger.zip_lookup_all',
        'tiger.zip_lookup_base',
        'tiger.zip_lookup',
        'tiger.county',
        'tiger.state',
        'tiger.place',
        'tiger.zip_state',
        'tiger.zip_state_loc',
        'tiger.cousub',
        'tiger.featnames',
        'tiger.addr',
        'tiger.zcta5',
        'tiger.loader_platform',
        'tiger.loader_variables',
        'tiger.loader_lookuptables',
        'tiger.tract',
        'tiger.tabblock',
        'tiger.bg',
        'tiger.pagc_gaz',
        'tiger.pagc_lex',
        'tiger.pagc_rules',
    ];

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Carbon::setTestNow(Carbon::parse($this->testNow));

        $this->artisan('cache:clear');
        $this->artisan('config:cache');
        $this->artisan('migrate');

        $this->loadTestDump();

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

    protected function loadTestDump($truncateExcept = ['migrations', 'password_resets', 'roles'],
                                    $prepareSequencesExcept = ['migrations', 'password_resets', 'roles'])
    {
        $dump = $this->getFixture('dump.sql', false);

        $databaseTables = $this->getTables();
        $scheme = config('database.default');

        $this->clearDatabase($scheme, $databaseTables, array_merge($this->postgisTables, $truncateExcept));

        if (empty($dump)) {
            return;
        }

        DB::unprepared($dump);

        if ($scheme === 'pgsql') {
            $this->prepareSequences($databaseTables, array_merge($this->postgisTables, $prepareSequencesExcept));
        }
    }

    protected function getTables()
    {
        if (empty(self::$tables)) {
            self::$tables = app('db.connection')
                ->getDoctrineSchemaManager()
                ->listTableNames();
        }

        return self::$tables;
    }

    public function clearDatabase($scheme, $tables, $except)
    {
        if ($scheme === 'pgsql') {
            $query = $this->getClearPsqlDatabaseQuery($tables, $except);
        } elseif ($scheme === 'mysql') {
            $query = $this->getClearMySQLDatabaseQuery($tables, $except);
        }

        if (!empty($query)) {
            app('db.connection')->unprepared($query);
        }
    }

    public function getClearPsqlDatabaseQuery($tables, $except = ['migrations'])
    {
        return $this->arrayConcat($tables, function ($table) use ($except) {
            if (in_array($table, $except)) {
                return '';
            } else {
                return "TRUNCATE {$table} RESTART IDENTITY CASCADE; \n";
            }
        });
    }

    public function getClearMySQLDatabaseQuery($tables, $except = ['migrations'])
    {
        $query = "SET FOREIGN_KEY_CHECKS = 0;\n";

        $query .= $this->arrayConcat($tables, function ($table) use ($except) {
            if (in_array($table, $except)) {
                return '';
            } else {
                return "TRUNCATE TABLE {$table}; \n";
            }
        });

        return "{$query} SET FOREIGN_KEY_CHECKS = 1;\n";
    }

    public function prepareSequences($tables, $except)
    {
        $query = $this->arrayConcat($tables, function ($table) use ($except) {
            if (in_array($table, $except)) {
                return '';
            } else {
                return "SELECT setval('{$table}_id_seq', (select max(id) from {$table}));\n";
            }
        });

        app('db.connection')->unprepared($query);
    }

    function arrayConcat($array, $callback)
    {
        $content = '';

        foreach ($array as $key => $value) {
            $content .= $callback($value, $key);
        }

        return $content;
    }
}
