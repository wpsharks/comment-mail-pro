<?php
/**
 * Sub. Management Sub. Form Base
 *
 * @since     141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Sub. Management Sub. Form Base
 *
 * @since 141111 First documented version.
 */
class SubManageSubFormBase extends AbsBase
{
    /*
     * Instance-based properties.
     */

    /**
     * @var string|null Unique subscription key.
     *
     * @since 141111 First documented version.
     */
    protected $sub_key;

    /**
     * @var boolean Editing?
     *
     * @since 141111 First documented version.
     */
    protected $is_edit;

    /**
     * @var \stdClass|null Subscription.
     *
     * @since 141111 First documented version.
     */
    protected $sub;

    /**
     * @var form_fields Class instance.
     *
     * @since 141111 First documented version.
     */
    protected $form_fields;

    /*
     * Static properties.
     */

    /**
     * @var array Form field config. args.
     *
     * @since 141111 First documented version.
     */
    protected static $form_field_args = [
      'ns_id_suffix'   => '-manage-sub-form',
      'ns_name_suffix' => '[manage][sub_form]',
      'class_prefix'   => 'manage-sub-form-',
    ];

    /**
     * @var boolean Processing form?
     *
     * @since 141111 First documented version.
     */
    protected static $processing = false;

    /**
     * @var array Any processing errors.
     *
     * @since 141111 First documented version.
     */
    protected static $processing_errors = [];

    /**
     * @var array Any processing error codes.
     *
     * @since 141111 First documented version.
     */
    protected static $processing_error_codes = [];

    /**
     * @var array Any processing errors w/ HTML markup.
     *
     * @since 141111 First documented version.
     */
    protected static $processing_errors_html = [];

    /**
     * @var array Any processing successes.
     *
     * @since 141111 First documented version.
     */
    protected static $processing_successes = [];

    /**
     * @var array Any processing success codes.
     *
     * @since 141111 First documented version.
     */
    protected static $processing_success_codes = [];

    /**
     * @var array Any processing successes w/ HTML markup.
     *
     * @since 141111 First documented version.
     */
    protected static $processing_successes_html = [];

    /**
     * @var boolean Processing email change?
     *
     * @since 141111 First documented version.
     */
    protected static $processing_email_key_change = false;

    /*
     * Instance-based constructor.
     */

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param integer $sub_key Unique subscription key.
     */
    public function __construct($sub_key = null)
    {
        parent::__construct();

        if (isset($sub_key)) { // Editing?
            $this->is_edit = true;
            $this->sub_key = trim((string)$sub_key);
            $this->sub     = $this->plugin->utils_sub->get($this->sub_key);
        }
        $this->form_fields = new FormFields(static::$form_field_args);

        $this->maybeDisplay();
    }

    /*
     * Instance-based form generation.
     */

    /**
     * Displays form.
     *
     * @since 141111 First documented version.
     */
    protected function maybeDisplay()
    {
        $_this   = $this;
        $sub_key = $this->sub_key;
        $is_edit = $this->is_edit;
        $sub     = $this->sub;

        $form_fields = $this->form_fields;

        $current_value_for = function ($key_prop) use ($_this) {
            return $_this->currentValueFor($key_prop);
        };
        $hidden_inputs     = function () use ($_this) {
            return $_this->hiddenInputs();
        };
        $processing        = static::$processing;

        $processing_errors      = static::$processing_errors;
        $processing_error_codes = static::$processing_error_codes;
        $processing_errors_html = static::$processing_errors_html;

        $processing_successes        = static::$processing_successes;
        $processing_success_codes    = static::$processing_success_codes;
        $processing_successes_html   = static::$processing_successes_html;
        $processing_email_key_change = static::$processing_email_key_change;

        $error_codes = []; // Initialize.

        if ($this->is_edit && !$this->sub_key) {
            $error_codes[] = 'missing_sub_key';
        } else if ($this->is_edit && !$this->sub
                   && static::$processing
                   && static::$processing_successes
                   && static::$processing_email_key_change
        ) {
            $error_codes[] = 'invalid_sub_key_after_email_key_change';
        } else if ($this->is_edit && !$this->sub) {
            $error_codes[] = 'invalid_sub_key';
        } else if ($this->is_edit && $this->sub_key !== $this->sub->key) {
            $error_codes[] = 'invalid_sub_key';
        } else if (!$this->is_edit && !$this->plugin->options['enable']) {
            $error_codes[] = 'new_subs_disabled';
        } else if (!$this->is_edit && !$this->plugin->options['new_subs_enable']) {
            $error_codes[] = 'new_subs_disabled';
        }
        $template_vars = get_defined_vars(); // Everything above.
        $template      = new Template('site/sub-actions/manage-sub-form.php');

        status_header(200); // Status header.
        nocache_headers(); // Disallow caching.
        header('Content-Type: text/html; charset=UTF-8');

        exit($template->parse($template_vars));
    }

    /*
     * Instance-based helpers.
     */

    /**
     * Collects current value for a particular property.
     *
     * @since 141111 First documented version.
     *
     * @param string $key_prop The key/property to acquire.
     *
     * @return string|null The property value; else `NULL`.
     */
    public function currentValueFor($key_prop)
    {
        if (!($key_prop = (string)$key_prop)) {
            return null; // Not possible.
        }
        if (!static::$processing || static::$processing_error_codes) {
            if (isset($_REQUEST[GLOBAL_NS]['manage']['sub_form'][$key_prop])) {
                return trim(stripslashes((string)$_REQUEST[GLOBAL_NS]['manage']['sub_form'][$key_prop]));
            }
        }
        if ($this->is_edit && isset($this->sub->{$key_prop})) {
            return trim((string)$this->sub->{$key_prop});
        }
        if (is_null($current_email_latest_info = &$this->cacheKey(__FUNCTION__, 'current_email_latest_info'))) {
            $current_email_latest_info = $this->plugin->utils_sub->current_email_latest_info();
        }
        if (!$this->is_edit && !static::$processing && in_array($key_prop, ['fname', 'lname', 'email'], true)) {
            // We can try to autofill fname, lname, email for new subscriptions.
            if (!empty($current_email_latest_info->{$key_prop})) {
                return trim((string)$current_email_latest_info->{$key_prop});
            }
        }
        if (!$this->is_edit && !static::$processing && in_array($key_prop, ['fname', 'lname', 'email'], true)) {
            // We can try to autofill fname, lname, email for new subscriptions.
            $current = wp_get_current_commenter();

            switch ($key_prop) {
                case 'fname':
                    if (!empty($current['comment_author'])) {
                        return $this->plugin->utils_string->firstName((string)$current['comment_author']);
                    }
                    break;

                case 'lname':
                    if (!empty($current['comment_author'])) {
                        return $this->plugin->utils_string->lastName((string)$current['comment_author']);
                    }
                    break;

                case 'email':
                    if (!empty($current['comment_author_email'])) {
                        return (string)$current['comment_author_email'];
                    }
                    break;
            }
        }
        if (!$this->is_edit && !static::$processing && in_array($key_prop, ['fname', 'lname', 'email'], true)) {
            // We can try to autofill fname, lname, email for new subscriptions.
            $current = wp_get_current_user();

            switch ($key_prop) {
                case 'fname':
                    if (!empty($current->first_name)) {
                        return $this->plugin->utils_string->firstName((string)$current->first_name);
                    }
                    break;

                case 'lname':
                    if (!empty($current->last_name)) {
                        return $this->plugin->utils_string->lastName('- '.(string)$current->last_name);
                    }
                    break;

                case 'email':
                    if (!empty($current->user_email)) {
                        return (string)$current->user_email;
                    }
                    break;
            }
        }
        return null; // Default value.
    }

    /**
     * Hidden inputs needed for form processing.
     *
     * @since 141111 First documented version.
     *
     * @return string Hidden inputs needed for form processing.
     *
     * @TODO  Add an nonce to these fields.
     */
    public function hiddenInputs()
    {
        /* Important for this to come first!
         * We want form processing to take place first.
         * i.e. Array keys need to be submitted in a specific order. */
        $hidden_inputs = $this->form_fields->hiddenInput(['name' => '_'])."\n";

        if ($this->is_edit && $this->sub) {
            $hidden_inputs .= $this->form_fields->hiddenInput(
                [
                  'name'          => 'ID',
                  'current_value' => $this->sub->ID,
                ]
              )."\n";
            $hidden_inputs .= $this->form_fields->hiddenInput(
                [
                  'name'          => 'key',
                  'current_value' => $this->sub->key,
                ]
              )."\n";
            $hidden_inputs .= $this->form_fields->hiddenInput(
                [
                  'name'          => 'post_id',
                  'current_value' => $this->sub->post_id,
                ]
              )."\n";
            $hidden_inputs .= $this->form_fields->hiddenInput(
                [
                  'name'          => 'comment_id',
                  'current_value' => $this->sub->comment_id,
                ]
              )."\n";
            $hidden_inputs .= $this->form_fields->hiddenInput(
                [
                  'root_name'     => true,
                  'name'          => GLOBAL_NS.'[manage][sub_edit]',
                  'current_value' => $this->sub->key,
                ]
              )."\n";
        } else { // Adding a new subscription in this default case.
            $hidden_inputs .= $this->form_fields->hiddenInput(
                [
                  'root_name'     => true,
                  'name'          => GLOBAL_NS.'[manage][sub_new]',
                  'current_value' => 0,
                ]
              )."\n";
        }
        $current_summary_nav_vars // Include these too.
          = $this->plugin->utils_url->sub_manage_summary_nav_vars();

        foreach (array_keys(sub_manage_summary::$default_nav_vars) as $_summary_nav_var_key) {
            if (isset($current_summary_nav_vars[$_summary_nav_var_key])) {
                $hidden_inputs .= $this->form_fields->hiddenInput(
                    [
                      'root_name'     => true,
                      'name'          => GLOBAL_NS.'[manage][summary_nav]['.$_summary_nav_var_key.']',
                      'current_value' => (string)$current_summary_nav_vars[$_summary_nav_var_key],
                    ]
                  )."\n";
            }
        }
        unset($_summary_nav_var_key); // Housekeeping.

        return $hidden_inputs;
    }

    /*
     * Public static processors.
     */

    /**
     * Constructs a comment ID row via AJAX.
     *
     * @since 141111 First documented version.
     *
     * @param integer $post_id A post ID.
     *
     * @return string HTML markup for this select field row.
     *    If no options (or too many options; this returns an input field instead.
     *
     * @see   sub_manage_actions::sub_form_comment_id_row_via_ajax()
     */
    public static function commentIdRowViaAjax($post_id)
    {
        $post_id     = (integer)$post_id;
        $form_fields = new FormFields(static::$form_field_args);

        $template_vars = get_defined_vars(); // Everything above.
        $template      = new Template('site/sub-actions/manage-sub-form-comment-id-row-via-ajax.php');

        return $template->parse($template_vars);
    }

    /**
     * Form processor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Incoming action request args.
     *
     * @see   sub_manage_actions::sub_form()
     */
    public static function process(array $request_args)
    {
        $plugin = plugin(); // Needed below.

        $args               = [ // Behavioral args.
                                'process_confirmation'          => true,
                                'user_initiated'                => true,
                                'ui_protected_data_keys_enable' => true,
                                'ui_protected_data_user'        => wp_get_current_user(),
        ];
        static::$processing = true; // Flag as `TRUE`; along w/ other statics below.

        if (isset($request_args['key'])) { // Key sanitizer; for added security.
            $request_args['key'] = $sub_key = $plugin->utils_sub->sanitize_key($request_args['key']);
        }
        if (isset($request_args['ID'])) { // Updating an existing subscription via ID?
            $sub_updater = new SubUpdater($request_args, $args); // Run updater.

            if ($sub_updater->hasErrors()) { // Updater has errors?
                static::$processing_errors      = $sub_updater->errors();
                static::$processing_error_codes = $sub_updater->errorCodes();
                static::$processing_errors_html = $sub_updater->errorsHtml();
            } else if ($sub_updater->didUpdate()) { // Updated?
                static::$processing_successes        = $sub_updater->successes();
                static::$processing_success_codes    = $sub_updater->successCodes();
                static::$processing_successes_html   = $sub_updater->successesHtml();
                static::$processing_email_key_change = $sub_updater->emailKeyChanged();
            }
        } else if ($plugin->options['enable'] && $plugin->options['new_subs_enable']) {
            // This check is for added security only. The form should not be available.

            $sub_inserter = new SubInserter($request_args, $args); // Run inserter.

            if ($sub_inserter->hasErrors()) { // Inserter has errors?
                static::$processing_errors      = $sub_inserter->errors();
                static::$processing_error_codes = $sub_inserter->errorCodes();
                static::$processing_errors_html = $sub_inserter->errorsHtml();
            } else if ($sub_inserter->didInsert()) { // Inserted?
                static::$processing_successes      = $sub_inserter->successes();
                static::$processing_success_codes  = $sub_inserter->successCodes();
                static::$processing_successes_html = $sub_inserter->successesHtml();
            }
        }
    }
}
