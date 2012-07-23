<?php

/* FXTracker Main File
 * Initializes FXTracker and the main functions.
 */

function BugTrackerMain()
{
	// Our usual stuff.
	global $context, $txt, $sourcedir, $scripturl;

	// Load the language and template. Oh, don't forget our CSS file, either.
	loadLanguage('BugTracker');
	loadTemplate('BugTracker');
	loadTemplate(false, 'bugtracker');

	// Are we allowed to view this?
	isAllowedTo('bugtracker_view');

	// A list of all actions we can take.
	// 'action' => 'bug tracker function',
	$sactions = array(
		'credits' => 'Credits',

		'edit' => 'Edit',
		'edit2' => 'SubmitEdit',

		'home' => 'Home',

		'mark' => 'MarkEntry',
		'mark2' => 'SubmitMarkEntry',

		'new' => 'NewEntry',
		'new2' => 'SubmitNewEntry',

		'projectindex' => 'ViewProject',

		'remove' => 'RemoveEntry',

		'maintenance' => 'Maintenance',
		'maintenance2' => 'PerformMaintenance',

		'view' => 'View',
		'viewtype' => 'ViewType',
		'viewstatus' => 'ViewStatus',
	);

	// Allow mod creators to easily snap in.
	call_integration_hook('integrate_bugtracker_actions', array(&$sactions));

	// Default is home.
	$action = 'home';

	// Try to see if we have any other action to use!
	if (!empty($_GET['sa']) && !empty($sactions[$_GET['sa']]) && function_exists('BugTracker' . $sactions[$_GET['sa']]))
		$action = $_GET['sa'];

	// And add a bit onto the linktree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker',
		'name' => $txt['bugtracker'],
	);

	// Then, execute the function!
	call_user_func('BugTracker' . $sactions[$action]);
}

function BugTrackerHome()
{
	// Global some stuff
	global $smcFunc, $context, $user_info, $user_profile, $txt;

	// Set the page title.
	$context['page_title'] = $txt['bugtracker_index'];

	// Grab the projects.
	$request = $smcFunc['db_query']('', '
		SELECT
			id, name, description, issuenum, featurenum
		FROM {db_prefix}bugtracker_projects'
	);

	// Start empty...
	$context['bugtracker']['projects'] = array();
	while ($project = $smcFunc['db_fetch_assoc']($request))
	{
		$context['bugtracker']['projects'][$project['id']] = array(
			'id' => $project['id'],
			'name' => $project['name'],
			'num' => array(
				'issues' => (int) $project['issuenum'],
				'features' => (int) $project['featurenum'],
			),
			'description' => parse_bbc($project['description']),
			'entries' => array(),
		);
	}

	// Clean up.
	$smcFunc['db_free_result']($request);

	// Grab the entries we are allowed to view.
	$where = !allowedTo('bugtracker_viewprivate') ? 'WHERE private = 0' : '';
	$request = $smcFunc['db_query']('', '
		SELECT
			id, name, description, type,
			tracker, private, project,
			status, attention, progress

		FROM {db_prefix}bugtracker_entries
		' . $where
	);

	// If we have zero or less(?), don't bother fetching them. 
	$context['bugtracker']['entries'] = array();
	$context['bugtracker']['feature'] = array();
	$context['bugtracker']['issue'] = array();
	$context['bugtracker']['attention'] = array();
	while ($entry = $smcFunc['db_fetch_assoc']($request))
	{
		// Then we're ready for some action.
		$context['bugtracker']['entries'][$entry['id']] = array(
			'id' => $entry['id'],
			'name' => $entry['name'],
			'shortdesc' => shorten_subject($entry['description'], 50),
			'desc' => $entry['description'], // As there may be a LOT of entries, do *NOT* use parse_bbc() here!
			'type' => $entry['type'],
			'tracker' => $entry['tracker'], // Again, if there are a lot of entries, loading member data for everything may *horribly* slow down the place.
			'private' => $entry['private'], // Is a boolean anyway.
			'project' => array(),
			'status' => $entry['status'],
			'attention' => $entry['attention'],
			'progress' => (empty($entry['progress']) ? '0' : $entry['progress']) . '%'
		);

		$pid = $entry['project'];
		if (array_key_exists($pid, $context['bugtracker']['projects']))
			$context['bugtracker']['entries'][$entry['id']]['project'] = $context['bugtracker']['projects'][$pid];

		// Also create a list of issues and features!
		$context['bugtracker'][$entry['type']][] = $context['bugtracker']['entries'][$entry['id']];

		// Is the status of this entry "attention"? If so, add it to the list of attention requirements thingies!
		if ($entry['attention'])
			$context['bugtracker']['attention'][] = $context['bugtracker']['entries'][$entry['id']];
	}

	// Clean up.
	$smcFunc['db_free_result']($request);

	// Put the last 5 entries of each category in a new array.
	$context['bugtracker']['latest']['issues'] = array_reverse(array_slice($context['bugtracker']['issue'], -5));
	$context['bugtracker']['latest']['features'] = array_reverse(array_slice($context['bugtracker']['feature'], -5));

	// What's our template, doc?
	$context['sub_template'] = 'TrackerHome';
}

function BugTrackerView()
{
	// Our usual variables.
	global $context, $smcFunc, $user_info, $user_profile, $txt, $scripturl;

	// Grab the info for this issue, along with the project, and if we can't, tell the user that the issue does not exist.
	$request = $smcFunc['db_query']('', '
		SELECT
			e.id AS entry_id, e.name AS entry_name, e.description, e.type,
			e.tracker, e.private, e.startedon, e.project,
			e.status, e.attention, e.progress,
			p.id, p.name As project_name
		FROM {db_prefix}bugtracker_entries AS e
		INNER JOIN {db_prefix}bugtracker_projects AS p ON (e.project = p.id)
		WHERE e.id = {int:entry}',
		array(
			'entry' => $_GET['entry'],
		)
	);

	// Do we have anything? Or too much?
	if ($smcFunc['db_num_rows']($request) == 0 || $smcFunc['db_num_rows']($request) >= 2)
		fatal_lang_error('entry_no_exist');

	// Pick our data.
	$data = $smcFunc['db_fetch_assoc']($request);

	// Are we allowed to view private issues, and is this one of them?
	if (!allowedTo('bugtracker_viewprivate') && $data['private'] == 1)
		fatal_lang_error('entry_is_private', false);

	// Load the data for the tracker.
	loadMemberData($data['tracker']);

	// Put the data in $context for the template!
	$context['bugtracker']['entry'] = array(
		'id' => $data['entry_id'],
		'name' => $data['entry_name'],
		'desc' => parse_bbc($data['description']),
		'type' => $data['type'],
		'tracker' => $user_profile[$data['tracker']],
		'private' => $data['private'],
		'started' => $data['startedon'],
		'project' => array(
			'id' => (int) $data['project'],
			'name' => $data['project_name'],
		),
		'status' => $data['status'],
		'attention' => $data['attention'],
		'progress' => (empty($data['progress']) ? '0' : $data['progress']) . '%',
		'is_new' => isset($_GET['new']),
	);

	// Stuff the linktree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $context['bugtracker']['entry']['project']['id'],
		'name' => $data['project_name'],
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker;sa=view;entry=' . $data['entry_id'],
		'name' => sprintf($txt['entrytitle'], $data['entry_id'], $data['entry_name']),
	);

	// Setup permissions... Not just one of them!
        $own_any = array('mark', 'mark_new', 'mark_wip', 'mark_done', 'mark_reject', 'mark_attention', 'reply', 'edit', 'remove');
        $is_own = $context['user']['id'] == $data['tracker'];
        foreach ($own_any as $perm)
        {
                $context['can_bt_' . $perm . '_any'] = allowedTo('bt_' . $perm . '_any');
                $context['can_bt_' . $perm . '_own'] = allowedTo('bt_' . $perm . '_own') && $is_own;
        }
	
	// If we can mark something.... tell us!
        $context['bt_can_mark'] = allowedTo(array('can_bt_mark_own', 'can_bt_mark_any')) && allowedTo(array('can_bt_mark_new_own', 'can_bt_mark_new_any', 'can_bt_mark_wip_own', 'can_bt_mark_wip_any', 'can_bt_mark_done_own', 'can_bt_mark_done_any', 'can_bt_mark_reject_own', 'can_bt_mark_reject_any'));

	// Set the title.
	$context['page_title'] = sprintf($txt['view_title'], $data['entry_id']);

	// Then tell SMF what template to load.
	$context['sub_template'] = 'TrackerView';
}

function BugTrackerMarkEntry()
{
	// Globalizing...
	global $context, $scripturl, $smcFunc;

	// Load data associated with this entry, if it exists.
	$data = fxdb::grabEntry($_GET['entry']);

	// No entry? No marking.
	if (!$data)
		fatal_lang_error('entry_no_exists');

	// Then, are we allowed to do this kind of stuff?
	if (allowedTo('bt_mark_any') || (allowedTo('bt_mark_own') && $context['user']['id'] == $data['tracker']))
	{
		// A list of possible types.
		$types = array('new', 'wip', 'done', 'dead', 'reject', 'attention');

		// Allow people to integrate with this.
		call_integration_hook('bt_mark_types', $types);
		
		// Not in the list?
		if (!in_array($_GET['as'], $types))
			fatal_lang_error('entry_mark_failed');

		// Because I like peanuts.
		if ($_GET['as'] == 'dead')
			fatal_error('You killed my entry! Murderer!', false);

		// Are we resetting attention?
		if ($_GET['as'] == 'attention')
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_entries
				SET attention={int:attention}
				WHERE id={int:id}',
				array(
					'attention' => $data['attention'] ? 0 : 1,
					'id' => $data['id'],
				)
			);

			redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $data['id']);
		}

		// And 'nother hook for this...
		call_integration_hook('bt_mark', array(&$_GET['as']));

		// So it is. Mark it!
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}bugtracker_entries
			SET status={string:newstatus}
			WHERE id={int:id}',
			array(
				'newstatus' => $_GET['as'],
				'id' => $data['id'],
			));

		// And redirect us back.
		redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $data['id']);
	}
	else
		fatal_lang_error('entry_unable_mark');
}

function BugTrackerEdit()
{
	global $context, $smcFunc;

	// Are we using a valid entry id?
	$result = $smcFunc['db_query']('', '
		SELECT
			id, name, description, type,
			tracker, private, project,
			status, attention, progress
		FROM {db_prefix}bugtracker_entries
		WHERE id = {int:entry}',
		array(
			'entry' => $_GET['entry'],
		)
	);

}

function BugTrackerSubmitEdit()
{
	global $context, $smcFunc;
}

function BugTrackerNewEntry()
{
	global $context, $smcFunc, $txt, $scripturl, $sourcedir;

	// Are we allowed to create new entries?
	isAllowedTo('bt_add');

	// Load the project data.
	$result = $smcFunc['db_query']('', '
		SELECT
			id, name
		FROM {db_prefix}bugtracker_projects
		WHERE id = {int:project}',
		array(
			'project' => $_GET['project']
		)
	);

	// Wait.... There is no project like this? Or there's more with the *same* ID? :O
	if ($smcFunc['db_num_rows']($result) == 0 || $smcFunc['db_num_rows']($result) > 1)
		fatal_lang_error('project_no_exist');

	// So we have just one...
	$project = $smcFunc['db_fetch_assoc']($result);

	// Validate the stuff.
	$context['bugtracker']['project'] = array(
		'id' => (int) $project['id'],
		'name' => $project['name']
	);

	// We want the default SMF WYSIWYG editor.
	require_once($sourcedir . '/Subs-Editor.php');

	// Some settings for it...
	$editorOptions = array(
		'id' => 'entry_desc',
		'value' => '',
		'height' => '175px',
		'width' => '100%',
		// XML preview.
		'preview_type' => 2,
	);
	create_control_richedit($editorOptions);

	// Store the ID.
	$context['post_box_name'] = $editorOptions['id'];
	
	// Setup the page title...
	$context['page_title'] = $txt['entry_add'];

	// Set up the linktree, too...
	$context['linktree'][] = array(
		'name' => $project['name'],
		'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $project['id']
	);
	$context['linktree'][] = array(
		'name' => $txt['entry_add'],
		'url' => $scripturl . '?action=bugtracker;sa=new;project=' . $project['id']
	);

	// Then, set what template we should use!
	$context['sub_template'] = 'BugTrackerAddNew';
}

function BugTrackerSubmitNewEntry()
{
	global $smcFunc, $context, $sourcedir, $scripturl;

	// Start with checking if we can add new stuff...
	isAllowedTo('bt_add');

	// Load Subs-Post.php, will need that!
	include($sourcedir . '/Subs-Post.php');

	// Then, is the required is_fxt POST set?
	if (!isset($_POST['is_fxt']) || empty($_POST['is_fxt']))
		fatal_lang_error('save_failed');

	// Pour over these variables, so they can be altered and done with.
	$entry = array(
		'title' => $_POST['entry_title'],
		'type' => $_POST['entry_type'],
		'private' => !empty($_POST['entry_private']),
		'description' => $_POST['entry_desc'],
		'mark' => $_POST['entry_mark'],
		'attention' => !empty($_POST['entry_attention']),
		'project' => $_POST['entry_projectid']
	);

	// Check if the title, the type or the description are empty.
	if (empty($entry['title']))
		fatal_lang_error('no_title', false);

	// Type...
	if (empty($entry['type']) || !in_array($entry['type'], array('issue', 'feature')))
		fatal_lang_error('no_type', false);

	// And description.
	if (empty($entry['description']))
		fatal_lang_error('no_description', false);

	// Are we submitting a valid mark? (rare condition)
	if (!in_array($entry['mark'], array('new', 'wip', 'done', 'reject')))
		fatal_lang_error('save_failed');

	// Check if the project exists.
	$result = $smcFunc['db_query']('', '
		SELECT
			id
		FROM {db_prefix}bugtracker_projects
		WHERE id = {int:project}',
		array(
			'project' => $entry['project'],
		)
	);

	// The "real" check ;)
	if ($smcFunc['db_num_rows']($result) == 0 || $smcFunc['db_num_rows']($result) > 1)
		fatal_lang_error('project_no_exist');

	// Preparse the message.
	preparsecode($entry['description']);

	// Okay, lets prepare the entry data itself! Create an array of the available types.
	$fentry = array(
		'title' => $smcFunc['htmlspecialchars']($entry['title']),
		'type' => strtolower($entry['type']),
		'private' => (int) $entry['private'],
		'description' => $entry['description'], // No htmlspecialchars here because it'll fail to parse <br />s correctly!
		'mark' => strtolower($entry['mark']),
		'attention' => (int) $entry['attention'],
		'project' => (int) $entry['project'],
	);

	// Assuming we have everything ready now, lets do this! Insert this stuff first.
	$smcFunc['db_insert']('insert',
		'{db_prefix}bugtracker_entries',
		array(
			'name' => 'string',
			'description' => 'string',
			'type' => 'string',
			'tracker' => 'int',
			'private' => 'int',
			'project' => 'int',
			'status' => 'string',
			'attention' => 'int',
			'progress' => 'int'
		),
		array(
			$fentry['title'],
			$fentry['description'],
			$fentry['type'],
			$context['user']['id'],
			$fentry['private'],
			$fentry['project'],
			$fentry['mark'],
			$fentry['attention'],
			0
		)
	);
			
	// Grab the ID of the entry just inserted.
	$entryid = $smcFunc['db_insert_id']('{db_prefix}bugtracker_entries', 'id');

	// What type is this again?
	$type = $fentry['type'] == 'issue' ? 'issue' : 'feature'; // In case this gets changed later on!
	
	// Then update the count at the projects.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}bugtracker_projects
		SET ' . $type . 'num=' . $type . 'num+1
		WHERE id = {int:project}', 
		array(
			'project' => $fentry['project'],
		)
	);
	
	// Then we're ready to opt-out!
	redirectexit($scripturl . '?action=bugtracker;sa=view;entry=' . $entryid . ';new');
}

function BugTrackerViewProject()
{
	global $context, $smcFunc, $txt, $scripturl, $user_profile;

	// Load the project data.
	$result = $smcFunc['db_query']('', '
		SELECT
			id, name
		FROM {db_prefix}bugtracker_projects
		WHERE id = {int:project}',
		array(
			'project' => (int) $_GET['project'],
		)
	);

	// Got something?
	if ($smcFunc['db_num_rows']($result) == 0 || $smcFunc['db_num_rows']($result) > 1)
		fatal_lang_error('project_no_exist');

	// Fetch it!
	$pdata = $smcFunc['db_fetch_assoc']($result);
	
	// Grab the entries.
	$private = !allowedTo('bugtracker_viewprivate') ? 'AND private="0"' : '';
	$result = $smcFunc['db_query']('', '
		SELECT
			id, name, description, type,
			status, progress, private,
			attention
		FROM {db_prefix}bugtracker_entries
		WHERE project = {int:projectid}
		' . $private . '
		ORDER BY id DESC',
		array(
			'projectid' => $pdata['id'],
		)
	);

	// If we've got none, too bad. Just start dammit.
	$closed = 0;
	$entries = array();
	$attention = array();
	while ($entry = $smcFunc['db_fetch_assoc']($result))
	{
		// Then we're ready for some action.
		$entries[$entry['id']] = array(
			'id' => $entry['id'],
			'name' => $entry['name'],
			'shortdesc' => shorten_subject($entry['description'], 50),
			'type' => $entry['type'],
			'private' => ($entry['private'] == 1 ? true : false),
			'status' => $entry['status'],
			'attention' => $entry['attention'],
               		'progress' => empty($entry['progress']) ? '0%' : $entry['progress'] . '%',
		);

		// Is the status of this entry "attention"? If so, add it to the list of attention requirements thingies!
		if ($entry['attention'])
			$attention[] = $entries[$entry['id']];

		if ($entry['status'] == 'done')
			$closed++;
	}

	// Load the template.
	loadTemplate('BugTrackerProject');
	
	// How many items are closed?
	$context['bugtracker']['num_closed'] = $closed;

	// What do we have, from issues and such?
	$context['bugtracker']['entries'] = $entries;
	$context['bugtracker']['attention'] = $attention;
	$context['bugtracker']['project'] = $pdata;

	// Also stuff the linktree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker;sa=projectindex;project=' . $context['bugtracker']['project']['id'],
		'name' => $context['bugtracker']['project']['name'],
	);
	
	// Page title time!
	$context['page_title'] = $context['bugtracker']['project']['name'];

	// Can we add new entries?
	$context['can_bt_add'] = allowedTo('bt_add');

	// And the sub template.
	$context['sub_template'] = 'TrackerViewProject';
}

function BugTrackerRemoveEntry()
{
	// TODO: Make this work with a trash can.
	global $context, $smcFunc, $scripturl;
	
	if (empty($_GET['entry']))
		fatal_lang_error('entry_no_exist');
	
	// Then try to load the issue data.
	$result = $smcFunc['db_query']('', '
		SELECT 
			id, name, project, tracker, type
		FROM {db_prefix}bugtracker_entries
		WHERE id = {int:entry}',
		array(
			'entry' => (int) $_GET['entry'],
		)
	);

	// None? Or more then one?
	if ($smcFunc['db_num_rows']($result) == 0 || $smcFunc['db_num_rows']($result) > 1)
		fatal_lang_error('entry_no_exist');

	// Fetch the data.
	$data = $smcFunc['db_fetch_assoc']($result);

	// Hmm, okay. Are we allowed to remove this entry?
	if (allowedTo('bt_remove_any') || (allowedTo('bt_remove_own') && $context['user']['id'] == $data['tracker']))
	{
		// Remove it ASAP.
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}bugtracker_entries
			WHERE id = {int:id}',
			array(
				'id' => $data['id'],
			)
		);

		// And count one down from the project.
		$type = $data['type'] == 'issue' ? 'issue' : 'feature';
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}bugtracker_projects
			SET ' . $type . 'num = ' . $type . 'num-1
			WHERE id = {int:pid}',
			array(
				'pid' => $data['project'],
			)
		);
		
		// And redirect back to the project index.
		redirectexit($scripturl . '?action=bugtracker;sa=projectindex;project=' . $data['project']);
	}
	else
		fatal_lang_error('remove_entry_noaccess', false);
}

function BugTrackerViewType()
{
	global $context, $smcFunc, $txt, $scripturl;

	// Start by checking if we are grabbing a valid type!
	$types = array('feature', 'issue');

	if (!in_array($_GET['type'], $types))
		fatal_lang_error('project_no_exist');

	// Okay, then start loading every entry.
	$private = !allowedTo('bugtracker_viewprivate') ? 'AND private="0"' : '';
	$result = $smcFunc['db_query']('', '
		SELECT
			e.id AS entry_id, e.name AS entry_name, e.description, e.type,
			e.tracker, e.private, e.startedon, e.project,
			e.status, e.attention, e.progress,
			p.id, p.name As project_name
		FROM {db_prefix}bugtracker_entries AS e
		INNER JOIN {db_prefix}bugtracker_projects AS p ON (e.project = p.id)
		WHERE e.type = {string:type}
		' . $private . '
		ORDER BY id DESC',
		array(
			'type' => $_GET['type'],
		)
	);

	// Fetch 'em!
	$closed = 0;
	$entries = array();
	$attention = array();
	while ($entry = $smcFunc['db_fetch_assoc']($result))
	{
		// Then we're ready for some action.
		$entries[$entry['entry_id']] = array(
			'id' => $entry['entry_id'],
			'name' => $entry['entry_name'],
			'shortdesc' => shorten_subject($entry['description'], 50),
			'type' => $entry['type'],
			'private' => ($entry['private'] == 1 ? true : false),
			'status' => $entry['status'],
			'attention' => $entry['attention'],
			'project' => array(
				'id' => $entry['id'],
				'name' => $entry['project_name'],
			),
               		'progress' => empty($entry['progress']) ? '0%' : $entry['progress'] . '%',
		);

		// Is the status of this entry "attention"? If so, add it to the list of attention requirements thingies!
		if ($entry['attention'])
			$attention[] = $entries[$entry['entry_id']];

		if ($entry['status'] == 'done')
			$closed++;
	}

	// So matey tell me what ya got. Wait no, tell $context!
	$context['bugtracker']['entries'] = $entries;
	$context['bugtracker']['attention'] = $attention;
	$context['bugtracker']['num_closed'] = $closed;
	$context['bugtracker']['viewtype_type'] = $_GET['type'];

	// Set up the linktree.
	$context['linktree'][] = array(
		'name' => sprintf($txt['view_all'], $_GET['type']),
		'url' => $scripturl . '?action=bugtracker;sa=viewtype;type=' . $_GET['type'],
	);

	// And the sub-template.
	$context['sub_template'] = 'TrackerViewType';
	
}

function BugTrackerViewStatus()
{
	global $context, $smcFunc, $txt, $scripturl;

	// Start by checking if we are grabbing a valid type!
	$types = array('new', 'wip', 'done', 'reject');

	if (!in_array($_GET['status'], $types))
		fatal_lang_error('project_no_exist');

	// Okay, then start loading every entry.
	$private = !allowedTo('bugtracker_viewprivate') ? 'AND private="0"' : '';
	$result = $smcFunc['db_query']('', '
		SELECT
			e.id AS entry_id, e.name AS entry_name, e.description, e.type,
			e.tracker, e.private, e.startedon, e.project,
			e.status, e.attention, e.progress,
			p.id, p.name As project_name
		FROM {db_prefix}bugtracker_entries AS e
		INNER JOIN {db_prefix}bugtracker_projects AS p ON (e.project = p.id)
		WHERE e.status = {string:status}
		' . $private . '
		ORDER BY id DESC',
		array(
			'status' => $_GET['status'],
		)
	);

	// Fetch 'em!
	$closed = 0;
	$entries = array();
	$attention = array();
	while ($entry = $smcFunc['db_fetch_assoc']($result))
	{
		// Then we're ready for some action.
		$entries[$entry['entry_id']] = array(
			'id' => $entry['entry_id'],
			'name' => $entry['entry_name'],
			'shortdesc' => shorten_subject($entry['description'], 50),
			'type' => $entry['type'],
			'private' => ($entry['private'] == 1 ? true : false),
			'status' => $entry['status'],
			'attention' => $entry['attention'],
			'project' => array(
				'id' => $entry['id'],
				'name' => $entry['project_name'],
			),
               		'progress' => empty($entry['progress']) ? '0%' : $entry['progress'] . '%',
		);

		// Is the status of this entry "attention"? If so, add it to the list of attention requirements thingies!
		if ($entry['attention'])
			$attention[] = $entries[$entry['entry_id']];

		if ($entry['status'] == 'done')
			$closed++;
	}

	// So matey tell me what ya got. Wait no, tell $context!
	$context['bugtracker']['entries'] = $entries;
	$context['bugtracker']['attention'] = $attention;
	$context['bugtracker']['num_closed'] = $closed;

	// Set up the linktree.
	$context['linktree'][] = array(
		'name' => sprintf($txt['view_all'], $txt['status_' . $_GET['status']]),
		'url' => $scripturl . '?action=bugtracker;sa=viewtype;type=' . $_GET['status'],
	);

	// And the sub-template.
	$context['sub_template'] = 'TrackerViewType';
	
}


?>