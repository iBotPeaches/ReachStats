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

$CACHE['metadata'] = array(
    'array' => 1,
    'allow_unload' => 0,
    'default_load' => 1,
    'recache_file' => IPSLib::getAppDir('reachstat') . '/sources/classReachStats.php',
    'recache_class' => 'reachStats',
    'recache_function' => 'setUp'
    );
$CACHE['challenges'] = array(
	'array' => 1,
	'allow_unload' => 0,
	'default_load' => 1,
	'recache_file' => IPSLib::getAppDir('reachstat') . '/sources/classReachStats.php',
	'recache_class' => 'reachStats',
	'recache_function' => 'getTheChallenges'
);
$CACHE['compare']	 = array(
	'array' => 1,
	'allow_unload'	=> 0,
	'default_load' => 0,
	'recache_file' => IPSLib::getAppDir('reachstat') . '/sources/pointsClass.php',
	'recache_class' => 'pointsClass',
	'recache_function' => 'setCacheCompareArray'
);
$CACHE['leaderboard'] = array(
	'array' => 1,
	'allow_unload'	=> 0,
	'default_load' => 1,
	'recache_file' => IPSLib::getAppDir('reachstat') . '/sources/classReachStats.php',
	'recache_class' => 'reachStats',
	'recache_function' => 'setLeaderboards'
);
/* $CACHE['front_leaderboard'] = array(
	'array' => 1,
	'allow_unload'	=> 0,
	'default_load' => 1,
	'recache_file' => IPSLib::getAppDir('reachstat') . '/sources/classReachStats.php',
	'recache_class' => 'reachStats',
	'recache_function' => 'setIndexLeaderboard'
);*/


