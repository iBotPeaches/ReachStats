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

class profile_weapons extends profile_plugin_parent
	{
	/**
	 * Attachment object
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $attach;
	protected $data;

	/**
	 * Feturn HTML block
	 *
	 * @access	public
	 * @param	array		Member information
	 * @return	string		HTML block
	 */
	public function return_html_block( $member=array() )
	{

//		/* Load reachStat class */
//		if ( ! $this->registry->isClassLoaded( 'reachStats' ) )
//		{
//
//			/* Classes */
//			require_once( IPSLib::getAppDir( 'reachstat' ) . '/sources/classReachStats.php' );
//			$this->reach = new reachStats( ipsRegistry::instance() );
//		}
//
//		//-----------------------------------------
//		// Return content..
//		//-----------------------------------------
//
//		/* We can assume since were here that the data has been updated, so we should just pull it */
//		$this->data = $this->reach->getReachData($this->memberData['member_id']);
//
//		/* num set */
//		$num = 0;
//		$num1 = 0;
//
//		/* Weapons (needed for Google Graphs API */
//		foreach ($this->data['stats'] as $stat){
//
//			/* Check for vehicle */
//			if ($stat['type'] == 'vehicle') {
//				$temp1[$num]= $stat;
//
//				/* seperate counters */
//				$num++;
//			}
//			else
//			{
//				$temp[$num1]  = $stat;
//
//				/* add it */
//				$num1++;
//			}
//		}
//
//		/* sort for IDs */
//		sort($temp);
//
//		/* encode Kills */
//		$google  = json_encode($temp);
//
//		/* cleanup */
//		unset($num);
//		unset($num1);
//		unset($temp);
//		unset($temp1);

		/* Test data */
		$data =<<<EOD
[{"Key":4,"deaths":124,"name":"Magnum - M6G Pistol","type":"weapon","kills":199,"ratio":1.6048387096774},{"Key":5,"deaths":152,"name":"Assault Rifle - MA37 ICWS","type":"weapon","kills":98,"ratio":0.64473684210526},{"Key":6,"deaths":339,"name":"DMR - M392","type":"weapon","kills":560,"ratio":1.6519174041298},{"Key":7,"deaths":70,"name":"Shotgun - M45 TS","type":"weapon","kills":154,"ratio":2.2},{"Key":8,"deaths":143,"name":"Sniper Rifle - SRS99","type":"weapon","kills":306,"ratio":2.1398601398601},{"Key":9,"deaths":45,"name":"Rocket Launcher - M41SSR","type":"weapon","kills":153,"ratio":3.4},{"Key":10,"deaths":2,"name":"Spartan Laser - WAV M6 GGNR","type":"weapon","kills":6,"ratio":3},{"Key":11,"deaths":142,"name":"Frag Grenade - M9 HE-DP","type":"weapon","kills":188,"ratio":1.3239436619718},{"Key":12,"deaths":4,"name":"GrenadeLauncher - M319 IGL","type":"weapon","kills":4,"ratio":1},{"Key":13,"deaths":1,"name":"Plasma Pistol - T25 DEP","type":"weapon","ratio":-1,"kills":0},{"Key":14,"deaths":16,"name":"Needler - T33 GML","type":"weapon","kills":69,"ratio":4.3125},{"Key":16,"deaths":16,"name":"Plasma Repeater - T51 DER\/I","type":"weapon","kills":16,"ratio":1},{"Key":17,"deaths":72,"name":"Needler Rifle - T31 Rifle","type":"weapon","kills":181,"ratio":2.5138888888889},{"Key":19,"deaths":10,"name":"Plasma Launcher - T52 GML\/E","type":"weapon","kills":21,"ratio":2.1},{"Key":20,"kills":3,"name":"Gravity Hammer - T2 EW\/H","type":"weapon","deaths":3,"ratio":1},{"Key":21,"deaths":56,"name":"Energy Sword - T1 EW\/S","type":"weapon","kills":84,"ratio":1.5},{"Key":22,"deaths":56,"name":"Plasma Grenade - T1 AP-G","type":"weapon","kills":129,"ratio":2.3035714285714},{"Key":23,"deaths":8,"name":"Concussion Rifle - T50 DER\/H","type":"weapon","kills":20,"ratio":2.5},{"Key":41,"deaths":16,"name":"Falling Damage","type":"weapon","ratio":-16,"kills":0},{"Key":42,"deaths":10,"name":"Collision Damage","type":"weapon","ratio":-10,"kills":0},{"Key":43,"deaths":297,"name":"Melee","type":"weapon","kills":272,"ratio":0.91582491582492},{"Key":44,"deaths":1,"name":"Explosion","type":"weapon","kills":50,"ratio":50},{"Key":46,"deaths":23,"name":"Flag","type":"weapon","kills":38,"ratio":1.6521739130435},{"Key":47,"deaths":10,"name":"Bomb - No. 14 Anti-tank Mine","type":"weapon","kills":8,"ratio":0.8},{"Key":48,"deaths":2,"name":"Bomb Explosion","type":"weapon","ratio":-2,"kills":0},{"Key":49,"deaths":2,"name":"Ball - Cranium.","type":"weapon","kills":4,"ratio":2},{"Key":54,"deaths":13,"name":"Heavy Machine Gun - AIE-486H","type":"weapon","kills":6,"ratio":0.46153846153846},{"Key":55,"deaths":2,"name":"Plasma Cannon - T42 DESW","type":"weapon","kills":5,"ratio":2.5},{"Key":63,"kills":22,"name":"Focus Rifle - T52 SAR","type":"weapon","deaths":4,"ratio":5.5},{"Key":64,"kills":8,"name":"Fuel Rod Gun - Type-33 LAAW","type":"weapon","ratio":8,"deaths":0}]
EOD;

		$content = $this->registry->output->getTemplate('stattrack')->kills_graphNew($data);

		/* send home */
		return $content;
	}
}