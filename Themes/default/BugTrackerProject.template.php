<?php

/* FXTracker Project Template */

function template_TrackerViewProject()
{
	global $context, $scripturl, $txt, $settings;

	echo '
	<div class="buttonlist">
		<ul>
			<li>
				<a href="', $scripturl, '?action=bugtracker;sa=projindex;id=', $context['bugtracker']['project']['id'] . (isset($_GET['viewclosed']) ? '' : ';viewclosed'), '">
					<span>', $txt[(isset($_GET['viewclosed']) ? 'hideclosed' : 'viewclosed')], '</span>
				</a>
			</li>

			<li>
				<a class="active" href="', $scripturl, '?action=bugtracker;sa=new;proj=', $context['bugtracker']['project']['id'], '">
					<span>', $txt['new_entry'], '</span>
				</a>
			</li>
		</ul>
	</div><br />
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
					<th scope="col" width="18%" class="last_th">
						', $txt['type'], '
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
								<a href="', $scripturl, '?action=bugtracker;sa=view;id=', $entry['id'], '">
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
					<th scope="col" width="18%" class="last_th">
						', $txt['type'], '
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
