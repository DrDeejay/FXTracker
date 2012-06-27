<?php

/* FXTracker Database Framework */

if (!defined('SMF'))
	die('This framework requires SMF to be loaded.');

class fxdb
{
	// Grab all entries, even if we have none.
	public function grabEntries($sort = 'desc')
	{
		// Need the db functions?
		global $smcFunc, $user_profile, $context;

		// Grab 'em all.
		$plusPrivate = !allowedTo('bugtracker_viewprivate') ? 'WHERE private="0"' : '';
		$result = $smcFunc['db_query']('', '
			SELECT id, name, description, type, tracker, private, startedon, project, status, attention, progress
			FROM {db_prefix}bugtracker_entries
			' . $plusprivate . '
			ORDER BY id ' . $sort);

		// Make a nice list of them.
		$data['entries'] = array();
		$data['issue'] = array();
		$data['feature'] = array();
		$data['attention'] = array();
		while ($entry = $smcFunc['db_fetch_assoc']($result))
		{
			// Load the member data for this tracker.
			loadMemberData($entry['tracker']);

			// Then we're ready for some action.
			$data['entries'][$entry['id']] = array(
				'id' => $entry['id'],
				'name' => htmlspecialchars($entry['name']),
				'shortdesc' => shorten_subject($entry['description'], 50),
				'desc' => $entry['description'],
				'type' => $entry['type'],
				'tracker' => $user_profile[$entry['tracker']],
				'private' => ($entry['private'] == 1 ? true : false),
				'started' => $entry['startedon'],
				'project' => $entry['project'],
				'status' => $entry['status'],
				'attention' => $entry['attention'],
				'progress' => empty($entry['progress']) ? '0%' : $entry['progress'] . '%',
			);

			// Assuming the project exists, add this into the list of entries from that project.
			if (isset($context['bugtracker']['projects'][$entry['project']]))
				$context['bugtracker']['projects'][$entry['project']]['entries'][] = $entry['id'];

			// Also create a list of issues and features!
			$data[$entry['type']][] = $data['entries'][$entry['id']];

			// Is the status of this entry "attention"? If so, add it to the list of attention requirements thingies!
			if ($entry['attention'])
				$data['attention'][] = $data['entries'][$entry['id']];
		}
	
		// Clean up.
		$smcFunc['db_free_result']($entry);

		// And return the data.
		return $data;
	}
	// Okay this is just a simple set of functions which interact with the FXTracker database tables.
	public function grabEntry($id)
	{
		// We require SMF's DB functions.
		global $smcFunc;

		// Execute the query.
		$result = $smcFunc['db_query']('', '
			SELECT id, name, description, type, tracker, private, startedon, project, status, attention, progress
			FROM {db_prefix}bugtracker_entries
			WHERE id={int:id}',
			array(
				'id' => $id,
			));

		// Got anything? No?
		if ($smcFunc['db_num_rows']($result) == 0)
		{
			// Clean the result.
			$smcFunc['db_free_result']($result);
			return false;
		}
		
		// Yeah, we do, so fetch it.
		$data = $smcFunc['db_fetch_assoc']($result);

		// Free the result. Fly as a bird!
		$smcFunc['db_free_result']($result);

		return $data;
	}

	// Grab all projects.
	public function grabProjects($sort = 'desc')
	{
		// smcFunc is needed here!
		global $smcFunc;

		$projects = $smcFunc['db_query']('', '
			SELECT id, name, description, issuenum, featurenum, lastnum
			FROM {db_prefix}bugtracker_projects');

		// Make a nice list of them projects.
		$data = array();
		while ($project = $smcFunc['db_fetch_assoc']($projects))
		{
			// Add it into the list.
			$data[$project['id']] = array(
				'id' => $project['id'],
				'name' => htmlspecialchars($project['name']),
				'desc' => $project['description'],
				'num' => array(
					'issues' => $project['issuenum'],
					'features' => $project['featurenum'],
				),
				'last' => $project['lastnum'],
				'entries' => array(),
			);
		}

		// Clean out the result data.
		$smcFunc['db_free_result']($projects);

		// And return the actual data!
		return $data;
	}

	// Grab a project.
	public function grabProject($id)
	{
		global $smcFunc;

		// Load the project data.
		$project = $smcFunc['db_query']('', '
			SELECT id, name, description, issuenum, featurenum, lastnum
			FROM {db_prefix}bugtracker_projects
			WHERE id={int:id}',
			array(
				'id' => $_GET['id'],
			));

		// Got anything? No? That sucks.
		if ($smcFunc['db_num_rows']($project) == 0)
		{
			// Clean the result.
			$smcFunc['db_free_result']($project);
			return false;
		}

		// And fetch the data.
		$data = $smcFunc['db_fetch_assoc']($project);

		// Clean this stuff up too, mate. Yep, talking to you.
		$smcFunc['db_free_result']($project);

		// And return the data.
		return $data;
	}

	// Grab all issues from a project. Requires a $context variable to insert stuff to.
	public function grabProjectEntries($projid, &$context)
	{
		global $smcFunc;

		// Just load all the issues assigned with this project. Displaying will happen later on.
		$plusPrivate = !allowedTo('bugtracker_viewprivate') ? 'AND private="0"' : '';
		$entries = $smcFunc['db_query']('', '
			SELECT id, name, description, type, tracker, private, startedon, project, status, attention, progress
			FROM {db_prefix}bugtracker_entries
			WHERE project={int:project}
			' . $plusprivate . '
			ORDER BY id DESC',
			array(
				'project' => $_GET['id'],
			));

		// Fetch all the data from here.
		$context['entries'] = array();
		$context['attention'] = array();
		while ($entry = $smcFunc['db_fetch_assoc']($entries))
		{
			// Load the member data for those trackers.
			loadMemberData($entry['tracker']);

			// Then we're ready for some action.
			$context['entries'][$entry['id']] = array(
				'id' => $entry['id'],
				'name' => htmlspecialchars($entry['name']),
				'shortdesc' => shorten_subject($entry['description'], 50),
				'desc' => $entry['description'],
				'type' => $entry['type'],
				'tracker' => $user_profile[$entry['tracker']],
				'private' => ($entry['private'] == 1 ? true : false),
				'started' => $entry['startedon'],
				'project' => $entry['project'],
				'status' => $entry['status'],
				'attention' => $entry['attention'],
                'progress' => empty($entry['progress']) ? '0%' : $entry['progress'] . '%',
			);

			// Is the status of this entry "attention"? If so, add it to the list of attention requirements thingies!
			if ($entry['attention'])
				$context['attention'][] = $context['entries'][$entry['id']];
		}

		// Free the result.
		$smcFunc['db_free_result']($entries);

		// Return the data.
		return $true;
	}
	
	// Recalculate things like how many features a project has.
	public function recalculateStats()
	{
		// Global our database function. Only thing we need now.
		global $smcFunc;
		
		// Start with calculating and sorting the amount of entries.
		$entries = fxdb::grabEntries();
		
		// Then grab the list of projects.
		$projects = fxdb::grabProjects();
		
		// Empty out the count of each project.
		foreach ($projects as $project)
		{
			$projects[$project['id']]['num'] = array(
				'issues' => 0,
				'features' => 0,
			);
		}
		
		// Calculate how many issues each project has.
		foreach ($entries['issue'] as $entry)
		{
			// Check if this project exists.
			if (!isset($projects[$entry['project']]))
				continue;
				
			// Then add one up.
			$projects[$entry['project']]['num']['issues']++;
		}
		
		// Do the same for features.
		foreach ($entries['feature'] as $entry)
		{
			// Does this project exist.
			if (!isset($projects[$entry['project']]))
				continue;
				
			// Then add one up again.
			$projects[$entry['project']]['num']['features']++;
		}
		
		// Now reinsert the data.
		foreach ($projects as $project)
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}bugtracker_projects
				SET featurenum={int:features}, issuenum={int:issues}
				WHERE id={int:id}',
				array(
					'features' => $project['num']['features'],
					'issues' => $project['num']['issues'],
					'id' => $project['id'],
				));
		}
		
		// Should of have succeed if we came this far.
		return true;
	}
}
