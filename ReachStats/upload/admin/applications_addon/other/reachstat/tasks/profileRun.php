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

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	/* Needed vars */
	protected $class;
	protected $task			= array();
	protected $restrict_log	= false;
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	protected $counter 		= 0;
	protected $tier 		= array();

	/* Other data */
	private $data = array ();

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param 	object		ipsRegistry reference
	 * @param 	object		Parent task class
	 * @param	array 		This task data
	 * @return	void
	 */
	public function __construct( ipsRegistry $registry, $class, $task )
	{
		/* Make registry objects */
		$this->registry	= $registry;
		$this->DB		= $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang		= $this->registry->getClass('class_localization');
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();

		$this->class	= $class;
		$this->task		= $task;
	}

	/**
	 * Run this task
	 *
	 * @access    public
	 * @return    void
	 */
	public function runTask()
	{
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		$this->class->appendTaskLog( $this->task, 'Task ran' );

		/* Load our classes */
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
		require_once (IPSLib::getAppDir('reachstat') . '/sources/library.php');
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classImageMaking.php' );
		$this->image   = new imageClass( $this->registry );
		$this->reach   = new reachStats( $this->registry );
		$this->library = new Library( $this->registry);

		/* Load Language Files */
		$this->registry->getClass('class_localization')->loadLanguageFile( array('public_errors', 'public_reachstat' ));

		/* Temp motion to stop these tasks from screwing up */
		if (!$this->settings['reach_online'])
		{
			exit();
		}

		/* Look for our previous point. If not restart */
		$previous = $this->cache->getCache('reach_lastid');
		$_max     = $this->cache->getCache('reach_maxid');

		/* Check if max is equal to previous. Then redo  */
		if ($previous == $_max)
		{
			$previous = null;
		}

		/* Check if that storage was succesful */
		if (intval($previous) == null)
		{
			$run = true; #re-run from the top.
		}
		else
		{
			$run = false; #re-start from previous
		}

		//-----------------------------------------
		// Load Members List
		//-----------------------------------------

		switch($run){
			case true:
				/* Run through the IDs, and restart */
				$max = $this->DB->buildAndFetch( array(
										'select' 		=> 'COUNT(*)',
										 'from' 		=> 'reachstat',
										 'where' 		=> 'inactive=0'));
				/* Gotta set it */
				$_max = intval($max['COUNT(*)']);
				unset($max);

				/* Lets find the users we want to pull */
				$this->DB->build( array(
					'select' => 'id,gamertag,stat_date,sig_date,inactive,ip_address',
					'from'	 => 'reachstat',
					'where'  => 'inactive=0',
					'order'  => 'id ASC',
					'limit'	 => array (0, intval($this->settings['max_pro_num']))));

				/* Run em */
				$out = $this->DB->execute();

				/* Bring those in and lets loop em */
				while($row = $this->DB->fetch($out))
				{
					/* Now get Tier Data, we might need it */
					$this->tier = $this->library->getUserData($row['id']);

					/* Now we need to see if
					 * A) They need to be recached
					 * B) If they are banned
					 * C) Check for any flags
					 */

					/* Check if banned */
					if ($this->tier['groupID'] == $this->settings['banned_group'])
					{
						continue;
					}

					/* Check if ignored and/or banned */
					if ($row['inactive'] == 3 || $row['inactive'] == 2)
					{
						continue;
					}

					/* Check if they are inactive, but haven't been flagged. */
					if ($this->tier['visit'] > $this->tier['tier']['inactive'])
					{
						/* Now update into Database. */
						$this->DB->update('reachstat', array(
							'id'	   		=> intval($row['id']),
							'inactive'		=> intval(1)),
							"id=" . intval($row['id']));
					}

					//------------------------------------------------------
					// Passed initial tests
					//------------------------------------------------------
					if ($this->tier['data'] > $this->tier['tier']['time_ttl'])
					{
						//------------------------------------------------------
						// Recache their DATA, then SIGS
						//------------------------------------------------------
							$this->cleanGlobals();

							/* data */
							$this->reach->doItAll($row['id'], true);

							/* sigs */
							$this->image->doThemAll($row['id'],	$this->reach->unParseGT($row['gamertag']) );
					}
					/* Check here */
					$lastid = $row['id'];
					$this->counter++;
				}
				break;

			case false:
				/* Use the supplied ID, $previous and restart at that ID */
				$this->DB->allow_sub_select = 1;

				/* Lets find the users we want to pull */
				$this->DB->build( array(
						'select' => 'id,gamertag,stat_date,sig_date,inactive,ip_address',
						'from'	 => 'reachstat',
						'where'  => 'inactive=0',
						'order'  => 'id ASC',
						'limit'	 => array (intval($previous), intval($this->settings['max_pro_num']))));

				/* Run em */
				$out = $this->DB->execute();

				/* Bring those in and lets loop em */
				while($row = $this->DB->fetch($out))
				{
					/* Now get Tier Data, we might need it */
					$this->tier = $this->library->getUserData($row['id']);

					/* Now we need to see if
					   * A) They need to be recached
					   * B) If they are banned
					   * C) Check for any flags
					*/

					/* Check if banned */
					if ($this->tier['groupID'] == $this->settings['banned_group'])
					{
						continue;
					}

					/* Check if ignored and/or banned */
					if ($row['inactive'] == 3 || $row['inactive'] == 2)
					{
						continue;
					}

					/* Check if they are inactive, but haven't been flagged. */
					if ($this->tier['visit'] > $this->tier['tier']['inactive'])
					{
						/* Now update into Database. */
						$this->DB->update('reachstat', array(
							'id'	   			=> intval($row['id']),
							'inactive'			=> intval(1)),
						"id=" . intval($row['id']));
					}

					//------------------------------------------------------
					// Passed initial tests
					//------------------------------------------------------
					if ($this->tier['data'] > $this->tier['tier']['time_ttl'])
					{

						//------------------------------------------------------
						// Recache their DATA, then SIGS
						//------------------------------------------------------

						$this->cleanGlobals();
						/* data */
						$this->reach->doItAll($row['id'], true);

						/* sigs */
						$this->image->doThemAll($row['id'],$row['gamertag'], true );

						/* debug */
						//IPSDebug::addLogMessage($row['id'],'profileRun',$row,true,false);
					}
					/* Check here */
					$lastid = $row['id'];
				}
				break;

			default:
				break;
		}

		/* End.... store last ID */
		$this->cache->setCache('reach_maxid', $_max);
		$this->cache->setCache('reach_lastid', $this->counter);

		$this->class->appendTaskLog( $this->task, 'Task finished.' );

		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------

		$this->class->unlockTask( $this->task );
	}

	public function cleanGlobals()
	{
		/* If running from task clear the globals */
		unset($this->reach->data);
		unset($this->reach->weapons);
		unset($this->reach->medals);
		unset($this->reach->commendations);
		unset($this->reach->i);
		unset($this->image->sigSettings);
		unset($this->image->template);
		unset($this->image->pathToSigs);
		unset($this->image->sigs);
		unset($this->image->allSigs);
		unset($this->image->data);
	}
}