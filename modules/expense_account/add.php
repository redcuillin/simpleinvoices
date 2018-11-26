<?php

use Inc\Claz\DomainId;
use Inc\Claz\Util;

global $smarty;

//stop the direct browsing to this file - let index.php handle which files get displayed
Util::isAccessAllowed();

//if valid then do save
if (!empty($_POST['name'])) {
	include("modules/expense_account/save.php");
}

$smarty->assign('domain_id', DomainId::get());
$smarty->assign('pageActive'   , 'expense_account');
$smarty->assign('subPageActive', 'add');
$smarty->assign('active_tab'   , '#money');
