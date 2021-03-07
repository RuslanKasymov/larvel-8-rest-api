<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

trait LoadTestDumpTrait
{
    protected static $tables;
    protected array $truncateExcept = ['migrations', 'password_resets', 'roles'];
    protected array $prepareSequencesExcept = ['migrations', 'password_resets', 'roles'];
    protected array $postgisTables = [
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

    protected function loadTestDump()
    {
        $dump = $this->getFixture('dump.sql', false);

        $databaseTables = $this->getTables();
        $scheme = config('database.default');

        $this->clearDatabase($scheme, $databaseTables, array_merge($this->postgisTables, $this->truncateExcept));

        if (empty($dump)) {
            return;
        }

        DB::unprepared($dump);

        if ($scheme === 'pgsql') {
            $this->prepareSequences($databaseTables, array_merge($this->postgisTables, $this->prepareSequencesExcept));
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
