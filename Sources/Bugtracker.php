<?php

/* FXTracker Main File
 * Initializes FXTracker and the main functions.
 */

function BugTrackerMain()
{
	// Our usual stuff.
	global $context, $txt, $sourcedir;

	// Are we allowed to view this?
	isAllowedTo('bugtracker_view');
	
	// Load the language and template.
	loadLanguage('BugTracker');
	loadTemplate('BugTracker');

	// Include our database class.
	require($sourcedir . '/FXTracker/Class-Database.php');

	// A list of all actions we can take.
	// 'action' => 'bug tracker function',
	$sactions = array(
		'credits' => 'Credits',
		'edit' => 'Edit',
		'home' => 'Home',
		'mark' => 'MarkEntry',
		'new' => 'NewEntry',
		'projindex' => 'ViewProject',
		'remove' => 'RemoveEntry',
		'submit' => 'SubmitData',
		'view' => 'View',
	);

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
	$context['bugtracker']['projects'] = fxdb::grabProjects();

	// Grab the entries we are allowed to view.
	$data = fxdb::grabEntries();

	// Grab a list of them. This is cluttered.
	$context['bugtracker']['entries'] = $data['entries'];
	$context['bugtracker']['issue'] = $data['issue'];
	$context['bugtracker']['feature'] = $data['feature'];
	$context['bugtracker']['attention'] = $data['attention'];

	// Put the last 5 entries of each category in a new array.
	$context['bugtracker']['latest']['issues'] = array_slice($context['bugtracker']['issue'], -5);
	$context['bugtracker']['latest']['features'] = array_slice($context['bugtracker']['feature'], -5);

	// What's our template, doc?
	$context['sub_template'] = 'TrackerHome';
}

function BugTrackerView()
{
	// Our usual variables.
	global $context, $smcFunc, $user_info, $user_profile, $txt;

	// Grab the info for this issue, and if we can't, tell the user that the issue does not exist.
	$data = fxdb::grabEntry($_GET['id']);

	// Not valid?
	if (!$data)
		fatal_lang_error('entry_no_exist');

	// Are we allowed to view private issues, and is this one of them?
	if (!allowedTo('bugtracker_viewprivate') && $data['private'] == 1)
		fatal_lang_error('entry_is_private');

	// No? Set the title.
	$context['page_title'] = sprintf($txt['view_title'], $data['id']);

	// Load the data for the tracker.
	loadMemberData($data['tracker']);

	// Put the data in $context for the template!
	$context['bugtracker']['entry'] = array(
		'id' => $data['id'],
		'name' => htmlspecialchars($data['name']),
		'desc' => parse_bbc($data['description']),
		'type' => $data['type'],
		'tracker' => $user_profile[$data['tracker']],
		'private' => ($data['private'] == 1 ? true : false),
		'started' => $data['startedon'],
		'project' => $data['project'],
		'status' => $data['status'],
		'attention' => $data['attention'],
		'progress' => (empty($data['progress']) ? '0' : $data['progress']) . '%',
	);

	// Also load the data for the project.
	$projres = $smcFunc['db_query']('', '
		SELECT id, name
		FROM {db_prefix}bugtracker_projects
		WHERE id={int:project}',
		array(
			'project' => $data['project'],
		));

	// Parse it.
	$project = $smcFunc['db_fetch_assoc']($projres);
	
	// Clean stuff up again.
	$smcFunc['db_free_result']($projres);
		
	// Load it.
	$context['bugtracker']['project'] = array(
		'id' => $project['id'],
		'name' => htmlspecialchars($project['name']),
	);

	// Stuff the linktree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker;sa=projindex;id=' . $project['id'],
		'name' => htmlspecialchars($project['name']),
	);
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker;sa=view;id=' . $data['id'],
		'name' => sprintf($txt['entrytitle'], $data['id'], $data['name']),
	);

	// Then tell SMF what template to load.
	$context['sub_template'] = 'TrackerView';
}

function BugTrackerMarkEntry()
{
	// Globalizing...
	global $context, $scripturl, $smcFunc;

	// Load data associated with this entry, if it exists.
	$entry = $smcFunc['db_query']('', '
		SELECT id, status, tracker, attention
		FROM {db_prefix}bugtracker_entries
		WHERE id="{int:id}"',
		array(
			'id' => $_GET['id'],
		)
	);

	// Do we have an entry? :D
	if ($smcFunc['db_num_rows']($entry) == 0)
		fatal_lang_error('entry_no_exist');

	// Fetch the data.
	$data = $smcFunc['db_fetch_assoc']($entry);

	// Clean up after your ***.
	$smcFunc['db_free_result']($entry);

	// Then, are we allowed to do this kind of stuff?
	if (allowedTo('bt_mark_any') || (allowedTo('bt_mark_own') && $context['user']['id'] == $data['tracker']))
	{
		// A list of possible types.
		$types = array('new', 'wip', 'done', 'reject', 'attention');
		
		// Not in the list?
		if (!in_array($_GET['as'], $types))
			fatal_lang_error('entry_mark_failed');

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

			redirectexit($scripturl . '?action=bugtracker;sa=view;id=' . $data['id']);
		}

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
		redirectexit($scripturl . '?action=bugtracker;sa=view;id=' . $data['id']);
	}
	else
		fatal_lang_error('entry_unable_mark');
}

function BugTrackerEdit()
{
	global $boardurl, $context, $smcFunc, $user_profile, $scripturl, $txt;

	// Try to load the current entry.
	$entry = $smcFunc['db_query']('', '
		SELECT id, name, description, type, tracker, private, startedon, project, status, attention, progress
		FROM {db_prefix}bugtracker_entries
		WHERE id={int:id}',
		array(
			'id' => $_GET['id'],
		));

	// Do we have anything?
	if ($smcFunc['db_num_rows']($entry) == 0)
		fatal_lang_error('entry_no_exist');

	// Grab the data.
	$data = $smcFunc['db_fetch_assoc']($entry);

	// So are we allowed to edit this?
	if (allowedTo('bt_edit_any') || (allowedTo('bt_edit_own') && $data['tracker'] == $context['user']['id']))
	{
		// Include SCEditor.
		$context['html_headers'] .= '
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<link rel="stylesheet" href="' . $boardurl . '/Sources/FXTracker/SCEditor/jquery.sceditor.min.css" type="text/css" media="all" />
		<script type="text/javascript" src="' . $boardurl . '/Sources/FXTracker/SCEditor/jquery.sceditor.min.js"></script>
		<script>
		$(document).ready(function() {
			$("textarea").sceditorBBCodePlugin({
				style: "' . $boardurl . '/Sources/FXTracker/SCEditor/jquery.sceditor.min.css",
				emoticonsRoot: "' . $boardurl . '/Sources/FXTracker/SCEditor/",
			});
		});
		</script>';

		// Put the data in $context!
		$context['bugtracker']['entry'] = array(
			'id' => $data['id'],
			'name' => htmlspecialchars($data['name']),
			'desc' => $data['description'],
			'type' => $data['type'],
			'tracker' => $user_profile[$data['tracker']],
			'private' => ($data['private'] == 1 ? true : false),
			'started' => $data['startedon'],
			'project' => $data['project'],
			'status' => $data['status'],
			'attention' => $data['attention'],
			'progress' => empty($data['progress']) ? '0' : $data['progress'],
		);

		// Set up the customizable options.
		$context['bugtracker']['edit']['title'] = $txt['entry_edit'];
		$context['bugtracker']['edit']['formlink'] = $scripturl . '?action=bugtracker;sa=submit;type=edit;id=' . $data['id'];
		$context['bugtracker']['edit']['method'] = 'post';
		$context['bugtracker']['edit']['type'] = 'edit';

		// Set up the linktree.
		$context['linktree'][] = array(
			'url' => '#',
			'name' => $txt['entry_edit']
		);

		// And set the template.
		$context['sub_template'] = 'TrackerEdit';
	}
}

function BugTrackerNewEntry()
{
	global $boardurl, $context, $smcFunc, $user_profile, $scripturl, $txt;

	// Try to load the current project
	$entry = $smcFunc['db_query']('', '
		SELECT id, name, description, issuenum, featurenum, lastnum
		FROM {db_prefix}bugtracker_projects
		WHERE id={int:id}',
		array(
			'id' => $_GET['proj'],
		));

	// Do we have anything?
	if ($smcFunc['db_num_rows']($entry) == 0)
		fatal_lang_error('entry_no_exist');

	// Grab the data.
	$data = $smcFunc['db_fetch_assoc']($entry);

	// So are we allowed to add stuff?
	if (allowedTo('bt_add'))
	{
		// Include SCEditor.
		$context['html_headers'] .= '
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<link rel="stylesheet" href="' . $boardurl . '/Sources/FXTracker/SCEditor/jquery.sceditor.min.css" type="text/css" media="all" />
		<script type="text/javascript" src="' . $boardurl . '/Sources/FXTracker/SCEditor/jquery.sceditor.min.js"></script>
		<script>
		$(document).ready(function() {
			$("textarea").sceditorBBCodePlugin({
				style: "' . $boardurl . '/Sources/FXTracker/SCEditor/jquery.sceditor.min.css",
				emoticonsRoot: "' . $boardurl . '/Sources/FXTracker/SCEditor/",
			});
		});
		</script>';

		// Some dummy data.
		$context['bugtracker']['entry'] = array(
			'id' => $data['id'],
			'name' => '',
			'desc' => '',
			'type' => 'issue',
			'tracker' => '',
			'private' => false,
			'started' => '',
			'project' => '',
			'status' => '',
			'attention' => '',
			'progress' => '0',
		);

		// Set up the customizable options.
		$context['bugtracker']['edit']['title'] = $txt['entry_add'];
		$context['bugtracker']['edit']['formlink'] = $scripturl . '?action=bugtracker;sa=submit;type=new;id=' . $data['id'];
		$context['bugtracker']['edit']['method'] = 'post';
		$context['bugtracker']['edit']['type'] = 'new';

		// Set up the linktree.
		$context['linktree'][] = array(
			'url' => '#',
			'name' => $txt['entry_add']
		);

		// And set the template.
		$context['sub_template'] = 'TrackerEdit';
	}
}

function BugTrackerViewProject()
{
	global $context, $smcFunc, $txt;

	// Load the project data.
	$context['bugtracker']['project'] = fxdb::grabProject($_GET['id']) or fatal_lang_error('no_such_project');
	
	// Grab the entries.
	fxdb::grabProjectEntries($context['bugtracker']['project']['id'], $context['bugtracker']);

	// Load the template.
	loadTemplate('BugTrackerProject');

	// Also stuff the linktree.
	$context['linktree'][] = array(
		'url' => $scripturl . '?action=bugtracker;sa=projindex;id=' . $context['bugtracker']['project']['id'],
		'name' => $context['bugtracker']['project']['name'],
	);

	//die(var_Dump($context['bugtracker']['project']));
	
	// Page title time!
	$context['page_title'] = $context['bugtracker']['project']['name'];

	// And the sub template.
	$context['sub_template'] = 'TrackerViewProject';
}

function BugTrackerSubmitData()
{
	global $context, $smcFunc, $scripturl;

	// Okay, which type of data do we have to deal with?
	if (empty($_POST['type_save']) || !is_string($_POST['type_save']) || empty($_POST['entry_id']) || !is_numeric($_POST['entry_id']) || empty($_POST['entry_title']) || !is_string($_POST['entry_title']) || empty($_POST['entry_type']) || !is_string($_POST['entry_type']) || empty($_POST['entry_desc']) || !is_string($_POST['entry_desc']))
		fatal_lang_error('save_failed');

	// Switching time.
	switch ($_POST['type_save'])
	{
		case 'edit':
			$title = $smcFunc['db_escape_string']($_POST['entry_title']);
			$type = $smcFunc['db_escape_string']($_POST['entry_type']);
			$desc = $smcFunc['db_escape_string']($_POST['entry_desc']);
			$progress = is_numeric($_POST['entry_progress']) && $_POST['entry_progress'] <= 100 ? $_POST['entry_progress'] : 0;

			// We should be ready to update it then.
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_entries
				SET name={string:title}, type={string:type}, description={string:desc}, progress={int:progress}
				WHERE id={int:id}',
				array(
					'title' => $title,
					'type' => $type,
					'desc' => $desc,
					'id' => $_POST['entry_id'],
					'progress' => $progress,
				));
				
			// Is the progress 100%?
			if ($progress == 100)
			{
				// Mark this as solved!
				$smcFunc['db_query']('', '
					UPDATE {db_prefix}bugtracker_entries
					SET status={string:status}
					WHERE id={int:id}',
					array(
						'id' => $_POST['entry_id'],
						'status' => 'done',
					));
			}
			
			// Also recalculate the stats.
			fxdb::recalculateStats();

			// And redirect.
			redirectexit($scripturl . '?action=bugtracker;sa=view;id=' . $_POST['entry_id']);

			break;
		case 'new':
			// Okay. Filter them.
			$title = $smcFunc['db_escape_string']($_POST['entry_title']);
			$type = $smcFunc['db_escape_string']($_POST['entry_type']);
			$desc = $smcFunc['db_escape_string']($_POST['entry_desc']);
			$progress = is_numeric($_POST['entry_progress']) && $_POST['entry_progress'] <= 100 ? $_POST['entry_progress'] : 0;

			// Then we should be able to add them in.
			$smcFunc['db_query']('', '
				INSERT INTO {db_prefix}bugtracker_entries (name, description, type, tracker, private, startedon, project, status, attention)
				VALUES ({string:name}, {string:desc}, {string:type}, {int:uid}, {int:private}, CURRENT_TIMESTAMP, {int:id}, {string:status}, 0)',
				array(
					'name' => $title,
					'desc' => $desc,
					'type' => $type,
					'uid' => $context['user']['id'],
					'private' => isset($_POST['entry_private']) ? 1 : 0,
					'id' => $_POST['entry_id'],
					'status' => $progress == 100 ? 'done' : 'new',
					'progress' => $progress
				));
				
			// Also recalculate the stats.
			fxdb::recalculateStats();

			// Redirect back to the project index.
			redirectexit($scripturl . '?action=bugtracker;sa=projindex;id=' . $_POST['entry_id'] . ($progress == 100 ? ';viewsolved' : ''));
			
			break;
		default:
			fatal_lang_error('save_failed');
			break;
	}
}

function BugTrackerRemoveEntry()
{
	// Checks first please!
	isAllowedTo('bt_remove_entry');
	
	// Then
}

?>
