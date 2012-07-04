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
	</div>
	<table class="fullwidth">
		<tr>
			<td class="halfwidth">
				<div class="title_bar">
					<h3 class="titlebg">
						', $txt['bugtracker_latest_issues'], '
					</h3>
				</div>
			</td>
			<td class="halfwidth">
				<div class="title_bar">
					<h3 class="titlebg">
						', $txt['bugtracker_latest_features'], '
					</h3>
				</div>
			</td>
		</tr>
	</table>
	<table class="fullwidth">
		<tr>
			<td>
				<div class="plainbox">
					<table class="fullwidth">
						<tr class="halfwidth floatleft">
							<td>';

	// Load the list of entries from the latest features!
	if (!empty($context['bugtracker']['latest']['issues']))
	{
		$i = 0;
		foreach ($context['bugtracker']['latest']['issues'] as $entry)
		{
			$i++;
			echo '
								', $i, '. <a href="', $scripturl, '?action=bugtracker;sa=view;id=', $entry['id'], '">', $entry['name'], '</a><br />';
		}
	}
	else
		echo $txt['bugtracker_no_latest_entries'];

	echo '
							</td>
						</tr>
						<tr class="halfwidth floatright">
							<td>';

	// Load the list of entries from the latest features!
	if (!empty($context['bugtracker']['latest']['features']))
	{
		$i = 0;
		foreach ($context['bugtracker']['latest']['features'] as $entry)
		{
			$i++;
			echo '
					', $i, '. <a href="', $scripturl, '?action=bugtracker;sa=view;id=', $entry['id'], '">', $entry['name'], '</a><br />';
		}
	}
	else
		echo $txt['bugtracker_no_latest_entries'];

	echo '						</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table><br />

	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['images_url'], '/bugtracker/projects.png" class="icon" alt="" />', $txt['bugtracker_projects'], '
		</h3>
	</div>';

	// Show the project list.
	$windowbg = 0;
	foreach ($context['bugtracker']['projects'] as $project)
	{
		echo '
	<div class="windowbg', $windowbg == 0 ? '' : '2', '">
		<span class="topslice"><span></span></span>
		<div class="info" style="margin-left: 10px">
			<a class="projsubject" href="', $scripturl, '?action=bugtracker;sa=projindex;id=', $project['id'], '">', $project['name'], '</a> - ', sprintf($txt['issues'], $project['num']['issues']), ', ', sprintf($txt['features'], $project['num']['features']), '<br />
			', htmlspecialchars($project['desc']), '
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
								<a href="', $scripturl, '?action=bugtracker;sa=view;id=', $entry['id'], '">
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
						', !empty($context['bugtracker']['projects'][$entry['project']]['name']) ? $context['bugtracker']['projects'][$entry['project']]['name'] : $txt['na'], '
					</td>
				</tr>';
		}
		echo '
			</tbody>
		</table>
	</div><br />';
	}

	// The info centre?
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<img src="', $settings['images_url'], '/bugtracker/infocenter.png" alt="" class="icon" /> ', $txt['info_centre'], '
		</h3>
	</div>
	<div class="plainbox">
		<strong>', $txt['total_entries'], '</strong> ', count($context['bugtracker']['entries']), '<br />
		<strong>', $txt['total_projects'], '</strong> ', count($context['bugtracker']['projects']), '<br /><br />
	</div>';
	
	// And our last batch of HTML.
	echo '
	<br class="clear" />';
}

function template_TrackerView()
{
	global $context, $txt, $scripturl, $settings;

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
				<a class="active" href="', $scripturl, '?action=bugtracker;sa=reply;id=', $context['bugtracker']['entry']['id'], '"><span>', $txt['reply'], '</span></a>
			</li>';
	
	// Are we allowed to edit this entry?
	if ($context['can_bt_edit_any'] || $context['can_bt_edit_own'])
		echo '
			<li>
				<a href="', $scripturl, '?action=bugtracker;sa=edit;id=', $context['bugtracker']['entry']['id'], '"><span>', $txt['editentry'], '</span></a>
			</li>';

	// Or allowed to remove it?
	if ($context['can_bt_remove_any'] || $context['can_bt_remove_own'])
		echo '
			<li>
				<a href="', $scripturl, '?action=bugtracker;sa=remove;id=', $context['bugtracker']['entry']['id'], '"><span>', $txt['removeentry'], '</span></a>
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
					<a href="', $scripturl, '?action=bugtracker;sa=projindex;id=', $context['bugtracker']['entry']['project'], '">', $context['bugtracker']['project']['name'], '</a><br />
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
		if ($context['can_bt_mark_new_any'] || $context['can_bt_mark_new_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'new' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=new;id=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_new'], '</span>
				</a>
			</li>';
		if ($context['can_bt_mark_wip_any'] || $context['can_bt_mark_wip_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'wip' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=wip;id=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_wip'], '</span>
				</a>
			</li>';
		if ($context['can_bt_mark_done_any'] || $context['can_bt_mark_done_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'done' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=done;id=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_done'], '</span>
				</a>
			</li>';
		if ($context['can_bt_mark_reject_any'] || $context['can_bt_mark_reject_own'])
			echo '
			<li>
				<a ', $context['bugtracker']['entry']['status'] == 'reject' ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=reject;id=', $context['bugtracker']['entry']['id'], '">
					<span>', $txt['mark_reject'], '</span>
				</a>
			</li>';
		echo '
		</ul>
	</div>';
	}

	if ($context['can_bt_mark_attention_any'] || $context['can_bt_mark_attention_own'])
		echo '
	<div class="buttonlist floatleft">
		<ul>
			<li>
				<a ', $context['bugtracker']['entry']['attention'] ? 'class="active"' : '', ' href="', $scripturl, '?action=bugtracker;sa=mark;as=attention;id=', $context['bugtracker']['entry']['id'], '">
					<span>', $context['bugtracker']['entry']['attention'] ? $txt['mark_attention_undo'] : $txt['mark_attention'], '</span>
				</a>
			</li>
		</ul>
	</div>';
	
	echo '
	<br class="clear" />';
}

function template_TrackerEdit()
{
	// This is a highly customizable template. Almost everything in here can be customized by the source.
	global $context, $txt;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			', $context['bugtracker']['edit']['title'], '
		</h3>
	</div>
	<!-- Start the form -->
	<form action="', $context['bugtracker']['edit']['formlink'], '" method="', $context['bugtracker']['edit']['method'], '">
	<input type="hidden" name="entry_id" value="', $context['bugtracker']['entry']['id'], '" />
	<input type="hidden" name="type_save" value="', $context['bugtracker']['edit']['type'], '" />
	<div class="windowbg">
		<span class="topslice"><span></span></span>
		<div style="margin-left: 10px">
			<table style="width:100%">
				<tr>
					<td style="width: 50%">', $txt['entry_title'], '</td>
					<td style="width: 50%">
						<input type="text" size="50" name="entry_title" value="', $context['bugtracker']['entry']['name'], '" />
					</td>
				</tr>
				<tr>	<td style="width: 50%">', $txt['entry_type'], '</td>
					<td style="width: 50%">
						<input type="radio" name="entry_type" value="issue" ', $context['bugtracker']['entry']['type'] == 'issue' ? 'checked="checked"' : '', '/> ', $txt['bugtracker_issue'], '<br />
						<input type="radio" name="entry_type" value="feature" ', $context['bugtracker']['entry']['type'] == 'feature' ? 'checked="checked"' : '', '/> ', $txt['bugtracker_feature'], '
					</td>
				</tr>
				<tr>
					<td style="width: 50%">', $txt['entry_progress'], '</td>
					<td style="width: 50%">
						<input type="text" size="2" name="entry_progress" value="', $context['bugtracker']['entry']['progress'], '" />
					</td>
				</tr>
				<tr>
					<td class="halfwidth">', $txt['entry_private'], '</td>
					<td class="halfwidth">
						<input type="checkbox" name="entry_private"', $context['bugtracker']['entry']['private'] ? ' checked="checked"' : '', ' />
					</td>
				</tr>
			</table>
		</div>
		<span class="botslice"><span></span></span>
	</div><br />
	<div class="cat_bar">
		<h3 class="catbg">
			', $txt['entry_desc'], '
		</h3>
	</div>
	<textarea name="entry_desc" style="height: 300px;" class="fullwidth">', $context['bugtracker']['entry']['desc'], '</textarea><br />

	<strong>', $txt['info_change_status'], '</strong>
	<div class="floatright"><input type="submit" value="', $txt['entry_submit'], '" /></div><br />
	</form>
	<br class="clear" />';
}


?>