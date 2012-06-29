<?php

/* FXTracker - Hooks */

function fxt_actions(&$actionArray)
{
	// Add the action! Quick!
	$actionArray['bugtracker'] = array('Bugtracker.php', 'BugTrackerMain');
}

function fxt_permissions(&$permissionGroups, &$permissionList)
{
	// Load the language for this.
	loadLanguage('BugTracker');

	// Permission groups...
	$permissionGroups['membergroup']['simple'] = array('fxt_classic');
	$permissionGroups['membergroup']['classic'] = array('fxt_classic');
}
