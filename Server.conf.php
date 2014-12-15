<?php
Config::set_parameter_pair (array ('connport', 7121) );
Config::set_parameter_pair (array ('connip', '123.456.789.012') );
Config::set_parameter_pair (array ('bindip', '0.0.0.0') );
Config::set_parameter_pair (array ('linkpassword', 'electronoobrocks') );
Config::set_parameter_pair (array ('bindserver', 'spicy.ukchatters.co.uk') );
Config::set_parameter_pair (array ('bindid', 3) );
Config::set_parameter_pair (array ('binddescription', "electronoob RoxMySox") );


/* database configuration */
Config::set_parameter_pair (array ('database_engine', "MySQL") );
Config::set_parameter_pair (array ('database_hostname', "localhost") );
Config::set_parameter_pair (array ('database_username', "Joomla") );
Config::set_parameter_pair (array ('database_database', "madatabase") );
Config::set_parameter_pair (array ('database_password', "mapasswordz") );
Config::set_parameter_pair (array ('database_joomla_prefix', "prefix_") );
Config::set_parameter_pair (array ('database_ariel_prefix', "old_") );

// setup channels to join and monitor

Config::set_parameter_pair(array ('channels',array(
	'geek',
	'asian',
	'dating',
	'staff',
	'ukchatters',
	'england',
	'scotland',
	'wales',
	'ireland',
	'cafe',
	'20s',
	'40s',
	'trivia',
	'help',
	'youngatheart',
	'gay',
	'lesbians',
	'east-anglia',
	'london',
        'Birmingham',
        'leeds',
        'glasgow',
        'sheffield',
        'bradford',
        'edinburgh',
        'liverpool',
        'manchester',
        'bristol',
        'cardiff',
        'coventry',
        'nottingham',
        'leicester',
        'sunderland',
        'belfast',
        'newcastle',
        'brighton',
        'hull',
        'plymouth',
        'stoke-on-trent',
        'wolverhampton',
        'swansea',
        'southampton',
        'aberdeen',
        'dublin',
	'music',
	'comfycorner',
	'northern-ireland'
)));
