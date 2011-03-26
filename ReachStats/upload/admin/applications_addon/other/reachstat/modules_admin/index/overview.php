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


class admin_reachstat_index_overview extends ipsCommand
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
		/* EMBEDDDDD */
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/library.php' );
		$this->library = new Library( $registry );

		/* Get some data */
		$tmp = $this->DB->buildAndFetch( array(
						 'select' => 'COUNT(*)',
						 'from' => 'reachstat',
						 'where' => 'inactive=0'));

		$tmp2 = $this->DB->buildAndFetch( array(
				 'select' => 'COUNT(*)',
				 'from' => 'reachstat'));

		/* Gotta get the right information via $tmp */
		$this->data['usersAct'] = $tmp['COUNT(*)'];
		$this->data['users']    = $tmp2['COUNT(*)'];

		/* Set version */
		$this->data['version'] = HR_VERSION;

		/* Size of reach folder */
		$this->data['size'] = $this->library->size_readable($this->library->dirsize(DOC_IPS_ROOT_PATH . '/reach/'));

		//-----------------------------------------
		// Load HTML
		//-----------------------------------------

		$this->html = $this->registry->output->loadTemplate( 'cp_skin_overview' );
		ipsRegistry::getClass( 'class_localization')->loadLanguageFile( array( 'admin_reachstat' ) );


		//-----------------------------------------
		// Pass to CP output hander
		//-----------------------------------------
		$this->registry->output->html .= $this->html->overviewSplash($this->data);
		$this->registry->getClass('output')->html_main .= $this->registry->getClass('output')->global_template->global_frame_wrapper();
		$this->registry->getClass('output')->sendOutput();
	}
}