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

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}


class admin_reachstat_tools_tools extends ipsCommand
{
/**
 * Shortcut for url
 *
 * @access	private
 * @var		string			URL shortcut
 */
private $form_code;


/**
 * Skin object
 *
 * @access	private
 * @var		object			Skin templates
 */
private $html;

/* For passing purposes */
private $data = array();
/**
 * Main class entry point
 *
 * @access	public
 * @param	object		ipsRegistry reference
 * @return	void		[Outputs to screen]
 */

	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Load HTML
		//-----------------------------------------
		$this->registry	= $registry;
		$this->DB		= $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang		= $this->registry->getClass('class_localization');
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();

		$this->html = $this->registry->output->loadTemplate( 'cp_skin_overview' );
		ipsRegistry::getClass( 'class_localization')->loadLanguageFile( array( 'admin_reachstat' ) );

		//-----------------------------------------
		// What shall we do?
		//-----------------------------------------

		switch ( $this->request['do'] )
		{
			case 'rebuildusers':
				$this->rebuild_users();
				break;
		}

		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------
		$this->registry->output->html .= $this->html->tools();
		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}

	/* Start redoing everyone */
	private function rebuild_users()
	{
		/* get our max */
		$acp_max = $this->cache->getCache('acp_max');

		/* vars */
		$done = 0;
		$pergo = 1;
		$last = 0;
		$output = array();
		$new	= array();
		$this->html->form_code    = '&amp;module=tools&amp;section=tools';

		/* We love classes. This is OOOOOP */
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
		$this->reach = new reachStats( $this->registry );
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/library.php' );
		$this->library = new Library( $this->registry );
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classImageMaking.php' );
		$this->image = new imageClass( $this->registry );

		/* grab start and end if in the URL, this->request does all cleaning */
		$start  = intval($this->request['st']) >=0 ? intval($this->request['st']) : 0;
		$end    = intval($this->request['pergo']) ? intval($this->request['pergo']) : 100;
		$dis    = intval($this->request['dis']) >=0 ? intval($this->request['dis']) : 0;

		/* more to do? */
		$tmp = $this->DB->buildAndFetch( array( 'select' => 'id', 'from' => 'reachstat', 'limit' => array($dis,1)  ) );
		$max = intval( $tmp['id'] );

		ipsRegistry::DB()->allow_sub_select=1;

		/* limits???? */
		$this->DB->build(
			array(
				'select' => 'id, gamertag, settings, stat_date,sig_date,ip_address,inactive',
				'from'	 => 'reachstat',
				'order'	 => 'id ASC',
				'where'	 => 'id > ' . $start,
				'limit'	 => array($pergo)
				)
		);


		/* get caches */
		if (intval($acp_max) == 0)
		{
			/* Lets redo the caches */
			$this->reach->getChallenges(true);
			$this->reach->setUp();
		}

		/* pull this junk */
		$outer = $this->DB->execute();

		while( $r = $this->DB->fetch( $outer ) )
		{

			/* we don't care if inactive here */
			$new['inactive'] = $r['inactive'];
			$new['settings'] = $r['settings'];

			/* clean the previous */
			$this->cleanGlobals();

			/* lets get the new data */
			$this->reach->doItAll($r['id'],true);

			/* now onto the sigs */
			$this->image->doThemAll($r['id'], $this->reach->unParseGT($r['gamertag']), true );

			$last = $r['id'];

			/* one more done */
			$done++;
		}


		//-----------------------------------------
		// Finish - or more?...
		//-----------------------------------------

		if ( ! $done and ! $max )
		{
			//-----------------------------------------
			// Done..
			//-----------------------------------------
			$this->cache->setCache('acp_max', $done);
			$text = "<b>Rebuild completed</b><br />".implode( "<br />", $output );
			$url  = "{$this->settings['base_url']}{$this->html->form_code}&do=tools";
			$time = 2;
		}
		else
		{
			//-----------------------------------------
			// More..
			//-----------------------------------------

			$dis  = $dis + $done;

			$text = "<b>Up to {$dis} processed so far, continuing...</b><br />".implode( "<br />", $output );
			$url  = $this->settings['base_url'] . $this->html->form_code . '&do=' . $this->request['do'] . '&type='.$type.'&pergo='.$pergo.'&st='.$last.'&dis='.$dis;
			$time = 0;
		}

		//-----------------------------------------
		// Bye....
		//-----------------------------------------

		$this->registry->output->redirect( $url, $text, $time );
	}

	private function cleanGlobals()
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