<?php
/**
 * i18n Utilities.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * i18n Utilities.
 *
 * @since 141111 First documented version.
 */
class UtilsI18n extends AbsBase
{
    /**
     * Action past tense translation.
     *
     * @since 141111 First documented version.
     *
     * @param string $action    An action; e.g. `confirm`, `delete`, `unconfirm`, etc.
     * @param string $transform Defaults to `lower`.
     *
     * @return string The string translation for the given `$action`.
     */
    public function actionEd($action, $transform = 'lower')
    {
        $action = $i18n = strtolower(trim((string) $action));

        switch ($action) {
            case 'reconfirm':
                $i18n = __('reconfirmed', SLUG_TD);
                break;

            case 'confirm':
                $i18n = __('confirmed', SLUG_TD);
                break;

            case 'unconfirm':
                $i18n = __('unconfirmed', SLUG_TD);
                break;

            case 'suspend':
                $i18n = __('suspended', SLUG_TD);
                break;

            case 'trash':
                $i18n = __('trashed', SLUG_TD);
                break;

            case 'update':
                $i18n = __('updated', SLUG_TD);
                break;

            case 'delete':
                $i18n = __('deleted', SLUG_TD);
                break;

            default: // Default case handler.
                if ($action) { // Only if it's not empty.
                    $i18n = __(rtrim($action, 'ed').'ed', SLUG_TD);
                }
                break;
        }
        if (ctype_alnum($i18n)) {
            switch ($transform) {
                case 'lower':
                    $i18n = strtolower($i18n);
                    break;

                case 'upper':
                    $i18n = strtoupper($i18n);
                    break;

                case 'ucwords':
                    $i18n = ucwords($i18n);
                    break;
            }
        }
        return $i18n;
    }

    /**
     * Status label translation.
     *
     * @since 141111 First documented version.
     *
     * @param string $status    A status e.g. `approve`, `hold`, `unconfirmed`, etc.
     * @param string $transform Defaults to `lower`.
     *
     * @return string The string translation for the given `$status`.
     */
    public function statusLabel($status, $transform = 'lower')
    {
        $status = $i18n = strtolower(trim((string) $status));

        switch ($status) {
            case 'approve':
                $i18n = __('approved', SLUG_TD);
                break;

            case 'hold':
                $i18n = __('pending', SLUG_TD);
                break;

            case 'trash':
                $i18n = __('trashed', SLUG_TD);
                break;

            case 'spam':
                $i18n = __('spammy', SLUG_TD);
                break;

            case 'delete':
                $i18n = __('deleted', SLUG_TD);
                break;

            case 'open':
                $i18n = __('open', SLUG_TD);
                break;

            case 'closed':
                $i18n = __('closed', SLUG_TD);
                break;

            case 'unconfirmed':
                $i18n = __('unconfirmed', SLUG_TD);
                break;

            case 'subscribed':
                $i18n = __('subscribed', SLUG_TD);
                break;

            case 'suspended':
                $i18n = __('suspended', SLUG_TD);
                break;

            case 'trashed':
                $i18n = __('trashed', SLUG_TD);
                break;

            default: // Default case handler.
                if ($status) { // Only if it's not empty.
                    $i18n = __(rtrim($status, 'ed').'ed', SLUG_TD);
                }
                break;
        }
        if (ctype_alnum($i18n)) {
            switch ($transform) {
                case 'lower':
                    $i18n = strtolower($i18n);
                    break;

                case 'upper':
                    $i18n = strtoupper($i18n);
                    break;

                case 'ucwords':
                    $i18n = ucwords($i18n);
                    break;
            }
        }
        return $i18n;
    }

    /**
     * Event label translation.
     *
     * @since 141111 First documented version.
     *
     * @param string $event     An event e.g. `inserted`, `updated`, `deleted`, etc.
     * @param string $transform Defaults to `lower`.
     *
     * @return string The string translation for the given `$event`.
     */
    public function eventLabel($event, $transform = 'lower')
    {
        $event = $i18n = strtolower(trim((string) $event));

        switch ($event) {
            case 'inserted':
                $i18n = __('inserted', SLUG_TD);
                break;

            case 'updated':
                $i18n = __('updated', SLUG_TD);
                break;

            case 'overwritten':
                $i18n = __('overwritten', SLUG_TD);
                break;

            case 'purged':
                $i18n = __('purged', SLUG_TD);
                break;

            case 'cleaned':
                $i18n = __('cleaned', SLUG_TD);
                break;

            case 'deleted':
                $i18n = __('deleted', SLUG_TD);
                break;

            case 'invalidated':
                $i18n = __('invalidated', SLUG_TD);
                break;

            case 'notified':
                $i18n = __('notified', SLUG_TD);
                break;

            default: // Default case handler.
                if ($event) { // Only if it's not empty.
                    $i18n = __(rtrim($event, 'ed').'ed', SLUG_TD);
                }
                break;
        }
        if (ctype_alnum($i18n)) {
            switch ($transform) {
                case 'lower':
                    $i18n = strtolower($i18n);
                    break;

                case 'upper':
                    $i18n = strtoupper($i18n);
                    break;

                case 'ucwords':
                    $i18n = ucwords($i18n);
                    break;
            }
        }
        return $i18n;
    }

    /**
     * Deliver option label translation.
     *
     * @since 141111 First documented version.
     *
     * @param string $deliver   A delivery option; e.g. `asap`, `hourly`, etc.
     * @param string $transform Defaults to `lower`.
     *
     * @return string The string translation for the given `$deliver` option.
     */
    public function deliverLabel($deliver, $transform = 'lower')
    {
        $deliver = $i18n = strtolower(trim((string) $deliver));

        switch ($deliver) {
            case 'asap':
                $i18n = __('instantly', SLUG_TD);
                break;

            case 'hourly':
                $i18n = __('hourly', SLUG_TD);
                break;

            case 'daily':
                $i18n = __('daily', SLUG_TD);
                break;

            case 'weekly':
                $i18n = __('weekly', SLUG_TD);
                break;

            default: // Default case handler.
                if ($deliver) { // Only if it's not empty.
                    $i18n = __(rtrim($deliver, 'ed').'ed', SLUG_TD);
                }
                break;
        }
        if (ctype_alnum($i18n)) {
            switch ($transform) {
                case 'lower':
                    $i18n = strtolower($i18n);
                    break;

                case 'upper':
                    $i18n = strtoupper($i18n);
                    break;

                case 'ucwords':
                    $i18n = ucwords($i18n);
                    break;
            }
        }
        return $i18n;
    }

    /**
     * Sub. type label translation.
     *
     * @since 141111 First documented version.
     *
     * @param string $sub_type  A sub. type; i.e. `comments`, `comment`.
     * @param string $transform Defaults to `lower`.
     *
     * @return string The string translation for the given `$sub_type`.
     */
    public function subTypeLabel($sub_type, $transform = 'lower')
    {
        $sub_type = $i18n = strtolower(trim((string) $sub_type));

        switch ($sub_type) {
            case 'comments':
                $i18n = __('all comments', SLUG_TD);
                break;

            case 'comment':
                $i18n = __('replies only', SLUG_TD);
                break;

            default: // Default case handler.
                if ($action) { // Only if it's not empty.
                    $i18n = __(rtrim($action, 'ed').'ed', SLUG_TD);
                }
                break;
        }
        if (ctype_alnum($i18n)) {
            switch ($transform) {
                case 'lower':
                    $i18n = strtolower($i18n);
                    break;

                case 'upper':
                    $i18n = strtoupper($i18n);
                    break;

                case 'ucwords':
                    $i18n = ucwords($i18n);
                    break;
            }
        }
        return $i18n;
    }

    /**
     * `X subscription` or `X subscriptions`.
     *
     * @since 141111 First documented version.
     *
     * @param int $counter Total subscriptions; i.e. a counter value.
     *
     * @return string The phrase `X subscription` or `X subscriptions`.
     */
    public function subscriptions($counter)
    {
        $counter = (integer) $counter; // Force integer.

        if (empty($counter)) { // If no results, add a no subscriptions message.
            return sprintf(_n('No Subscriptions (View)','No Subscriptions (View)', $counter, SLUG_TD), $counter);
        }
        else {
            return sprintf(_n('%1$s Subscriptions Total (View All)','%1$s Subscriptions Total (View All)', $counter, SLUG_TD), $counter);
        }
    }

    /**
     * `X sub. event log entry` or `X sub. event log entries`.
     *
     * @since 141111 First documented version.
     *
     * @param int $counter Total sub. event log entries; i.e. a counter value.
     *
     * @return string The phrase `X sub. event log entry` or `X sub. event log entries`.
     */
    public function subEventLogEntries($counter)
    {
        $counter = (integer) $counter; // Force integer.

        return sprintf(_n('%1$s sub. event log entry', '%1$s sub. event log entries', $counter, SLUG_TD), $counter);
    }

    /**
     * `X queued notification` or `X queued notifications`.
     *
     * @since 141111 First documented version.
     *
     * @param int $counter Total queued notifications; i.e. a counter value.
     *
     * @return string The phrase `X queued notification` or `X queued notifications`.
     */
    public function queuedNotifications($counter)
    {
        $counter = (integer) $counter; // Force integer.

        return sprintf(_n('%1$s queued notification', '%1$s queued notifications', $counter, SLUG_TD), $counter);
    }

    /**
     * `X queue event log entry` or `X queue event log entries`.
     *
     * @since 141111 First documented version.
     *
     * @param int $counter Total queue event log entries; i.e. a counter value.
     *
     * @return string The phrase `X queue event log entry` or `X queue event log entries`.
     */
    public function queueEventLogEntries($counter)
    {
        $counter = (integer) $counter; // Force integer.

        return sprintf(_n('%1$s queue event log entry', '%1$s queue event log entries', $counter, SLUG_TD), $counter);
    }

    /**
     * A confirmation/warning regarding log entry deletions.
     *
     * @since 141111 First documented version.
     *
     * @return string Confirmation/warning regarding log entry deletions.
     */
    public function logEntryJsDeletionConfirmationWarning()
    {
        return __('Delete permanently? Are you sure?', SLUG_TD)."\n\n".
               __('WARNING: Deleting log entries is not recommended, as this will have an impact on statistical reporting.', SLUG_TD)."\n\n".
               __('If you want statistical reports to remain accurate, please leave ALL log entries intact.', SLUG_TD);
    }
}
