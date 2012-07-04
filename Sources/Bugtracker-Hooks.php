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
	$permissionGroups['membergroup']['simple'] = array('fxt_simple');
	$permissionGroups['membergroup']['classic'] = array('fxt_classic');

	// And then the permissions themselves, in all their glory!
	$permissionList['membergroup']['bugtracker_view'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bugtracker_viewprivate'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_add'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_edit_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_edit_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_remove_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_remove_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_reply_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_reply_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_new_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_new_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_wip_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_wip_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_done_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_done_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_reject_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_reject_own'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_attention_any'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bt_mark_attention_own'] = array(false, 'fxt_classic', 'fxt_simple');
	/*$permissionList['membergroup']['bugtracker_view'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bugtracker_view'] = array(false, 'fxt_classic', 'fxt_simple');
	$permissionList['membergroup']['bugtracker_view'] = array(false, 'fxt_classic', 'fxt_simple');*/
}