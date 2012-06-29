<?php

/* FXTracker - Temporary Installer */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');

if (SMF == 'SSI')
	db_extend('packages'); 

add_integration_function('integrate_pre_include', '$sourcedir/Bugtracker-Hooks.php', true);
add_integration_function('integrate_actions', 'fxt_actions', true);
add_integration_function('integrate_load_permissions', 'fxt_permissions', true);

echo 'Installer done';

?>
