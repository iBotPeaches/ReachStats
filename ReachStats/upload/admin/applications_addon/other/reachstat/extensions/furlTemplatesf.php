<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */
if ( !defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}
$_SEOTEMPLATES = array(

'app=reachstat' => array( 	 'app'			 => 'reachstat',
							 'allowRedirect' => 1,
							 'out'		     => array( '#app=reachstat$#i', 'reachstats/' ),
							 'in'			 => array( 'regex'   => "#/reachstat/?$#i",
													   'matches' => array( array( 'app', 'reachstat' )))),);