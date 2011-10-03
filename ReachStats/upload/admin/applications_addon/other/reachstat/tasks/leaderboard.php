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

		/* classes */
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
		$this->reach = new reachStats( $this->registry );

		/* New data */
		$this->reach->setLeaderboards();

		/* do the work */
		$this->cache->rebuildCache('leaderboard','reachstat');

		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------

		$this->class->unlockTask( $this->task );
	}
}