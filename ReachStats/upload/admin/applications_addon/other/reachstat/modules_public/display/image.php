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

class public_reachstat_display_image extends ipsCommand
{
	/*
	 * $output = cached before sent to template
	 * $data   = All gamer data
	 * $id     = ID of page visiting, not memberID
	 * $curGT  = Current Gamertag of page
	 * $diff   = Recache / data times
	 */
	protected $output				= "";
	protected $data 				= array ();
	protected $id 					= null;
	protected $gt         			= "";
	protected $diff 				= array();
	protected $debug 				= DEBUG_MODE;
	protected $kb 					= 'http://reachstuff.com/kb/page/';
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
		/* Load language Files */
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_errors', 'public_reachstat' ));

		/* Check online */
		//$this->registry->getClass('library')->checkOnline();

		/* This does cache work */
		$this->pullTehNFO();

		/* Are we recaching images? */
		$loc = $this->request['loc'];
		switch($loc)
		{
			case 'recache':
				$this->_recache($this->id, $this->gt);
				break;

			default:
				break;
		}

		/* Need to have ReachData avaiable */
		$this->data = $this->registry->getClass('reach')->getAllReachData($this->id);

		/* Determine if we need to redo images, or not */
		if ($this->times['sigs'] > ($this->times['tier']['time_ttl']) )
		{

			/* Only do this if its your page */
			if ($this->id == $this->memberData['member_id'])
			{
				/* Can't cache yet, so grab it */
				$sigs = $this->registry->getClass('image')->doThemAll($this->id,$this->gt, false);
			}
			else
			{
				/* Just grab data, don't redo. */
				$sigs = $this->registry->getClass('image')->getSigs($this->id);
			}
		}
		else
		{
			/* Just grab data, don't redo. */
			$sigs = $this->registry->getClass('image')->getSigs($this->id);
		}

		/* Ready stuff for public viewing */
		$this->times['sigTime'] = $this->registry->getClass('library')->time_duration($this->times['sigs']);
		$this->times['visitTime'] = $this->registry->getClass('library')->time_duration($this->times['visit']);

		/* Attach stuff and go */
		$this->output .= $this->registry->output->getTemplate('stattrack')->imagePage($this->data, $sigs, $this->times);
		$this->output .= $this->registry->getClass('library')->addFooter();
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
	}

	public function pullTehNFO()
	{
		/* Pull the GT */
		$this->gt = IPSText::parseCleanValue($this->request['gt']);

		/* If Gamertag is blank, they didn't pass it */
		if ($this->gt == "")
		{
			$this->registry->getClass('output')->showError( $this->lang->words['no_gt_passed'],"2012", false, '2012' );
		}
		else
		{
			/* Navigation Bar */
			$this->registry->output->addNavigation( $this->lang->words['reach_manage_gamertag'], 'app=reachstat' );
			$this->registry->output->addNavigation( $this->lang->words['img_nav'] . $this->gt, 'app=reachstat&amp;module=display&amp;section=image&amp;gt='. $this->gt );
			$this->registry->output->setTitle($this->lang->words['img_nav'] . $this->gt);
		}

		/* They passed GT, but does it exist? */
		$check = intval($this->registry->getClass('reach')->findAndCheckID($this->gt));

		/* Get the ID from their GT that we no exists */
		if (!($check == 0))
		{
			/* It exists. Find it */
			$this->id = intval($check);
		}
		else
		{
			$this->registry->getClass('output')->showError($this->lang->words['gt_no_exists'], "2013",false,'2013');
		}

		/* Finish up with Tier Data */
		$this->times = $this->registry->getClass('library')->getUserData($this->id);

		/* Check for banned image */
		if ($this->times['tier']['dynimg'] == 0)
		{
			$this->registry->getClass('output')->showError($this->lang->words['gt_banned_img'], "2014",false,'2014');
		}

		/* Unset some stuff */
		unset($check);

	}

	private function _recache($id, $gt)
	{
		/* Our settings disabled? */
		if ($this->settings['recache_perm'])
		{
			$this->registry->getClass('output')->showError( $this->lang->words['no_recache_acp_off'],"2015", false, '2015' );
		}

		/* can recache? */
		if (in_array( $this->memberData['member_group_id'], explode( ",", $this->settings['recache_abil'] ) ) )
		{
			$flag2 = true;

					/* Prevent mass recaching. */
					if ($this->times['tier']['control'] == 1 or $this->times['data'] > $this->settings['min_recache_time']or $flag2 == true)
					{
						$this->registry->getClass('image')->doThemAll($id,$gt);

					}
					else
					{
						$this->registry->getClass('output')->showError( $this->lang->words['spam_img_recache'],"<a href='".$this->kb."2016-r18'>2016</a>", false, '2016' );
					}
		}
		else
		{
			$flag2 = false;
			$this->registry->getClass('output')->showError( $this->lang->words['no_perm_img'],"2017", false, '2017' );
		}
			/* Lets check if we own this account */
			if ($this->memberData['member_id'] == $id )
			{
				ipsRegistry::getClass('output')->redirectScreen( $this->lang->words['redirect_images'], $this->settings['base_url'] . "app=reachstat&amp;module=display&amp;section=image&amp;gt={$gt}" );
			}
			else
			{
				ipsRegistry::getClass('output')->redirectScreen( $this->lang->words['re_recache_p1'] . $this->gt . $this->lang->words['re_recache_p2'], $this->settings['base_url'] . "app=reachstat&amp;module=display&amp;section=image&amp;gt={$gt}" );
			}
	}
}