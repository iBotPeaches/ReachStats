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

class reachStats
{
	/* Vars */
	protected $curGamertag 				= "";
	protected $gameCount				= 0;
	protected $id 						= 0;
	protected $version 					= HR_VERSION;
	protected $debug 					= DEBUG_MODE;
	protected $api_key					= "";
	protected $item;
	protected $tempNum					= 0;

	/* halo data */
	public $data 					= array();
	public $weapons					= array();
	public $medals					= array();
	public $commendations			= array();
	public $i						= 0;
	public $challenges				= array();
	protected $cachedData			= array();
	protected $kb 					= 'http://reachstuff.com/kb/page/';
	public $commLevels				= array('not_awarded', 'iron', 'bronze', 'silver', 'gold', 'onyx', 'max');

	/**
	 * Constructor
	 */
	function __construct(ipsRegistry $ipsRegistry)
	{
		/* Make objects and stuff */
		$this->registry = &$ipsRegistry;
		$this->DB = $this->registry->DB();
		$this->settings = &$this->registry->fetchSettings();
		$this->request = &$this->registry->fetchRequest();
		$this->lang = $this->registry->getClass('class_localization');
		$this->member = $this->registry->member();
		$this->memberData = &$this->registry->member()->fetchMemberData();
		$this->cache = $this->registry->cache();
		$this->caches = &$this->cache->fetchCaches();
	}

	/**
	 * reachStats::getGamertag()
	 *
	 * @return
	 */
	public function getGamertag($userID, $flag = true)
	{
		/* Check for user ID */
		if (!$userID == 0)
		{
			$value = $this->DB->buildAndFetch(array(
				'select' => 'gamertag',
				'from' => 'reachstat',
				'where' => "id='" . $this->memberData['member_id'] .
			    "'"));

			/* Clean that junk */
			$value['gamertag'] = $this->unParseGT($value['gamertag']);

			return $value['gamertag'];
		} else
		{
			/* Flag = true */
			if (($flag == true))
			{
				$this->registry->getClass('output')->showError( $this->lang->words['no_user_id'],"<a href='".$this->kb."2005-r6'>2005</a>", false, '2005' );
			}
		}
	}

	public function setGamertag($userID, $gt)
	{
		/* Check if the GT exists */
		$gtExists = $this->DB->buildAndFetch(array(
			'select' => 'id',
			'from' => 'reachstat',
			'where' => "gamertag='" . $gt .
		"'"));

		/* Go on */
		if ($gtExists['id'] == null)
		{
			/* Check if we have a user ID */
			if (!$userID == 0)
			{
				/* Check the DB first */
				$result = $this->DB->buildAndFetch(array(
					'select' => '*',
					'from' => 'reachstat',
					'where' => "id='" . intval($userID) .
				    "'"));

				/* COMPARE */
				if ($result == null)
				{
					/* Its blank so add it in */
					$this->DB->insert('reachstat', array(
						'id' => intval($userID),
						'gamertag' => $gt,
						'inactive' => intval(0),
						'ip_address'	   => IPSText::parseCleanValue($_SERVER['REMOTE_ADDR'])));
				} else
				{
					/* o teh nos. It exists */
					$this->DB->update('reachstat', array(
						'id' 		=> intval($userID),
						'gamertag'  => $gt),
					"id=" . intval($userID));
				}

				/* Lets add it to the members table */
				$this->DB->update('members',array(
				'has_reachstat' => intval(1)),
				"member_id=" .intval($userID));
			}
			else
			{
				$this->registry->getClass('output')->showError($this->lang->words['bad_user'], "<a href='".$this->kb."2018-r20'>2018</a>", false, '2018' );
			}
		}
		else
		{
			$this->registry->getClass('output')->showError( $this->lang->words['gt_exists'],"<a href='".$this->kb."2004-r5'>2004</a>", false, '2004' );
		}
	}

	/**
	 * reachStats::checkGTExists()
	 *
	 * @params GT to check
	 * @params true to save, false to not
	 * @return true /no GT
	 * 		   false/GT
	 */
	public function checkGTExists($gt, $save)
	{
		/* Set the URL to check GT */
		$gt = str_replace(' ', '%20', $gt);
		$url = "http://www.bungie.net/stats/reach/default.aspx?player=" . $gt;

		/* get CURL */
		$this->_initCURL();

		/* Vroom vroom go get em boys */
		$contents = $this->fileManage->getFileContents($url);

		/* The regex check */
		if (preg_match('/ctl00_mainContent_notFoundPanel/s', $contents))
		{
			/* Regex Failed */
			return true;
		} else
		{
			/* Real GT */
			/* Double check if were saving */
			if ($save === true)
			{
				$this->setGamertag($this->memberData['member_id'], $gt);
			}
			return false;
		}
		# $took = round(microtime(true) - $timer, 2);
	}

	private function _initCURL()
	{
		/* Get our built in CURL management */
		require_once (IPS_KERNEL_PATH . 'classFileManagement.php');
		$this->fileManage = new classFileManagement($this->registry);
	}

	public function setUp()
	{
		/* get api key manually */
		$this->api_key = ipsRegistry::$settings['api_key'];

		/* Only run if we have API key */
		if (($this->api_key != null))
		{
			/* Need Library for this */
			require_once (IPSLib::getAppDir('reachstat') . '/sources/library.php');
			$this->library = new Library($this->registry);

			/* Engine vrooom */
			$this->_initCURL();

			/* Lets start our JSON readout */
			$tempURL = 'http://www.bungie.net/api/reach/reachapijson.svc/game/metadata/' . $this->api_key;

			/* check for bad api key */
			$http_code = $this->library->get_http_response_code($tempURL);

			/* boot us out of here */
			if ($http_code != "200")
			{
				$this->registry->getClass('output')->showError( $this->lang->words['reponse_failed'],"<a href='".$this->kb."2028-r30'>2028</a>", false, '2028' );
			}

			/* Download it via fileManage, then decode it */
			$data = json_decode($this->fileManage->getFileContents($tempURL), true);

			/* make sure that api key works */
			$this->apiCheck($data);

			/* reset */
			$id = 0;

			/* Loop for commedations and add them to array */
			foreach ($data['Data']['AllCommendationsById'] as $item ){
				$this->cachedData['commendations'][$id] = $item;
				$id++;
			}

			/* reset again */
			$id = 0;

			/* Foreach for the maps/levels */
			foreach ($data['Data']['AllMapsById'] as $item ){
				$this->cachedData['maps'][$id] = $item;
				$id++;
			}

			/* Foreach for the medals */
			foreach ($data['Data']['AllMedalsById'] as $item ){
				$this->cachedData['medals'][$item['Key']] = $item;
			}

			/* Sort to align ID with Key */
			asort($this->cachedData['medals']);

			/* weaps :o */
			foreach ($data['Data']['AllWeaponsById'] as $item ){
				$this->cachedData['weapons'][$item['Key']]['Key'] = $item['Key'];
				$this->cachedData['weapons'][$item['Key']]['Value']['Description'] = $item['Value']['Description'];

				/* Clean the name */
				$pos = strpos($item['Value']['Name'], '-');

				/* lol vehicle check */
				$tempItem = $this->containsVehicleWord($item['Value']['Name']);

				/* Hacky way to remove vehicle from being removed */
				if($tempItem == true)
				{
					$pos = null;
				}
				/* check if $pos is not null */
				if(!($pos) == null) {

					/* get portion of string */
					$tempName = substr($item['Value']['Name'], 0, $pos);

				}
				else
				{
					$tempName = $item['Value']['Name'];
				}

				/* Not set to tempName */
				$this->cachedData['weapons'][$item['Key']]['Value']['Name']  = $tempName;

				/* Cleanup */
				unset($pos);
				unset($tempName);
			}

			/* Sort to align ID with Key */
			asort($this->cachedData['weapons']);

			/* Last reset */
			$id = 0;

			/* game variants :o */
			foreach ($data['Data']['GameVariantClassesKeysAndValues'] as $item ){
				$this->cachedData['variants'][$id] = $item;
				$id++;
			}

			/* Ranks */
			foreach ($data['Data']['GlobalRanks'] as $item)
			{
				$this->cachedData['ranks'][$item['Id']] = $item;
			}
			/* dump */
			unset($data);

			//--------------------------------------------
			// Storage of CACHE
			//--------------------------------------------

			/* Check if DEBUG_MODE is enabled */
			if ($this->debug == 1)
			{
				print "<textarea cols='50' rows='20'>"; print_r( $this->cachedData ); print "</textarea>";
				exit();
			}
			else
			{

				/* set cache */
				$this->cache->setCache( 'metadata', $this->cachedData,  array( 'array' => 1, 'donow' => 1 ) );

				/* cleanup */
				unset($this->cachedData);
			}
		}


	}

	public function apiCheck(&$response)
	{
		/* Make sure it comes back */
		if ($response['reason'] != 'Okay')
		{
			$this->registry->getClass('output')->showError( $this->lang->words['api_failed'],"<a href='".$this->kb."2027-r27'>2027</a>", false, '2027' );
		}
	}

	/* Pass the name of the medal, we'll return the ID */
	public function matchMedalToID($passedMedal)
	{
		/* Load cache for keys */
		if( !$this->caches['metadata'] )
		{
			$this->caches['metadata'] = $this->cache->getCache('metadata');
		}

		/* long long long */
		foreach($this->caches['metadata']['medals'] as $medal)
		{
			/* Check if we exist */
			if ($medal['Value']['Name'] == $passedMedal) {
				return $medal['Value']['Id'];
			}
		}
		/* ouch, we found nothing */
		return -1;
	}

	public function getTheChallenges()
	{
		$this->getChallenges(true);
	}

	public function getChallenges($force = false)
	{
		/* Need Library for this */
		require_once (IPSLib::getAppDir('reachstat') . '/sources/library.php');
		$this->library = new Library($this->registry);

		/* get api key manually */
		$this->api_key = ipsRegistry::$settings['api_key'];

		if (($this->api_key != null))
		{

		//---------------------------------------------
		// Try and load CACHE, otherwise redo
		//---------------------------------------------

		/* Load cache for keys */
		if( !$this->caches['challenges'] && $force == false )
		{
			$this->caches['challenges'] = $this->cache->getCache('challenges');

			/* send back */
			return $this->caches['challenges'];
		}

		/* Engine vrooom */
		$this->_initCURL();

		/* check for api key noob */
		if (($this->api_key == null))
		{
			if (!(IN_ACP))
			{
				$this->registry->getClass('output')->showError( $this->lang->words['no_api_key'],"<a href='".$this->kb."2003-r4'>2003</a>", false, '2003' );
			}
		}

		/* Lets start our JSON readout */
		$tempURL = 'http://www.bungie.net/api/reach/reachapijson.svc/game/challenges/'
			. $this->api_key;

		/* check for bad api key */
		$http_code = $this->library->get_http_response_code($tempURL);

		/* boot us out of here */
		if ($http_code != "200")
		{
			if (!(IN_ACP))
			{
				$this->registry->getClass('output')->showError( $this->lang->words['reponse_failed'],"<a href='".$this->kb."2028-r30'>2028</a>", false, '2028' );
			}
		}


		/* Download it via fileManage, then decode it */
		$information = json_decode($this->fileManage->getFileContents($tempURL), true);

		/* make sure that api key works */
		$this->apiCheck($information);

		/* init vars */
		$k = 0;

		/* foreach */
		foreach ($information['Daily'] as $daily){

			/* Check for daily */
			if ($daily['IsWeeklyChallenge'] == false)
			{
				$this->challenges[$k]['cR']      = $this->registry->getClass('class_localization')->formatNumber(intval($daily['Credits']));
				$this->challenges[$k]['des']     = $daily['Description'];
				$this->challenges[$k]['expires'] = $this->_getDate($daily['ExpirationDate']);
				$this->challenges[$k]['name']    = $daily['Name'];
				$this->challenges[$k]['type']	 = 'daily';

				/* ++ */
				$k++;
			}
			/* What is a weekly doing in the daily :o */
			else if($daily['IsWeeklyChallenge'] == true)
			{
				$this->challenges[$k]['cR']      = $this->registry->getClass('class_localization')->formatNumber(intval($daily['Credits']));
				$this->challenges[$k]['des']     = $daily['Description'];
				$this->challenges[$k]['expires'] = $this->_getDate($daily['ExpirationDate']);
				$this->challenges[$k]['name']    = $daily['Name'];
				$this->challenges[$k]['type']	 = 'weekly';
			}

		}
		unset($daily);

		/* foreach again */
		foreach ($information['Weekly'] as $weekly)
		{
			/* Check for weekly */
			if ($weekly['IsWeeklyChallenge'] == true)
			{
				$this->challenges[$k]['cR']      = $this->registry->getClass('class_localization')->formatNumber(intval($weekly['Credits']));
				$this->challenges[$k]['des']     = $weekly['Description'];
				$this->challenges[$k]['expires'] = $this->_getDate($weekly['ExpirationDate']);
				$this->challenges[$k]['name']    = $weekly['Name'];
				$this->challenges[$k]['type']	 = 'weekly';

				/* ++ */
				$k++;
			}
			/* What is a daily doing in here? */
			else if($weekly['IsWeeklyChallenge'] == false)
			{
				$this->challenges[$k]['cR']      = $this->registry->getClass('class_localization')->formatNumber(intval($weekly['Credits']));
				$this->challenges[$k]['des']     = $weekly['Description'];
				$this->challenges[$k]['expires'] = $this->_getDate($weekly['ExpirationDate']);
				$this->challenges[$k]['name']    = $weekly['Name'];
				$this->challenges[$k]['type']	 = 'daily';
			}
		}

		//--------------------------------------------------
		// CACHE THIS JUNK
		//--------------------------------------------------
		/* set cache */
		$this->cache->setCache( 'challenges', $this->challenges,  array( 'array' => 1, 'donow' => 1 ) );

		/* cleanup */
		if ($force != true) {
			return($this->challenges);
		}
	}
	}

	/**
	 * Returns human name of a digit
	 *
	 * reachStats::getKey()
	 * @access public
	 * @param  int     ID to be checked
	 * @param  string  Category
	 * @param  bool    Weather to return just Name or full Array
	 * @return
	 */
	public function getKey($key, $type, $full=false)
	{
		/* Load our cache */
		if( !$this->caches['metadata']['weapons'] )
		{
			/* Rebuild it, then get it */
			$this->cache->rebuildCache('metadata','reachstat');
			$this->caches['metadata'] = $this->cache->getCache('metadata');
		}

		/* Make sure they did something right */
		if (in_array($type,array('commendations','maps','medals','weapons','variants','ranks')) == true)
		{
			//$this->registry->getClass('output')->showError('type: ' + $type + " key: " + $key, "<a href='".$this->kb."2029-r31'>2029</a>",false,'2029');
		}

		/* Check if we should return entire array */
		if ($full == true)
		{
			return $this->caches['metadata'][$type][$key];
		}
		else
		{
			/* Bug fix for ranks */
			if ($type == 'ranks')
			{
				return $this->caches['metadata'][$type][$key]['DisplayName'];
			}
			/* Bug fix for variants */
			if ($type == 'variants')
			{
				return $this->caches['metadata'][$type][$key]['Key'];
			}
			/* Send the name that matches ID */
			return $this->caches['metadata'][$type][$key]['Value']['Name'];
		}
		break;
	}


	/*
	 * $number -> Value of commendations
	 * $id     -> ID of that commendation
	 *
     */
	public function matchCommToImg($number, $id)
	{
		/* First lets figure out which commendation were dealing with */
		$comm = $this->getKey($id,'commendations', true);


	}

	/**
	 * reachStats::doItAll()
	 * @params ID
	 * @params Task or not
	 * @return
	 */
	public function doItAll($id, $task = false)
	{
		/* test this junk */
		//$this->matchCommToImg(10123,6);

		/* get api key manually */
		$this->api_key = ipsRegistry::$settings['api_key'];

		/* int plox */
		$id = intval($id);

		/* Load cache for keys */
		if( !$this->caches['metadata'] )
		{
			$this->caches['metadata'] = $this->cache->getCache('metadata');
		}

		/* Need Library for this */
		require_once (IPSLib::getAppDir('reachstat') . '/sources/library.php');
		$this->library = new Library($this->registry);

		/* BuildAndFetch */
		$result = $this->DB->buildAndFetch(array(
			'select' => 'id,gamertag,stat_date,sig_date,inactive,ip_address',
			'from' => 'reachstat',
			'where' => "id='" . intval($id) . "'"));

		/* Don't check if were testing */
		if ($this->memberData['member_id'] != 1 || $task != true)
		{
			/* Check for inactivity */
			if ($result['inactive'] == 1)
			{
				/* Throw error if inactive */
				if($this->memberData['member_id'])
				{
					/* get rid of error if inactivity is off */
					if(!(ipsRegistry::$settings['inactive_flag'])
					{
						$this->registry->getClass('output')->showError($this->lang->words['gt_inactive'], "<a href='".$this->kb."2023-r25'>2023</a>",false,'2023');
					}
				}
			}
		}

		/* Engine vrooom */
		$this->_initCURL();

		/* Keep one parsed for passing in URLs */
		$gt = $this->ParseGT($result['gamertag']);
		$this->data['gt'] = $this->unParseGT($gt);
		$this->data['mem_id'] = intval($id);

		//------------------------------------------------
		// Verify if GT exists
		//------------------------------------------------
		//if ($this->checkGTExists($this->data['gt'],false ) == true);
		//{
			/* Only error if not using the task */
			//if ($task == false) {
					#error out
				//}
		//}

		//------------------------------------------------
		// LOAD nostats in order to get basic stuff
		//------------------------------------------------

		/* Lets start our JSON readout */
		$tempURL = 'http://www.bungie.net/api/reach/reachapijson.svc/player/details/nostats/'
			. $this->api_key . "/" . rawurlencode($this->data['gt']);

		/* check for bad api key */
		$http_code = $this->library->get_http_response_code($tempURL);

		/* boot us out of here */
		if ($http_code != "200")
		{
			$this->registry->getClass('output')->showError( $this->lang->words['reponse_failed'],"<a href='".$this->kb."2028-r30'>2028</a>", false, '2028' );
		}

		/* Download it via fileManage, then decode it */
		$information = json_decode($this->fileManage->getFileContents($tempURL), true);

		/* Only do this once */
		$this->data['currentRank'] 		  = $this->_getCurGlobalRank($information['CurrentRankIndex']);
		$this->data['currentRankIndex']   = $information['CurrentRankIndex'];
		$this->data['rank_id']	  		  = $this->_findRankID($this->data['currentRankIndex']);
		$this->data['emblem']      		  = $this->_getEmblem($information['Player']['ReachEmblem']);
		$this->data['spartan']	   		  = 'http://www.bungie.net' . $information['PlayerModelUrl'];
		$this->data['lastActive']  		  = $this->_getDate($information['Player']['last_active']);
		$this->data['firstActive'] 		  = $this->_getDate($information['Player']['first_active']);
		$this->data['serviceTag']  		  = $information['Player']['service_tag'];
		$this->data['daily_ch_complete']  = $information['Player']['daily_challenges_completed'];
		$this->data['weekly_ch_complete'] = $information['Player']['weekly_challenges_completed'];
		//$this->data['gamesPlayed'] 		  = $information['Player']['games_total'];
		$this->data['armorCompletion']	  = $this->_getArmoury($information['Player']['armor_completion_percentage']);

		/* Commendations */
		foreach ($information['Player']['CommendationState'] as $common)
		{

			/* Check for 0, then skip */
			if ($common['Value'] == 0) {
				continue;
			}

			/* Store value of it */
			$this->commendations[$common['Key']]['value'] = $common['Value'];

			/* Store key of it */
			$this->commendations[$common['Key']]['Key'] = $common['Key'];

			/* Now store the name of it, using sweet function */
			$this->commendations[$common['Key']]['Name'] = $this->getKey($common['Key'],'commendations');

		}
		/* Re allign IDs */
		ksort($this->commendations);

		//-------------------------------------------
		// START: Spartan & Emblem Storage
		//-------------------------------------------

		if ($tester != 1)
		{
			/* Spartan */
			$pathToSpartan = DOC_IPS_ROOT_PATH . 'reach/spartans/' . $id . '-spartan.png';

			/* go */
			$spartan = $this->fileManage->getFileContents($this->data['spartan']);

			/* Open the Stream */
			$fp = fopen($pathToSpartan, 'w');

			/* Write then Close */
			fwrite($fp, $spartan);
			fclose($fp);

			/* bi bi */
			unset($spartan);
			unset($pathToSpartan);
			unset($fp);
		}

		//-------------------------------------------
		// END: Spartan & Emblem Storage
		//-------------------------------------------


		/* Lets start our JSON readout */
		$tempURL = 'http://www.bungie.net/api/reach/reachapijson.svc/player/details/byPlaylist/'
			. $this->api_key . "/" . rawurlencode($this->data['gt']);

		/* Download it via fileManage, then decode it */
		$data = json_decode($this->fileManage->getFileContents($tempURL), true);

		/* data contains lots of stuff now */
		foreach ($data['StatisticsByPlaylist'] as $playlist){

			/* Check for our playlists */
			if ($playlist['VariantClass'] == 3 || $playlist['VariantClass'] == 2 || $playlist['VariantClass'] == 1) {

				/* Now lets add the static stuff, then proceed on */
				$this->data['totalMedals']    += $playlist['TotalMedals'];
				$this->data['totalKills']     += $playlist['total_kills'];
				$this->data['totalDeaths']    += $playlist['total_deaths'];
				$this->data['totalBetrayals'] += $playlist['total_betrayals'];
				$this->data['totalWins']      += $playlist['total_wins'];
				$this->data['gamesPlayed']    += $playlist['game_count'];
				$this->data['totalAssists']   += $playlist['total_assists'];

				/* debug for kills/deaths */
				if ($this->debug)
				{
					IPSDebug::fireBug( 'info', $playlist['game_count'] . " for " . $this->getKey($playlist['VariantClass'],'variants',false) );
					IPSDebug::fireBug( 'info', 'Total Kills Running: ' .$this->data['totalKills'] . ' and specifially ' . $playlist['total_kills'] . ' added for ' . $this->getKey($playlist['VariantClass'],'variants',false) );
					IPSDebug::fireBug( 'info', 'Total Deaths Running: ' .$this->data['totalDeaths'] . ' and specifially ' . $playlist['total_deaths'] . ' added for ' . $this->getKey($playlist['VariantClass'],'variants',false) );
				}
				/* We add total seconds together here, then at the end convert */

				//IPSDebug::fireBug('info','We are at ' . $this->data['totalPlaytime'] . " before");
				$this->data['totalPlaytime'] += $this->_getTimePlayed($playlist['total_playtime']);
				//IPSDebug::fireBug('info','We are at ' . $this->library->time_duration($this->_getTimePlayed($playlist['total_playtime'])) . " then total " . $this->data['totalPlaytime'] . " after on the playlist: " . $playlist['HopperId']);

				//---------------------------------------------------
				// Weapons Kills & Deaths
				//---------------------------------------------------

				foreach ($playlist['DeathsByDamageType'] as $death)
				{


					/* Check for 0 to save processing */
					if ($death['Value'] == 0) {

						/* then skip */
						continue;
					}
					else
					{
						/* add key */
						$this->weapons[$death['Key']]['Key'] = $death['Key'];

						/* Add deaths to the key of the weapon */
						$this->weapons[$death['Key']]['deaths'] += $death['Value'];

						/* Check if it needs the name */
						if ($this->weapons[$death['Key']]['name'] == null)
						{

							/* Now get the name */
							$this->weapons[$death['Key']]['name'] = $this->getKey($death['Key'],'weapons');

							/* Check for vehicle */
							if ($this->containsVehicleWord($this->weapons[$death['Key']]['name']) == true )
							{
								$this->weapons[$death['Key']]['type'] = 'vehicle';
							}
							else
							{
								$this->weapons[$death['Key']]['type'] = 'weapon';
							}
						}
					}
				}

				/* Now for the kills */
				foreach ($playlist['KillsByDamageType'] as $kill )
				{
					/* Check for 0, then skip */
					if ($kill['Value'] == 0) {

						/* skip */
						continue;
					}
					else
					{
						/* add key */
						$this->weapons[$kill['Key']]['Key'] = $kill['Key'];

						/* Add the kills */
						$this->weapons[$kill['Key']]['kills'] += $kill['Value'];

						/* Check if we need to get name */
						if ($this->weapons[$kill['Key']]['name'] == null)
						{

							/* get the name then */
							$this->weapons[$kill['Key']]['name'] = $this->getKey($kill['Key'],'weapons');

							/* Check for vehicle */
							if ($this->containsVehicleWord($this->weapons[$kill['Key']]['name']) == true )
							{
								$this->weapons[$kill['Key']]['type'] = 'vehicle';
							}
							else
							{
								$this->weapons[$kill['Key']]['type'] = 'weapon';
							}

						}
					}
				}
				//----------------------------------------
				// Medals
				//----------------------------------------
				foreach ($playlist['MedalCountsByType'] as $medal)
				{
					/* Check for 0, then skip */
					if ($medal['Value'] == 0) {

						/* skip */
						continue;
					}
					else
					{
						/* Store value of it */
						$this->medals[$medal['Key']]['value'] += $medal['Value'];

						/* only do once */
						if ($this->medals[$medal['Key']]['Key'] == 0)
						{
							/* Now store the name of it, using sweet function */
							$this->medals[$medal['Key']]['Name'] = $this->getKey($medal['Key'],'medals');
						}

						/* Store key of it */
						$this->medals[$medal['Key']]['Key'] = $medal['Key'];
					}
				}

				/* sort */
				ksort($this->medals);
				arsort($this->medals);
			}
			else
			{
				/* skip this one, it doesn't equal gametype 1,2,3 (Arena, Competitive, Invasion) */
				continue;
			}

		}

		/* get total time */
		$this->data['totalPlaytime'] = $this->library->time_duration($this->data['totalPlaytime'],'yMwdhm');

		/* work something out for games played */
		$this->data['gamesLost'] = ($this->data['gamesPlayed'] - $this->data['totalWins']);

		/* Get KD */
		$this->data['kd_ratio']  = round($this->data['totalKills'] / $this->data['totalDeaths'],2);

		/* Lets run some cleanup */
		unset($common);
		unset($death);
		unset($kill);
		unset($information);

		//------------------------------------------------
		// K/D Ratio for Weapons
		//------------------------------------------------
		foreach ($this->weapons as $weap){

			/* Lets keep a running K/D */
			if (array_key_exists('deaths', $weap) && intval($weap['deaths']) != 0) {

				/* Double check */
				if (array_key_exists('kills', $weap) && intval($weap['kills']) != 0) {

					/* Divide kills by deaths */
					$this->weapons[$weap['Key']]['ratio'] = round(($weap['kills'] / $weap['deaths']),2);
				}
				else
				{
					/* Kills is 0, but deaths is not */
					$this->weapons[$weap['Key']]['ratio'] = -(intval(($weap['deaths'])));

					/* make kills column for templates */
					$this->weapons[$weap['Key']]['kills'] = 0;
				}

			}
			else
			{
				/* No deaths, so ratio is kills */
				$this->weapons[$weap['Key']]['ratio'] = $weap['kills'];

				/* make deaths column for template */
				$this->weapons[$weap['Key']]['deaths'] = 0;
			}
		}

		/* Few done with all the kills/deaths */
		ksort($this->weapons);

		/* clean */
		unset($medal);
		unset($tempURL);
		unset($weap);
		unset($playlist);

		//------------------------------------------------
		// BEFORE WE SAVE. SET THE LEADERBOARDS
		//------------------------------------------------
		$this->_setLeaderboards(&$this->data, $this->weapons);
		
		/* Save all this crap we got */
		$postBack = $this->saveReachData($gt, $id, &$this->data, &$this->weapons, &$this->medals, &$this->commendations, $task);

		/* Pull what we want from $this->data  */
		$tempData = $postBack;
		$tempData['gt'] = $this->data['gt'];
		$tempData['mem_id'] = $this->data['mem_id'];
		$tempData['currentRankIndex'] = $this->data['currentRankIndex'];
		$tempData['emblem'] = $this->data['emblem'];
		$tempData['spartan'] = $this->data['spartan'];
		$tempData['rank_id'] = $this->data['rank_id'];

		/* moar memmmory */
		unset($postBack);
		unset($this->data);

		/* join back together */
		//$tempData['info'] = $this->data;
		$tempData['stats'] = $this->weapons;
		$tempData['medals'] = $this->medals;
		$tempData['commendations'] = $this->commendations;

		/* we want our kilos back :o */
		unset($this->weapons);
		unset($this->commendations);
		unset($this->medals);

		//IPSDebug::fireBug( 'info', 'Ending Kills: ' . $this->data['totalKills']);
		//IPSDebug::fireBug( 'info', 'Ending Deaths: ' . $this->data['totalDeaths']);

		/* send meh back */
		return $tempData;
	}


	public function containsVehicleWord($string)
	{
		/* List of vehicle words */
		$words['0'] = 'warthog';
		$words['1'] = 'scorpion';
		$words['2'] = 'banshee';
		$words['3'] = 'ghost';
		$words['4'] = 'mongoose';
		$words['5'] = 'revenant';
		$words['6'] = 'wraith';

		/* string lower */
		$string = strtolower($string);

		/* Loop for each word */
		for ($i = 0; $i < count($words); $i++)
		{
			/* bug */
			$pattern = '/';
			$pattern .= $words[$i];
			$pattern .= '/';

			/* Check if it exists */
			if (preg_match($pattern, $string)) {

				/* break out */
				return true;
			}
			else
			{
				$flag = false;
			}
		}
		/* go home */
		return $flag;
	}

	public function _getPoints($gt, $id, $data)
	{
		/* This will get the GT / ID, and return Pts value */
	}

	private function _getTimePlayed($time)
	{

		/* Check for days, hours, minutes, seconds */
		if (preg_match('/P(?P<days>[0-9]*)DT(?P<hours>[0-9]*)H(?P<minutes>[0-9]*)M(?P<seconds>[0-9]*)S/s', $time, $regs)) {

			/* now dump */
			$totalSeconds = ($regs['days'] * 86400) + ($regs['hours'] * 3600) + ($regs['minutes'] * 60) + $regs['seconds'];
		}
		else
		{

			/* Check for days, hours, minutes */
			if (preg_match('/P(?P<days>[0-9]*)DT(?P<hours>[0-9]*)H(?P<minutes>[0-9]*)M/s', $time, $regs)) {

				/* now dump */
				$totalSeconds = ($regs['days'] * 86400) + ($regs['hours'] * 3600) + ($regs['minutes'] * 60);
			}
			else
			{

				/* Break down this code */
				if (preg_match('/PT(?P<hours>[0-9]*)H(?P<minutes>[0-9]*)M(?P<seconds>[0-9]*)S/s', $time, $regs)) {

					/* now dump */
					$totalSeconds = ($regs['hours'] * 3600) + ($regs['minutes'] * 60) + $regs['seconds'];

				}
				else
				{
					/* Check for days and minutes */
					if (preg_match('/P(?P<days>[0-9]*)DT(?P<minutes>[0-9]*)M(?P<seconds>[0-9]*)S/s', $time, $regs)) {

						/* now dump */
						$totalSeconds = ($regs['days'] * 86400) + ($regs['minutes'] * 60) + $regs['seconds'];
					}
					else
					{

						/* Check for minutes and seconds */
						if(preg_match('/PT(?P<minutes>[0-9]*)M(?P<seconds>[0-9]*)S/s', $time, $regs)) {

							/* now dump */
							$totalSeconds = ($regs['minutes'] * 60) + $regs['seconds'];
						}
						else
						{
							/* Check for hours and seconds */
							if (preg_match('/PT(?P<hours>[0-9]*)H(?P<seconds>[0-9]*)S/s', $time, $regs)) {

								/* now dump */
								$totalSeconds = ($regs['hours'] * 3600) + $regs['seconds'];
							}
							else
							{

								/* error not found */
								$totalSeconds = -1;
							}
						}

					}
				}
			}
		}

		//IPSDebug::fireBug('info',$time . ' converts to ' . $totalSeconds . ' and that becomes ' . $this->library->time_duration($totalSeconds));
		//$this->tempNum += $totalSeconds;
		//IPSDebug::fireBug('info', $temp . ' running total is now ' . $this->library->time_duration($this->tempNum));

		/* Send back */
		return $totalSeconds;

	}

	private function _getEmblem($subject, $size = 70)
	{

		/* check for array */
		if (is_array($subject))
		{
			/* thanks: http://www.haloreachapi.net/wiki/Emblems */

			/* Emblem to Link */
			$bi = $subject['background_index'];
			$fi = $subject['foreground_index'];
			$fl = $subject['flags'] ? 0 : 1;
			$c0 = $subject['change_colors'][0];
			$c1 = $subject['change_colors'][1];
			$c2 = $subject['change_colors'][2];
			$c3 = $subject['change_colors'][3];

			/* Send it back */
			return "http://www.bungie.net/Stats/emblem.ashx?s=$size&0=$c0&1=$c1&2=$c2&3=$c3&fi=$fi&bi=$bi&fl=$fl&m=3";

		} else
		{
			return "";
		}
	}

	private function _getDate($subject)
	{
		/* Time to mess with this wierd looking string	 */
		if (preg_match('%/Date.(?P<date>[0-9]*)-%s', $subject, $regs)) {

			/* Delete those trailing zeros */
			$piece['date'] = substr($regs['date'], 0, -3);

			/* Get it in clean version */
			$piece['dateClean'] = date("F j, Y, g:i a",$piece['date']);

			/* Send it back */
			return $piece;

		} else {
			return array();
		}

	}

	private function saveReachData($gt, $id, $data = array(), $weapons = array(), $medals = array(), $commendations = array(), $task = false)
	{
		//--------------------------------------------
		// START: Hacked Improper Fix
		//--------------------------------------------
			$tempNess = array ();

			$tempNess['rank']              = $data['currentRank'];
			$tempNess['rank_id']		   = $data['rank_id'];
			$tempNess['last_active']       = $data['lastActive']['date'];
			$tempNess['games_played']      = $data['gamesPlayed'];
			$tempNess['games_won']         = $data['totalWins'];
			$tempNess['games_lost']        = $data['gamesLost'];
			$tempNess['total_kills']       = $data['totalKills'];
			$tempNess['total_deaths'] 	   = $data['totalDeaths'];
			$tempNess['total_betrayals']   = $data['totalBetrayals'];
			$tempNess['daily_challenges']  = $data['daily_ch_complete'];
			$tempNess['weekly_challenges'] = $data['weekly_ch_complete'];
			$tempNess['total_playtime']    = $data['totalPlaytime'];
			$tempNess['total_medals']      = $data['totalMedals'];
			$tempNess['first_played']      = $data['firstActive']['date'];
			$tempNess['armory_completion'] = $data['armorCompletion'];
			$tempNess['win_percent']       = round(($data['totalWins'] / $data['gamesPlayed']),2);
			$tempNess['kd_ratio']          = $data['kd_ratio'];
			$tempNess['total_assists']     = $data['totalAssists'];
			$tempNess['service_tag']       = $data['serviceTag'];

			/* Now remove old parts of array, again. very hacky */
			unset($data['currentRank'], $data['gamesPlayed'],$data['totalWins'],$data['lastActive'],
				$data['gamesLost'], $data['totalKills'], $data['totalDeaths'], $data['totalBetrayals'],
				$data['daily_ch_complete'], $data['weekly_ch_complete'], $data['totalPlaytime'],
				$data['totalMedals'], $data['firstActive'], $data['armorCompletion'], $data['kd_ratio'],
				$data['totalAssists'], $data['serviceTag'], $data['rank_id']);

		//--------------------------------------------
		// END: Hacked Improper Fix
		//--------------------------------------------

		/* Serialize these arrays for storage */
		$serialData 		 = serialize($data);
		$serialWeaps 		 = serialize($weapons);
		$serialMedals 		 = serialize($medals);
		$serialCommendations = serialize($commendations);

		//------------------------------------------------------
		// Is this the task running?
		//------------------------------------------------------
		if (($task == true)) {
			/* o teh nos. We exist. Update nao */
			$this->DB->update('reachstat', array(
			'id' 			 => intval($id),
			'gamertag'  	 => $gt,
			'rank'			 => $tempNess['rank'],
			'rank_id'		 => $tempNess['rank_id'],
			'last_active'	 => $tempNess['last_active'],
			'games_played'   => $tempNess['games_played'],
			'games_won'	  	 => $tempNess['games_won'],
			'games_lost'	 => $tempNess['games_lost'],
			'total_kills'	 => $tempNess['total_kills'],
			'total_deaths'   => $tempNess['total_deaths'],
			'total_betrayals'  => $tempNess['total_betrayals'],
			'daily_challenges' => $tempNess['daily_challenges'],
			'weekly_challenges'=> $tempNess['weekly_challenges'],
			'total_playtime'   => $tempNess['total_playtime'],
			'total_medals'	   => $tempNess['total_medals'],
			'total_assists'	   => $tempNess['total_assists'],
			'first_played'	   => $tempNess['first_played'],
			'armory_completion'=> $tempNess['armory_completion'],
			'win_percent'	 => $tempNess['win_percent'],
			'kd_ratio'		 => $tempNess['kd_ratio'],
			'service_tag'	 => $tempNess['service_tag'],
 			'data' 			 => $serialData,
			'weapons'		 => $serialWeaps,
			'medals'		 => $serialMedals,
			'commendations'  => $serialCommendations,
			'stat_date'   	 => time()),
			"id=" . intval($id));
		}
		else
		{

			/* o teh nos. We exist. Update nao */
			$this->DB->update('reachstat', array(
				'id' 			=> intval($id),
				'gamertag'  	=> $gt,
				'rank'			 => $tempNess['rank'],
				'rank_id'		 => $tempNess['rank_id'],
				'last_active'	 => $tempNess['last_active'],
				'games_played'   => $tempNess['games_played'],
				'games_won'	  	 => $tempNess['games_won'],
				'games_lost'	 => $tempNess['games_lost'],
				'total_kills'	 => $tempNess['total_kills'],
				'total_deaths'   => $tempNess['total_deaths'],
				'total_betrayals'  => $tempNess['total_betrayals'],
				'daily_challenges' => $tempNess['daily_challenges'],
				'weekly_challenges'=> $tempNess['weekly_challenges'],
				'total_playtime'   => $tempNess['total_playtime'],
				'total_medals'	   => $tempNess['total_medals'],
				'total_assists'	   => $tempNess['total_assists'],
				'first_played'	   => $tempNess['first_played'],
				'armory_completion'=> $tempNess['armory_completion'],
				'win_percent'	 => $tempNess['win_percent'],
				'kd_ratio'		 => $tempNess['kd_ratio'],
				'service_tag'	 => $tempNess['service_tag'],
				'data' 			=> $serialData,
				'weapons'		=> $serialWeaps,
				'medals'		=> $serialMedals,
				'commendations' => $serialCommendations,
				'stat_date'   	=> time(),
			    'ip_address'	=> IPSText::alphanumericalClean($_SERVER['REMOTE_ADDR'],'.')),
			"id=" . intval($id));

			return $tempNess;
		}
	}

	/**
	 * reachStats::getReachData()
	 *
	 * @params memberID
	 * @return unserialized data array of information
	 */
	public function getReachData($id)
	{
		$result = $this->DB->buildAndFetch(array(
			'select' => '*',
			'from' => 'reachstat',
				'where' => "id='" . intval($id) .
		    "'"));
		/* ABANDON */
		die("use getAllReachData");


		/* Unserialize, then pass back */
		return (unserialize($result['data']));
	}

	public function getAllReachData($id, $type = 'all')
	{
		/* Need Library for this */
		require_once (IPSLib::getAppDir('reachstat') . '/sources/library.php');
		$this->library = new Library($this->registry);

		/* Switch for the different types of data */
		switch($type){
			case 'all':
				$result = $this->DB->buildAndFetch(array(
					'select' => 'rank,last_active,games_played,games_won,games_lost,total_kills,total_deaths,total_betrayals,
						daily_challenges,weekly_challenges,total_playtime,total_medals,first_played,armory_completion,win_percent,
						kd_ratio,total_assists,service_tag, data,weapons,medals,commendations,settings, rank_id',
					'from' => 'reachstat',
					'where' => "id='" . intval($id) .
				"'"));

				//------------------------------------------------------
				// Mega ARRAY is MEGA
				//
				// Due to not using TEXT anymore.
				//------------------------------------------------------

				$tempData 					= unserialize($result['data']);
				$tempData['rank']              = $result['rank'];
				$tempData['rank_id']	 	   = $result['rank_id'];
				$tempData['last_active']       = $this->library->time_duration((time() - $result['last_active']),'yMwdhm') . " ago."; #time since played
				$tempData['games_played']      = $result['games_played'];
				$tempData['games_won']         = $result['games_won'];
				$tempData['games_lost']        = $result['games_lost'];
				$tempData['total_kills']       = $result['total_kills'];
				$tempData['total_deaths'] 	   = $result['total_deaths'];
				$tempData['total_betrayals']   = $result['total_betrayals'];
				$tempData['daily_challenges']  = $result['daily_challenges'];
				$tempData['weekly_challenges'] = $result['weekly_challenges'];
				$tempData['total_playtime']    = $result['total_playtime'];
				$tempData['total_medals']      = $result['total_medals'];
				$tempData['first_played']      = date("F j, Y, g:i a", $result['first_played']); #Change to neat timestamp
				$tempData['armory_completion'] = $result['armory_completion'];
				$tempData['win_percent']       = $result['win_percent'] . "%";
				$tempData['kd_ratio']          = $result['kd_ratio'];
				$tempData['total_assists']     = $result['total_assists'];
				$tempData['service_tag']       = $result['service_tag'];
				$tempData['stats'] 			   = unserialize($result['weapons']);
				$tempData['medals']			   = unserialize($result['medals']);
				$tempData['commendations']     = unserialize($result['commendations']);
				$tempData['settings']          = unserialize($result['settings']);

				/* go home */
				return $tempData;

				break;
			case 'stats':
				$result = $this->DB->buildAndFetch(array(
					'select' => 'data',
					'from' => 'reachstat',
					'where' => "id='" . intval($id) .
				"'"));

				/* return just stats */
				return unserialize($result['data']);

				/* break out */
				break;


			default:
				break;
		}
	}

	public function findAndCheckID($gt)
	{
		/* Don't trust the user */
		$gtCleaned = $this->ParseGT(($gt));

		/* grab first letter/number for comparison */
		$firstL = substr($gtCleaned,0,1);

		/* Can't search it, so gotta build it */
		$this->DB->build(array(
			'select' => 'gamertag, id',
			'from' => 'reachstat',
			'order' => 'gamertag',
			'where' => "gamertag LIKE '" . $firstL . "%'"));

		/* run */
		$this->DB->execute();

		/* one by one, we will find one */
		while ($r = $this->DB->fetch())
		{
			/* OUR GTs EQUAL YET */
			if (strtoupper($r['gamertag']) === strtoupper($gtCleaned))
			{
				/* Return ID it matched */
				return $r['id'];

			}
		}
		return 0;
	}

	public function findAndCheckGT($id)
	{
		/* intval that junk */
		$id = intval($id);

		/* Have ID, reverse for GT */
		$result = $this->DB->buildAndFetch(array(
		 		'select' => 'gamertag',
		 		'from' => 'reachstat',
				'where' => "id='" . $id .
		     "'"));

		/* Send it back */
		return ($this->unParseGT($result['gamertag']));
	}

	/**
	 * reachStats::unParseGT()
	 *
	 * @return unParse %20 to NULL
	 */
	public function unParseGT($gt)
	{
		return str_replace('%20', ' ', $gt);
	}

	/**
	 * reachStats::ParseGT()
	 *
	 * @return Parse NULL to %20
	 */
	public function ParseGT($gt)
	{
		return str_replace(' ', '%20', $gt);
	}

	public function removeCommas($string)
	{
		return str_replace(",", "", $string);
	}

	public function verifyUser($id)
	{

	}

	public function killUser($id, $gt)
	{
		# We love classes. This is OOOOOP
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
		$this->reach = new reachStats( $this->registry );
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/library.php' );
		$this->library = new Library( $this->registry );
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classImageMaking.php' );
		$this->image = new imageClass( $this->registry );

		/* Remove trace of members table */
		$this->DB->update('members',array(
				'has_reachstat' => intval(0)),
		"member_id=" .intval($userID));

		/* Now clear DB. Per ToS */
		$this->DB->update('reachstat', array(
				'data'	   		=> null,
				'weapons' 		=> null,
				'medals' 		=> null,
				'commendations' => null,
				'id'	   		=> intval($id),
				'inactive'		=> intval(1)),
		"id=".intval($id));

		/* Lets start by removing all dynamic images, by inactivity */
		$sigs = $this->image->doThemAll($id,$gt);

		/* kill sigs */
		unset($sigs);
	}

	/**
	 * reachStats::_getCurGlobalRank()
	 *
	 * @param mixed $information
	 * @return
	 */
	public function _getCurGlobalRank($information)
	{
		return $this->getKey($information,'ranks',false);
	}

	/**
	 * reachStats::_getArmoury()
	 *
	 * @param mixed $information
	 * @return
	 */
	public function _getArmoury($information)
	{
		return round(intval(100 * $information), 2);
	}

	/**
	 * reachStats::_findRankID()
	 *
	 * @param mixed $this
	 * @return
	 */
	private function _findRankID($id)
	{
		switch($id){
			case '0':
				return 0;
			case '1':
				return 1;
			case '2':
				return 2;
			case '3':
				return 3;
			case '4':
				return 4;
			case '5':
				return 5;
			case '6':
				return 6;
			case '7':
				return 7;
			case '8':
				return 8;
			case '9':
				return 9;
			case '10':
				return 10;
			case '11':
				return 11;
			case '12':
				return 12;
			case '13':
				return 13;
			case '14':
				return 14;
			case '15':
				return 15;
			case '16':
				return 16;
			case '17':
				return 17;
			case '18':
				return 18;
			case '19':
				return 19;
			case '20':
				return 20;
			case '21':
				return 21;
			case '22':
				return 22;
			case 'D1B61C33-CED1-4486-898F-1FF2ABB5D903':
				return 23;
			case '46242210-613E-4BBB-BBB2-7BFD213B7C17':
				return 24;
			case '9F5FB05B-397F-4041-9117-44A8F85BDDAF':
				return 25;
			case '8A3CBCDE-C1D3-410B-9164-2A9C81D2884E':
				return 26;
			case '8F898B2D-382D-4CC8-A36B-039604E5AD2D':
				return 27;
			case 'E19508D7-DA71-4A16-8F2A-3EE079288E22':
				return 28;
			case '6611E021-F52A-4670-9C55-0DD1BE93406F':
				return 29;
			case 'A4BF62C6-1E3F-468A-9A73-8237600B2AD3':
				return 30;
			case '199BF309-E033-4C2A-8005-A42C004E176C':
				return 31;
			case '8C9E5E33-4341-4366-A3F6-D7E71099ECA4':
				return 32;
			case '6225A514-823A-495D-A773-842C623FEAB0':
				return 33;
			case 'F4A6BCA9-2471-4C0E-8D91-313D975D23A3':
				return 34;
			case '3EDB9AC5-31D9-4033-A4EF-0AE1AFCF0A15':
				return 35;
			case 'BDCA8FEA-6F34-453D-9AB5-12CBFCC937CA':
				return 36;
			case '21387978-F833-4B54-B71A-FB628D741425':
				return 37;
			case '97E7B7FD-D858-41AC-BE94-0F79D97E9446':
				return 38;
			case 'E50B16A8-EAC9-4D98-B012-3B88338C01C3':
				return 39;
			case '5249F04B-3792-4610-9AAD-9ED5B4BC1704':
				return 40;
			case '889E9CEE-26D3-41A9-BB9D-DFEDB1AFB7AF':
				return 41;
			case 'C3E07E45-8839-4815-A29D-48EC3EB51795':
				return 42;
			case 'BE341DA7-EE2B-40C8-BE8E-129F163B11D2':
				return 43;
			case '773053BB-4635-4ED9-BC24-819E5E2801DF':
				return 44;
			case 'BA18BEFE-483D-4326-B3C6-FDBB0EDD5DCB':
				return 45;
			case '1898E1E0-579A-4122-BAC0-01672487F839':
				return 46;
			case '2BAF7ADE-5693-4C30-80F3-8BB49973186A':
				return 47;
			case '012465F9-E91D-4976-A671-BFCEDD63F371':
				return 48;
			case 'DA19D690-E227-4FDF-81ED-738261746BDB':
				return 49;
			default:
				return -1;
		}
	}

	public function loadLeaderboards()
	{
		/* Load cache for keys */
		if( !$this->caches['leaderboard'] )
		{
			$this->caches['leaderboard'] = $this->cache->getCache('leaderboard');
		}
	}

	public function setLeaderboards()
	{
		//------------------------------------------------
		// Query Mania
		//------------------------------------------------
			$stats = array();
			$i = 1;

		//-------------
		//START RANK
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,rank_id,rank',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'rank_id DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['rank'][0]['name'] = 'Rank';
		$stats['rank'][0]['des']  = 'Highest Rank';

		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['rank'][$i]['num'] = $row['rank'];
			$stats['rank'][$i]['gt']  = $row['gamertag'];
			$stats['rank'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START WIN PERCENT
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,win_percent',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'win_percent DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['wper'][0]['name'] = 'Win Percent';
		$stats['wper'][0]['des']  = 'Best Wining Percentage';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['wper'][$i]['num'] = $row['win_percent'] . '%';
			$stats['wper'][$i]['gt']  = $row['gamertag'];
			$stats['wper'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START KD Ratio
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,kd_ratio',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'kd_ratio DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['kd'][0]['name'] = 'K/D Ratio';
		$stats['kd'][0]['des']  = 'Best Kills / Death';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['kd'][$i]['num'] = $row['kd_ratio'];
			$stats['kd'][$i]['gt']  = $row['gamertag'];
			$stats['kd'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}
		//-------------
		//START ApG
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,ApG',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'ApG DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['apg'][0]['name'] = 'ApG';
		$stats['apg'][0]['des']  = 'Assists per Game';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['apg'][$i]['num'] = $row['ApG'];
			$stats['apg'][$i]['gt']  = $row['gamertag'];
			$stats['apg'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START BpG
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,BpG',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'BpG DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['bpg'][0]['name'] = 'BpG';
		$stats['bpg'][0]['des']  = 'Betrayals per Game';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['bpg'][$i]['num'] = $row['BpG'];
			$stats['bpg'][$i]['gt']  = $row['gamertag'];
			$stats['bpg'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START KpG
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,KpG',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'KpG DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['kpg'][0]['name'] = 'KpG';
		$stats['kpg'][0]['des']  = 'Kills per Game';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['kpg'][$i]['num'] = $row['KpG'];
			$stats['kpg'][$i]['gt']  = $row['gamertag'];
			$stats['kpg'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}
		//-------------
		//START DpG
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,DpG',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'DpG DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['dpg'][0]['name'] = 'DpG';
		$stats['dpg'][0]['des']  = 'Deaths per Game';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['dpg'][$i]['num'] = $row['DpG'];
			$stats['dpg'][$i]['gt']  = $row['gamertag'];
			$stats['dpg'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START SpG
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,SpG',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'SpG DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['spg'][0]['name'] = 'SpG';
		$stats['spg'][0]['des']  = 'Snipes per Game';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['spg'][$i]['num'] = $row['SpG'];
			$stats['spg'][$i]['gt']  = $row['gamertag'];
			$stats['spg'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START MpG
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,MpG',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'MpG DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['mpg'][0]['name'] = 'MpG';
		$stats['mpg'][0]['des']  = 'Medals per Game';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['mpg'][$i]['num'] = $row['MpG'];
			$stats['mpg'][$i]['gt']  = $row['gamertag'];
			$stats['mpg'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START total_games
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,total_games',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'total_games DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['games'][0]['name'] = 'Total Games';
		$stats['games'][0]['des']  = 'Total Games Played';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['games'][$i]['num'] = $this->registry->getClass('class_localization')->formatNumber($row['total_games']);
			$stats['games'][$i]['gt']  = $row['gamertag'];
			$stats['games'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START total_kills
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,total_kills',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'total_kills DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['kills'][0]['name'] = 'Total Kills';
		$stats['kills'][0]['des']  = 'Total Kills earned';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['kills'][$i]['num'] = $this->registry->getClass('class_localization')->formatNumber($row['total_kills']);
			$stats['kills'][$i]['gt']  = $row['gamertag'];
			$stats['kills'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START total_assists
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,total_assists',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'total_assists DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['assists'][0]['name'] = 'Total Assists';
		$stats['assists'][0]['des']  = 'Total Assists Earned';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['assists'][$i]['num'] = $this->registry->getClass('class_localization')->formatNumber($row['total_assists']);
			$stats['assists'][$i]['gt']  = $row['gamertag'];
			$stats['assists'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START total_deaths
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,total_deaths',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'total_deaths DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['deaths'][0]['name'] = 'Total Deaths';
		$stats['deaths'][0]['des']  = 'Total Deaths Earned';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['deaths'][$i]['num'] = $this->registry->getClass('class_localization')->formatNumber($row['total_deaths']);
			$stats['deaths'][$i]['gt']  = $row['gamertag'];
			$stats['deaths'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START total_medals
		//-------------
		$this->DB->build( array(
			'select'		=> 'gamertag,member_id,total_medals',
			'from'			=> 'reach_leaderboard',
			'order'			=> 'total_medals DESC',
			'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['medals'][0]['name'] = 'Total Medals';
		$stats['medals'][0]['des']  = 'Total Medals Obtained';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['medals'][$i]['num'] = $this->registry->getClass('class_localization')->formatNumber($row['total_medals']);
			$stats['medals'][$i]['gt']  = $row['gamertag'];
			$stats['medals'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		//-------------
		//START chest_completion
		//-------------
		$this->DB->build( array(
		'select'		=> 'gamertag,member_id,chest_completion',
		'from'			=> 'reach_leaderboard',
		'order'			=> 'chest_completion DESC',
		'limit'			=> array(0,3)));

		$out = $this->DB->execute();
		$i = 1;

		$stats['chest'][0]['name'] = 'Armory Unlocked';
		$stats['chest'][0]['des']  = 'Percentage of Armory Unlocked';
		/* Bring those in and lets loop em */
		while($row = $this->DB->fetch($out))
		{
			$stats['chest'][$i]['num'] = $row['chest_completion'] . "%";
			$stats['chest'][$i]['gt']  = $row['gamertag'];
			$stats['chest'][$i]['mem_id'] = $row['member_id'];
			$i++;
		}

		/* set cache */
		$this->cache->setCache( 'leaderboard', $stats,  array( 'array' => 1, 'donow' => 1 ) );


	}

	/**
	 * reachStats::_setLeaderboards()
	 *
	 * @param mixed $this
	 * @return
	 */
	public function _setLeaderboards($data, $weapons)
	{

		/* Check for 400 kills and 100 games */
		if (($data['totalKills'] < 400) && ($data['gamesPlayed'] < 100)) {

		}
		else
		{
			$this->DB->replace('reach_leaderboard', array(
				'member_id'		=> intval($data['mem_id']),
				'gamertag'		=> $data['gt'],
				'rank_id'		=> intval($data['rank_id']),
				'rank'			=> $data['currentRank'],
				'win_percent'	=> floatval(((round($data['totalWins'] / $data['gamesPlayed'], 2)) * 100)),
				'kd_ratio'		=> floatval(round($data['totalKills'] / $data['totalDeaths'], 2)),
				'ApG'			=> floatval(round($data['totalAssists'] / $data['gamesPlayed'], 2)),
				'BpG'			=> floatval(round($data['totalBetrayals'] / $data['gamesPlayed'],2)),
				'KpG'			=> floatval(round($data['totalKills'] / $data['gamesPlayed'], 2)),
				'DpG'			=> floatval(round($data['totalDeaths'] / $data['gamesPlayed'], 2)),
				'SpG'			=> floatval(round( $weapons['8']['kills'] / $data['gamesPlayed'],2)),
				'MpG'			=> floatval(round($data['totalMedals'] / $data['gamesPlayed'], 2)),
				'total_games'	=> intval($data['gamesPlayed']),
				'total_kills'	=> intval($data['totalKills']),
				'total_assists'	=> intval($data['totalAssists']),
				'total_deaths'	=> intval($data['totalDeaths']),
				'total_medals'	=> intval($data['totalMedals']),
				'chest_completion' => intval($data['armorCompletion'])),
				array (
					'where'	=> "member_id='".$data['mem_id']."'")
			);
		}
	}
}
