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

/**
 * Plug in name (Default tab name)
 */
$CONFIG['plugin_name']        = 'Basic';

/**
 * Language string for the tab
 */
$CONFIG['plugin_lang_bit']    = 'tab_basic';

/**
 * Plug in key (must be the same as the main {file}.php name
 */
$CONFIG['plugin_key']         = 'basic';

/**
 * Show tab?
 */
$CONFIG['plugin_enabled']     = 1;

/**
 * Order
 */
$CONFIG['plugin_order'] = 1;