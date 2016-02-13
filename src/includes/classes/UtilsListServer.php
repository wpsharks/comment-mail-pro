<?php
/**
 * List Server Utilities
 *
 * @since 151224 Adding list server integrations.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;


    if(!defined('WPINC')) // MUST have WordPress.
        exit('Do NOT access this file directly: '.basename(__FILE__));
    if(!class_exists('\\'.__NAMESPACE__.'\\utils_list_server'))
    {
        /**
         * List Server Utilities
         *
         * @since 151224 Adding list server integrations.
         */
        class utils_list_server extends abs_base
        {
            /**
             * Subscribe to list.
             *
             * @since 151224 Adding support for mailing lists.
             *
             * @param array $args User ID, email address, IP, etc. Details needed by the list server.
             *
             * @return string Subscriber ID. If already exists on the list, the existing ID.
             */
            public function maybe_subscribe(array $args)
            {
                if(!$this->plugin->options['list_server_enable'])
                    return ''; // Nothing to do here.

                switch($this->plugin->options['list_server'])
                {
                    case 'mailchimp':
                        $mailchimp = new list_server_mailchimp();
                        return $mailchimp->subscribe(array(), $args);
                }
                return ''; // Default; i.e., unsupported list server.
            }

            /**
             * Unsubscribe from list.
             *
             * @since 151224 Adding support for mailing lists.
             *
             * @param array $args User ID, email address, IP, etc. Details needed by the list server.
             *
             * @return boolean True if removed from the list. True if not on the list. False on failure.
             */
            public function maybe_unsubscribe(array $args)
            {
                if(!$this->plugin->options['list_server_enable'])
                    return false; // Nothing to do here.

                switch($this->plugin->options['list_server'])
                {
                    case 'mailchimp':
                        $mailchimp = new list_server_mailchimp();
                        return $mailchimp->unsubscribe(array(), $args);
                }
                return false; // Default; i.e., unsupported list server.
            }
        }
    }
