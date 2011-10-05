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

class pointsClass
{
/* Vars */
	protected $curGamertag		 = "";
	protected $gameCount		 = 0;
	protected $id 				 = 0;
	protected $version 			 = HR_VERSION;
	protected $debug			 = DEBUG_MODE;
	protected $data 			 = array ();
	protected $points		     = array ();
	protected $compare			 = array ();
	protected $final			 = array ();

	/*
	   * POINTS EXPLAINED
	   *
	*/
/*
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
	 * pointsClass::saveData()
	 * @params $p1 (array $gt/$id)
	 * @params $p2 (array $gt/$id)
	 * @params $p1data (Player 1 Data Array)
	 * @params $p2data (Player 2 Data Array)
	 * @return
	 */
	public function saveData($p1 = array(), $p2= array(), $p1data= array(), $p2data= array())
	{

	}

	private function _checkCompare($p1, $p2)
	{
		//-----------------------------------------------
		// Generate the MD5 Hash for comparisons
		//-----------------------------------------------

		/* Remove all spaces, strlower, then md5 the combined GTs */
		$cleanP1 = str_replace(" ",null, $p1['gt']);
		$cleanP1 = strtolower($cleanP1);

		/* Now p2 */
		$cleanP2 = str_replace(" ", null, $p2['gt']);
		$cleanP2 = str_replace(" ", null, $cleanP2);

		/* Join em */
		$total = $cleanP1;
		$total .= $cleanP2;

		/* Drop em */
		unset($cleanP2);
		unset($cleanP1);

		/* MD5 it */
		$total = md5($total);


		/* Scan the DB and look for this comparison, using $total */

	}

	/* ID should be p1['id'] and p1['gt'] etc */
	public function compare2People($p1, $p2)
	{

		/* Start this shiz out */
		$start = microtime();

		/* Fill static compare array */
		$this->_fillCompareArray();

		/* Load Player 1 */
		$result = $this->DB->buildAndFetch(array(
					'select' => 'data',
					'from' => 'reachstat',
					'where' => "id='" . intval($p1['id']) . "'"));

		/* Make sure Player 1 can be found */
		if ($result == "") {
			$this->registry->getClass('output')->showError( $this->lang->words['p1_failed'],"2019", false, '2019' );
		}
		else
		{
			/* Dump data to $this->data */
			$this->data = $result['data'];

			/* Init some junk */
			$this->_fillPlayerArray();

			/* Must check validation before running this */
			$this->_validate();

			/* init */
			$num = 0;

			/* Run the foreach */
			foreach ($this->points as $item ){
				$this->final['p1'][$num] = $item['pts'];
				$num++;
			}

			/* clear */
			unset($result);
		}

		/* Load Player 2 */
		$result = $this->DB->buildAndFetch(array(
					'select' => 'data',
					'from' => 'reachstat',
					'where' => "id='" . intval($p2['id']) . "'"));

		/* Make sure Player 2 can be found */
		if ($result == "") {
			$this->registry->getClass('output')->showError( $this->lang->words['p2_failed'],"2020", false, '2020' );
		}
		else
		{
			/* Dump data to $this->data */
			$this->data = $result['data'];

			/* Init some junk */
			$this->_fillPlayerArray();

			/* Must check validation before running this */
			$this->_validate();

			/* init */
			$num = 0;

			/* Run the foreach */
			foreach ($this->points as $item ){
				$this->final['p2'][$num] = $item['pts'];
				$num++;
			}

			/* clear */
			unset($result);
		}

			//-----------------------------------------------
			// Now $this->final contains both comparisons
			//-----------------------------------------------

			/* Loop for each record in $this->final */
			for($x = 0; $x <= $max; $x++)
			{
				/* High or low */
				$check = $this->compare[$x]['type'];

				/* Set place to 1 for winner
				 * 0 for loser
				 */

				/* Sort for high/low */
				switch($check){
					case 0: //Highest Wins

						/* Check if p1 is greater */
						if ($this->final['p1'][$x]['pts'] >= $this->final['p2'][$x]['pts'])
						{
							$this->final['p1'][$x]['place'] = 1;
							$this->final['p2'][$x]['place'] = 0;
						}
						else
						{
							$this->final['p1'][$x]['place'] = 0;
							$this->final['p2'][$x]['place'] = 1;
						}

						break;
					case 1: // Lowest Wins

						/* Check if p1 is lower */
						if ($this->final['p1'][$x]['pts'] <= $this->final['p2'][$x]['pts'])
						{
							$this->final['p1'][$x]['place'] = 1;
							$this->final['p2'][$x]['place'] = 0;
						}
						else
						{
							$this->final['p1'][$x]['place'] = 0;
							$this->final['p2'][$x]['place'] = 1;
						}

						break;
					case 2: // No Compare

						/* Check if p1 is higher, but no compare */
						if ($this->final['p1'][$x]['pts'] >= $this->final['p2'][$x]['pts'])
						{
							$this->final['p1'][$x]['place'] = 1;
							$this->final['p2'][$x]['place'] = 0;
						}
						else
						{
							$this->final['p1'][$x]['place'] = 0;
							$this->final['p2'][$x]['place'] = 1;
						}
						break;
				}

			}

		//-----------------------------------------------
		// Now $this->final now contains winners and losers
		//-----------------------------------------------


		//-----------------------------------------------
		// Generate the MD5 Hash for comparisons
		//-----------------------------------------------

		/* Remove all spaces, strlower, then md5 the combined GTs */
		$cleanP1 = str_replace(" ", null, $p1['gt']);
		$cleanP1 = strtolower($cleanP1);

		/* Now p2 */
		$cleanP2 = str_replace(" ", null, $p2['gt']);
		$cleanP2 = strtolower($cleanP2);

		/* Join em */
		$total = $cleanP1;
		$total .= $cleanP2;

		/* Drop em */
		unset($cleanP2);
		unset($cleanP1);

		/* MD5 it */
		$total = md5($total);

	}

	/* Player comparison fill */
	private function _fillPlayerArray($return)
	{

		/* Load the array */
		$this->points['1'] = array (
				'pts'  => round(($this->data['totalWins'] / $this->data['gamesPlayed']), 2)
				);
		$this->points['2'] = array (
				'pts'  => round(($this->data['totalKills'] / $this->data['totalDeaths']), 2),
				);
		$this->points['3'] = array (
				'pts'  => round(($this->data['totalAssists'] / $this->data['gamesPlayed']), 2),
				);
		$this->points['4'] = array (
				'pts'  => round(($this->data['totalKills'] / $this->data['gamesPlayed']), 2),
				);
		$this->points['5'] = array (
				'pts'  => round(($this->data['totalDeaths'] / $this->data['gamesPlayed']), 2),
				);
		$this->points['6'] = array (
				'pts' => round(($this->data['stats']['8']['kills'] / $this->data['gamesPlayed']), 2),
				);
		$this->points['7'] = array (
				'pts' => ($this->data['gamesPlayed']),
				);
		$this->points['8'] = array (
				'pts' => ($this->data['totalKills']),
				);
		$this->points['9'] = array (
				'pts' => ($this->data['totalAssists']),
				);
		$this->points['10'] = array (
				'pts' => ($this->data['totalDeaths']),
				);
		$this->points['11'] = array (
				'pts' => ($this->data['totalMedals']),
				);
		$this->points['12'] = array (
				'pts' => ($this->data['currentRank']),
				);

		/* We return or not */
		if ($return === true)
		{
			$this->returnPlayerArray();
		}
	}

	public function returnPlayerArray()
	{
		return $this->points;
	}

	public function returnCompareArray()
	{
		return $this->compare;
	}

	public function setCacheCompareArray()
	{
		/* Get our caches */
		$this->fillCompareArray(false, true);

		/* save */
		$this->cache->setCache( 'compare', $this->compare,  array( 'array' => 1, 'donow' => 1 ) );

		/* unset */
		unset($this->compare);
	}

	public function fillCompareArray($return, $force)
	{
		/* See if we can load our cache */
		if( (!$this->caches['compare']) || ($force === true) )
		{

			/*
			   * Big Points beginning
			   * pts = math for the points
			   * name = short form of thing
			   * calc = explain how to get
			   * type 1 = highest 0 = lowest 2= no compare
			*/
			$this->compare['1'] = array(
					'name' => 'Win Percent',
					'calc' => 'Calculated by dividing wins by total games.',
					'type' => 1);

			$this->compare['2'] = array(
					'name' => 'K/D Spread',
					'calc' => 'Calculated by diving total kills by total deaths.',
					'type' => 1);

			$this->compare['3'] = array(
					'name' => 'ApG (Assists Per Game)',
					'calc' => 'Total Assists divided by total games, to get ApG.',
					'type' => 1);

			$this->compare['4'] = array(
					'name' => 'KpG (Kills Per Game)',
					'calc' => 'Total Kills divided by total games, to get KpG.',
					'type' => 1);

			$this->compare['5'] = array(
					'name' => 'DpG (Deaths Per Game)',
					'calc' => 'Total Deaths divided by total games, to get DpG.',
					'type' => 0);

			$this->compare['6'] = array(
					'name' => 'SpG (Snipes Per Game)',
					'calc' => 'Total Sniper Kills divided by total games, to get SpG.',
					'type' => 1);

			$this->compare['7'] = array(
					'name'=> 'Total Games',
					'calc' => 'Combined Games for all playlists.',
					'type' => 2);
			$this->compare['8'] = array(
					'name' => 'Total Kills',
					'calc' => 'Combined Kills for all playlists.',
					'type' => 2);
			$this->compare['9'] = array(
					'name' => 'Total Assists',
					'calc' => 'Combined Assists in all playlists.',
					'type' => 2);
			$this->compare['10'] = array(
					'name' => 'Total Deaths',
					'calc' => 'Combined Deaths in all playlists.',
					'type' => 2);
			$this->compare['11'] = array(
					'name' => 'Total Medals',
					'calc' => 'Combined Medal Count in all playlists.',
					'type' => 2);
			$this->compare['12'] = array(
					'name' => 'Highest Level Reached',
					'calc' => 'Highest Obtained Matchmaking Level',
					'pts' => 2);


			/* Do we send data back to user? */
			if (($return === true))
			{
				$this->returnCompareArray();
			}
		}
		else
		{
				$this->caches['compare'] = $this->cache->getCache('compare');

				/* send back */
				return $this->caches['compare'];

		}
	}

	/* Checks various requirements for comparing */
	private function _validate(){

		/* 100 games */
		if ($this->data['total']['stats']['gameCount'] < 100)
		{
			$this->registry->getClass('output')->showError( $this->lang->words['100_games'],"2021", false, '2021' );
		}
		/* 400 Kills */
		if ($this->data['total']['stats']['totalKills'] < 400)
		{
			$this->registry->getClass('output')->showError( $this->lang->words['400_kills'],"2022", false, '2022' );
		}
	}

	public function getPoints($data)
	{
		/* public now */
		$this->data = $data;
		unset($data);

		/* Fill our data array */
		$this->_fillPlayerArray(false);

		/* Thanks for the data */
		print "<textarea cols='50' rows='20'>"; print_r( $this->points ); print "</textarea>";
		print "<textarea cols='50' rows='20'>"; print_r( $this->data ); print "</textarea>";
		exit();
	}
}
