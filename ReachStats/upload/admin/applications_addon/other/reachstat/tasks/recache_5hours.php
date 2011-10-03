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
	/**
	 * Parent task manager class
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $class;

	/**
	 * This task data
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $task			= array();

	/**
	 * Prevent logging
	 *
	 * @access	protected
	 * @var		boolean
	 */
	protected $restrict_log	= false;

	/**#@+
	 * Registry Object Shortcuts
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	/**#@-*/

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
		$this->registry	  = $registry;
		$this->DB		  = $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang		  = $this->registry->getClass('class_localization');
		$this->member	  = $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	  = $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();

		$this->class    	= $class;
		$this->task    		= $task;
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

		//-----------------------------------------
		// Load Members List
		//-----------------------------------------

		echo $argv[1];

		$_max = $this->DB->buildAndFetch( array( 'select' => 'COUNT(*)',
												 'from' => 'reachstat',
												 'where' => 'inactive=0'));



		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------

		$this->class->unlockTask( $this->task );
	}
}