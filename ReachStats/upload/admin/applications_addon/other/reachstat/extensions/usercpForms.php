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

if (! defined('IN_IPB')) {
    print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
    exit();
}

class usercpForms_reachstat extends public_core_usercp_manualResolver implements interface_usercp {
	public $tab_name 					= "Stats";
    public $ok_message 					= 'Your gamertag exists, and has been saved.';
    public $hide_form_and_save_button   = false;
    public $uploadFormMax 				= 0;
	public $kb 							= 'http://reachstuff.com/kb/page/';
	public $tier 						= 0;

    /**
     * Initiate this module
     *
     * @access public
     * @return void
     */
    public function init()
    {
    	/* Set the tab name */
        $this->tab_name = $this->lang->words['reach_manage_gamertag'];

    	/* Class setup */
    	require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
    	$this->reach = new reachStats($this->registry );
    	require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/library.php' );
    	$this->library = new Library( $this->registry );

    	/* Load lang files */
    	$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_errors', 'public_reachstat' ), 'reachstat');


    }

    /**
     * Return links for this tab
     * You may return an empty array or FALSE to not have
     * any links show in the tab.
     *
     * The links must have 'area=xxxxx'. The rest of the URL
     * is added automatically.
     * 'area' can only be a-z A-Z 0-9 - _
     *
     * @access public
     * @return array array of links
     */
    public function getLinks()
    {
         $array = array();

        $array[] = array('url' => 'area=settings',
            'title' => "Gamertag Settings",
            'area' => 'settings',
            'active' => $this->request['tab'] == 'reachstat' && $this->request['area'] == 'settings' ? 1 : 0
            );
    	$array[] = array('url' =>'area=mysigs',
    		'title' => $this->lang->words['sig_tab'],
    		'area'  => 'mysigs',
    		'active' => $this->request['tab'] == 'reachstat' && $this->request['area'] == 'mysigs' ? 1 : 0
    		);

        return $array;
    }

    /**
     * Reset the area
     * This is used so that you can reset teh $_AREA, to fix the the left-hand menu highlighting
     * for custom events (e.g. announcements)
     *
     * @access public
     * @param string $ Area
     * @return string Area
     */
    public function resetArea($area)
    {
        return $area;
    }

    /**
     * Run custom event
     *
     * If you pass a 'do' in the URL / post form that is not either:
     * save / save_form or show / show_form then this function is loaded
     * instead. You can return a HTML chunk to be used in the UserCP (the
     * tabs and footer are auto loaded) or redirect to a link.
     *
     * If you are returning HTML, you can use $this->hide_form_and_save_button = 1;
     * to remove the form and save button that is automatically placed there.
     *
     * @access public
     * @param string $ Current 'area' variable (area=xxxx from the URL)
     * @return mixed html or void
     */
    public function runCustomEvent($currentArea)
    {
        // -----------------------------------------
        // INIT
        // -----------------------------------------
        $html = '';

        // -----------------------------------------
        // What to do?
        // -----------------------------------------
        switch ($currentArea) {
            case 'settings':
                $html = $this->_customEvent_reachSettings();
                break;
        	case 'mysigs':
        		$html = $this->_customEvent_sigSettings();
        		break;

        }
        // -----------------------------------------
        // Turn off save button
        // -----------------------------------------
        	$this->hide_form_and_save_button = 1;
        // -----------------------------------------
        // Return
        // -----------------------------------------
        return $html;
    }

    private function _customEvent_reachSettings()
    {

    }

	private function _customEvent_sigSettings()
	{

	}

    /**
     * UserCP Form Show
     *
     * @access public
     * @param string $ Current area as defined by 'get_links'
     * @param array $ Array of member / core_sys_login information (if we're editing)
     * @return string Processed HTML
     */
    public function showForm($current_area, $errors = array())
    {
        // -----------------------------------------
        // Where to go, what to see?
        // -----------------------------------------
        switch ($current_area) {
            case 'settings':
                return $this->showReachSettings();
                break;
        	case 'mysigs':
        		return $this->showSigSettings();
        		break;

        }
    }

    /**
     * usercpForms_reachstat::showReachSettings()
     *
     * @return template to show GT
     */
    public function showReachSettings()
    {
    	/* Build the query to grab their gamertag */
            $value = $this->DB->buildAndFetch(array(
                    'select' => 'id,gamertag,ip_address',
                    'from' => 'reachstat',
                    'where' => "id='" .$this->memberData['member_id']."'"));

    	/* Get highest Tier */
    	$this->tier = $this->library->determineHighestTier($this->memberData['member_group_id']);

    	/* reset OK message. Previous function changes it */
    	$this->ok_message = 'Your gamertag exists, and has been saved.';

    	/* Remove save button if they added GT */
    	if (($value['gamertag'] != "")) {

    		$this->hide_form_and_save_button = 1;
    	}
    	/* Check if banned */
    	if ($this->tier == 0) {
    		return $this->registry->output->getTemplate('stattrack')->reach_ucp("gt_field", 'Banned Users cannot add GTs');
    	}
    	/* Simple check to see if they have one */
    	if (($value['gamertag'] == "")) {
    		return $this->registry->output->getTemplate('stattrack')->reach_ucp("gt_field", "");
    	}
		else
		{

			/* Convert %20 to space for displaying */
			$value['gamertag'] = $this->reach->unParseGT($value['gamertag']);

			/* head home */
			return $this->registry->output->getTemplate('stattrack')->reach_ucp("gt_field", $value['gamertag']);
		}
    }

	public function showSigSettings()
	{
		/* Add our bsmselect */
		$this->registry->output->addToDocumentHead( 'importcss', $this->settings['board_url'] . '/public/style_css/jquery.bsmselect.css');

		//--------------------------------------------
		// Sig Settings
		//--------------------------------------------
			$value = $this->DB->buildAndFetch(array(
				'select' => 'settings,sigs,data,medals',
				'from'	 => 'reachstat',
				'where'	 => "id='" .$this->memberData['member_id']."'"));

		/* set medals in */
		$medals = unserialize($value['medals']);

		//---------------------------------------------
		// Medal Work
		//-----------------------------------------------
		$templateMedals = array ();
		$i = 0;

		/* Foreach the medals for the output */
		foreach ($medals as $medal){
			$templateMedals[$i] = ($medal['Name'] . " (" . $medal['value'] . ")");
			$i++;
		}

		/* unset, sort, encode */
		unset($medals);
		asort($templateMedals);
		$templateMedals = json_encode($templateMedals);

		/* check value */
		if ($value['settings'] != null)
		{
			/* undecode */
			$tempValues = unserialize($value['settings']);

			/* dump */
			$radioColor['id'] = intval($tempValues['2']['id']);

			/* check for sp */
			if ($tempValues['2']['sp'] == null)
			{
				$radioColor['sp'] = 0;
			}
			else
			{
				$radioColor['sp'] = $tempValues['2']['sp'];
			}

			//----------------------------------------
			// Sig 1
			//----------------------------------------
			if (($tempValues['1']))
			{
				$i = 0;

				/* Grab each medal */
				foreach ($tempValues['1'] as $medal )
				{
					$pickedMedals[$i] = $medal;
					$i++;
				}
			}

			//-------------------------------------------
			// Sig 5
			//-------------------------------------------
			if (($tempValues['5']))
			{
				/* Set sig 5 color to stored color value */
				$radioColor['color'] = $tempValues['5']['color'];
			}
		}
		else
		{
			/* Set some defaults */
			$radioColor['id'] = 1;
			$radioColor['sp'] = 0;
			$radioColor['color'] = 'aqua'; #Set sig 4 default color
		}

		/* Convert to case names */
		switch($radioColor['id'])
		{
			case 1:
				$radioColor['name'] = '1_green';
				break;
			case 2:
				$radioColor['name'] = '2_blue';
				break;
			case 3:
				$radioColor['name'] = '3_black';
				break;
			case 4:
				$radioColor['name'] = '4_pink';
				break;
			case 5:
				$radioColor['name'] = '5_grey';
				break;
			case 6:
				$radioColor['name'] = '6_red';
				break;
			default:
				$radioColor['name'] = '1_green';
				break;
		}


		//------------------------------------------------------
		// Reload all the possible colors
		//------------------------------------------------------

		/* Bring in the colors */
		$sig5Colors = $this->fillSig5();
		$i = 0;

		/* Find which one they selected */
		foreach ($sig5Colors as $color )
		{
				$sig5[$i]['name'] = $color['name'];
				$sig5[$i]['formal'] = ucfirst(strtolower($color['name'])); #capitilize
				$sig5[$i]['active'] = ($color['name'] == $radioColor['color']) ? 'selected' : null;

			$i++;
		}
		/* clear */
		unset($value);
		unset($tempValues);
		unset($sig5Colors);

		return $this->registry->output->getTemplate('stattrack')->reach_mysigs(json_encode($radioColor), $templateMedals, $pickedMedals, $sig5);
	}

    /**
     * UserCP Form Check
     *
     * @access public
     * @param string $ Current area as defined by 'get_links'
     * @return string Processed HTML
     */
    public function saveForm($current_area)
    {
        // -----------------------------------------
        // Where to go, what to see?
        // -----------------------------------------
        switch ($current_area) {
            default:
            case 'settings':
                return $this->saveReachSettings();
                break;
        	case 'mysigs':
        		return $this->saveSigSettings();
        		break;

        }
    }

    /**
     * UserCP Save Form: Settings
     *
     * @access public
     * @return array Errors
     */
    public function saveReachSettings()
    {
    	$id = $this->memberData['member_id'];
		$this->ok_message = 'Your gamertag exists, and has been saved.';

    	/* Get highest Tier */
    	$this->tier = $this->library->determineHighestTier($this->memberData['member_group_id']);

    	/* If Banned Tier, then do not save. */
    	if ($this->tier == 0) {
    		$this->registry->getClass('output')->showError($this->lang->words['tier_banned'], "<a href='".$this->kb."2007-r9'>2007</a>",false,'2007');

    	}
		/* Temp var of their GT */
    	$check = $this->reach->checkGTExists(IPSText::parseCleanValue($this->request['gt_field']),true);

    	$this->DB->freeResult();
    	$this->DB->execute();

    	/* Lets grab their GT and check */
    	/* Build the query to grab their gamertag */
    	$value = $this->DB->buildAndFetch(array(
					'select' => 'gamertag,id,ip_address',
					'from' => 'reachstat',
					'where' => "id='".intval($id)."'"));

    	/* Update thier stuff */
    	require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
    	$this->reach = new reachStats( $this->registry );
    	require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classImageMaking.php' );
    	$this->image = new imageClass( $this->registry );

    	/* Lets see if the GTs are different, meaning they are changing */
    	if (($this->reach->unParseGT($value['gamertag']) != $this->request['gt_field'])) {
    		$this->registry->getClass('output')->showError($this->lang->words['gt_changed'], "<a href='".$this->kb."2024-r46'>2024</a>",false,'2024');
    	}
    	//------------------------------------------------
    	// Update Sigs/Data first time
    	//------------------------------------------------

    	/* store default settings */
    	$this->reach->doItAll($this->memberData['member_id']);
    	$this->image->doThemAll($this->memberData['member_id'], $value['gamertag']);

    	/* set default settings */
    	$this->saveSigSettings();

    	/* Check */
    	if (($value['gamertag'] == $this->request['gt_field'])) {
    		return false;
    	}
    	else
    	{

    	/* Make sure this GT exists. No morons passing fakes here */
		if ($check == true) {
			$this->registry->getClass('output')->showError($this->lang->words['tag_no_exist'], "<a href='".$this->kb."2000-r1'>2000</a>",false,'2000');
		}
    		else
    		{
    			//set the field
    			$gamertag = IPSText::parseCleanValue($this->request['gt_field']);

    			return true;
    		}

			/* If we get down here, something went wrong. Return false */
    		return false;
    	}

    }

	/**
	 * usercpForms_reachstat::saveSigSettings()
	 *
	 * @return
	 */
	public function saveSigSettings()
	{
		/* again :o */
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
		$this->reach = new reachStats( $this->registry );

		/* Set to 0, just in case they aren't populated */
		$radio['2']['id'] = 0;
		$radio['2']['sp'] = 0;

		/* Grab each radio and then foreach */
		$radio['2']['id'] = $this->request['radioColor']['value'];
		$radio['2']['sp'] = $this->request['spartan_enable'];

		$radio['5']['color'] = $this->request['sig5Colors'];

		/* Check if were empty */
		$i = 0;
		if (!(empty($this->request['medals'])) && count($this->request['medals'] > 3))
		{
			foreach ($this->request['medals'] as  $medal)
			{
				/* Get last occurence of a space, then go from 0 to that space */
				$radio['1'][$i]['Name'] = substr($medal, 0, strrpos($medal,' '));
				$radio['1'][$i]['Id']   = $this->reach->matchMedalToID($radio['1'][$i]['Name']);
				$i++;
			}
			/* Set OK message */
			$this->ok_message = 'Your sig settings have been saved';
		}
		else
		{
			$value = $this->DB->buildAndFetch(array(
				'select' => 'settings',
				'from'	 => 'reachstat',
				'where'	 => "id='" .$this->memberData['member_id']."'"));

			/* set previous settings in */
			$temp = unserialize($value['settings']);
			$radio['1'] = $temp['1'];

			/* tenary just in case they haven't used the 5th sig image */
			$radio['5'] = ($radio['5'] == null ) ? $temp['5'] : $this->request['sig5Colors'];

			/* bi */
			unset($temp);
			unset($value);

		}

		/* Check for too many medal selections */
		if (count($this->request['medals']) > 4)
		{
			/* Set OK message */
			$this->ok_message = 'You have selected too many medals. The max is 4.';
		}

		/* Send to DB */
		$this->DB->update('reachstat', array(
			'id' 		=> intval($this->memberData['member_id']),
			'settings'  => serialize($radio)),
				"id=" . intval($this->memberData['member_id']));


	}
	private function fillSig5()
	{
		$colour['0']['name'] = "aqua";
		$colour['1']['name'] = "black";
		$colour['2']['name'] = "blue";
		$colour['3']['name'] = "brick";
		$colour['4']['name'] = "brown";
		$colour['5']['name'] = "cobalt";
		$colour['6']['name'] = "coral";
		$colour['7']['name'] = "cyan";
		$colour['8']['name'] = "drab";
		$colour['9']['name'] = "forest";
		$colour['10']['name'] = "gold";
		$colour['11']['name'] = "green";
		$colour['12']['name'] = "ice";
		$colour['13']['name'] = "khaki";
		$colour['14']['name'] = "lavender";
		$colour['15']['name'] = "maroon";
		$colour['16']['name'] = "olive";
		$colour['17']['name'] = "orchid";
		$colour['18']['name'] = "pale";
		$colour['19']['name'] = "peach";
		$colour['20']['name'] = "rose";
		$colour['21']['name'] = "rust";
		$colour['22']['name'] = "sage";
		$colour['23']['name'] = "seafoam";
		$colour['24']['name'] = "silver";
		$colour['25']['name'] = "steel";
		$colour['26']['name'] = "tan";
		$colour['27']['name'] = "teal";
		$colour['28']['name'] = "violet";
		$colour['29']['name'] = "white";
		$colour['30']['name'] = "yellow";

		return $colour;
	}
}
