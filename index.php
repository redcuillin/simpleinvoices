<?php
/*
 * Script: index.php
 * Main controller file for SimpleInvoices
 * License:
 * GPL v3 or above
 */
// stop browsing to files directly - all viewing to be handled by index.php
// if browse not defined then the page will exit
if (!defined("BROWSE")) define("BROWSE", "browse");

// **********************************************************
// The include configs and requirements stuff section - START
// **********************************************************

// Load stuff required before init.php
require_once "include/init_pre.php";

$module = isset($_GET['module']) ? filenameEscape($_GET['module']) : null;
$view   = isset($_GET['view'])   ? filenameEscape($_GET['view'])   : null;
$action = isset($_GET['case'])   ? filenameEscape($_GET['case'])   : null;

// globals set in the init.php logic
$databaseBuilt     = false;
$databasePopulated = false;

// Will be set in the following init.php call to extensions that are enabled.
$ext_names = array();
$help_image_path = "images/common/";

$config = null;
// Note: include/functions.php and include/sql_queries.php loaded by this include.
require_once "include/init.php";
global $smarty,
       $smarty_output,
       $menu,
       $LANG,
       $logger,
       $siUrl,
       $config,
       $auth_session,
       $early_exit;

$logger->log("index.php - After init.php", Zend_Log::DEBUG);
foreach ($ext_names as $ext_name) {
    if (file_exists("extensions/$ext_name/include/init.php")) {
        require_once ("extensions/$ext_name/include/init.php");
    }
}
$logger->log("index.php - After processing init.php for extensions", Zend_Log::DEBUG);

$smarty->assign("help_image_path", $help_image_path);

// **********************************************************
// The include configs and requirements stuff section - END
// **********************************************************
$smarty->assign("ext_names", $ext_names);
$smarty->assign("config"   , $config);
$smarty->assign("module"   , $module);
$smarty->assign("view"     , $view);
$smarty->assign("siUrl"    , $siUrl);
$smarty->assign("LANG"     , $LANG);
$smarty->assign("enabled"  , array($LANG['disabled'],$LANG['enabled']));

// Menu - hide or show menu
$menu = (isset($menu) ? $menu : true);

// Check for any unapplied SQL patches when going home
// TODO - redo this code
$logger->log("index.php - module[$module] view[$view] " .
             "databaseBuilt[$databaseBuilt] databasePopulated[$databasePopulated]", Zend_Log::DEBUG);
if (($module == "options") && ($view == "database_sqlpatches")) {
    include_once ('include/sql_patches.php');
    donePatches();
} else {
    // Check that database structure has been built and populated.
    $skip_db_patches = false;
    if (!$databaseBuilt) {
        $module = "install";
        $view == "structure" ? $view = "structure" : $view = "index";
        $skip_db_patches = true; // do installer
    } else if (!$databasePopulated) {
        $module = "install";
        $view == "essential" ? $view = "essential" : $view = "structure";
        $skip_db_patches = true; // do installer
    }

    $logger->log("index.php - skip_db_patches[$skip_db_patches]", Zend_Log::DEBUG);

    // See if we need to verify patches have been loaded.
    if (!$skip_db_patches) {
        // If default user or an active session exists, proceed with check.
        if ($config->authentication->enabled == 0 || isset($auth_session->id)) {
            include_once ('./include/sql_patches.php');
            // Check if there are patches to process
            if (getNumberOfPatches() > 0) {
                $view = "database_sqlpatches";
                $module = "options";
                if ($action == "run") {
                    runPatches();
                } else {
                    listPatches();
                }
                $menu = false;
            } else {
                // There aren't patches to apply. So check to see if there are invoices in db.
                // If so, show the home page as default. Otherwise show Manage Invoices page
                if ($module == null) {
                    if (Invoice::count() > 0) {
                        $module = "invoices";
                        $view = "manage";
                    } else {
                        $module = "index";
                        $view = "index";
                    }
                }
            }
        }
    }
}

$logger->log("index.php - module[" . (empty($module) ? "" : $module) .
                         "] view[" . (empty($view) ? "" : $view) .
                       "] action[" . (empty($action) ? "" : $action) .
                           "] id[" . (empty($_GET['id']) ? "" : $_GET['id']) .
                         "] menu[$menu]", Zend_Log::DEBUG);

// Don't include the header if requested file is an invoice template.
// For print preview etc.. header is not needed
if (($module == "invoices") && (strstr($view, "template"))) {
    // Loop through the extensions. Load the module path php file for it if one exists.
    // TODO: Make this more efficient.
    $extensionInvoiceTemplateFile = 0;
    foreach ($ext_names as $ext_name) {
        if (file_exists("extensions/$ext_name/modules/invoices/template.php")) {
            include_once ("extensions/$ext_name/modules/invoices/template.php");
            $extensionInvoiceTemplateFile++;
        }
    }

    // Get the default module path php if their aren't any for enabled extensions.
    if (($extensionInvoiceTemplateFile == 0) && ($my_path = getCustomPath("invoices/template", 'module'))) {
        include_once ($my_path);
    }
    exit(0);
}
$logger->log("index.php - After invoices/template", Zend_Log::DEBUG);

// Check for "api" module or a "xml" or "ajax" "page request" (aka view)
if (strstr($module, "api") || (strstr($view, "xml") || (strstr($view, "ajax")))) {
    $extensionXml = 0;
    foreach ($ext_names as $ext_name) {
        if (file_exists("extensions/$ext_name/modules/$module/$view.php")) {
            include ("extensions/$ext_name/modules/$module/$view.php");
            $extensionXml++;
        }
    }

    // Load default if none found for enabled extensions.
    if ($extensionXml == 0 && $my_path = getCustomPath("$module/$view", 'module')) {
        include ($my_path);
    }
    exit(0);
}
$logger->log("index.php - After api/xml or ajax", Zend_Log::DEBUG);

// **********************************************************
// Prep the page - load the header stuff - START
// **********************************************************

// To remove the js error due to multiple document.ready.function()
// in jquery.datePicker.js, jquery.autocomplete.conf.js and jquery.accordian.js
// without instances in manage pages - Ap.Muthu
// TODO: fix the javascript or move datapicker to extjs to fix this hack - not nice
$extension_jquery_files = "";
foreach ($ext_names as $ext_name) {
    if (file_exists("extensions/$ext_name/include/jquery/$ext_name.jquery.ext.js")) {
        // @formatter:off
        $extension_jquery_files .=
            '<script type="text/javascript" src="extensions/' .
                     $ext_name . '/include/jquery/' .
                     $ext_name . '.jquery.ext.js">' .
            '</script>';
        // @formatter:on
    }
}
$smarty->assign("extension_jquery_files", $extension_jquery_files);

$logger->log("index.php - After extension_jquery_files", Zend_Log::DEBUG);

// Load any hooks that are defined for extensions
foreach ($ext_names as $ext_name) {
    if (file_exists("extensions/$ext_name/templates/default/hooks.tpl")) {
        $smarty->$smarty_output("extensions/$ext_name/templates/default/hooks.tpl");
    }
}
// Load standard hooks file. Note that any module hooks loaded will not be
// impacted by loading this file.
$smarty->$smarty_output("custom/hooks.tpl");

$logger->log("index.php - after custom/hooks.tpl", Zend_Log::DEBUG);

if (!in_array($module . "_" . $view, $early_exit)) {
    $extensionHeader = 0;
    foreach ($ext_names as $ext_name) {
        if (file_exists("extensions/$ext_name/templates/default/header.tpl")) {
            $smarty->$smarty_output("extensions/$ext_name/templates/default/header.tpl");
            $extensionHeader++;
        }
    }

    if ($extensionHeader == 0) {
        $my_path = getCustomPath('header');
        $smarty->$smarty_output($my_path);
    }
}
$logger->log("index.php - after header.tpl", Zend_Log::DEBUG);

// **********************************************************
// Prep the page - load the header stuff - END
// **********************************************************

// **********************************************************
// Include php file for the requested page section - START
// **********************************************************
// See https://simpleinvoices.group/howto page extension topic.
$extension_php_insert_files = array();
if ($extension_php_insert_files) {} // Show variable as used.

$perform_extension_php_insertions = (($module == 'system_defaults' && $view == 'edit'));
$extensionPhpFile = 0;
foreach ($ext_names as $ext_name) {
    $phpfile = "extensions/$ext_name/modules/$module/$view.php";
    if (file_exists($phpfile)) {
        // If $perform_extension_php_insertions is true, then the extension php
        // file content is to be included in the standard php file. Otherwise,
        // the file is a replacement for the standard php file.
        if ($perform_extension_php_insertions) {
            // @formatter:off
            $vals = array("file"   => $phpfile,
                          "module" => $module,
                          "view"   => $view);
            $extension_php_insert_files[$ext_name] = $vals;
            // @formatter:on
        } else {
            include $phpfile;
            $extensionPhpFile++;
        }
    }
}
$logger->log("index.php - After extension_php_insert_files, etc.", Zend_Log::DEBUG);

if ($extensionPhpFile == 0 && ($my_path = getCustomPath("$module/$view", 'module'))) {
    $logger->log("index.php - my_path[$my_path]", Zend_Log::DEBUG);
    include $my_path;
}
// **********************************************************
// Include php file for the requested page section - END
// **********************************************************
if ($module == "export" || $view == "export") {
    exit(0);
}
$logger->log("index.php - After export/export exit", Zend_Log::DEBUG);

// **********************************************************
// Post load javascript files - START
// NOTE: This is loaded after the .php file so that it can
// use script load for the .php file.
// **********************************************************
foreach ($ext_names as $ext_name) {
    if (file_exists("extensions/$ext_name/include/jquery/$ext_name.post_load.jquery.ext.js.tpl")) {
        $smarty->$smarty_output("extensions/$ext_name/include/jquery/$ext_name.post_load.jquery.ext.js.tpl");
    }
}

// NOTE: Don't load the default file if we are processing an authentication "auth" request.
// if ($extensionPostLoadJquery == 0 && $module != 'auth') {
if ($module != 'auth') {
    $smarty->$smarty_output("include/jquery/post_load.jquery.ext.js.tpl");
}
$logger->log("index.php - post_load...", Zend_Log::DEBUG);

// **********************************************************
// Post load javascript files - END
// **********************************************************

// **********************************************************
// Main: Custom menu - START
// **********************************************************
if ($menu) {
    // Check for menu.tpl files for extensions. The content of these files is:
    //
    // <!-- BEFORE:tax_rates -->
    // <li>
    // <a {if $pageActive == "custom_flags"} class="active"{/if} href="index.php?module=custom_flags&amp;view=manage">
    // {$LANG.custom_flags_upper}
    // </a>
    // </li>
    // {if $subPageActive == "custom_flags_view"}
    // <li>
    // <a class="active active_subpage" href="#">
    // {$LANG.view}
    // </a>
    // </li>
    // {/if}
    // {if $subPageActive == "custom_flags_edit"}
    // <li>
    // <a class="active active_subpage" href="#">
    // {$LANG.edit}
    // </a>
    // </li>
    // {/if}
    //
    // This means the content of the extension's menu.tpl file will be inserted before the
    // following line in the default menu.tpl file:
    //
    // <!~- SECTION:tax_rates -->
    //
    // If no matching section is found, the file will NOT be inserted.
    $my_path = getCustomPath('menu');
    $logger->log("index.php - menu my_path[$my_path]", Zend_Log::DEBUG);

    $menutpl = $smarty->fetch($my_path);
    $lines = array();
    $sections = array();
    Funcs::menuSections($menutpl, $lines, $sections);
    $menutpl = Funcs::mergeMenuSections($ext_names, $lines, $sections);
    echo $menutpl;
}
$logger->log("index.php - After menutpl processed", Zend_Log::DEBUG);

// **********************************************************
// Main: Custom menu - END
// **********************************************************

// **********************************************************
// Main: Custom layout - START
// **********************************************************
if (!in_array($module . "_" . $view, $early_exit)) {
    $extensionMain = 0;
    foreach ($ext_names as $ext_name) {
        if (file_exists("extensions/$ext_name/templates/default/main.tpl")) {
            $smarty->$smarty_output("extensions/$ext_name/templates/default/main.tpl");
            $extensionMain++;
        }
    }

    if ($extensionMain == "0") {
        $smarty->$smarty_output(getCustomPath('main'));
    }
}
$logger->log("index.php - After main.tpl", Zend_Log::DEBUG);
// **********************************************************
// Main: Custom layout - END
// **********************************************************

// **********************************************************
// Smarty template load - START
// **********************************************************
$extensionTemplates = 0;
$my_tpl_path = '';
$path = '';
// For extensions with a report, this logic allows them to be inserted into the
// the report menu (index.tpl) without having to replicate the content of that
// file. There two ways to insert content; either as a new menu section or as
// an appendage to an existing section. There are examples of each of these.
// Refer to the "expense" extension report index.tpl file for insertion of
// a new menu section. Note the "data-section" with the "BEFORE" entry. This
// tells the program to insert the menu before the menu section with the
// "$LANG.xxxxx" value that appears following the "BEFORE" statement. To
// append to an existing menu section, refer to the report index.tpl file
// for the "past_due_report" extension. Note the "data-section" attribute
// in the "<span ...>" tag. This tells the program to insert the report
// menu item at the end of the section with "$LANG.xxxxx" value assigned
// to the attribute.
$extension_insertion_files = array();
$perform_extension_insertions = (($module == 'reports'         && $view == 'index') ||
                                 ($module == 'system_defaults' && $view == 'manage'));

foreach ($ext_names as $ext_name) {
    $tpl_file = "extensions/$ext_name/templates/default/$module/$view.tpl";
    if (file_exists($tpl_file)) {
        // If $perform_extension_insertions is true, the $path and
        // $extensionTemplates are not set/incremented intentionally.
        // The logic runs through the normal report template logic
        // with the index.tpl files for each one of the extensions
        // reports will be loaded for the section it goes in.
        if ($perform_extension_insertions) {
            $content = file_get_contents($tpl_file);
            $type = "";
            if (($pos = strpos($content, 'data-section="')) === false) {
                $section = $smarty->tpl_vars['LANG']->value['other'];
            } else {
                $pos += 14;
                $str = substr($content, $pos);
                if (preg_match('/^BEFORE \{\$LANG\./', $str)) {
                    $pos += 14;
                    $type = "BEFORE ";
                } else {
                    $pos += 7;
                    $type = "";
                }
                $end = strpos($content, '}', $pos);
                $len = $end - $pos;
                $lang_element = substr($content, $pos, $len);
                $section = $smarty->tpl_vars['LANG']->value[$lang_element];
            }
            // @formatter:off
            $vals = array("file"    => $tpl_file,
                          "module"  => $module,
                          "section" => $type . $section);
            $extension_insertion_files[] = $vals;
            // @formatter:on
        } else {
            $path = "extensions/$ext_name/templates/default/$module/";
            $my_tpl_path = $tpl_file;
            $extensionTemplates++;
        }
    }
}
$logger->log("index.php - After $module/$view.tpl", Zend_Log::DEBUG);

// TODO: if more than one extension has a template for the requested file, thats trouble :(
// This won't happen for reports, standard menu.tpl and system_defaults menu.tpl given
// changes implemented in this file for them. Similar changes should be implemented for
// other templates as needed.
if ($extensionTemplates == 0) {
    if ($my_tpl_path = getCustomPath("$module/$view")) {
        $path = dirname($my_tpl_path) . '/';
        $extensionTemplates++;
    }
}

$smarty->assign("extension_insertion_files"   , $extension_insertion_files);
$smarty->assign("perform_extension_insertions", $perform_extension_insertions);
$smarty->assign("path"                        , $path);

$smarty->$smarty_output($my_tpl_path);
$logger->log("index.php - After output my_tpl_path[$my_tpl_path]", Zend_Log::DEBUG);

// If no smarty template - add message
if ($extensionTemplates == 0) {
    error_log("NO TEMPLATE!!! for module[$module] view[$view]");
}
// **********************************************************
// Smarty template load - END
// **********************************************************

// **********************************************************
// Footer - START
// **********************************************************
if (!in_array($module . "_" . $view, $early_exit)) {
    $extensionFooter = 0;
    foreach ($ext_names as $ext_name) {
        if (file_exists("extensions/$ext_name/templates/default/footer.tpl")) {
            $smarty->$smarty_output("extensions/$ext_name/templates/default/footer.tpl");
            $extensionFooter++;
        }
    }

    if ($extensionFooter == 0) {
        $smarty->$smarty_output(getCustomPath('footer'));
    }
}
$logger->log("index.php - At END", Zend_Log::DEBUG);
// **********************************************************
// Footer - END
// **********************************************************
