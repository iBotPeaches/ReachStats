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
class imageClass {

	/* Vars */
	protected $gt 				= "";
	protected $userID			= 0;
	protected $id				= 0;
	protected $max 				= 5; #CHANGE THIS TO MAX SIGS.
	protected $pathToEmblem70   = null;
	protected $pathToEmblem45   = null;
	protected $pathToEmblem29   = null;
	protected $pathToRank26		= null;
	public $pathToSigs 			= array();
	public $template 			= array();
	public $sigs 				= array();
	public $allSigs 			= array();
	public $data 				= array();
	public    $sigSettings		= array();
	public $tier				= null;
	public $tempPath			= null;
	protected $flag				= false;
	protected $inactive 		= 0;
	protected $use				= 0;
	protected $counter 			= 0;
	protected $sigCount         = 0;
	protected $fonts 			= array();
	protected $medals			= array();

	/**
 * Constructor
 */
	function __construct(ipsRegistry $ipsRegistry)
	{
		/* Make objects and stuff */
		$this->registry 		= &$ipsRegistry;
		$this->DB 				= $this->registry->DB();
		$this->settings 		= &$this->registry->fetchSettings();
		$this->request 			= &$this->registry->fetchRequest();
		$this->lang 			= $this->registry->getClass('class_localization');
		$this->member 			= $this->registry->member();
		$this->memberData 		= &$this->registry->member()->fetchMemberData();
		$this->cache 			= $this->registry->cache();
		$this->caches 			= &$this->cache->fetchCaches();

		/* var dump */
		$userID = $this->memberData['member_id'];
	}

	/**
	 * reachStats::umImageNao()
	 * @params USER ID int
	 * @params GAMERTAG string
	 * @params DATA array
	 * @params int of image type
	 * @return
	 */
	public function umImageNao($id, $gt, $data, $imageType, $sets, $task)
	{
		/* reset settings everytime */
		$this->sigSettings = $sets;

		/* check or die */
		if(!function_exists("gd_info"))
		{
			die("GD is not installed on this server. Please ask the admin to install GD.");
		}

		/* do this once moron */
		if ($this->counter == 0)
		{
			/* Get the CURL files */
			require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
			$this->fileManage = new classFileManagement( $this->registry);
			require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
			require_once( IPSLib::getAppDir( 'reachstat') . '/sources/library.php' );
			$this->reach = new reachStats( $this->registry );
			$this->library = new Library( $this->registry );

			/* Load Language Files */
			$this->registry->getClass('class_localization')->loadLanguageFile( array('public_errors', 'public_reachstat' ));

			/* This has to be set, since we cannot change CONSTANTS */
			$this->tempPath = DOC_IPS_ROOT_PATH;

			/* Set the vars ONCE */
			$this->id 	= $id;
			$this->gt 	= $this->reach->unParseGT($data['gt']);
			$this->data = $data;
			$this->tier = $this->library->getUserData($this->id);

			/* Check for inactiveness */
			$result = $this->DB->buildAndFetch(array(
				'select' 	=> 'inactive',
				'from' 		=> 'reachstat',
				'where' 	=> "id='" . $id . "'"));


			/* Load the fonts via function */
			$this->loadFonts();

		}

		/* Doesn't do anything, if they are trully inactive */
		if ((($result['inactive'] == 1) && FLAG_INACTIVE ) && ($task != TRUE))
		{
			/* Throw error if inactive */
			if($this->memberData['member_id'])
			{
				$this->registry->getClass('output')->showError($this->lang->words['gt_inactive'], "2023",false,'2023');
			}
		}
		else
		{

		//-------------------------------------------
		// Check for folder, then delete/make
		//-------------------------------------------
		if (!(file_exists($this->tempPath . 'reach/sigs/' . $this->id . '/')))
		{
			/* make that folder */
			mkdir($this->tempPath . 'reach/sigs/' . $id . '/' , IPS_FILE_PERMISSION);
		}

		/* Temp */
		$q = $imageType;

		/* Path & Directory */
		$pathToSigs = "{$this->tempPath}reach/sigs/{$id}/{$id}-{$q}.png";
		$dirToSigs  = "{$this->settings['board_url']}/reach/sigs/{$id}/{$id}-{$q}.png";

		/* Emblem */
		$pathToMedals = "{$this->tempPath}reach/medals/";
		//$ftpToEmblem  = "/emblems/{$id}-0.png";

		/* Clean the Gamertag */
		$gt =  str_replace('%20', ' ',$gt);
		$this->gt = str_replace('%20',' ',$this->gt);

		/* do this once moron */
		if ($this->counter == 0 || $this->settings['emblem'] == true)
		{

			//--------------------------------------------------------
			// Sig Settings Checkpoint #1
			//--------------------------------------------------------
			/* Switch for various patterns */
			switch($imageType)
			{
				case 1:
				case 2:
				case 3:
				default:
					/* nothing yet */

				case 5:

					break;

				break;


			}
		}
			/* do this once moron */
			if ($this->counter == 0)
			{
					/* Update their Emblem */
				//$test = preg_replace('/70/','29',$this->data['emblem']);
					$emblem70 = $this->fileManage->getFileContents($this->data['emblem']);
					$emblem29 = $this->fileManage->getFileContents(preg_replace('/70/','29',$this->data['emblem']));
					$emblem45 = $this->fileManage->getFileContents(preg_replace('/70/','45',$this->data['emblem']));

					/* Create image from this stream */
					$emblemImage70 = imagecreatefromstring($emblem70);
					$emblemImage45 = imagecreatefromstring($emblem45);
				    $emblemImage29 = imagecreatefromstring($emblem29);

					/* Retain Transparency */
					imagealphablending( $emblemImage70, false );
					imagesavealpha( $emblemImage70, true );
					imagealphablending( $emblemImage45, false );
					imagesavealpha( $emblemImage45, true );
					imagealphablending( $emblemImage29, false );
					imagesavealpha( $emblemImage29, true );

					/* Write the stream */
					$this->pathToEmblem70 = "{$this->tempPath}reach/emblems/{$id}-70.png";
					$this->pathToEmblem45 = "{$this->tempPath}reach/emblems/{$id}-45.png";
				    $this->pathToEmblem29 = "{$this->tempPath}reach/emblems/{$id}-29.png";

					imagepng($emblemImage70,$this->pathToEmblem70);
					imagepng($emblemImage45,$this->pathToEmblem45);
				    imagepng($emblemImage29,$this->pathToEmblem29);

					imagedestroy($emblemImage70);
					imagedestroy($emblemImage45);
				    imagedestroy($emblemImage29);

				    unset($emblem70);
					unset($emblem45);
				    unset($emblem29);


				//--------------------------------------------
				// Rank Image
				//--------------------------------------------
				$this->pathToRank26 = "{$this->tempPath}reach/ranks/26/{$this->data['currentRankIndex']}.png";
			}

	//---------------------------------------------------
	// Watermarking
	//---------------------------------------------------

	/* Lets allow them to disable the watermark here
	   * Just set $watermark to a blank PNG image
	   * Core libraries and classes don't allow any changes yet
	   * Our code currently doesn't allow for just disabling of the watermark
	   * FFVF: Fix so we don't apply a blank image
	*/

			/* This usergroup have permissions to remove the watermark */
			if ($this->tier['tier']['wm'] == 1) # SHOULD BE 1
			{
				$watermark = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/watermarks/blank.png");
				//$watermark     = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/watermarks/default.png");
			}
			else
			{

				/* Were going to try and load the watermarks with the imageID */
				$check = file_exists(DOC_IPS_ROOT_PATH . "reach/watermarks/" . $imageType . ".png");

				/* Now if they have that ID load it, otherwise don't */
				if ($check === true)
				{
					/* Double check for grey image */
					if ($this->sigSettings['2']['id'] == 5 || $this->sigSettings['2']['id'] == 4)
					{
						$watermark     = imagecreatefrompng(DOC_IPS_ROOT_PATH . "reach/watermarks/2.png");
					}
					else
					{
						$watermark     = imagecreatefrompng(DOC_IPS_ROOT_PATH . "reach/watermarks/" . $imageType . ".png");
					}
				}
				else
				{
					$watermark     = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/watermarks/default.png");
				}
			}

		/* Get Dimesions */
		$water_w = imagesx($watermark);
		$water_h = imagesy($watermark);

		/* Set specific margins, to move away from sides */
		$marge_right = 10;
		$marge_left  = 10;

			//-------------------------------------------------
			// Make the image using imagecreate
			//-------------------------------------------------

		/* Switch for various patterns */
		switch($imageType)
		{
			case 1: #Pack 1
				$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack1.png");
				$size = getimagesize(DOC_IPS_ROOT_PATH ."reach/templates/pack1.png");
				$this->use = 0;
				break;

			case 2: #Pack 2

				/* Check for settings */
				if (($this->sigSettings['id']))
				{

					if ($this->sigSettings['sp'] == 1 && $this->sigSettings['id'] == 1)
					{
						$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack2.png");
						$size = getimagesize(DOC_IPS_ROOT_PATH ."reach/templates/pack2.png");
					}
					else
					{
						/* check for that image */
						if ((file_exists(DOC_IPS_ROOT_PATH ."reach/templates/pack2-" . $this->sigSettings['id'] . ".png")))
						{
							$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack2-" . $this->sigSettings['id'] . ".png");
							$size = getimagesize(DOC_IPS_ROOT_PATH ."reach/templates/pack2-" . $this->sigSettings['id'] . ".png");
						}
						else
						{
							$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack2.png");
							$size = getimagesize(DOC_IPS_ROOT_PATH ."reach/templates/pack2.png");
						}
					}
				}
				else
				{
					$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack2.png");
					$size = getimagesize(DOC_IPS_ROOT_PATH ."reach/templates/pack2.png");
				}
				$this->use = 0;
				break;

			case 3: #Pack 3
				$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack3.png");
				$size = getimagesize(DOC_IPS_ROOT_PATH ."reach/templates/pack3.png");
				$this->use = 1;
				break;

			case 4: #Pack 4
				$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack4.png");
				$size = getimagesize(DOC_IPS_ROOT_PATH ."reach/templates/pack4.png");
				$this->use = 0;
				break;

			case 5:
				/* Check if were null */
				if ($this->sigSettings == null)
				{
					$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack5-aqua.png");
					$size = getimagesize(DOC_IPS_ROOT_PATH . "reach/templates/pack5-aqua.png");
				}
				else
				{

						if ((file_exists(DOC_IPS_ROOT_PATH ."reach/templates/pack5-" . $this->sigSettings['color'] . ".png")))
						{
							$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack5-" . $this->sigSettings['color'] . ".png");
							$size = getimagesize(DOC_IPS_ROOT_PATH . "reach/templates/pack5-" . $this->sigSettings['color'] . ".png");
						}
						else
						{
							$template = imagecreatefrompng(DOC_IPS_ROOT_PATH ."reach/templates/pack5-aqua.png");
							$size = getimagesize(DOC_IPS_ROOT_PATH . "reach/templates/pack5-aqua.png");
						}

				}
				$this->use = 0;
				break;

			default:
				break;
		}


		/* Set up some default colors */
		$white = imagecolorallocate($template, 255, 255, 255);
		$black = imagecolorallocate($template, 0, 0, 0);
		$grey  = imagecolorallocate($template, 160, 160, 160);


		/* Add IF statement to determine if inactive during task. Then don't update data */
		if (($this->tier['visit'] > $this->tier['tier']['inactive'] && FLAG_INACTIVE) && ($task != TRUE))
		{
			switch($imageType)
			{
				case 1:
					/* Write the message */
					imagecopy($template,$watermark,imagesx($template) - ($water_w) - 11 , imagesy($template) - ($water_h + 3), 0, 0, imagesx($watermark), imagesy($watermark));
					imagestring($template, $this->fonts[0], 160, 40, $this->lang->words['inactive_1'], $black);
					imagestring($template, $this->fonts[0], 160, 62, $this->lang->words['inactive_2'], $black);
					break;

				case 2:
					imagecopy($template,$watermark,imagesx($template) - ($water_w) - 85 , imagesy($template) - ($water_h + 4), 0, 0, imagesx($watermark), imagesy($watermark));
					imagestring($template, $this->fonts[0], 160, 40, $this->lang->words['inactive_1'], $black);
					imagestring($template, $this->fonts[0], 160, 62, $this->lang->words['inactive_2'], $black);
					break;

				case 3:
					imagecopy($template,$watermark,imagesx($template)- ($water_w) - 5 , imagesy($template) - ($water_h + 5), 0, 0, imagesx($watermark), imagesy($watermark));
					imagestring($template, $this->fonts[0], 80, 1, $this->lang->words['inactive_1'], $white);
					imagestring($template, $this->fonts[0], 80, 9, $this->lang->words['inactive_2'], $white);
					break;

				case 4:
					imagecopy($template,$watermark,imagesx($template)- ($water_w) - 92 , imagesy($template) - ($water_h + 10), 0, 0, imagesx($watermark), imagesy($watermark));
					imagestring($template, $this->fonts[0], 20, 40, $this->lang->words['inactive_1'], $black);
					imagestring($template, $this->fonts[0], 20, 55, $this->lang->words['inactive_2'], $black);
					break;

				case 5:
					imagecopy($template,$watermark,imagesx($template)- ($water_w) - 53 , imagesy($template) - ($water_h + 2), 0, 0, imagesx($watermark), imagesy($watermark));
					imagestring($template, $this->fonts[0], 30, 1, $this->lang->words['inactive_1'], $white);
					imagestring($template, $this->fonts[0], 30, 15, $this->lang->words['inactive_2'], $white);
					break;

				default:
					break;
			}

			/* Set the filter */
			$this->inactive = 1;

			/* Retain Transparency */
			imagealphablending( $template, false );
			imagesavealpha( $template, true );
		}
	 	else
	 	{
	 		$this->inactive = 0;

		//-------------------------------------------------
		// Merging Image
		//-------------------------------------------------

			switch($imageType)
			{
				case 1: # Pack 1
					/* Create image from this stream */
					$emblem = imagecreatefrompng($this->pathToEmblem70);
					imagecopy($template,$emblem,imagesx($template) - (imagesx($emblem) + 14), imagesy($template) - (imagesy($emblem) + 24), 0, 0, imagesx($emblem), imagesy($emblem));
					imagedestroy($emblem);

					//-------------------------
					// Merging of Medals
					//-------------------------


					if ($this->sigSettings['1'])
					{
						/* coordinates for placing the 6 medals */
						$xCoord = array (
						'0' => 110, #110
						'1' => 170, #170
						'2' => 110, #230
						'3' => 170, #110
						'4' => 170, #170
						'5' => 230); #230

						$yCoord = array (
						'0' => 10, #10
						'1' => 50, #10
						'2' => 50, #10
						'3' => 10, #50
						'4' => 50, #50
						'5' => 50); #50

						$i = 0;
						foreach ($this->sigSettings as $medal)
						{
							/* Check for max, to prevent others */
							if ($i > 3)
							{
								continue;
							}

							/* Grab image, add text, destroy. Rinse and repeat */

							/* check if image exists -1 bug fattwam */
							if (file_exists(($pathToMedals . "36/" . $medal['Id'] . ".png")))
							{
								$medals[$i] = imagecreatefrompng($pathToMedals . "36/" . $medal['Id'] . ".png");
								imagecopy($template,$medals[$i],(imagesx($template) - ((imagesx($medals[$i]) + $xCoord[$i])) ), (imagesy($medals[$i]) - ((imagesy($medals[$i]) - $yCoord[$i]))), 0, 0, imagesx($medals[$i]), imagesy($medals[$i]));
								$this->imagettfstroketext($template,8,0,(imagesx($template) - ((imagesx($medals[$i]) + $xCoord[$i] - 25)) ), (imagesy($medals[$i]) - ((imagesy($medals[$i]) - $yCoord[$i] - 35))),$white, $black,$this->fonts['4'], $this->registry->getClass('class_localization')->formatNumber($this->data['medals'][$medal['Id']]['value']), 1);
								imagedestroy($medals[$i]);
							}
							$i++;
						}
					}
					/* Merge in progress */
					imagecopy($template,$watermark,imagesx($template) - ($water_w) - 11 , imagesy($template) - ($water_h + 3), 0, 0, imagesx($watermark), imagesy($watermark));

					/* Add Kills/Data/Anything */
					imagettftext($template, 18, 0, 5, 24, $black, $this->fonts[6], $this->gt . " .:. " . $this->data['service_tag']); # GT
					imagettftext($template, 9, 0, 7, 45, $black, $this->fonts[6], 'Armory Completed: ' . $this->data['armory_completion'] . "%");
					imagettftext($template, 9, 0, 7, 60, $black, $this->fonts[6], 'Daily Challenges Completed: ' . $this->data['daily_challenges']);
					imagettftext($template, 9, 0, 7, 75, $black, $this->fonts[6], 'Weekly Challenges Completed: ' . $this->data['weekly_challenges']);
					imagettftext($template, 9, 0, 7, 90, $black ,$this->fonts[6], 'Reach Playtime: ' . $this->data['total_playtime']);
					break;

				case 2: #Pack 2

					//---------------------------------------------
					// Sig Template 1 is different
					//---------------------------------------------

					if ((intval($this->sigSettings['sp']) == 1))
					{
						/* Merge in progress */
						imagecopy($template,$watermark,imagesx($template) - ($water_w) - 90 , imagesy($template) - ($water_h + 8), 0, 0, imagesx($watermark), imagesy($watermark));

						/* Add the data based on this template */
						imagettftext($template, 22, 0, 36, 36, $black, $this->fonts[3], $this->gt . " : " . $this->data['service_tag']);

						/* Kills etc */
						imagettftext($template, 10, 0, 31, 83, $black, $this->fonts[3], '     Kills: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_kills'])); # Kills
						imagettftext($template, 10, 0, 30, 99, $black, $this->fonts[3], 'Deaths: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_deaths'])); # Deaths
						imagettftext($template, 10, 0, 160, 83, $black, $this->fonts[3], 'Games Played: '. $this->registry->getClass('class_localization')->formatNumber( ($this->data['games_played']))); # Defeats
						imagettftext($template, 10, 0, 160, 99, $black, $this->fonts[3], '          Victories: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['games_won'])); # Victories
						imagettftext($template, 10, 0, 291, 83, $black, $this->fonts[3], ' Assists: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_assists'])); # Assissits
						imagettftext($template, 10, 0, 290, 99, $black, $this->fonts[3], 'Medals: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_medals'])); # Medals
						imagettftext($template, 16, 0, 36, 60, $black, $this->fonts[3], $this->data['rank'] );
					}
					else
					{
						/* Turn text white */
						if ((intval($this->sigSettings['id'] == 3)))
						{
							$black = imagecolorallocate($template, 255, 255, 255);
						}

						//------------------------------------------------------
						// ALL Other SIGS
						//------------------------------------------------------

						if (!($this->sigSettings['id'] == null))
						{
							/* Merge their emblem into top right */
							$emblem = imagecreatefrompng($this->pathToEmblem70);
							imagecopy($template,$emblem,imagesx($template) - (imagesx($emblem) + 8), imagesy($template) - (imagesy($emblem) + 4), 0, 0, imagesx($emblem), imagesy($emblem));
							imagedestroy($emblem);
						}
						/* Merge in progress */
						imagecopy($template,$watermark,imagesx($template) - ($water_w) - 85 , imagesy($template) - ($water_h + 4), 0, 0, imagesx($watermark), imagesy($watermark));

						/* Add the data based on this template */
						imagettftext($template, 22, 0, 25, 25, $black, $this->fonts[3], $this->gt . " : " . $this->data['service_tag']);

						/* Kills etc */
						imagettftext($template, 10, 0, 31, 70, $black, $this->fonts[3], '     Kills: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_kills'])); # Kills
						imagettftext($template, 10, 0, 30, 86, $black, $this->fonts[3], 'Deaths: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_deaths'])); # Deaths
						imagettftext($template, 10, 0, 160, 70, $black, $this->fonts[3], 'Games Played: '. $this->registry->getClass('class_localization')->formatNumber( ($this->data['games_played']))); # Defeats
						imagettftext($template, 10, 0, 160, 86, $black, $this->fonts[3], '          Victories: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['games_won'])); # Victories
						imagettftext($template, 10, 0, 291, 70, $black, $this->fonts[3], ' Assists: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_assists'])); # Assissits
						imagettftext($template, 10, 0, 290, 86, $black, $this->fonts[3], 'Medals: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_medals'])); # Medals
						imagettftext($template, 16, 0, 36, 48, $black, $this->fonts[3], $this->data['rank'] );
					}

					/* Retain Transparency */
					imagealphablending( $template, false );
					imagesavealpha( $template, true );
					break;

				case 3: #Tiny Sig
					/* Merge in progress */
					imagecopy($template,$watermark,imagesx($template)- ($water_w) - 5 , imagesy($template) - ($water_h + 5), 0, 0, imagesx($watermark), imagesy($watermark));

					/* Add some text */
					$this->imagettfstroketext($template,8,0,3,13,$white,$black,$this->fonts['4'], ('Reach Playtime: ' . $this->data['total_playtime']),1);

					/* Retain Transparency */
					imagealphablending( $template, false );
					imagesavealpha( $template, true );
					break;

				case 4: #Rank / Emblem
					/* Merge in progress */
					imagecopy($template,$watermark,imagesx($template)- ($water_w) - 92 , imagesy($template) - ($water_h + 10), 0, 0, imagesx($watermark), imagesy($watermark));

					/* Create image from this medal */
					$emblem = imagecreatefrompng($this->pathToEmblem45);
					imagecopy($template,$emblem,imagesx($template) - (imagesx($emblem) + 10), imagesy($template) - (imagesy($emblem) + 10), 0, 0, imagesx($emblem), imagesy($emblem));
					imagedestroy($emblem);

					/* Create image from this medal */
					$rank = imagecreatefrompng($this->pathToRank26);
					imagecopy($template,$rank,imagesx($template) - (imagesx($rank) + 99), imagesy($template) - (imagesy($rank) + 26), 0, 0, imagesx($rank), imagesy($rank));
					imagedestroy($rank);

					/* Add some text */
					imagettftext($template, 10, 0, 15, 50, $black, $this->fonts[7], '      Kills: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_kills']) . '                Defeats: '. $this->registry->getClass('class_localization')->formatNumber( $this->data['games_lost']));
					imagettftext($template, 10, 0, 15, 65, $black, $this->fonts[7], 'Deaths: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['total_deaths']).     '               Victories: ' . $this->registry->getClass('class_localization')->formatNumber( $this->data['games_won'])); # Victories;
					imagettftext($template, 10, 0, 15, 80, $black, $this->fonts[7], 'Armory Completed: ' . $this->data['armory_completion'] . "%");
					/* Add the data based on this template */
					imagettftext($template, 18, 0, 15, 25, $black, $this->fonts[7], $this->gt);
					imagettftext($template, 18, 0, 290, 25, $black, $this->fonts[7], $this->data['service_tag']);

					/* Retain Transparency */
					imagealphablending( $template, false );
					imagesavealpha( $template, true );
					break;

				case 5: #Tiny little guy
					/* Merge in progress */
					imagecopy($template,$watermark,imagesx($template)- ($water_w) - 53 , imagesy($template) - ($water_h + 2), 0, 0, imagesx($watermark), imagesy($watermark));

					/* Create image from this medal */
					$emblem = imagecreatefrompng($this->pathToEmblem29);
					imagecopy($template,$emblem,imagesx($template) - (imagesx($emblem) + 215 ), imagesy($template) - (imagesy($emblem) + 2), 0, 0, imagesx($emblem), imagesy($emblem));
					imagedestroy($emblem);

					/* Create image from this medal */
					$rank = imagecreatefrompng($this->pathToRank26);
					imagecopy($template,$rank,imagesx($template) - (imagesx($rank) + 18), imagesy($template) - (imagesy($rank) + 4), 0, 0, imagesx($rank), imagesy($rank));
					imagedestroy($rank);

					/* Add the data based on this template */
					imagettftext($template, 12, 0, 40, 15, $white, $this->fonts[7], $this->gt);
					imagettftext($template, 12, 0, 40, 30, $white, $this->fonts[7], $this->data['service_tag']);

					/* Retain Transparency */
					imagealphablending( $template, false );
					imagesavealpha( $template, true );
					break;

				default:
					break;

			}
	 	}

		//--------------------------------------
		// START: This runs every sig
		//--------------------------------------

		/* Convert that stream into an image */
		imagepng($template, $pathToSigs);

		}

		/* Send that image to be saved */
		$this->_saveImage($q,$dirToSigs, $pathToSigs);

		/* Clean up old stuff */
		imagedestroy($watermark);
		imagedestroy($template);

		/* counter for one time things */
		$this->counter++;
	}

	private function loadFonts()
	{
		/* List of fonts, statically loaded */
		//$this->fonts[0] = $this->tempPath . 'reach/fonts/cambriab.ttf';
		//$this->fonts[1] = $this->tempPath . 'reach/fonts/berlinsans.ttf';
		//$this->fonts[2] = $this->tempPath . 'reach/fonts/calibri.ttf';
		$this->fonts[3] = $this->tempPath . 'reach/fonts/myriad_reg.otf';
		$this->fonts[4] = $this->tempPath . 'reach/fonts/visitor.ttf';
		//$this->fonts[5] = $this->tempPath . 'reach/fonts/gothic.ttf';
		$this->fonts[6] = $this->tempPath . 'reach/fonts/segoeuil.ttf';
		$this->fonts[7] = $this->tempPath . 'reach/fonts/conduit.ttf';

		return $this->fonts;
	}

	public function getSigs($id)
	{
		/* Loading Sigs, much like loading data */
		$result = $this->DB->buildAndFetch(array(
			'select' 		=> 'sigs',
			'from' 			=> 'reachstat',
			'where' 		=> "id='" . intval($id) . "'"));

		/* Unserialize then return */
		return unserialize($result['sigs']);
	}

	private function _saveImage($imageNum, $dir, $path)
	{
		/* Setting Path/DIR */
		$sigs['url']  = $dir;
		$sigs['path'] = $path;
		$sigs['use']  = $this->use;

		/*Need these nums for BBCODE/DIRECT url */
		$sigs['num1']  = intval(($imageNum + $imageNum));
		$sigs['num2']  = intval(($imageNum+ $imageNum + 1));

		/* Settings added in */
		//$sigs['settings'] = serialize($settings);

		/* Add this to total sigs */
		$this->sigs[$this->sigCount] = $sigs;
		$this->sigCount++;

		/* Add at end */
		if ($this->sigCount == $this->max)
		{
			$this->_saveAllImages($this->id,$this->sigs);
		}

	}

	private function _saveAllImages($id, $sigs)
	{

		/* Lets delete the emblems */
		if (file_exists($this->pathToEmblem70))
		{
			unlink($this->pathToEmblem70);
		}
		if (file_exists($this->pathToEmblem45))
		{
			unlink($this->pathToEmblem45);
		}
		if (file_exists($this->pathToEmblem29))
		{
			unlink($this->pathToEmblem29);
		}
		/* Serialize array for storage */
		$serialData = serialize($sigs);

		/* Now update into Database. */
		$this->DB->update('reachstat', array(
			'id'	   => intval($id),
			'sigs'	   => $serialData,
			'sig_date' => time(),
			'inactive'	=> $this->inactive),
			"id=".intval($id));
	}

	public function doThemAll($id, $gt, $task = false)
	{
		$this->counter = 0;
		require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
		$this->reach = new reachStats( $this->registry );

		$data = null;
		$this->gt = null;

		/* Need fresh data */
		$data = $this->reach->getAllReachData($id);

		/* Loop for each image */
		for ($x = 1; $x <= $this->max; $x++)
		{
			$this->umImageNao($id, $gt, $data, $x, $data['settings'][$x], $task);
		}

		return $this->sigs;
	}

	private function _permCheck($id)
	{
		/* temp var */
		$tier = array();

		/* We need to get their groupID using just their ID */
		$result = $this->DB->buildAndFetch(array(
				'select' 		=> 'member_group_id',
				'from' 			=> 'members',
				'where' 		=> "member_id='" . intval($id) . "'"));

		/* Send to the tier check */
		$this->tier = $this->libary->getUserData($result['member_group_id']);
	}

	public function deleteAllImages($id)
	{

	}

	/**
	 * Writes the given text with a border into the image using TrueType fonts.
	 * @author John Ciacia < John@extreme-hq.com >
	 * @param image An image resource.
	 * @param size The font size.
	 * @param angle The angle in degrees to rotate the text.
	 * @param x Upper left corner of the text.
	 * @param y Lower left corner of the text.
	 * @param textcolor This is the color of the main text.
	 * @param strokecolor This is the color of the text border.
	 * @param fontfile The path to the TrueType font you wish to use.
	 * @param text The text string in UTF-8 encoding.
	 * @param px Number of pixels the text border will be.
	 * @return Returns an array with 8 elements representing four points making the bounding
	 *         box of the text. The order of the points is lower left, lower right, upper right,
	 *         upper left. The points are relative to the text regardless of the angle, so
	 *         "upper left" means in the top left-hand corner when you see the text horizontally.
	 * @see http://us.php.net/manual/en/function.imagettftext.php
	 * @see http://forum.codecall.net
	 * @see http://www.extreme-hq.com
	 */
	private function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px)
	{

		for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
			for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
				$bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

		return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
	}
}