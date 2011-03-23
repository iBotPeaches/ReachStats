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
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class publicSessions__reachstat
{
	public function getSessionVariables()
	{
		$return_array = array();
		$return_array['location_1_type'] = 'overview';

		if ((( ipsRegistry::$request['gt'] ))) {
			$return_array['location_1_type'] = 'gamerprofile';
			$return_array['location_1_id'] = intval( $this->memberData['member_id'] );
		}
	}

	public function parseOnlineEntries( $array=array() )
	{
	}
}


class fetchSkin__reachstat{
	/**
	 * Constructor
	 */
	function __construct(){

	}
	public function fetchSkin()
	{
		//return 12;
	}
}