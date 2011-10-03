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

class public_reachstat_display_leaderboards extends ipsCommand
{
	protected $output				= "";
	protected $data 				= array ();
	protected $id 					= null;
	protected $gt         			= "";
	protected $diff 				= array();
	protected $debug 				= DEBUG_MODE;
	protected $version              = HR_VERSION;
	protected $tier					= array();

	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Set our default vars */
		$this->id = $this->memberData['member_id'];
		$this->gamertag = $this->registry->getClass('reach')->getGamertag(intval($this->id), false);

		/* Cache Loading */
		if(!$this->caches['leaderboard'])
		{
			$this->caches['leaderboard'] = $this->cache->getCache('leaderboard');
		}

		/* Send to the output */
		$this->output .= $this->registry->output->getTemplate('stattrack')->leaderboardPage();
		$this->registry->getClass('library')->addJS();
		$this->output .= $this->registry->getClass('library')->addFooter();
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}

}