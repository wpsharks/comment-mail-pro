<?php
/*[pro exclude-file-from="lite"]*/
/**
 * Replies via Email; SparkPost Webhook Listener.
 *
 * @since 16xxxx Adding SparkPost integration.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Replies via Email; SparkPost Webhook Listener.
 *
 * @since 16xxxx Adding SparkPost integration.
 */
class RveSparkPost extends AbsBase
{
    /**
     * @var string
     *
     * @since 16xxxx
     */
    protected $key;

    /**
     * @var object
     *
     * @since 16xxxx
     */
    protected $message;

    /**
     * Class constructor.
     *
     * @param string $key Secret key.
     *
     * @since 16xxxx Adding SparkPost.
     */
    public function __construct($key)
    {
        parent::__construct();

        $this->key = trim((string) $key);

        $this->prepWebhook();
        $this->maybeProcess();
    }

    /**
     * Key for this webhook.
     *
     * @since 16xxxx Adding SparkPost.
     */
    public static function key()
    {
        $plugin = plugin();
        $class  = get_called_class();
        return $plugin->utils_enc->hmacSha256Sign($class);
    }

    /**
     * Prepare webhook.
     *
     * @since 16xxxx Adding SparkPost.
     */
    protected function prepWebhook()
    {
        ignore_user_abort(true);
        nocache_headers();
    }

    /**
     * Process webhook event.
     *
     * @since 16xxxx Adding SparkPost.
     */
    protected function maybeProcess()
    {
        if (!$this->plugin->options['replies_via_email_enable']) {
            return; // Replies via email are disabled currently.
        }
        if ($this->plugin->options['replies_via_email_handler'] !== 'sparkpost') {
            return; // SparkPost is not the currently selection RVE handler.
        }
        if ($this->key !== static::key()) {
            return; // Not authorized.
        }
        $this->collectMessage();
        $this->processMessage();
    }

    /**
     * Collect SparkPost message.
     *
     * @since 16xxxx Adding SparkPost.
     */
    protected function collectMessage()
    {
        $response = file_get_contents('php://input');
        $response = json_decode((string) $response);

        if (empty($response[0]->msys->relay_message)) {
            return; // Nothing to do.
        } elseif (!is_object($response[0]->msys->relay_message)) {
            return; // Expecting JSON-encoded message.
        }
        $this->message = $response[0]->msys->relay_message;
    }

    /**
     * Process SparkPost message.
     *
     * @since 16xxxx Adding SparkPost.
     */
    protected function processMessage()
    {
        foreach ($this->events as $_event) {
            // Iterate all events.

            if (empty($_event->ts) || $_event->ts < strtotime('-7 days')) {
                continue; // Missing timestamp; or it's very old.
            }
            if (empty($_event->event) || $_event->event !== 'inbound') {
                continue; // Expecting an inbound event.
            }
            if (empty($_event->msg) || !($_event->msg instanceof \stdClass)) {
                continue; // Expecting a msg object w/ properties.
            }
            $_reply_to_email = $this->issetOr($_event->msg->email, '', 'string');

            $_from_name  = $this->issetOr($_event->msg->from_name, '', 'string');
            $_from_email = $this->issetOr($_event->msg->from_email, '', 'string');

            $_subject = $this->issetOr($_event->msg->subject, '', 'string');

            $_text_body = $this->issetOr($_event->msg->text, '', 'string');
            $_html_body = $this->issetOr($_event->msg->html, '', 'string');

            if (isset($_event->msg->spam_report->score)) {
                $_spam_score = (float) $_event->msg->spam_report->score;
            } else {
                $_spam_score = 0.0; // Default value.
            }
            if (isset($_event->msg->spf->result)) {
                $_spf_result = strtolower((string) $_event->msg->spf->result);
            } else {
                $_spf_result = 'none'; // Default value.
            }
            if (isset($_event->msg->dkim->signed)) {
                $_dkim_signed = (bool) $_event->msg->dkim->signed;
            } else {
                $_dkim_signed = false; // Default value.
            }
            if (isset($_event->msg->dkim->valid)) {
                $_dkim_valid = (bool) $_event->msg->dkim->valid;
            } else {
                $_dkim_valid = false; // Default value.
            }
            $this->maybeProcessCommentReply(
                [
                    'reply_to_email' => $_reply_to_email,

                    'from_name'  => $_from_name,
                    'from_email' => $_from_email,

                    'subject' => $_subject,

                    'text_body' => $_text_body,
                    'html_body' => $_html_body,

                    'spam_score' => $_spam_score,

                    'spf_result' => $_spf_result,

                    'dkim_signed' => $_dkim_signed,
                    'dkim_valid'  => $_dkim_valid,
                ]
            );
        }
        unset($_event); // Housekeeping.
    }

    /**
     * Processes a comment reply.
     *
     * @since 16xxxx Adding SparkPost.
     *
     * @param array $args Input email/event arguments.
     */
    protected function maybeProcessCommentReply(array $args)
    {
        $default_args = [
            'reply_to_email' => '',

            'from_name'  => '',
            'from_email' => '',

            'subject' => '',

            'text_body' => '',
            'html_body' => '',

            'spam_score' => 0.0,

            'spf_result' => 'none',

            'dkim_signed' => false,
            'dkim_valid'  => false,
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $reply_to_email = trim((string) $args['reply_to_email']);

        $from_name  = trim((string) $args['from_name']);
        $from_email = trim((string) $args['from_email']);

        $subject = trim((string) $args['subject']);

        $text_body = trim((string) $args['text_body']);
        $html_body = trim((string) $args['html_body']);

        $spam_score = (float) $args['spam_score'];

        $spf_result = trim(strtolower((string) $args['spf_result']));

        $dkim_signed = (bool) $args['dkim_signed'];
        $dkim_valid  = (bool) $args['dkim_valid'];

        $force_status = null; // Initialize.

        if (!$reply_to_email) { // Must have this.
            return; // Missing `Reply-To:` address.
        }
        $text_body = $this->plugin->utils_string->htmlToText($text_body);
        $html_body = $this->plugin->utils_string->htmlToRichText($html_body);

        if (!($rich_text_body = $this->coalesce($html_body, $text_body))) {
            return; // Empty reply; nothing to do here.
        }
        if ($spam_score >= (float) $this->plugin->options['rve_mandrill_max_spam_score']) {
            $force_status = 'spam'; // Force this to be considered `spam`.
        }
        if (($spf_check = (int) $this->plugin->options['rve_mandrill_spf_check_enable'])) {
            if (($spf_check === 1 && !in_array($spf_result, ['pass', 'neutral', 'softfail', 'none'], true))
                || ($spf_check === 2 && !in_array($spf_result, ['pass', 'neutral', 'none'], true))
                || ($spf_check === 3 && !in_array($spf_result, ['pass', 'neutral'], true))
                || ($spf_check === 4 && !in_array($spf_result, ['pass'], true))
            ) {
                $force_status = 'spam'; // Force this to be considered `spam`.
            }
        }
        if (($dkim_check = (int) $this->plugin->options['rve_mandrill_dkim_check_enable'])) {
            if (($dkim_check === 1 && $dkim_signed && !$dkim_valid) || ($dkim_check === 2 && (!$dkim_signed || !$dkim_valid))) {
                $force_status = 'spam'; // Force this to be considered `spam`.
            }
        }
        $post_comment_args = compact(
            'reply_to_email',
            //
            'from_name',
            'from_email',
            //
            'subject',
            //
            'rich_text_body',
            //
            'force_status'
        );
        $this->plugin->utils_rve->maybePostComment($post_comment_args);
    }

    /**
     * Setup SparkPost webhook.
     *
     * @since 16xxxx Adding SparkPost.
     */
    public static function setupWebhook()
    {
        $plugin = plugin(); // Plugin instance.

        if ($plugin->options['rve_sparkpost_api_key'] && $plugin->options['rve_sparkpost_reply_to_email'] && strpos($plugin->options['rve_sparkpost_reply_to_email'], '@')) {
            if ($plugin->options['rve_sparkpost_webhook_setup_hash'] !== md5($plugin->options['rve_sparkpost_api_key'].$plugin->options['rve_sparkpost_reply_to_email'])) {
                $rve_sparkpost_reply_to_domain = trim(strstr($plugin->options['rve_sparkpost_reply_to_email'], '@'), '@');

                if ($plugin->options['rve_sparkpost_webhook_id']) {
                    wp_remote_request('https://api.sparkpost.com/api/v1/relay-webhooks/'.urlencode($plugin->options['rve_sparkpost_webhook_id']), [
                        'method'  => 'DELETE',
                        'timeout' => 5,
                        'headers' => [
                            'Authorization' => $plugin->options['rve_sparkpost_api_key'],
                        ],
                    ]);
                    $plugin->options['rve_sparkpost_webhook_id'] = '';
                }
                wp_remote_request('https://api.sparkpost.com/api/v1/inbound-domains', [
                    'method'  => 'POST',
                    'timeout' => 5,

                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => $plugin->options['rve_sparkpost_api_key'],
                    ],
                    'body' => json_encode([
                        'domain' => $rve_sparkpost_reply_to_domain,
                    ]),
                ]);
                $wp_remote_response = wp_remote_request('https://api.sparkpost.com/api/v1/relay-webhooks', [
                    'method'  => 'POST',
                    'timeout' => 5,

                    'headers' => [
                        'Content-Type'  => 'application/json',
                        'Authorization' => $plugin->options['rve_sparkpost_api_key'],
                    ],
                    'body' => json_encode([
                        'name'       => NAME.' Webhook',
                        'target'     => $plugin->utils_url->rveSparkPostWebhookUrl(),
                        'auth_token' => md5(wp_salt()),
                        'match'      => [
                            'protocol' => 'SMTP',
                            'domain'   => $rve_sparkpost_reply_to_domain,
                        ],
                    ]),
                ]);
                $api_response = json_decode(wp_remote_retrieve_body($wp_remote_response));

                if (is_object($api_response) && !empty($api_response->results->id)) {
                    $plugin->options['rve_sparkpost_webhook_setup_hash'] = md5($plugin->options['rve_sparkpost_api_key'].$plugin->options['rve_sparkpost_reply_to_email']);
                    $plugin->options['rve_sparkpost_webhook_id']         = $api_response->results->id;
                }
            }
        }
    }
}
