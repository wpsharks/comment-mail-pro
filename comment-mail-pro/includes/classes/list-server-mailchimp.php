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
                if(!class_exists('Mailchimp')) // Include the MailChimp API class here.
                    include_once dirname(dirname(dirname(__FILE__))).'/submodules/mailchimp/src/Mailchimp.php';

                $mailchimp                = new \Mailchimp($this->plugin->options['list_server_mailchimp_api_key'], array('timeout' => 30));

                if(empty($list['id'])) // If no specific list ID, use global config. option.
                    $list['id'] = $this->plugin->options['list_server_mailchimp_list_id'];

                $default_args             = array(
                    'email'               => '',
                    'fname'               => '',
                    'lname'               => '',
                    'ip'                  => '',
                );
                $args                     = array_merge($default_args, $args);
                $args                     = array_intersect_key($args, $default_args);

                $mailchimp_subscribe_args = array(
                    'list_id'             => (string)$list['id'],
                    'email'               => array('email' => (string)$args['email']),
                    'merge_array'         => array(
                        'MERGE1'          => (string)$args['fname'],
                        'MERGE2'          => (string)$args['lname'],
                        'OPTIN_IP'        => (string)$args['ip'],
                        'OPTIN_TIME'      => date('Y-m-d H:i:s'),
                    ),
                    'email_type'          => 'html',
                    'double_optin'        => true,
                    'update_existing'     => true,
                    'replace_interests'   => true,
                    'send_welcome'        => true,
                );
                $mailchimp_response       = call_user_func_array(array($mailchimp->lists, 'subscribe'), $mailchimp_subscribe_args);

                if(!empty($mailchimp_response['leid']) && is_string($mailchimp_response['leid']))
                    return (string)$mailchimp_response['leid'];

                return ''; // Failure.
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
                if(!class_exists('Mailchimp')) // Include the MailChimp API class here.
                    include_once dirname(dirname(dirname(__FILE__))).'/submodules/mailchimp/src/Mailchimp.php';

                $mailchimp                = new \Mailchimp($this->plugin->options['list_server_mailchimp_api_key'], array('timeout' => 30));

                if(empty($list['id'])) // If no specific list ID, use global config. option.
                    $list['id'] = $this->plugin->options['list_server_mailchimp_list_id'];

                $default_args             = array(
                    'email'               => '',
                    'fname'               => '',
                    'lname'               => '',
                    'ip'                  => '',
                );
                $args                     = array_merge($default_args, $args);
                $args                     = array_intersect_key($args, $default_args);

                $mailchimp_subscribe_args = array(
                    'list_id'             => (string)$list['id'],
                    'email'               => array('email' => (string)$args['email']),
                    'delete_member'       => false,
                    'send_goodbye'        => true,
                    'send_notify'         => true,
                );
                $mailchimp_response       = call_user_func_array(array($mailchimp->lists, 'unsubscribe'), $mailchimp_unsubscribe_args);

                return !empty($mailchimp_response['complete']); // Test the return value for a true complete key.
            }
        }
    }
}
