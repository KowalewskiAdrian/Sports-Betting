<?php namespace Illuminate\Database\Connectors;

class MySqlConnector extends Connector implements ConnectorInterface {

	/**
	 * Establish a database connection.
	 *
	 * @param  array  $config
	 * @return \PDO
	 */
	public function connect(array $config)
	{
		$dsn = $this->getDsn($config);

		// We need to grab the PDO options that should be used while making the brand
		// new connection instance. The PDO options control various aspects of the
		// connection's behavior, and some might be specified by the developers.
		$options = $this->getOptions($config);

		$connection = $this->createConnection($dsn, $config, $options);
		
		// We need to explicitly exec the 'use' mysql command in case the 
		// unix_socket option is used in the dsn.
		if (isset($config['unix_socket']))
		{
			$connection->exec("use {$config['database']};");
		}

		$collation = $config['collation'];

		// Next we will set the "names" and "collation" on the clients connections so
		// a correct character set will be used by this client. The collation also
		// is set on the server but needs to be set here on this client objects.
		$charset = $config['charset'];

		$names = "set names '$charset'".
			( ! is_null($collation) ? " collate '$collation'" : '');

		$connection->prepare($names)->execute();

		// If the "strict" option has been configured for the connection we'll enable
		// strict mode on all of these tables. This enforces some extra rules when
		// using the MySQL database system and is a quicker way to enforce them.
		if (isset($config['strict']) && $config['strict'])
		{
			$connection->prepare("set session sql_mode='STRICT_ALL_TABLES'")->execute();
		}

		return $connection;
	}

	/**
	 * Create a DSN string from a configuration.
	 *
	 * @param  array   $config
	 * @return string
	 */
	protected function getDsn(array $config)
	{
		// First we will create the basic DSN setup as well as the port if it is in
		// in the configuration options. This will give us the basic DSN we will
		// need to establish the PDO connections and return them back for use.
		extract($config);

		$dsn = "mysql:";
		
		if (!isset($config['unix_socket'])) 
		{
			$dsn .= "host={$host}";
	
			if (isset($config['port']))
			{
				$dsn .= ";port={$port}";
			}
		} 
		else 
		{
			$dsn .= "unix_socket={$config['unix_socket']}";
		}
		
		$dsn .= ";dbname={$database}";

		return $dsn;
	}

}
