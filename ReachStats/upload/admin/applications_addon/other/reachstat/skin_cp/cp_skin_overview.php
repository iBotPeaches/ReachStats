<?php
/*
   * Copyright (c) 2011 Reach Stats + AUTHORS
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files. Please make yourself
   * aware of the license assoicated with this project.
   *
   * THIS SOFTWARE IS PROVIDED "AS IS", WITHOUT ANY WARRANTY OF ANY KIND
   *
   * https://github.com/iBotPeaches/ReachStats
   * bugs: http://reachstuff.com/community/tracker/project-1-halo-reach-stats/
   *
   * ~peaches
*/


class cp_skin_overview extends output
{

/**
 * Prevent our main destructor being called by this class
 *
 * @access	public
 * @return	void
 */
public function __destruct()
{
}

/**
 * Overview screen
 *
 */

public function overviewSplash($data ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<table width='100%'>
	<tr>
		<td valign='top' width='50%' style='padding-right:2px;'>
			<div class="acp-box">
				<h3>Overview</h3>
				<table class='double_pad alternate_rows' cellspacing='0'>
					<tr>
						<td><strong>Active Accounts:</strong></td>
						<td>{$data['usersAct']}</td>
					</tr>
					<tr>
						<td><strong>Accounts:</strong></td>
						<td>{$data['users']}</td>
					</tr>
					<tr>
						<td><strong>Folder Size:</strong></td>
						<td>{$data['size']}</td>
					</tr>
		</td>
	</tr>
</table>
<td valign='top' width='50%' style='padding-left:2px;'>
			<div class="acp-box">
				<h3>Information</h3>
				<table class='double_pad alternate_rows' cellspacing='0'>
					<tr>
						<td>Version:</td>
						<td>{$data['version']}</td>
					</tr>
				</table>
			</div>
</td>
HTML;

$IPBHTML .= <<<HTML
	</table>
</div>
HTML;

	//--endhtml--//
	return $IPBHTML;
}

public function tools() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<form action='{$this->settings['base_url']}module=tools&section=tools&do=rebuildusers' method='POST'>
	<div class='acp-box'>
		<h3 class='padded'>Recache Everything</h3>
		<table cellpadding='4' cellspacing='0' width='100%'>
			<tr>
				<td width='100%' class='tablerow2'><strong>Rebuild Users</strong><div class='desc'>This will rebuild all users sigs/stats regardless if they are inactive or not.</div></td>
			</tr>
			<tr>
				<td class='tablerow2' width='100%' style='text-align:center;'>
					<input type='submit' class='button' value='Rebuild Stats' />
				</td>
			</tr>
		</table>
	</div>
</form>
HTML;

$IPBHTML .= <<<HTML
	</table>
</div>
HTML;

		//--endhtml--//
		return $IPBHTML;
	}
}