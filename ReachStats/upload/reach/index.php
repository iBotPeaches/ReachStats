<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2010
 */

define( 'IPB_THIS_SCRIPT', 'public' );
require_once( './initdata.php' );

require_once( IPS_ROOT_PATH . 'sources/base/ipsRegistry.php' );
require_once( IPS_ROOT_PATH . 'sources/base/ipsController.php' );

ipsController::run();

exit();