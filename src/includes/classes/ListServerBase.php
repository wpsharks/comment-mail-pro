<?php
/**
 * List Server Abstraction
 *
 * @since 151224 Adding support for mailing lists.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;



        /**
         * List Server Abstraction
         *
         * @since 151224 Adding support for mailing lists.
         */
        abstract class list_server_base extends AbsBase
        {
            /**
             * Subscribe to list.
             *
             * @since 151224 Adding support for mailing lists.
             *
             * @param array $list An array with details that identify a particular list.
             * @param array $args User ID, email address, IP, etc. Details needed by the list server.
             *
             * @return string Subscriber ID. If already exists on the list, the existing ID.
             */
            abstract public function subscribe(array $list, array $args);

            /**
             * Unsubscribe from list.
             *
             * @since 151224 Adding support for mailing lists.
             *
             * @param array $list An array with details that identify a particular list.
             * @param array $args User ID, email address, IP, etc. Details needed by the list server.
             *
             * @return boolean True if removed from the list. True if not on the list. False on failure.
             */
            abstract public function unsubscribe(array $list, array $args);
        }
    
