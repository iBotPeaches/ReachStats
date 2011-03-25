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

if ( ! defined( 'IN_IPB' ) )
	{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
	}

class public_reachstat_display_gamer extends ipsCommand
	{

	protected $output				= "";
	protected $data 				= array ();
	protected $version              = HR_VERSION;
	protected $times				= array();
	protected $recache 				= 0;
	protected $diff 				= array();
	protected $id 					= 0;
	protected $curGamertag 			= "";
	protected $loc					= "";
	protected $debug 				= DEBUG_MODE;
	protected $kb 					= 'http://reachstuff.com/kb/page/';
	/* TIME FOR the TABs */
	protected $member_id			= null;
	protected $_positions			= array( 0 => 0 );
	protected $app 					= array();
	protected $tab					= null;
	protected $firsttab				= '';
	protected $member				= array();
	protected $default_tab        	= '';
	protected $tabs					= array();
	protected $_tabs				= array();
	protected $tab_html				= null;

/**
 * Main class entry point
 *
 * @access	public
 * @param	object		ipsRegistry reference
 * @return	void		[Outputs to screen]
 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Load Language Files */
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_errors', 'public_reachstat' ));

		/* Check online */
		//$this->registry->getClass('library')->checkOnline();

		/* Location check */
		$this->loc = IPSText::parseCleanValue($this->request['loc']);

		/* Do the pull info anyway */
		$this->pullTehNFO();

		/* Switch for the location */
		switch ($this->loc)
		{
			case "adv":
				$this->advance();
				break;
			case "recache":
				$this->recache($this->times['gt'], $this->id);
				break;
			default:
				$this->normal();
				break;
		}

		/* DEBUG CHECK */
		if ($this->debug == 1)
		{
			print_r($this->data);
		}

		/* Send to the output */
		$this->registry->getClass('library')->addJS();
		$this->output .= $this->registry->getClass('library')->addFooter();
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}

	public function recache($gt, $id)
	{
		/* Our settings disabled? */
		if ($this->settings['recache_perm']) {
			$this->registry->getClass('output')->showError( $this->lang->words['no_recache_acp_off'],"<a href='".$this->kb."2008-r10'>2008</a>", false, '2008' );
		}

		/* can recache? */
		if (in_array( $this->memberData['member_group_id'], explode( ",", $this->settings['recache_abil'] ) ) )
		{
			/* Set it to true */
			$flag2 = true;

				/* Prevent mass recaching. */
				if ($this->times['tier']['control'] == 1 || $this->times['data'] > $this->settings['min_recache_time'] || $flag2 == true) {

								/* Now send/do work */
								$this->registry->getClass('library')->doubleCheck(&$this->times, $this->id); #remove inactive flag

								/* 3 checks later...we made it */
								$this->data = $this->registry->getClass('reach')->doItAll($id);
				}
				else
				{
					/* 3 checks later...we finally failed it */
					$this->registry->getClass('output')->showError( $this->lang->words['spam_gt_recache'],"<a href='".$this->kb."2009-r11'>2009</a>", false, '2009' );
				}

			/* Lets check if we own this account */
			if ($this->memberData['member_id'] == $id ) {
				ipsRegistry::getClass('output')->redirectScreen( $this->lang->words['redirect_recache'], $this->settings['base_url'] . "app=reachstat&amp;module=display&amp;section=gamer&amp;gt={$this->data['gt']}" );
			}
			else
			{
				ipsRegistry::getClass('output')->redirectScreen( $this->lang->words['re_recache_p1'] . $this->data['gt'] . $this->lang->words['re_recache_p2'], $this->settings['base_url'] . "app=reachstat&amp;module=display&amp;section=gamer&amp;gt={$this->data['gt']}" );
			}

		}
		else
		{
			/* No flag */
			$flag2 = false;

			/* Error out, we don't have permission to recache */
			$this->registry->getClass('output')->showError( $this->lang->words['no_perm'],"<a href='".$this->kb."2010-r13'>2010</a>", false, '2010' );
		}
	}

	public function pullTehNFO()
	{
		/* Get their variables now */
		$this->times['gt'] = IPSText::alphanumericalClean($this->request['gt'],' ');
		$this->times['id'] = intval($this->request['mid']);

		/* Work goes on here, if they pass ID but not GT */
		if ($this->times['gt'] == null && $this->times['id'] != null)
		{

			/* Get their GT */
			$this->times['gt'] = $this->registry->getClass('reach')->findAndCheckGT($this->times['id']);

			/* Back to the redirect */
			$this->registry->output->silentRedirect($this->settings['base_url'] . 'app=reachstat&amp;module=display&amp;section=gamer&amp;gt=' . $this->times['gt'] . '&amp;loc=' . $this->loc);
		}
		/* If Gamertag is blank, they didn't pass it */
		if ($this->times['gt'] == "") {
			$this->registry->getClass('output')->showError( $this->lang->words['no_gt'],"<a href='".$this->kb."2011-r13'>2011</a>", false, '2011' );
		}
		else
		{
			$this->registry->output->setTitle($this->lang->words['stat_nav'] . $this->times['gt']);
		}

		/* They passed GT, but does it exist? */
		$check = $this->registry->getClass('reach')->findAndCheckID($this->times['gt']);


		/* If the its not equal to 0, get it */
		if (!($check == 0))
		{
			/* It exists. Find it */
			$this->id = intval($check);
		}
		else
		{
			$this->registry->getClass('output')->showError( $this->lang->words['2001'],"<a href='".$this->kb."2001-r2'>2001</a>", false, '2001' );
		}

		/* Get the times for comparing */
		$this->times = $this->registry->getClass('library')->getUserData($this->id);

		/* Get their variables now */
		$this->times['gt'] = IPSText::alphanumericalClean($this->request['gt'],' ');

		/* Dump some vars */
		unset($check);

	}

	public function normal()
	{
		/* Are they inactive ? */
		if ($this->times['visit'] > $this->times['tier']['inactive']) {

			/* Delete their date */
			$this->data = $this->registry->getClass('reach')->getAllReachData($this->id);
		}
		else
		{

			/* Check the day old stats */
			if ($this->times['data'] > ($this->times['tier']['time_ttl']) )
			{
						   	/* Only do this if its your page */
						   	if ($this->id == $this->memberData['member_id'])
						   	{

						   		/* Now send/do work */
						   		$this->registry->getClass('library')->doubleCheck(&$this->times, $this->id); #remove inactive flag

								$this->data = $this->registry->getClass('reach')->doItAll($this->id);
						   		$this->pullTehNFO();
						   	}
						   	else
						   	{
						   		/* Nope, not your page. Were not going to re-cache for them */
						   		$this->data = $this->registry->getClass('reach')->getAllReachData($this->id);
						   	}
			}
			else
			{
				$this->data = $this->registry->getClass('reach')->getAllReachData($this->id);
			}
		}

		/* unset old stuff */
		unset($result);


		/* human form */
		$this->times['visitDiff']   = $this->registry->getClass('library')->time_duration($this->times['visit']);
		$this->times['dataAge']     = $this->registry->getClass('library')->time_duration($this->times['data']);

		/* Get User ID, based on their GT */
		$this->data['gt'] = $this->times['gt'];

		/* Is this our page...not ? */
		if (intval($this->memberData['member_id']) != $this->data['id'])
		{
			$this->data['visitGT'] = $this->registry->getClass('reach')->findAndCheckGT($this->memberData['member_id']);
		}
		else
		{
			$this->data['visitGT'] = $this->data['gt'];
		}

		/* num set */
		$num = 0;
		$num1 = 0;

		/* Weapons (needed for Google Graphs API */
		foreach ($this->data['stats'] as $stat){

			/* Check for vehicle */
			if ($stat['type'] == 'vehicle') {
				$temp1[$num]= $stat;

				/* seperate counters */
				$num++;
			}
			else
			{
				$temp[$num1]  = $stat;

				/* add it */
				$num1++;
			}
		}

		/* sort for IDs */
		sort($temp);
		sort($temp1);

		/* encode */
		$google  = json_encode($temp);
		$google1 = json_encode($temp1);

		/* cleanup */
		unset($num);
		unset($num1);
		unset($temp);
		unset($temp1);

		/* Navigation Bar */
		$this->registry->output->addNavigation( $this->lang->words['reach_manage_gamertag'], 'app=reachstat' );
		$this->registry->output->addNavigation( $this->lang->words['stat_nav'] . $this->times['gt'], 'app=reachstat&amp;module=display&amp;section=gamer&amp;gt=' . $this->times['gt'] );

		//IPSDebug::fireBug( 'info', 'Ending Gamer Kills: ' . $this->data['totalKills']);
		//IPSDebug::fireBug( 'info', 'Ending Game Deaths: ' . $this->data['totalDeaths']);

		/* Check for tabbed or not */
		if ((ipsRegistry::$settings['tab_gamer_enabled']) && ($no == TRUE))
		{
			/* Enabled */
			/* Search it for tabs */
			$this->confFind($google, $google1);

			# Send out
			$this->output .= $this->registry->output->getTemplate('stattrack')->gamerPage($this->times, $this->data, $this->memberData, $this->tabs, $this->default_tab, $this->tab_html, $google, $google1);
		}
		else{
			/* Disabled */
			$this->output .= $this->registry->output->getTemplate('stattrack')->gamerPageOld($this->times, $this->data, $this->memberData, $google, $google1);
		}
	}

	public function advance()
	{
		/* This is not used yet.
		   Once we add more stats we will need another stat page
		   this will serve as that page
		   kthxbi
		   */
	}

	public function confFind($google, $google1)
	{
		//-------------------------------
		// VARS
		//-------------------------------
		$this->member_id			= intval( $this->request['id'] ) ? intval( $this->request['id'] ) : intval( $this->request['MID'] );
		$this->member_id			= $this->member_id ? $this->member_id : $this->memberData['member_id'];
		$this->_positions			= array( 0 => 0 );
		$this->app 					= ipsRegistry::$applications['reachstat'];
		$this->tab					= substr( IPSText::alphanumericalClean( str_replace( '..', '', trim( $this->request['tab'] ) ) ), 0, 20 );
		$this->firsttab				= '';
		$this->member				= array();
		$this->default_tab       	 = '';
		$this->tabs					= array();
		$this->_tabs				= array();

		/* boom */
		$this->member['reach'] = $this->data;
		$this->member['weaps'] = $google;
		$this->member['vehicles'] = $google1;

			/* Send home if no ID */
			if ( ! $this->member_id )
			{
				$this->registry->output->silentRedirect( $this->settings['base_url'] );
			}
			/* Skip if disabled */
			if( ! $this->app['app_enabled'] )
			{
				continue;
			}

			/* Path to tabs */
			$custom_path = IPSLib::getAppDir( $this->app['app_directory'] ) . '/extensions/profileTabs';

			if ( is_dir( $custom_path ) )
			{
				foreach( new DirectoryIterator( $custom_path ) as $f )
				{
					if ( ! $f->isDot() && ! $f->isDir() )
					{
						$file = $f->getFileName();

						if( $file[0] == '.' )
						{
							continue;
						}

						if ( preg_match( "#\.conf\.php$#i", $file ) )
						{
							$classname = str_replace( ".conf.php", "", $file );

							require( $custom_path . '/' . $file );

							//-------------------------------
							// Allowed to use?
							//-------------------------------

							if ( $CONFIG['plugin_enabled'] )
							{
								$CONFIG['app']				= $this->app['app_directory'];

								$_position					= $this->library->_getTabPosition( $this->_positions, $CONFIG['plugin_order'] );
								$this->_tabs[ $_position ]		= $CONFIG;
								$this->_positions[]				= $_position;
							}
						}
					}
				}
			}
		/* sort em */
		ksort( $this->_tabs );

		foreach( $this->_tabs as $_pos => $data )
		{
			if( !$this->firsttab )
			{
				$this->firsttab = $data['plugin_key'];
			}

			$data['_lang']					= isset($this->lang->words[ $data['plugin_lang_bit'] ]) ? $this->lang->words[ $data['plugin_lang_bit'] ] : $data['plugin_name'];
			$this->tabs[ $data['plugin_key'] ]	= $data;
		}

		if ( ! $this->tab OR ( $this->tab != 'comments' AND $this->tab != 'settings' AND ! @file_exists( IPSLib::getAppDir( $this->tabs[ $this->tab ]['app'] ) . '/extensions/profileTabs/' . $this->tab . '.php' ) ) )
		{
			$this->tab         = $this->firsttab;
			$this->default_tab = $this->tabs[ $this->tab ]['app'] . ':' . $this->tab;
		}

		//-----------------------------------------
		// Grab default tab...
		//-----------------------------------------

		$tab_html = '';

		if ( $this->tab != 'comments' AND $this->tab != 'settings' )
		{
			if( file_exists( IPSLib::getAppDir( $this->tabs[ $this->tab ]['app'] ) . '/extensions/profileTabs/' . $this->tab . '.php' ) )
			{
				require( IPSLib::getAppDir( 'members' ) . '/sources/tabs/pluginParentClass.php' );
				require( IPSLib::getAppDir( $this->tabs[ $this->tab ]['app'] ) . '/extensions/profileTabs/' . $this->tab . '.php' );
				$_func_name		= 'profile_' . $this->tab;
				$plugin			=  new $_func_name( $this->registry );
				$this->tab_html	= $plugin->return_html_block( $this->member);
			}
		}
	}

}
