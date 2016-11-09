<?php
/*[pro exclude-file-from="lite"]*/
/**
 * List Server Utilities.
 *
 * @since     151224 Adding list server integrations.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * List Server Utilities.
 *
 * @since 151224 Adding list server integrations.
 */
class UtilsListServer extends AbsBase
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
    public function maybeSubscribe(array $args)
    {
        if (!$this->plugin->options['list_server_enable']) {
            return ''; // Nothing to do here.
        }
        switch ($this->plugin->options['list_server']) {
            case 'mailchimp': // MailChimp.
                try {
                    $Mailchimp    = new ListServerMailchimp();
                    $api_response = $Mailchimp->subscribe([], $args);
                    return !empty($api_response['leid']) ? (string) $api_response['leid'] : '';
                } catch (\Exception $Exception) {
                    return ''; // NOTE: If the user already unsubscribed, an exception is thrown.
                    // See: <https://github.com/websharks/comment-mail/issues/114#issuecomment-259559795>
                    // Nothing we can do about this at the present time.
                    // @TODO Check this in the planned API upgrade to see if there is a workaround.
                }
        }
        return '';
    }

    /**
     * Unsubscribe from list.
     *
     * @since 151224 Adding support for mailing lists.
     *
     * @param array $args User ID, email address, IP, etc. Details needed by the list server.
     *
     * @return bool True if removed from the list. True if not on the list. False on failure.
     */
    public function maybeUnsubscribe(array $args)
    {
        if (!$this->plugin->options['list_server_enable']) {
            return false; // Nothing to do here.
        }
        switch ($this->plugin->options['list_server']) {
            case 'mailchimp':
                try {
                    $Mailchimp    = new ListServerMailchimp();
                    $api_response = $Mailchimp->unsubscribe([], $args);
                    return !empty($api_response['complete']) ? true : false;
                } catch (\Exception $Exception) {
                    return false;
                }
        }
        return false;
    }
}
