<?php

namespace Illuminate\Tests\Integration\Database;

use PDO;

/**
 * @group MySQL
 * @requires extension pdo_mysql
 * @requires OS Linux|Darwin
 */
class DatabaseEmulatePreparesMySqlConnectionTest extends DatabaseMySqlConnectionTest
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.mysql.options', [
            PDO::ATTR_EMULATE_PREPARES => true,
        ]);
    }
}
