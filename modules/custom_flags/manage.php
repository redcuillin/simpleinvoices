<?php

use Inc\Claz\CustomFlags;
use Inc\Claz\Util;

/*
 * Script: manage.php
 * Custom flags manage page
 *
 * Authors:
 * Richard Rowley
 *
 * Last edited:
 * 2015-09-23
 *
 * License:
 * GPL v3 or above
 */
global $smarty;

// stop the direct browsing to this file - let index.php handle which files get displayed
Util::isAccessAllowed();

$cflgs = CustomFlags::getCustomFlags();
$smarty->assign('cflgs', $cflgs);

$smarty->assign('pageActive', 'custom_flags');
$smarty->assign('active_tab', '#setting');
