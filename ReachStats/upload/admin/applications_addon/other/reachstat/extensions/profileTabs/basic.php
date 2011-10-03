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

class profile_basic extends profile_plugin_parent
	{
	/**
	 * Attachment object
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $attach;

	/**
	 * Feturn HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array())
	{

		//-----------------------------------------
		// Return content..
		//-----------------------------------------
			//$content = $this->registry->output->getTemplate('stattrack')->vehi_graph($member['vehicles']);
			//$content = $this->registry->output->getTemplate('stattrack')->kills_graph($member['weaps']);
		$content = $this->registry->output->getTemplate('stattrack')->basic_stats($member['reach']);

		/* send home */
		return $content;
	}
}