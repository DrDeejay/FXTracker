<?php

/* FXTracker Templates */

function template_TrackerHome()
{
	// Global $context and other stuff.
	global $context, $txt, $scripturl, $settings;

	// Our latest issues and features.
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['images_url'], '/bugtracker/latest.png" class="icon" alt="" />', $txt['bugtracker_latest'], '
		</h3>
	</div>';

	// These are the latest xxx headers. Title bars, to be exact.
	echo '
	<div class="floatleft" style="width:49.9%">
		<div class="title_bar">
			<h3 class="titlebg">
				', $txt['bugtracker_latest_issues'], '
			</h3>
		</div>
	</div>
	<div class="floatright" style="width:49.9%">
		<div class="title_bar">
			<h3 class="titlebg">
				', $txt['bugtracker_latest_features'], '
			</h3>
		</div>
	</div>
	<br class="clear" />';

	// Now for the Latest xxx boxes
	echo '
	<div class="floatleft" style="width:49.9%">
		<div class="', !empty($context['bugtracker']['latest']['issues']) ? 'plainbox' : 'information', '">';

	// Load the list of entries from the latest issues, and display them in a list.
	if (!empty($context['bugtracker']['latest']['issues']))
	{
		// Instead of doing this ourselves, lets have <ol> do the numbering for us.
		echo '
			<ol style="margin:0;padding:0;padding-left:15px">';

		foreach ($context['bugtracker']['latest']['issues'] as $entry)
		{
			echo '
				<li>
					', !empty($entry['project']) ? '[<a href="' . $scripturl . '?action=bugtracker;sa=projectindex;project=' . $entry['project']['id'] . '">
						' . $entry['project']['name'] . '
					</a>] ' : '', '
					#', $entry['id'], ': <a href="', $scripturl, '?action=bugtracker;sa=view;entry=', $entry['id'], '">
						', $entry['name'], '
					</a>
				</li>';
		}
		
		echo '
			</ol>';
	}
	else
		echo $txt['bugtracker_no_latest_entries'];
		
	echo '
		</div>
	</div>
	<div class="floatright" style="width: 49.9%">
		<div class="', !empty($context['bugtracker']['latest']['features']) ? 'plainbox' : 'information', '">';

	// Load the list of entries from the latest features. Make a nice list of 'em!
	if (!empty($context['bugtracker']['latest']['features']))
	{
		// Again have <ol> do the work for us. That'll work better.
		echo '
			<ol style="margin:0;padding:0;padding-left:15px">';

		foreach ($context['bugtracker']['latest']['features'] as $entry)
		{
			echo '
				<li>
					', !empty($entry['project']) ? '[<a href="' . $scripturl . '?action=bugtracker;sa=projectindex;project=' . $entry['project']['id'] . '">
						' . $entry['project']['name'] . '
					</a>] ' : '', '
					#', $entry['id'], ': <a href="', $scripturl, '?action=bugtracker;sa=view;entry=', $entry['id'], '">
						', $entry['name'], '
					</a>
				</li>';
		}

		echo '
			</ol>';
	}
	else
		echo $txt['bugtracker_no_latest_entries'];

	echo '
		</div>
	</div>
	<br class="clear" />

	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['images_url'], '/bugtracker/projects.png" class="icon" alt="" />', $txt['bugtracker_projects'], '
		</h3>
	</div>';

	// Show the project list.
	$windowbg = 0;
	foreach ($context['bugtracker']['projects'] as $id => $project)
	{
		echo '
	<div class="windowbg', $windowbg == 0 ? '' : '2', '">
		<span class="topslice"><span></span></span>
		<div class="info" style="margin-left: 10px">
			<a class="subject" href="', $scripturl, '?action=bugtracker;sa=projectindex;project=', $id, '">
				', $project['name'], '
			</a> - ', sprintf($txt['issues'], $project['num']['issues']), ', ', sprintf($txt['features'], $project['num']['features']), '<br />
			', $project['description'], '
		</div>
		<span class="botslice"><span></span></span>
	</div>';
		
		$windowbg = ($windowbg == 0 ? 1 : 0);
	}

	echo '<br />
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['images_url'], '/bugtracker/attention.png" class="icon" alt="" />', sprintf($txt['items_attention'], count($context['bugtracker']['attention'])), '
		</h3>
	</div>';

	// Show the items requiring attention.
	if (count($context['bugtracker']['attention']) != 0)
	{
		// Headers.
		echo '
	<div class="tborder topic_table">
		<table class="table_grid" cellspacing="0" style="width: 100%">
			<thead>
				<tr class="catbg">
					<th scope="col" class="first_th" width="8%" colspan="2">&nbsp;</th>
					<th scope="col">
						', $txt['subject'], '
					</th>
					<th scope="col" width="18%">
						', $txt['status'], '
					</th>
					<th scope="col" width="18%">
						', $txt['type'], '
					</th>
					<th scope="col" width="16%" class="last_th">
						', $txt['project'], '
					</th>
				</tr>
			</thead>
			<tbody>';
		
		// And the content.
		foreach ($context['bugtracker']['attention'] as $entry)
		{
			echo '
				<tr>
					<td class="icon1 windowbg">
						<img src="', $settings['images_url'], '/bugtracker/', $entry['type'], '.png" alt="" />
					</td>
					<td class="icon2 windowbg">
						<img src="' . $settings['images_url'] . '/bugtracker/', $entry['status'] == 'wip' ? 'wip.gif' : $entry['status'] . '.png', '" alt="" />
					</td>
					<td class="subject windowbg2">
						<div>
							<span>
								<a href="', $scripturl, '?action=bugtracker;sa=view;entry=', $entry['id'], '">
									', $entry['name'], ' ', $entry['status'] == 'wip' ? '<span class="smalltext" style="color:#E00000">(' . $entry['progress'] . ')</span>' : '', '
								</a>
							</span>
							<p>', $entry['shortdesc'], '</p>
						</div>
					</td>
					<td class="stats windowbg">
						', $txt['status_' . $entry['status']], '
					</td>
					<td class="stats windowbg2">
						<a href="', $scripturl, '?action=bugtracker;sa=viewtype;type=', $entry['type'], '">', $txt['bugtracker_' . $entry['type']], '</a>
					</td>
					<td class="stats windowbg">
						', !empty($entry['project']) ? '<a href="' . $scripturl . '?action=bugtracker;sa=viewproject;project=' . $entry['project']['id'] . '">' . $entry['project']['name'] . '</a>' : $txt['na'], '
					</td>
				</tr>';
		}
		echo '
			</tbody>
		</table>
	</div><br />';
	}

	// The info centre? TODO
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['images_url'], '/bugtracker/infocenter.png" alt="" class="icon" /> ', $txt['info_centre'], '
		</h3>
	</div>
	<div class="plainbox">
		<strong>', $txt['total_entries'], '</strong> ', count($context['bugtracker']['entries']), '<br />
		<strong>', $txt['total_projects'], '</strong> ', count($context['bugtracker']['projects']), '<br />
		<strong>', $txt['total_issues'], '</strong> ', count($context['bugtracker']['issue']), ' (<a href="', $scripturl, '?action=bugtracker;sa=viewtype;type=issue">', $txt['view_all_lc'], '</a>)<br />
		<strong>', $txt['total_features'], '</strong> ', count($context['bugtracker']['feature']), ' (<a href="', $scripturl, '?action=bugtracker;sa=viewtype;type=feature">', $txt['view_all_lc'], '</a>)<br />
		<strong>', $txt['total_attention'], '</strong> ', count($context['bugtracker']['attention']), '
	</div>';
	
	// And our last batch of HTML.
	echo '
	<span class="centertext"><a href="', $scripturl, '?action=bugtracker;sa=admin">', $txt['bt_acp'], '</a></span>
	<br class="clear" />';
}

function template_TrackerView()
{
	global $context, $txt, $scripturl, $settings;

	// Is this new?
	if ($context['bugtracker']['entry']['is_new'])
		echo '
	<div class="information"><strong>', $txt['entry_added'], '</strong></div>';

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img class="icon" src="', $settings['images_url'], '/bugtracker/', $context['bugtracker']['entry']['type'], '.png" alt="" />
			', sprintf($txt['entrytitle'], $context['bugtracker']['entry']['id'], $context['bugtracker']['entry']['name']), '
		</h3>
	</div>
	<div class="buttonlist floatleft">
		<ul>
			<li>
				<a href="#comments">
					<span>', $txt['go_comments'], '</span>
				</a>
			</li>
		</ul>
	</div>
	<div class="buttonlist floatright">
		<ul>';

	// Are we allowed to reply to this entry?
	if ($context['can_bt_reply_any'] || $context['can_bt_reply_own'])
		echo '
			<li>
				<a class="active" href="', $scripturl, '?action=bugtracker;sa=reply;entry=', $context['bugtracker']['entry']['id'], '"><span>', $txt['reply'], '</span></a>
			</li>';
	
	// Are we allowed to edit this entry?
	if ($context['can_bt_edit_any'] || $context['can_bt_edit_own'])
		echo '
			<li>
				<a href="', $scripturl, '?action=bugtracker;sa=edit;entry=', $context['bugtracker']['entry']['id'], '"><span>', $txt['editentry'], '</span></a>
			</li>';

	// Or allowed to remove it?
	if ($context['can_bt_remove_any'] || $context['can_bt_remove_own'])
		echo '
			<li>
				<a onclick="return confirm(', javascriptescape($txt['really_delete']), ')" href="', $scripturl, '?action=bugtracker;sa=remove;entry=', $context['bugtracker']['entry']['id'], '"><span>', $txt['removeentry'], '</span></a>
			</li>';

	echo '
		</ul>
	</div>
	<table style="width: 100%">
		<tr>
			<td style="width: 5%">
				<div class="plainbox" style="text-align:right">
					<strong>', $txt['title'], ':</strong><br />
					<strong>', $txt['type'], ':</strong><br />
					<strong>', $txt['tracker'], ':</strong><br />
					<strong>', $txt['status'], ':</strong><br />
					<strong>', $txt['project'], ':</strong><br />
					', $context['bugtracker']['entry']['status'] == 'wip' ? '<strong>' . $txt['progress'] . '</strong><br />' : '', '
				</div>
			</td>
			<td style="width: 95%">
				<div class="plainbox">
					', $context['bugtracker']['entry']['name'], '<br />
					', $txt['bugtracker_' . $context['bugtracker']['entry']['type']], '<br />
					<a style="color:', $context['bugtracker']['entry']['tracker']['member_group_color'], '" href="', $scripturl, '?action=profile;u=', $context['bugtracker']['entry']['tracker']['id_member'], '">', $context['bugtracker']['entry']['tracker']['member_name'], '</a> (', $context['bugtracker']['entry']['tracker']['member_group'], ')<br />
					', $txt['status_' . $context['bugtracker']['entry']['status']] . ($context['bugtracker']['entry']['attention'] ? ' <strong>(' . $txt['status_attention'] . ')</strong>' : ''), '<br />
					<a href="', $scripturl, '?action=bugtracker;sa=projectindex;project=', $context['bugtracker']['entry']['project']['id'], '">', $context['bugtracker']['entry']['project']['name'], '</a><br />
					', $context['bugtracker']['entry']['status'] == 'wip' ? $context['bugtracker']['entry']['progress'] . '<br />' : '', '
				</div>
			</td>
		</tr>
	</table>
	<div class="title_bar">
		<h3 class="titlebg">
			<img class="icon" src="', $settings['images_url'], '/bugtracker/description.png" alt="" />
			', $txt['description'], '
		</h3>
	</div>
	<div class="windowbg2">		
		<span class="topslice"><span></span></span>
		<div style="margin-left: 10px">
			', $context['bugtracker']['entry']['desc'], '
		</div>
		<span class="botslice"><span></span></span>
	</div>';

	// Allowed to mark?
	if ($context['bt_can_mark'])
	{
		echo '

	<div class="buttonlist floatright">
		<ul>';
		// Mark as unassigned/new?
		if ($context['can_bt_mark_new_any'] || $context['can_bt_mark_new_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'new' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=new;entry=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_new'], '</span>
				</a>
			</li>';
		// Or as Work In Progress?
		if ($context['can_bt_mark_wip_any'] || $context['can_bt_mark_wip_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'wip' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=wip;entry=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_wip'], '</span>
				</a>
			</li>';
		// Mark as Resolved?
		if ($context['can_bt_mark_done_any'] || $context['can_bt_mark_done_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'done' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=done;entry=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_done'], '</span>
				</a>
			</li>';
		// Then as Rejected?
		if ($context['can_bt_mark_reject_any'] || $context['can_bt_mark_reject_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'reject' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=reject;entry=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_reject'], '</span>
				</a>
			</li>';
		echo '
		</ul>
	</div>';
	}

	// If we want it to be urgent, mark it as requiring attention!
	if ($context['can_bt_mark_attention_any'] || $context['can_bt_mark_attention_own'])
		echo '
	<div class="buttonlist floatleft">
		<ul>
			<li>
				<a ', $context['bugtracker']['entry']['attention'] ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=attention;entry=', $context['bugtracker']['entry']['id'], '">
					<span>', $context['bugtracker']['entry']['attention'] ? $txt['mark_attention_undo'] : $txt['mark_attention'], '</span>
				</a>
			</li>
		</ul>
	</div>';
	
	echo '
	<br class="clear" />';
}

function template_BugTrackerAddNew()
{
	// Globalling.
	global $context, $scripturl, $txt;

	// Start our form.
	echo '
	<form action="', $scripturl, '?action=bugtracker;sa=new2" method="post">';

	// Then, for the general information.
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['entry_add'], '
		</h3>
	</div>
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div style="margin-left:10px">
			<table class="fullwidth">';

	// The entry title. Lets start with that.
	echo '
				<tr>
					<td class="halfwidth">
						<strong>', $txt['title'], '</strong>
					</td>
					<td class="halfwidth">
						<input type="text" style="width: 98%" name="entry_title" value="" />
					</td>
				</tr>';

	// What kind of thing is this? Set the type, please.
	echo '
				<tr>
					<td class="halfwidth">
						<strong>', $txt['type'], '</strong>
					</td>
					<td class="halfwidth">
						<input type="radio" name="entry_type" value="issue" /> ', $txt['bugtracker_issue'], '
						<input type="radio" name="entry_type" value="feature" /> ', $txt['bugtracker_feature'], '
					</td>
				</tr>';
	
	// Does this entry need to be private?
	echo '
				<tr>
					<td class="halfwidth"></td>
					<td class="halfwidth">
						<input type="checkbox" name="entry_private" value="true" /> ', $txt['entry_private'], '
					</td>
				</tr>';

	// Close everything. And start the editor.
	echo '
			</table>

			<hr />
			
			<div id="bbcBox_message"></div>
			<div id="smileyBox_message"></div>
			', template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message') . '<br /><hr />';

	// Some users need extra choice.
	echo '
			', $txt['entry_mark_optional'], '<br />
			<input type="radio" name="entry_mark" value="new" checked="checked" /> ', $txt['mark_new'], '<br />
			<input type="radio" name="entry_mark" value="wip" /> ', $txt['mark_wip'], '<br />
			<input type="radio" name="entry_mark" value="done" /> ', $txt['mark_done'], '<br />
			<input type="radio" name="entry_mark" value="reject" /> ', $txt['mark_reject'], '<br />
			<input type="checkbox" name="entry_attention" value="true" /> ', $txt['mark_attention'], '<br /><br />

			', sprintf($txt['entry_posted_in'], $context['bugtracker']['project']['name']);

	// Some hidden stuff.
	echo '
			<input type="hidden" name="entry_projectid" value="', $context['bugtracker']['project']['id'], '" />
			<input type="hidden" name="is_fxt" value="true" />';

	// And our submit button and closing stuff.
	echo '	
			<div class="floatright" style="margin-right:10px">
				<input type="submit" value="', $txt['entry_submit'], '" class="button_submit" />
			</div>
		</div>		
		<span class="botslice"><span></span></span>
	</div>';

	// Close the form.
	echo '
	</form>';

	// Because content will break otherwise.
	echo '
	<br class="clear" />';
}

function template_TrackerViewType()
{
	global $context, $scripturl, $txt, $settings;

	echo '
	<div class="buttonlist">
		<ul>
			<li>
				<a href="', $scripturl, '?action=bugtracker;sa=viewtype;type=', $context['bugtracker']['viewtype_type'] . (isset($_GET['viewclosed']) ? '' : ';viewclosed'), '">
					<span>', isset($_GET['viewclosed']) ? $txt['hideclosed'] : $txt['viewclosed'] . ' [' . $context['bugtracker']['num_closed'] . ']', '</span>
				</a>
			</li>
		</ul>
	</div><br />';

	echo '	<div class="tborder topic_table">
		<table class="table_grid" cellspacing="0" style="width: 100%">
			<thead>
				<tr class="catbg">
					<th scope="col" class="first_th" width="8%" colspan="2">&nbsp;</th>
					<th scope="col">
						', $txt['subject'], '
					</th>
					<th scope="col" width="18%">
						', $txt['status'], '
					</th>
					<th scope="col" width="18%">
						', $txt['type'], '
					</th>
					<th scope="col" width="16%" class="last_th">
						', $txt['project'], '
					</th>
				</tr>
			</thead>
			<tbody>';
	$i = 0;
	foreach ($context['bugtracker']['entries'] as $entry)
	{
		if ($entry['status'] == 'done' && !isset($_GET['viewclosed']))
			continue;
		
		$i++;
		echo '
				<tr>
					<td class="icon1 windowbg">
						<img src="', $settings['images_url'], '/bugtracker/', $entry['type'], '.png" alt="" />
					</td>
					<td class="icon2 windowbg">
						', $entry['attention'] ? '<img src="' . $settings['images_url'] . '/bugtracker/attention.png" alt="" /><span style="font-size: 120%">/</span>' : '', '<img src="' . $settings['images_url'] . '/bugtracker/', $entry['status'] == 'wip' ? 'wip.gif' : $entry['status'] . '.png', '" alt="" />
					</td>
					<td class="subject windowbg2">
						<div>
							<span>
								<a href="', $scripturl, '?action=bugtracker;sa=view;entry=', $entry['id'], '">
									', $entry['name'], '
								</a> ', $entry['status'] == 'wip' ? '<span class="smalltext" style="color:#E00000">(' . $entry['progress'] . ')</span>' : '', '
							</span>
							<p>', $entry['shortdesc'], '</p>
						</div>
					</td>
					<td class="stats windowbg">
						', $txt['status_' . $entry['status']], '
					</td>
					<td class="stats windowbg2">
						', $txt['bugtracker_' . $entry['type']], '
					</td>
					<td class="stats windowbg">
						', !empty($entry['project']) ? '<a href="' . $scripturl . '?action=bugtracker;sa=viewproject;project=' . $entry['project']['id'] . '">' . $entry['project']['name'] . '</a>' : $txt['na'], '
					</td>
				</tr>';
	}
	echo '
		</table>';

	if ($i == 0)
		echo '<div class="centertext windowbg2" style="padding: 5px;">', $txt['no_items'], '</div>';

	echo '
	</div><br />
	<div class="title_bar">
		<h3 class="titlebg">
			', sprintf($txt['items_attention'], count($context['bugtracker']['attention'])), '
		</h3>
	</div>';
	if (count($context['bugtracker']['attention']) != 0)
	{
		echo '
	<div class="tborder topic_table">
		<table class="table_grid" cellspacing="0" style="width: 100%">
			<thead>
				<tr class="catbg">
					<th scope="col" class="first_th" width="8%" colspan="2">&nbsp;</th>
					<th scope="col">
						', $txt['subject'], '
					</th>
					<th scope="col" width="18%">
						', $txt['status'], '
					</th>
					<th scope="col" width="18%">
						', $txt['type'], '
					</th>
					<th scope="col" width="16%" class="last_th">
						', $txt['project'], '
					</th>
				</tr>
			</thead>
			<tbody>';
		foreach ($context['bugtracker']['attention'] as $entry)
		{
			echo '
				<tr>
					<td class="icon1 windowbg">
						<img src="', $settings['images_url'], '/bugtracker/', $entry['type'], '.png" alt="" />
					</td>
					<td class="icon2 windowbg">
						<img src="' . $settings['images_url'] . '/bugtracker/', $entry['status'] == 'wip' ? 'wip.gif' : $entry['status'] . '.png', '" alt="" />
					</td>
					<td class="subject windowbg2">
						<div>
							<span>
								<a href="', $scripturl, '?action=bugtracker;sa=view;entry=', $entry['id'], '">
									', $entry['name'], ' ', $entry['status'] == 'wip' ? '<span class="smalltext" style="color:#E00000">(' . $entry['progress'] . ')</span>' : '', '
								</a>
							</span>
							<p>', $entry['shortdesc'], '</p>
						</div>
					</td>
					<td class="stats windowbg">
						', $txt['status_' . $entry['status']], '
					</td>
					<td class="stats windowbg2">
						', $txt['bugtracker_' . $entry['type']], '
					</td>
					<td class="stats windowbg">
						', !empty($entry['project']) ? '<a href="' . $scripturl . '?action=bugtracker;sa=viewproject;project=' . $entry['project']['id'] . '">' . $entry['project']['name'] . '</a>' : $txt['na'], '
					</td>
				</tr>';
		}
		echo '
		</table>
	</div>';
	}

	echo '
	<br class="clear" />';
}

?>