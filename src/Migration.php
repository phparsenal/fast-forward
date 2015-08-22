<?php

namespace phparsenal\fastforward;

use nochso\ORM\DBA\DBA;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * Class Migration
 *
 * Collection of migration steps for moving along versions of the database.
 */
class Migration
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var OutputStyle
     */
    private $out;

    /**
     * @param Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
        $this->out = $this->client->getOutput();
    }

    /**
     * Make sure the database is up to date
     *
     * @throws \Exception
     */
    public function run()
    {
        $version = $this->getDatabaseVersion();
        $migrated = false;
        if ($version === null) {
            if ($this->hasTables()) {
                $migrated = $this->fromUnversioned();
            } else {
                $migrated = $this->fromBlank();
            }
        } else {
            $migrated = $this->fromVersion($version);
        }
        if ($migrated) {
            $this->saveDatabaseVersion();
            $this->out->success('Updated the database to version ' . Client::FF_VERSION);
        }
    }

    /**
     * Migrate the database from a known version to the most recent
     *
     * @param string $version Current version of the database
     *
     * @return bool True when a migration happened
     */
    private function fromVersion($version)
    {
        if (version_compare($version, Client::FF_VERSION) !== -1) {
            return false;
        }
        $this->out->writeln('Current database version is ' . Client::FF_VERSION);

        // New migrations go here >>>

        return true;
    }

    /**
     * Prepares the database when it is still unversioned.
     *
     * @return bool True when a migration happened
     */
    private function fromUnversioned()
    {
        $this->out->writeln('Updating database from unknown version');
        // There's already a bookmark table, but no settings yet.
        // Basically the state of the project as this was written.
        $sql = '
                CREATE TABLE IF NOT EXISTS "setting" (
                  "key" text NOT NULL,
                  "value" text NOT NULL
                )';
        DBA::execute($sql);
        return true;
    }

    /**
     * Initial setup of an empty database
     *
     * @return bool True when a migration happened
     *
     * @throws \Exception
     */
    private function fromBlank()
    {
        $this->out->note('No database found. Setting up a fresh one.');
        $setupStatements = $this->getBlankStatements();
        $this->out->progressStart(count($setupStatements));
        foreach ($setupStatements as $key => $singleSql) {
            $singleSql = trim($singleSql);
            try {
                $statement = DBA::prepare($singleSql);
                $statement->execute();
            } catch (\PDOException $e) {
                $msg = 'SQL error: ' . $e->getMessage() . "\nWhile trying to execute:\n$singleSql";
                throw new \Exception($msg);
            }
            $this->out->progressAdvance();
        }
        $this->out->progressFinish();
        return true;
    }

    /**
     * Returns a list of SQL statements to completely set up an empty database
     *
     * @return string[]
     *
     * @throws \Exception
     */
    private function getBlankStatements()
    {
        $schemaPath = 'asset/model.sql';
        if (!is_file($schemaPath)) {
            throw new \Exception("Schema file could not be found: \"$schemaPath\"\nPlease make sure that you have this file.");
        }
        $schemaSql = file_get_contents($schemaPath);
        if ($schemaSql === false) {
            throw new \Exception('Unable to read schema file: "' . $schemaPath . '"');
        }
        // Explode into single statements
        $schemaSqlList = explode(';', $schemaSql);
        // Remove entries with empty strings
        $schemaSqlList = array_filter($schemaSqlList, 'trim');
        return $schemaSqlList;
    }

    /**
     * Returns the current version of the database.
     *
     * Null when the version is unknown
     *
     * @return null|string
     */
    public function getDatabaseVersion()
    {
        try {
            return $this->client->getSetting(Settings::DATABASE_VERSION);
        } catch (\PDOException $e) {
            return null;
        }
    }

    private function saveDatabaseVersion()
    {
        $this->client->setSetting(Settings::DATABASE_VERSION, Client::FF_VERSION);
    }

    /**
     * Returns false when there are no tables at all
     *
     * @return bool
     */
    private function hasTables()
    {
        $sql = 'SELECT COUNT(*) FROM sqlite_master';
        $count = (int)DBA::execute($sql)->fetchColumn();
        return $count !== 0;
    }
}
