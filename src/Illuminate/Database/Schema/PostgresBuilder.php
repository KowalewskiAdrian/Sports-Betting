<?php

namespace Illuminate\Database\Schema;

class PostgresBuilder extends Builder
{
    /**
     * Determine if the given table exists.
     *
     * @param  string  $table
     * @return bool
     */
    public function hasTable($table)
    {
        [$database, $schema, $table] = $this->parseSchemaAndTable($table);

        $table = $this->connection->getTablePrefix().$table;

        return count($this->connection->select(
            $this->grammar->compileTableExists(), [$database, $schema, $table]
        )) > 0;
    }

    /**
     * Drop all tables from the database.
     *
     * @return void
     */
    public function dropAllTables()
    {
        $tables = [];

        $excludedTables = $this->connection->getConfig('dont_drop') ?? ['spatial_ref_sys'];

        foreach ($this->getAllTables() as $row) {
            $row = (array) $row;

            $table = reset($row);

            if (! in_array($table, $excludedTables)) {
                $tables[] = $table;
            }
        }

        if (empty($tables)) {
            return;
        }

        $this->connection->statement(
            $this->grammar->compileDropAllTables($tables)
        );
    }

    /**
     * Drop all views from the database.
     *
     * @return void
     */
    public function dropAllViews()
    {
        $views = [];

        foreach ($this->getAllViews() as $row) {
            $row = (array) $row;

            $views[] = reset($row);
        }

        if (empty($views)) {
            return;
        }

        $this->connection->statement(
            $this->grammar->compileDropAllViews($views)
        );
    }

    /**
     * Drop all types from the database.
     *
     * @return void
     */
    public function dropAllTypes()
    {
        $types = [];

        foreach ($this->getAllTypes() as $row) {
            $row = (array) $row;

            $types[] = reset($row);
        }

        if (empty($types)) {
            return;
        }

        $this->connection->statement(
            $this->grammar->compileDropAllTypes($types)
        );
    }

    /**
     * Get all of the table names for the database.
     *
     * @return array
     */
    public function getAllTables()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables((array) $this->connection->getConfig('schema'))
        );
    }

    /**
     * Get all of the view names for the database.
     *
     * @return array
     */
    public function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews((array) $this->connection->getConfig('schema'))
        );
    }

    /**
     * Get all of the type names for the database.
     *
     * @return array
     */
    public function getAllTypes()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTypes()
        );
    }

    /**
     * Get the column listing for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getColumnListing($table)
    {
        [$database, $schema, $table] = $this->parseSchemaAndTable($table);

        $table = $this->connection->getTablePrefix().$table;

        $results = $this->connection->select(
            $this->grammar->compileColumnListing(), [$database, $schema, $table]
        );

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }

    /**
     * Parse the search_path.
     *
     * @param string|array  $searchPath
     * @return array
     */
    protected function parseSearchPath($searchPath)
    {
        if (is_string($searchPath)) {
            preg_match_all('/[a-zA-z0-9$]{1,}/i', $searchPath, $matches);

            $searchPath = $matches[0];
        }

        array_walk($searchPath, function (&$schema) {
            $schema = trim($schema, '\'"');
        });

        return $searchPath;
    }

    /**
     * Parse the database object reference and extract the database, schema, and
     * table.
     *
     * @param  string  $reference
     * @return array
     */
    protected function parseSchemaAndTable($reference)
    {
        $searchPath = $this->parseSearchPath(
            $this->connection->getConfig('search_path') ?: 'public'
        );

        $parts = explode('.', $reference);

        // Use the connection's configured database by default.

        $database = $this->connection->getConfig('database');

        // If the reference contains a database name, use that instead.

        if (count($parts) === 3) {
            $database = $parts[0];

            array_shift($parts);
        }

        // Use the first schema in the search_path by default.

        $schema = $searchPath[0];

        // If the reference contains a schema, use that instead.

        if (count($parts) === 2) {
            $schema = $parts[0];

            array_shift($parts);
        }

        return [$database, $schema, $parts[0]];
    }
}
