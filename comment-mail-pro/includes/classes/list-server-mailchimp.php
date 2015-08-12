<?php
/**
 * MailChimp List Server
 *
 * @since 15xxxx Adding support for mailing lists.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace comment_mail // Root namespace.
{
    if(!defined('WPINC')) // MUST have WordPress.
        exit('Do NOT access this file directly: '.basename(__FILE__));
    if(!class_exists('\\'.__NAMESPACE__.'\\list_server_mailchimp'))
    {
        /**
         * MailChimp List Server
         *
         * @since 15xxxx Adding support for mailing lists.
         */
        class list_server_mailchimp extends list_server_base
        {
            /**
             * Subscribe to list.
             *
             * @since 15xxxx Adding support for mailing lists.
             *
             * @param array $list An array with details that identify a particular list.
             * @param array $args User ID, email address, IP, etc. Details needed by the list server.
             *
             * @return string Subscriber ID. If already exists on the list, the existing ID.
             */
            public function subscribe(array $list, array $args)
            {
                // Integration with MailChimp API.
            }

            /**
             * Unsubscribe from list.
             *
             * @since 15xxxx Adding support for mailing lists.
             *
             * @param array $list An array with details that identify a particular list.
             * @param array $args User ID, email address, IP, etc. Details needed by the list server.
             *
             * @return boolean True if removed from the list. True if not on the list. False on failure.
             */
            public function unsubscribe(array $list, array $args)
            {
                // Integration with MailChimp API.
            }
        }
    }
}
