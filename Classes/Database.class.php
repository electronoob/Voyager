<?php

Class Database {
	private static $database;
	private static $engine;
	private static $prefix;
	function init () {
		self::$engine = Config::get_parameter_pair ('database_engine');
		if (self::$engine == "MySQL") {
			$hostname = Config::get_parameter_pair ('database_hostname');
			$username = Config::get_parameter_pair ('database_username');
			$database = Config::get_parameter_pair ('database_database');
			$password = Config::get_parameter_pair ('database_password');
			$db = new MySQL ($hostname, $username, $password, $database);
			self::set_database ($db);
			$prefix = Config::get_parameter_pair ('database_joomla_prefix');
			self::set_prefix ( $prefix );
		}
	}
	function set_database ( $db ) {
		self::$database = $db;
	}
	function get_database ( ) {
		return self::$database;
	}
	function set_prefix ( $prefix ) {
		self::$prefix = $prefix;
	}
	function get_prefix () {
		return self::$prefix;
	}
	function check_password ($snick, $passkey) {
		if ($user = self::qdb_user ($snick)) {

                        /* CRYPT_BLOWFISH ? */
                        /* allow webchat clients to transmit entire hash and salt */
                        if ( ('$2y$' == substr($user['password'], 0, 4) ) & ('$2y$' == substr($passkey, 0, 4)) ) {
                                if (strcmp($user['password'], $passkey) === 0) {
                                        return 1;
                                } else {
                                        return 0;
                                }
                        }
                        if ('$2y$' == substr($user['password'], 0, 4)){
                                if (password_verify($passkey, $user['password'])) {
                                        return 1;
                                } else {
                                        return 0;
                                }
                        }

			$password = explode (':', $user['password']);
			$salt = $password[1];
			$password = $password[0];
			$saltypasskey = $passkey.$salt;
			$md5_saltypasskey = md5($saltypasskey);
			if ($md5_saltypasskey == $password) {
				return 1;
			} else {
				// if regular password failed to verify try to compare against salted md5 in the db
				If ($password == $passkey) return 1;
				return 0;
			}
		} else {
				return 0;
		}
	}
	function qdb_user($username) {
                $db = self::get_database ();
                $db_prefix = self::get_prefix ();
                if ($result = $db->query ("SELECT * FROM `".$db_prefix."users` WHERE `username` = '".$username."' LIMIT 1")) {
                        $user = $result->fetch_assoc ();
                        /* free result set */
                        $result->close();
		} else {
			return false;
		}
		return $user;
	}
	function test_database () {
		$db = self::get_database ();
		$db_prefix = self::get_prefix ();
		if ($result = $db->query ("SELECT * FROM `".$db_prefix."users` LIMIT 1")) {
			printf ("Select returned %d rows.\n", $result->num_rows);
			while ( $row = $result->fetch_assoc () )
			{
				return 1;
			}
			/* free result set */
			$result->close();
		} else {
			die("Unable to search database for it's users. There's a configuration problem.");
		}
	}
	// begin LogServ methods
	function insert ($table, $assoc) {
		$db = self::get_database ();
		$ariel_prefix = Config::get_parameter_pair ('database_ariel_prefix');
		foreach ($assoc as $key => $value) {
			$keys[]   = $db->real_escape_string ($key);
			$values[] = $db->real_escape_string ($value);
		}
		$compiled_keys   = "`".implode ("`,`" ,   $keys)."`";
		$compiled_values = "'".implode ("','" ,   $values)."'";
		$table = $ariel_prefix . $table;
		$query = "INSERT INTO `$table` ($compiled_keys) VALUES ($compiled_values)";
		$result = $db->query ($query);
		if ($result  === TRUE) {
			$db->commit ();
			return 1;
		} else {
			echo "unable to insert\n";
			printf("Errormessage: %s\n", $db->error);
			return 0;
		}
	}

        function select ($table, $assoc) {
		$db = self::get_database ();
		$ariel_prefix = Config::get_parameter_pair ('database_ariel_prefix');
                foreach ($assoc as $key => $value) {
                        $keys[]   = $db->real_escape_string ($key);
                        $values[] = $db->real_escape_string ($value);
                }
                $table = $ariel_prefix . $table;
		//$query = "INSERT INTO `$table` ($compiled_keys) VALUES ($compiled_values)";
		//                        'phptime'      => strtotime("-15 minute"),
                //		        'destination'  => $destination,
		$timestamp = $assoc['phptime'];
		$channel = $assoc['destination'];
		$source = $assoc['source'];
		$query = "SELECT `id`, `sqltimestamp`, `phptime`, `source`, `target`, `destination`, `message`, `event` FROM  `$table` WHERE  `phptime` > $timestamp AND  `destination` LIKE  '$channel' ORDER BY  `$table`.`id` ASC ";
		$data_array = array();
                if ($result = $db->query ($query)) {
                        while($row = $result->fetch_assoc()) {
                                $data_array[] = $row;
                        }
                        /* free result set */
                        $result->close();
                } else {
                        return false;
                }
                return $data_array;
	}
}
