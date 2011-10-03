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

class public_reachstat_display_index extends ipsCommand
{
	/**
	 * vars
	 */
	protected $output				= "";
	protected $noGT 				= 0; //have one
	protected $id 					= 0;
	protected $gamertag				= "";
	protected $debug 				= DEBUG_MODE;
	protected $version              = HR_VERSION;
	protected $data					= array ();

	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Load language Files */
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_errors', 'public_reachstat' ));

		/* Set our default vars */
		$this->id = $this->memberData['member_id'];
		$this->gamertag = $this->registry->getClass('reach')->getGamertag(intval($this->id), false);

		/* Get data */
		$this->data = $this->registry->getClass('library')->getUserData($this->id);

		/* If no GT, don't show it k? */
		if ($this->gamertag == "")
		{
			$noGT = 1;
		}

		/* Check online */
		//$this->registry->getClass('library')->checkOnline();

		/* Page Title */
		$this->registry->output->setTitle($this->registry->output->getTitle() . $this->lang->words['reach_manage_gamertag']);

 		/* Cache Loading */
		if(!$this->caches['challenges'])
		{
			$this->caches['challenges'] = $this->cache->getCache('challenges');
		}

		/* Cache Loading */
		if(!$this->caches['leaderboard'])
		{
			$this->caches['leaderboard'] = $this->cache->getCache('leaderboard');
		}

		/* Random image */
		$image = $this->registry->getClass('library')->getRandomPlaceholder();

		/* Send that junk out */
    	$this->output .= $this->registry->output->getTemplate('stattrack')->reach_home($this->gamertag, $image, $this->caches['challenges'], $this->caches['leaderboard']);

		/* Add copyright, JS, and then send */
		$this->registry->output->addNavigation( $this->lang->words['reach_manage_gamertag'], 'app=reachstat' );
		$this->output .= $this->registry->getClass('library')->addFooter();
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}

}
