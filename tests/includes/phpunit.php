<?php
/*
 * Bootstrap.
 */
namespace WebSharks\CommentMail\Pro;

require_once $_SERVER['CI_NFO_PHPUNIT_BOOTSTRAP'];
require_once $_SERVER['CI_NFO_WWW_DIR'].'/wp-load.php';

$GLOBALS[GLOBAL_NS]->optionsSave(['enable' => '1']);
