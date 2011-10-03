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

class public_reachstat_display_compare extends ipsCommand
{

	protected $output				= "";
	protected $data 				= array ();
	protected $version              = HR_VERSION;
	protected $times				= array ();
	protected $recache 				= 0;
	protected $diff 				= array();
	protected $id 					= 0;
	protected $curGamertag 			= "";
	protected $debug 				= DEBUG_MODE;
	protected $kb 					= 'http://reachstuff.com/kb/page/';

	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* We love classes. This is OOOOOP */
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/library.php' );
		require_once( IPSLib::getAppDir( 'reachstat') . '/sources/pointsClass.php' );
		$this->points = new pointsClass( $registry );
		$this->library = new Library( $registry );
		$this->reach = new reachStats( $registry );


		/* Switch */
		$this->doFirst();
		$this->normalRun();

		/* Send to the output */
		$this->library->addJS();


		/* Add the template */
		$this->output .= $this->registry->output->getTemplate('stattrack')->comparePage();
		$this->output .= $this->library->addFooter();
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}

	public function normalRun()
	{
		$this->data = $this->reach->getAllReachData($this->id);
		$this->points->getPoints($this->data);
	}

	public function doFirst()
	{
		/* Get their variables now */
		$this->times['gt'] = IPSText::alphanumericalClean($this->request['gt'],' ');
		$this->times['id'] = intval($this->request['mid']);

		/* Work goes on here, if they pass ID but not GT */
		if ($this->times['gt'] == null && $this->times['id'] != null)
		{
			/* Get their GT */
			$this->times['gt'] = $this->reach->findAndCheckGT($this->times['id']);

			/* Back to the redirect */
			$this->registry->output->silentRedirect($this->settings['base_url'] . 'app=reachstat&amp;module=display&amp;section=gamer&amp;gt=' . $this->times['gt'] . '&amp;loc=' . $this->loc);
		}

		/* If Gamertag is blank, they didn't pass it */
		if ($this->times['gt'] == "")
		{
			$this->registry->getClass('output')->showError( $this->lang->words['gt_no_exist'],"2025", false, '2025', '404' );
		}
		else
		{
			$this->registry->output->setTitle($this->lang->words['stat_nav'] . $this->times['gt']);
		}

		/* They passed GT, but does it exist? */
		$check = $this->reach->findAndCheckID($this->times['gt']);

		/* If the its not equal to 0, get it */
		if (!($check == 0))
		{
			/* It exists. Find it */
			$this->id = intval($check);
			$this->times['id'] = intval($check);
		}
		else
		{
			$this->registry->getClass('output')->showError( $this->lang->words['2001'],"2026", false, '2026', '404' );
		}

		/* Get the times for comparing */
		$this->times = $this->library->getUserData($this->id);

		/* Get their variables now */
		$this->times['gt'] = IPSText::alphanumericalClean($this->request['gt'],' ');

		/* Dump some vars */
		unset($check);
	}
}