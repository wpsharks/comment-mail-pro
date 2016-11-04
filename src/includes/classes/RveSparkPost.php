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
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
            return; // Nothing to do.
        }
        $response = json_decode((string) file_get_contents('php://input'));

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
        if (!$this->message) {
            return; // Not possible.
        } elseif (empty($this->message->protocol)) {
            return; // Not possible.
        } elseif (empty($this->message->webhook_id)) {
            return; // Not possible.
        } elseif ($this->message->protocol !== 'smtp') {
            return; // Not possible.
        } elseif ($this->message->webhook_id !== $this->plugin->options['rve_sparkpost_webhook_id']) {
            return; // Webhook ID does not match.
        } elseif (empty($this->message->content->headers)) {
            return; // Not possible.
        }
        $headers = []; // Initialize array of all headers.

        foreach ($this->message->content->headers as $_header) {
            $headers = array_merge($headers, (array) $_header);
        } // unset($_header); // Housekeeping.

        if (empty($headers['To']) || empty($headers['From'])) {
            return; // Not possible; missing headers.
        } elseif (!($to_addresses = array_values($this->plugin->utils_mail->parseAddressesDeep($headers['To'])))) {
            return; // Not possible; no recipient addresses.
        } elseif (!($from_addresses = array_values($this->plugin->utils_mail->parseAddressesDeep($headers['From'])))) {
            return; // Not possible; no from addresses.
        }
        $reply_to_email = $to_addresses[0]->email;

        $from_name  = trim($from_addresses[0]->fname.' '.$from_addresses[0]->lname);
        $from_email = $from_addresses[0]->email;

        $subject = $this->message->content->subject;

        $text_body = $this->message->content->text;
        $html_body = $this->message->content->html;

        $this->maybeProcessCommentReply(
            [
                'reply_to_email' => $reply_to_email,

                'from_name'  => $from_name,
                'from_email' => $from_email,

                'subject' => $subject,

                'text_body' => $text_body,
                'html_body' => $html_body,
            ]
        );
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
        ];
        $args = array_merge($default_args, $args);
        $args = array_intersect_key($args, $default_args);

        $reply_to_email = trim((string) $args['reply_to_email']);

        $from_name  = trim((string) $args['from_name']);
        $from_email = trim((string) $args['from_email']);

        $subject = trim((string) $args['subject']);

        $text_body = trim((string) $args['text_body']);
        $html_body = trim((string) $args['html_body']);

        if (!$reply_to_email) { // Must have this.
            return; // Missing `Reply-To:` address.
        }
        $text_body = $this->plugin->utils_string->htmlToText($text_body);
        $html_body = $this->plugin->utils_string->htmlToRichText($html_body);

        if (!($rich_text_body = $this->coalesce($html_body, $text_body))) {
            return; // Empty reply; nothing to do here.
        }
        $post_comment_args = compact(
            'reply_to_email',
            //
            'from_name',
            'from_email',
            //
            'subject',
            //
            'rich_text_body'
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
                $wp_remote_response = wp_remote_request('https://api.sparkpost.com/api/v1/inbound-domains', [
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
                $api_response_code = (int) wp_remote_retrieve_response_code($wp_remote_response);
                $api_response      = json_decode(wp_remote_retrieve_body($wp_remote_response));

                if ($api_response_code >= 400 && $api_response_code !== 409) {
                    $markup = sprintf(
                        __('<strong>RVE Error:</strong> The %1$s&trade; plugin was unable to complete the integration with SparkPost. When attempting to create an Inbound Domain the SparkPost API said: <pre>%2$s</pre>', SLUG_TD),
                        esc_html(NAME),
                        esc_html(!empty($api_response->errors[0]->message) ? $api_response->errors[0]->message : __('Unknown API error.', SLUG_TD))
                    );
                    $this->enqueueWarning($markup);
                    return; // Not possible.
                }
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
                $api_response_code = (int) wp_remote_retrieve_response_code($wp_remote_response);
                $api_response      = json_decode(wp_remote_retrieve_body($wp_remote_response));

                if ($api_response_code >= 400) {
                    $markup = sprintf(
                        __('<strong>RVE Error:</strong> The %1$s&trade; plugin was unable to complete the integration with SparkPost. When attempting to create a Relay Webhook the SparkPost API said: <pre>%2$s</pre>', SLUG_TD),
                        esc_html(NAME),
                        esc_html(!empty($api_response->errors[0]->message) ? $api_response->errors[0]->message : __('Unknown API error.', SLUG_TD))
                    );
                    $this->enqueueWarning($markup);
                    return; // Not possible.
                }
                if (is_object($api_response) && !empty($api_response->results->id)) {
                    $plugin->options['rve_sparkpost_webhook_setup_hash'] = md5($plugin->options['rve_sparkpost_api_key'].$plugin->options['rve_sparkpost_reply_to_email']);
                    $plugin->options['rve_sparkpost_webhook_id']         = $api_response->results->id;
                }
            }
        }
    }
}
