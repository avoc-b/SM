<?php
/**
 * Elly Framework
 * http://333.kg
 *
 * @author CS-SOFT <www.333.kg>
 * @copyright CS-SOFT 2014
 */

session_start();
ini_set('display_errors', TRUE);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
error_reporting(E_ALL);

include 'system/lib/core.class.php';
include 'system/lib/debug.class.php';
include 'system/lib/theme.class.php';
include 'system/lib/elly.class.php';
include 'system/lib/run.class.php';
include 'system/lib/controller.class.php';
include 'system/lib/model.class.php';
include 'system/lib/plugin.class.php';

$lang  = elly::LoadLang('common');

new Run();

?>