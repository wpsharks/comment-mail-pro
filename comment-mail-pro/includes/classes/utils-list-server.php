<?php
/**
 * List Server Utilities
 *
 * @since 15xxxx Adding list server integrations.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
    if(!defined('WPINC')) // MUST have WordPress.
        exit('Do NOT access this file directly: '.basename(__FILE__));
    if(!class_exists('\\'.__NAMESPACE__.'\\utils_list_server'))
    {
        /**
         * List Server Utilities
         *
         * @since 15xxxx Adding list server integrations.
         */
        class utils_list_server extends abs_base
        {
            // @TODO Process subscribe and unsubscribe actions.
        }
    }
}
