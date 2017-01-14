<?php
/**
 * Plugin.
 *
 * @since 160212 PSR compliance.
 */
namespace WebSharks\CommentMail\Pro;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly.');
}
require_once __DIR__.'/stub.php';

if (!Conflicts::check()) {
    require_once __DIR__.'/api.php';
    require_once __DIR__.'/stcr.php';
    $GLOBALS[GLOBAL_NS] = new Plugin();
}
