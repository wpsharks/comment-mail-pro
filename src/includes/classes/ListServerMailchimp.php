<?php
/*[pro exclude-file-from="lite"]*/
/**
 * MailChimp List Server.
 *
 * @since     151224 Adding support for mailing lists.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * MailChimp List Server.
 *
 * @since 151224 Adding support for mailing lists.
 */
class ListServerMailchimp extends ListServerBase
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
    public function subscribe(array $list, array $args)
    {
        $mailchimp = new \Mailchimp($this->plugin->options['list_server_mailchimp_api_key'], ['timeout' => 30]);

        if (empty($list['id'])) { // If no specific list ID, use global config. option.
            $list['id'] = $this->plugin->options['list_server_mailchimp_list_id'];
        }
        $default_args = [
            'double_optin' => true,

            'email' => '',
            'fname' => '',
            'lname' => '',
            'ip'    => '',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $mailchimp_subscribe_args = [
            'list_id'      => (string) $list['id'],
            'double_optin' => $args['double_optin'],

            'email'       => ['email' => (string) $args['email']],
            'merge_array' => [
                'MERGE1'     => (string) $args['fname'],
                'MERGE2'     => (string) $args['lname'],
                'OPTIN_IP'   => (string) $args['ip'],
                'OPTIN_TIME' => date('Y-m-d H:i:s'),
            ],
            'email_type' => 'html',

            'update_existing'   => true,
            'replace_interests' => true,
            'send_welcome'      => true,
        ];
        $mailchimp_response = call_user_func_array([$mailchimp->lists, 'subscribe'], $mailchimp_subscribe_args);

        if (!empty($mailchimp_response['leid']) && is_string($mailchimp_response['leid'])) {
            return (string) $mailchimp_response['leid'];
        }
        return ''; // Failure.
    }

    /**
     * Unsubscribe from list.
     *
     * @since 151224 Adding support for mailing lists.
     *
     * @param array $list An array with details that identify a particular list.
     * @param array $args User ID, email address, IP, etc. Details needed by the list server.
     *
     * @return bool True if removed from the list. True if not on the list. False on failure.
     */
    public function unsubscribe(array $list, array $args)
    {
        $mailchimp = new \Mailchimp($this->plugin->options['list_server_mailchimp_api_key'], ['timeout' => 30]);

        if (empty($list['id'])) { // If no specific list ID, use global config. option.
            $list['id'] = $this->plugin->options['list_server_mailchimp_list_id'];
        }
        $default_args = [
            'email' => '',
            'fname' => '',
            'lname' => '',
            'ip'    => '',
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $mailchimp_subscribe_args = [
            'list_id' => (string) $list['id'],
            'email'   => ['email' => (string) $args['email']],

            'delete_member' => false,
            'send_goodbye'  => true,
            'send_notify'   => true,
        ];
        $mailchimp_response = call_user_func_array([$mailchimp->lists, 'unsubscribe'], $mailchimp_unsubscribe_args);

        return !empty($mailchimp_response['complete']); // Test the return value for a true complete key.
    }
}
