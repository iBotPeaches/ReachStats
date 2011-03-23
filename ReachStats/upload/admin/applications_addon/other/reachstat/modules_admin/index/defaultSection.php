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

/**
 * Very simply returns the default section if one is not
 * passed in the URL
 */

$DEFAULT_SECTION = 'overview';
