<?php
/**
 * Plugin.
 *
 * @since 160212 PSR compliance.
 */
namespace WebSharks\CommentMail\Pro;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
require_once dirname(__FILE__).'/stub.php';

$GLOBALS[GLOBAL_NS] = new Plugin();
require_once dirname(__FILE__).'/api.php';
