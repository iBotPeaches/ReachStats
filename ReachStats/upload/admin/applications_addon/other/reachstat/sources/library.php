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
   * bugs: https://github.com/iBotPeaches/ReachStats/issues
   *
   * ~peaches
*/
class Library
{
	/* vars */
	protected $version = HR_VERSION;

	/**
	 * Constructor
	 */
	function __construct(ipsRegistry $ipsRegistry)
	{
		/* Make objects and stuff */
		$this->registry 		= &$ipsRegistry;
		$this->DB 				= $this->registry->DB();
		$this->settings 		= &$this->registry->fetchSettings();
		$this->request 			= &$this->registry->fetchRequest();
		$this->lang 			= $this->registry->getClass('class_localization');
		$this->member 			= $this->registry->member();
		$this->memberData 		= &$this->registry->member()->fetchMemberData();
		$this->cache 			= $this->registry->cache();
		$this->caches 			= &$this->cache->fetchCaches();

		/* quick var dump */
		$userID = $this->memberData['member_id'];
	}

	/**
	 * Library::addJS()
	 *
	 * @return
	 */
	public function addJS()
	{
		/* Adding Google Graphs API */
		$this->registry->output->addToDocumentHead( 'javascript', "http://www.google.com/jsapi" );
	}

	/**
	 * A function for making time periods readable
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     2.0.0
	 * @link        http://aidanlister.com/2004/04/making-time-periods-readable/
	 * @param       int     number of seconds elapsed
	 * @param       string  which time periods to display
	 * @param       bool    whether to show zero time periods
	 */
	function time_duration($seconds, $use = null, $zeros = false)
	{

		// Define time periods
		$periods = array (
		    'years'     => 31556926,
		    'Months'    => 2629743,
		    'weeks'     => 604800,
		    'days'      => 86400,
		    'hours'     => 3600,
		    'minutes'   => 60,
		    'seconds'   => 1
		    );

		// Break into periods
		$seconds = (float) $seconds;
		foreach ($periods as $period => $value) {
			if ($use && strpos($use, $period[0]) === false) {
				continue;
			}
			$count = floor($seconds / $value);
			if ($count == 0 && !$zeros) {
				continue;
			}
			$segments[strtolower($period)] = $count;
			$seconds = $seconds % $value;
		}

		//Nasty check 2
		if (empty($segments)){
		$segments['seconds'] = 0;
		}

		// Build the string
		foreach ($segments as $key => $value) {
			$segment_name = substr($key, 0, -1);
			$segment = $value . ' ' . $segment_name;
			if ($value != 1) {
				$segment .= 's';
			}
			$array[] = $segment;
		}

		$str = implode(', ', $array);
		return $str;
	}

	public function addFooter(){

		/* Check for version */
		if( ! $this->settings['reachstat_display_ver'] )
		{
			$this->version = '';
		}

		/* Return copyright */
		return "<div class='desc' style='margin-top:10px; margin-right: 15px; float: right; text-align:right'>ReachStats was created under Microsoft's <a href='http://www.xbox.com/en-US/community/developer/rules.htm' target='_blank' >Game Content Usage Rules</a> using assets from Halo Reach. <br />
				Halo Reach is a registered trademark of Bungie, LLC. This website is in no way affiliated with Bungie, LLC.<br />
				Powered by <a href='http://reachstuff.com/community/' title='ReachStuff' target= '_blank'>Reach Stats</a> {$this->version} &copy; ".date('Y')."</div><br /><br />";
	}

	/**
	 * Library::determineHighestTier()
	 * @params ID of member_group
	 * @return ID of highest tier int
	 */
	public function determineHighestTier($groupID)
	{
			//Test for Tier 4
			if (in_array( $groupID, explode( ",", $this->settings['tier_4_group'] ) ) )
			{
				return 4;
			}
			else if (in_array( $groupID, explode( ",", $this->settings['tier_3_group'] ) ) )
			{
				return 3;
			}
			else if  (in_array( $groupID, explode( ",", $this->settings['tier_2_group'] ) ) )
			{
				return 2;
			}
			else if  (in_array( $groupID, explode( ",", $this->settings['tier_1_group'] ) ) )
			{
				return 1;
			}
			else
			{
				return 0;
			}
	}

	/*
	 * Only returns the 3-digit HTTP code
	 */
	public function get_http_response_code($theURL)
	{
		$headers = get_headers($theURL);
		return substr($headers[0], 9, 3);
	}

	/*
	 * Pass member ID
	 * Return Group ID
	 */
	private function _getGroupID($id)
	{
		/* Get the group ID */
		$info = $this->DB->buildAndFetch(array(
			'select' 		=> 'member_group_id',
			'from' 			=> 'members',
			'where' 		=> "member_id='" . intval($id) . "'"));

		/* Return */
		return $info['member_group_id'];
	}

	public function getRandomPlaceholder()
	{
		/* Make the array first */
		$pH = array ();

		/* Manaully set them to our images */
		$pH[0]  = "blue_elite_tie";
		$pH[1]  = "blue_spartan_tie";
		$pH[2]  = "brown_elite_tie";
		$pH[3]  = "brown_spartan_tie";
		$pH[4]  = "gold_elite_tie";
		$pH[5]  = "gold_spartan_tie";
		$pH[6]  = "green_elite_tie";
		$pH[7]  = "green_spartan_tie";
		$pH[8]  = "olive_spartan_tie";
		$pH[9]  = "orange_elite_tie";
		$pH[10] = "orange_spartan_tie";
		$pH[11] = "pink_elite_tie";
		$pH[12] = "pink_spartan_tie";
		$pH[13] = "purple_elite_tie";
		$pH[14] = "purple_spartan_tie";
		$pH[15] = "red_elite_tie";
		$pH[16] = "red_spartan_tie";

		/* random lolololol */
		$test = $pH[mt_rand(0,count($pH))];

		/* Bug Check */
		while($test == "")
		{
			$test = $pH[mt_rand(0,count($pH))];
		}

		return $test;

	}

	public function getUserData($id)
	{
		//---------------------------------------------------------------
		// Only look for their groupID. If there not on their own page
		//---------------------------------------------------------------
		if (intval($this->memberData['member_id']) != intval($id))
		{
			$groupID = $this->_getGroupID($id);
		}
		else
		{
			$groupID = $this->memberData['member_group_id'];
		}

		/* Use that group ID to find Tier ID */
		$tierID = $this->determineHighestTier($groupID);

		/* Information
		   * Inactive (Seconds for inactivity to kick in)
		   * Banned (Weather Tier is banned or not)
		   * Compare (0 = NO, 1 = YES, 2 = CAN BE COMPARED)
		   * Dynamic Images (0 = NONE, 1 = ALL)
		   * Ads (0 = NONE, 1 = SOME, 2 = LOTS)
		   * WM (1 = NO WATERMARK, 0 = WATERMARK)
		   * CONTROL (1 = RECACHE ANYTHING, 0= RECACHE WHEN NEEDED)
		*/
		switch ($tierID)
		{
			case 4: #Staff (Staff)
				$result['tier']['inactive'] = 15552000; #180 days
				$result['tier']['time_ttl'] = 21600; # 6 hours
				$result['tier']['banned']   = 0;
				$result['tier']['compare']  = 1;
				$result['tier']['dynimg']   = 1;
				$result['tier']['ads']		= 0;
				$result['tier']['wm']       = 1;
				$result['tier']['control']  = 1;
				break;

			case 3: #Tier 3 (Top Payers)
				$result['tier']['inactive'] = 2592000; #30 days
				$result['tier']['time_ttl'] = 3600; # 1 hour
				$result['tier']['banned']   = 0;
				$result['tier']['compare']  = 1;
				$result['tier']['dynimg']   = 2;
				$result['tier']['ads']		= 1;
				$result['tier']['wm']       = 1;
				$result['tier']['control']  = 1;
				break;

			case 2: #Tier 2 (2nd Plan)
				$result['tier']['inactive'] = 1296000; #15 days
				$result['tier']['time_ttl'] = 21600; # 6 hours
				$result['tier']['banned']   = 0;
				$result['tier']['compare']  = 1;
				$result['tier']['dynimg']   = 1;
				$result['tier']['ads']		= 1;
				$result['tier']['wm']       = 1;
				$result['tier']['control']  = 1;
				break;

			case 1: #Tier 1 (Free Plan)
				$result['tier']['inactive'] = 259200; #3 days
				$result['tier']['time_ttl'] = 86400; # 24 hours
				$result['tier']['banned']   = 0;
				$result['tier']['compare']  = 2;
				$result['tier']['dynimg']   = 1;
				$result['tier']['ads']		= 2;
				$result['tier']['wm']       = 0;
				$result['tier']['control']  = 0;
				break;

			case 0: #Banned
				$result['tier']['inactive'] = -1; #-1 doesn't allow generating
				$result['tier']['time_ttl'] = -1; # n/a
				$result['tier']['banned']   = 1;
				$result['tier']['compare']  = 0;
				$result['tier']['dynimg']   = 0;
				$result['tier']['ads']		= 2;
				$result['tier']['wm']       = 0;
				$result['tier']['control']  = 0;
				break;

				/* Save tier ID */
				$result['tier']['id'] = $tierID;
		}

			/* Things we have
			 * groupID (Member Group ID)
			 * tierID  (Tier ID)
			 * userID  (Member User ID)
			 */
			$result['groupID'] = $groupID;
			$result['userID']  = $id;
			$result['tierID']  = $tierID;

			/* Check if they can recache, based on their group */
			if (in_array( $groupID, explode( ",", $this->settings['recache_abil'] ) ) )
			{
				/* They are viewing their own page */
				$result['stat']['recache'] = 1;
			}
			else
			{
				$result['stat']['recache'] = 0;
			}

			/* Is this our page...not ? */
			if (intval($this->memberData['member_id']) != $id)
			{
				$result['stat']['mine'] = 0;
			}
			else
			{
				/* They are viewing their own page */
				$result['stat']['mine'] = 1;
			}

			/* Check whoever is viewing the page */
			if ((in_array($this->memberData['member_group_id'], explode(",", $this->settings['recache_abil']))))
			{

				/* Is this our page...not ? */
				if (intval($this->memberData['member_id']) != $id)
				{
					$result['stat']['recache'] = 2;
				}
			}

			/* Pull the Gamer Stats cache */
			$temp['stats'] = $this->DB->buildAndFetch(array(
				'select' 		=> 'stat_date,sig_date',
				'from' 			=> 'reachstat',
				'where' 		=> "id='" . intval($id) . "'"));

			/* ARE WE ON OUR OWN PAGE. Saves 1 query */
			if ($this->memberData['member_id'] == $id)
			{
				$memInfo['last_activity'] = $this->memberData['last_activity'];
			}
			else
			{
				/* Lets get the last time they were active */
				$memInfo = $this->DB->buildAndFetch(array(
					'select' 		=> 'last_activity',
					'from'   		=> 'members',
					'where' 		=> "member_id='" . intval($id). "'"));

			}

			/* Get the date */
			$timeBefore = $memInfo['last_activity'];

			/* Get the time nao */
			$timenow = time();

			/* File the difference for gamer page & Sigs, then return */
			$result['visit'] = ($timenow - $timeBefore);
			$result['data']  = ($timenow - $temp['stats']['stat_date']);
			$result['sigs']  = ($timenow - $temp['stats']['sig_date']);

			/* unset some stuff */
			unset($temp);
			unset($memInfo);
			unset($timenow);
			unset($timeBefore);

			/* Check for perm blocked */
			if ($this->settings['recache_perm'] == 1)
			{
				$result['set'] = 1;
			}
			else
			{
				$result['set'] = 0;
			}

		/* The dark deed is done. */
		return $result;
	}

	/**
	 * Return human readable sizes
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.3.0
	 * @link        http://aidanlister.com/2004/04/human-readable-file-sizes/
	 * @param       int     $size        size in bytes
	 * @param       string  $max         maximum unit
	 * @param       string  $system      'si' for SI, 'bi' for binary prefixes
	 * @param       string  $retstring   return string format
	 */
	public function size_readable($size, $max = null, $system = 'si', $retstring = '%01.2f %s')
	{
	// Pick units
	$systems['si']['prefix'] = array('B', 'K', 'MB', 'GB', 'TB', 'PB');
	$systems['si']['size']   = 1000;
	$systems['bi']['prefix'] = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
	$systems['bi']['size']   = 1024;
	$sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];

	// Max unit to display
	$depth = count($sys['prefix']) - 1;
	if ($max && false !== $d = array_search($max, $sys['prefix'])) {
		$depth = $d;
	}

	// Loop
	$i = 0;
	while ($size >= $sys['size'] && $i < $depth) {
		$size /= $sys['size'];
		$i++;
	}

	return sprintf($retstring, $size, $sys['prefix'][$i]);
}

	/**
 * Calculate the size of a directory by iterating its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.2.0
 * @link        http://aidanlister.com/2004/04/calculating-a-directories-size-in-php/
 * @param       string   $directory    Path to directory
 */
	public function dirsize($path)
	{
		// Init
		$size = 0;

		// Trailing slash
		if (substr($path, -1, 1) !== DIRECTORY_SEPARATOR) {
			$path .= DIRECTORY_SEPARATOR;
		}

		// Sanity check
		if (is_file($path)) {
			return filesize($path);
		} elseif (!is_dir($path)) {
			return false;
		}

		// Iterate queue
		$queue = array($path);
		for ($i = 0, $j = count($queue); $i < $j; ++$i)
		{
			// Open directory
			$parent = $i;
			if (is_dir($queue[$i]) && $dir = @dir($queue[$i])) {
				$subdirs = array();
				while (false !== ($entry = $dir->read())) {
					// Skip pointers
					if ($entry == '.' || $entry == '..') {
						continue;
					}

					// Get list of directories or filesizes
					$path = $queue[$i] . $entry;
					if (is_dir($path)) {
						$path .= DIRECTORY_SEPARATOR;
						$subdirs[] = $path;
					} elseif (is_file($path)) {
						$size += filesize($path);
					}
				}

				// Add subdirectories to start of queue
				unset($queue[0]);
				$queue = array_merge($subdirs, $queue);

				// Recalculate stack size
				$i = -1;
				$j = count($queue);

				// Clean up
				$dir->close();
				unset($dir);
			}
		}

		return $size;
	}

	public function checkOnline()
	{
		/* Is this junk online and enabled? */
		if (!$this->settings['reach_online'])
		{
			/* The currently visiting member */
			if(in_array( $this->memberData['member_group_id'], explode( ",", $this->settings['reach_group_online'] ) ) )
			{
				/* Load cache and get out */
				$_LOAD['metadata'] = 1;
			}
			else
			{
				/* error out */
				$this->registry->getClass('output')->showError( $this->lang->words['system_offline'], "2006", false, '2006' );
			}
		}
	}

	/**
	 * Determines where to put custom profile tabs
	 *
	 * @access	protected
	 * @param	array 		$takenPositions		Array of positions that have been used
	 * @param	integer		$requestedPosition	Position to check
	 * @return	integer
	 */
	public function _getTabPosition( $takenPositions, $requestedPosition )
	{
		if( in_array( $requestedPosition, $takenPositions ) )
		{
			$requestedPosition++;
			$requestedPosition = $this->_getTabPosition( $takenPositions, $requestedPosition );
		}

		return $requestedPosition;
	}

	/**
	 * Library::doubleCheck()
	 *
	 * @param mixed $this
	 * @return
	 */
	public function doubleCheck($times, $id)
	{
		/* Are they inactive ? */
		if (!($times['visit'] > $times['tier']['inactive']))
		{
			/* Now update into Database. */
			$this->DB->update('reachstat', array(
			'id'	   		=> intval($id),
			'inactive'		=> 0),
			"id=" . intval($id));
		}
		else
		{
			/* Now update into Database. */
			$this->DB->update('reachstat', array(
			'id'	   		=> intval($id),
			'inactive'		=> 1),
			"id=" . intval($id));
		}

	}
}