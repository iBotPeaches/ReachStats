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


class admin_reachstat_settings_settings extends ipsCommand
{
	/**
	 * Settings gateway
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $settingsClass;

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
		// Load settings controller
		//-----------------------------------------

		$this->registry->class_localization->loadLanguageFile( array( 'admin_tools' ), 'core' );
		$this->registry->class_localization->loadLanguageFile( array( 'admin_lang' ), 'reachstat' );

		require_once( IPSLib::getAppDir( 'core' ) . '/modules_admin/tools/settings.php' );
		$this->settingsClass		= new admin_core_tools_settings();
		$this->settingsClass->makeRegistryShortcuts( $this->registry );
		$this->settingsClass->html				= $this->registry->output->loadTemplate( 'cp_skin_tools', 'core' );
		$this->settingsClass->form_code			= $this->settingsClass->html->form_code		= 'module=settings&amp;section=settings';
		$this->settingsClass->form_code_js		= $this->settingsClass->html->form_code_js	= 'module=settings&section=settings';
		$this->settingsClass->return_after_save	= $this->settings['base_url'] . $this->settingsClass->html->form_code;

		//-----------------------------------------
		// Show settings form
		//-----------------------------------------

		if( $this->request['do'] == 'tiers' )
		{

			$this->request['conf_title_keyword']	= 'tiers';
		}
		else
		{
			$this->request['conf_title_keyword']	= 'reach_settings';
		}

		//-----------------------------------------
		// View settings
		//-----------------------------------------

		$this->settingsClass->_viewSettings();

		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------

		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}

}