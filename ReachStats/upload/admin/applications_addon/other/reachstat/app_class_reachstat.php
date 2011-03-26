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

define('HR_VERSION', '0.8.4');
define('HR_RVERSION', '10014');
define('DEBUG_MODE', (ipsRegistry::$settings['debug_mode']));
define('API_KEY', (ipsRegistry::$settings['api_key']));
define('FLAG_INACTIVE', ipsRegistry::$settings['inactive_flag']);

if (!defined('IN_IPB')) {
    print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
    exit();
}

class app_class_reachstat
{

    /**
     * Constructor
     *
     * @access public
     * @param object $ ipsRegistry
     * @return void
     */
    public function __construct(ipsRegistry $registry)
    {
    	/* Make object */
    	$this->registry = $registry;
    	$this->DB       = $this->registry->DB();
    	$this->settings =& $this->registry->fetchSettings();
    	$this->request  =& $this->registry->fetchRequest();
    	$this->cache    = $this->registry->cache();
    	$this->caches   =& $this->registry->cache()->fetchCaches();
    	$this->lang     = $this->registry->getClass('class_localization');
    	$this->member   = $this->registry->member();
    	$this->memberData =& $this->registry->member()->fetchMemberData();

    	//---------------------------------------
    	// Only load this stuff if not in ACP
    	//---------------------------------------
        if (IN_ACP)
        {
            // Make them select the index instead of something else
            if (!ipsRegistry::$request['module'])
            {
                ipsRegistry::$request['module'] == 'overview';
            }
        } else
        {
        	/* Lets load the library */
	       	if ( ! $this->registry->isClassLoaded( 'library' ) )
	       	{
	       		require_once( IPSLib::getAppDir( 'reachstat' ) . "/sources/library.php" );
	       		$this->library = new Library($this->registry);
	       		$this->registry->setClass('library', $this->library);
	       	}
        	if ( ! $this->registry->isClassLoaded( 'reach' ) )
        	{

        		require_once( IPSLib::getAppDir( 'reachstat' ) . "/sources/classReachStats.php" );
        		$this->reach = new ReachStats($this->registry);
        		$this->registry->setClass('reach', $this->reach);
        	}
        	if ( ! $this->registry->isClassLoaded( 'image' ) )
        	{
        		require_once( IPSLib::getAppDir( 'reachstat' ) . "/sources/classImageMaking.php" );
        		$this->image = new imageClass($this->registry);
        		$this->registry->setClass('image', $this->image);
        	}

        }
    }

    	/**
    	 * Do some set up after ipsRegistry::init()
    	 *
    	 * @access	public
    	 */
    	public function afterOutputInit()
    	{
    		/* Flag if no API key */
    		if (API_KEY == null)
    		{
    			/* dont show the error if in acp ass */
    			if (!(IN_ACP))
    			{
    				$this->registry->getClass('output')->showError( $this->lang->words['no_api_key'],"<a href='".$this->kb."2003-r4'>2003</a>", false, '2003' );
    			}
    		}
    		/* Is this junk online and enabled? */
    		if (!$this->settings['reach_online'])
    		{

    			/* The currently visiting member */
    			if(in_array( $this->memberData['member_group_id'], explode( ",", $this->settings['reach_group_online'] ) ) )
    			{
    				/* Load cache and get out */
    				$_LOAD['metadata'] = 1;

    				/* Lets add the OFFLINE message */
    				$this->registry->getClass('output')->setTitle("OFFLINE: ");

    			}
    			else
    			{   /* Lets add the OFFLINE message */
    				$this->registry->getClass('output')->setTitle("OFFLINE: ");

    				/* error out */
    				$registry->getClass('output')->showError( $this->lang->words['system_offline'],"<a href='".$this->kb."2006-r7'>2006</a>", false, '2006' );
    			}
    		}
    	}

}
