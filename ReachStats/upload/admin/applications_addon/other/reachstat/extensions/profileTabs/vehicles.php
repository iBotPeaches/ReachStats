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
if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class profile_vehicles extends profile_plugin_parent
	{
	/**
	 * Attachment object
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $attach;

	/**
	 * Feturn HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() )
	{
		//-----------------------------------------
		// Return content..
		//-----------------------------------------

		/* set null */
		$content = "";

		/* add the stuff */
		$content .=<<<EOF
<script type="text/javascript">
//<![CDATA[
	var data = new google.visualization.DataTable();
		//dump the temp vars
			var vehicles  = {$data};
		//setup the graph
  			data.addColumn('string', 'Vehicle');
  			data.addColumn('number', 'Kills');
  			data.addColumn('number', 'Deaths');
        	data.addColumn('number', 'K/D Spread');
        	data.addRows(vehicles.length);
		//fill
		for (var i = 0; i < vehicles.length; i++){
        	data.setValue(i, 0, vehicles[i]['name']);
        	data.setValue(i, 1, vehicles[i]['kills']);
        	data.setValue(i, 2, vehicles[i]['deaths']);
			data.setValue(i, 3, vehicles[i]['ratio']);
		}
//]]>
</script>
			<div id="table3"></div>
EOF;
		/* send home */
		return $content;
	}
}