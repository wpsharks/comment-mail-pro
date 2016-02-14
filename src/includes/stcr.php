<?php
/**
 * StCR Back Compat.
 *
 * @since     141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

if (!defined('WPINC')) {
    exit('Do NOT access this file directly: '.basename(__FILE__));
}
namespace { // Global namespace.

    add_action(
        'init',
        function () {
            if (!is_admin() && !function_exists('subscribe_reloaded_show')) :
                function subscribe_reloaded_show()
                {
                    comment_mail::subOps();
                }
            endif;
        }
    );
}
