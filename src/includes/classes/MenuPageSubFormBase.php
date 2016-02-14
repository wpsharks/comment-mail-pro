<?php
/**
 * Menu Page Sub. Form Base
 *
 * @since     141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Menu Page Sub. Form Base
 *
 * @since 141111 First documented version.
 */
class MenuPageSubFormBase extends AbsBase
{
    /*
     * Instance-based properties.
     */

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
      'ns_id_suffix'   => '-sub-form',
      'ns_name_suffix' => '[sub_form]',
      'class_prefix'   => 'pmp-sub-form-',
    ];

    /*
     * Instance-based constructor.
     */

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param integer $sub_id Subscription ID.
     */
    public function __construct($sub_id = null)
    {
        parent::__construct();

        if (isset($sub_id)) { // Editing?
            $this->is_edit = true; // Flag as `TRUE`.
            $sub_id        = (integer)$sub_id; // Force integer.
            $this->sub     = $this->plugin->utils_sub->get($sub_id);

            if (!$this->sub) { // Unexpected scenario; fail w/ message.
                wp_die(__('Subscription ID not found.', $this->plugin->text_domain));
            }
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
        echo '<table class="form-table">';
        echo '   <tbody>';

        echo $this->form_fields->selectRow(
          [
            'placeholder'         => __('Select a Post ID...', $this->plugin->text_domain),
            'label'               => __('<i class="fa fa-fw fa-thumb-tack"></i> Post ID #', $this->plugin->text_domain),
            'name'                => 'post_id', 'required' => true, 'options' => '%%posts%%', 'current_value' => $this->currentValueFor('post_id'),
            'notes_after'         => __('Required; the Post ID they are subscribed to.', $this->plugin->text_domain),
            'input_fallback_args' => ['type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'placeholder' => '', 'current_value_empty_on_0' => true],
          ]
        );
        echo $this->form_fields->selectRow(
          [
            'placeholder'         => __('— All Comments/Replies —', $this->plugin->text_domain),
            'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID #', $this->plugin->text_domain),
            'name'                => 'comment_id', 'required' => false, 'options' => '%%comments%%', 'post_id' => $this->currentValueFor('post_id'), 'current_value' => $this->currentValueFor('comment_id'),
            'input_fallback_args' => ['type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'current_value_empty_on_0' => true],
          ]
        );
        echo $this->form_fields->selectRow(
          [
            'placeholder'         => __('— N/A; no WP User ID —', $this->plugin->text_domain),
            'label'               => __('<i class="fa fa-fw fa-user"></i> WP User ID #', $this->plugin->text_domain),
            'name'                => 'user_id', 'required' => false, 'options' => '%%users%%', 'current_value' => $this->currentValueFor('user_id'),
            'notes_after'         => __('Associates subscription w/ a WP User ID (if applicable) to improve statistical reporting.', $this->plugin->text_domain).
                                     ' '.__('If empty, the system will automatically try to find a matching user ID for the email address.', $this->plugin->text_domain),
            'input_fallback_args' => ['type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'current_value_empty_on_0' => true],
          ]
        );
        /* -------------------------------------------------------------------- */
        echo $this->form_fields->horizontalLineRow(/* -------------------------------------------------------------------- */);
        /* -------------------------------------------------------------------- */

        echo $this->form_fields->inputRow(
          [
            'type'  => 'email', // For `<input>` type.
            'label' => __('<i class="fa fa-fw fa-envelope-o"></i> Email', $this->plugin->text_domain),
            'name'  => 'email', 'required' => true, 'maxlength' => 100, 'current_value' => $this->currentValueFor('email'),
          ]
        );
        echo $this->form_fields->inputRow(
          [
            'label' => __('<i class="fa fa-fw fa-pencil-square-o"></i> First Name', $this->plugin->text_domain),
            'name'  => 'fname', 'required' => true, 'maxlength' => 50, 'current_value' => $this->currentValueFor('fname'),
          ]
        );
        echo $this->form_fields->inputRow(
          [
            'label' => __('<i class="fa fa-fw fa-level-up fa-rotate-90" style="margin-left:1px;"></i> Last Name', $this->plugin->text_domain),
            'name'  => 'lname', 'required' => false, 'maxlength' => 100, 'current_value' => $this->currentValueFor('lname'),
          ]
        );
        /* -------------------------------------------------------------------- */
        echo $this->form_fields->horizontalLineRow(/* -------------------------------------------------------------------- */);
        /* -------------------------------------------------------------------- */

        echo $this->form_fields->inputRow(
          [
            'label'       => __('<i class="fa fa-fw fa-bullseye"></i> IP Address', $this->plugin->text_domain),
            'name'        => 'insertion_ip', 'required' => false, 'maxlength' => 39, 'current_value' => $this->currentValueFor('insertion_ip'),
            'notes_after' => __('If empty, this is filled automatically when a subscriber confirms or updates their subscription.', $this->plugin->text_domain),
          ]
        );
        if ($this->plugin->options['geo_location_tracking_enable']) {
            echo $this->form_fields->inputRow(
              [
                'label'       => __('<i class="fa fa-fw fa-map-marker"></i> IP Region Code', $this->plugin->text_domain),
                'name'        => 'insertion_region', 'required' => false, 'maxlength' => 2, 'current_value' => $this->currentValueFor('insertion_region'),
                'notes_after' => sprintf(__('If empty, this is filled automatically when a subscriber confirms or updates their subscription. Here is a map of all %1$s; found in the second column of the CSV file.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.maxmind.com/download/geoip/misc/region_codes.csv', __('Region Codes', $this->plugin->text_domain))),
              ]
            );
            echo $this->form_fields->inputRow(
              [
                'label'       => __('<i class="fa fa-fw fa-globe"></i> IP Country Code', $this->plugin->text_domain),
                'name'        => 'insertion_country', 'required' => false, 'maxlength' => 2, 'current_value' => $this->currentValueFor('insertion_country'),
                'notes_after' => sprintf(__('If empty, this is filled automatically when a subscriber confirms or updates their subscription. Here is a map of all %1$s; found in the first column of the CSV file.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('http://www.maxmind.com/download/geoip/misc/region_codes.csv', __('Country Codes', $this->plugin->text_domain))),
              ]
            );
        }
        /* -------------------------------------------------------------------- */
        echo $this->form_fields->horizontalLineRow(/* -------------------------------------------------------------------- */);
        /* -------------------------------------------------------------------- */

        echo $this->form_fields->selectRow(
          [
            'placeholder' => __('Select a Status...', $this->plugin->text_domain),
            'label'       => __('<i class="fa fa-fw fa-flag-o"></i> Status', $this->plugin->text_domain),
            'name'        => 'status', 'required' => true, 'options' => '%%status%%', 'current_value' => $this->currentValueFor('status'),

            'nested_checkbox_args' => [
              'name'          => 'process_confirmation', // With additional checkbox option too.
              'label'         => __('Request confirmation via email', $this->plugin->text_domain).' <i class="fa fa-envelope-o"></i>',
              'current_value' => $this->currentValueFor('process_confirmation'),
            ],
          ]
        );
        echo $this->form_fields->selectRow(
          [
            'placeholder' => __('Select a Delivery Option...', $this->plugin->text_domain),
            'label'       => __('<i class="fa fa-fw fa-paper-plane-o"></i> Deliver', $this->plugin->text_domain),
            'name'        => 'deliver', 'required' => true, 'options' => '%%deliver%%', 'current_value' => $this->currentValueFor('deliver'),
            'notes_after' => __('Any value that is not <code>instantly</code> results in a digest instead of instant notifications.', $this->plugin->text_domain),
          ]
        );

        echo '   </tbody>';
        echo '</table>';

        echo '<hr />';

        echo '<p class="submit">';

        if ($this->is_edit) { // Include the ID and `subscription` we're updating.
            echo $this->form_fields->hiddenInput(['name' => 'ID', 'current_value' => $this->sub->ID]);
        }
        echo '   <input type="submit"'.
             ($this->is_edit  // Are we editing?
               ? ' value="'.esc_attr(__('Update Subscription', $this->plugin->text_domain)).'"'
               : ' value="'.esc_attr(__('Create Subscription', $this->plugin->text_domain)).'"').
             '    class="button button-primary" />';

        echo '</p>';
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
    protected function currentValueFor($key_prop)
    {
        if (!($key_prop = (string)$key_prop)) {
            return null; // Not possible.
        }
        if (isset($_REQUEST[GLOBAL_NS]['sub_form'][$key_prop])) {
            return trim(stripslashes((string)$_REQUEST[GLOBAL_NS]['sub_form'][$key_prop]));
        }
        if ($this->is_edit && isset($this->sub->{$key_prop})) {
            return trim((string)$this->sub->{$key_prop});
        }
        return null; // Default value.
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
     * @see   menu_page_actions::sub_form_comment_id_row_via_ajax()
     */
    public static function commentIdRowViaAjax($post_id)
    {
        $plugin = plugin();

        if (!current_user_can($plugin->manage_cap)) {
            if (!current_user_can($plugin->cap)) {
                return ''; // Unauthenticated; ignore.
            }
        }
        $post_id     = (integer)$post_id;
        $form_fields = new FormFields(static::$form_field_args);

        return $form_fields->selectRow(
          [
            'placeholder'         => __('— All Comments/Replies —', $plugin->text_domain),
            'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment ID #', $plugin->text_domain),
            'name'                => 'comment_id', 'required' => false, 'options' => '%%comments%%', 'post_id' => $post_id, 'current_value' => null,
            'input_fallback_args' => ['type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"'],
          ]
        );
    }

    /**
     * Get user ID info via AJAX.
     *
     * @since 141111 First documented version.
     *
     * @param integer $user_id A WP user ID.
     *
     * @return string JSON data object w/ user info.
     *
     * @see   menu_page_actions::sub_form_user_id_info_via_ajax()
     */
    public static function userIdInfoViaAjax($user_id)
    {
        $plugin = plugin();

        $default_info      = [
          'email' => '',
          'fname' => '',
          'lname' => '',

          'ip'      => '',
          'region'  => '',
          'country' => '',
        ];
        $default_info_json = json_encode($default_info);

        if (!current_user_can($plugin->manage_cap)) {
            if (!current_user_can($plugin->cap)) {
                return $default_info_json;
            }
        }
        if (!current_user_can('list_users')) {
            return $default_info_json;
        }
        $user_id = (integer)$user_id;
        $user    = new \WP_User($user_id);

        if (!$user->ID) { // Has no ID?
            return $default_info_json;
        }
        $info = [
          'email' => $user->user_email,
          'fname' => $plugin->utils_string->first_name('', $user),
          'lname' => $plugin->utils_string->last_name('', $user),

          'ip' => $plugin->utils_user->is_current($user) ? $plugin->utils_ip->current()
            : $plugin->utils_sub->email_last_ip($user->user_email),

          'region' => $plugin->utils_user->is_current($user) ? $plugin->utils_ip->current_region()
            : $plugin->utils_sub->email_last_region($user->user_email),

          'country' => $plugin->utils_user->is_current($user) ? $plugin->utils_ip->current_country()
            : $plugin->utils_sub->email_last_country($user->user_email),
        ];
        $info = array_merge($default_info, $info);
        $info = array_intersect_key($info, $default_info);

        return ($info_json = json_encode($info));
    }

    /**
     * Form processor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Incoming action request args.
     *
     * @see   menu_page_actions::sub_form()
     */
    public static function process(array $request_args)
    {
        $plugin = plugin(); // Needed below.

        if (!current_user_can($plugin->manage_cap)) {
            if (!current_user_can($plugin->cap)) {
                return; // Unauthenticated; ignore.
            }
        }
        $reporting_errors     = false; // Initialize.
        $process_confirmation = !empty($request_args['process_confirmation']);
        $args                 = compact('process_confirmation');

        if (isset($request_args['ID'])) { // Updating an existing subscription via ID?
            $sub_updater = new SubUpdater($request_args, $args); // Run updater.

            if ($sub_updater->didUpdate()) { // Updated successfully?
                $plugin->enqueue_user_notice( // Queue notice.
                  sprintf(__('Subscription ID #<code>%1$s</code> updated successfully.', $plugin->text_domain), esc_html($request_args['ID'])),
                  ['transient' => true, 'for_page' => $plugin->utils_env->current_menu_page()]
                );

                $redirect_to = $plugin->utils_url->page_table_nav_vars_only();
            } else { // There were errors; display those errors to the current user.
                $plugin->enqueue_user_error( // Queue error notice.
                  sprintf(__('Failed to update subscription ID #<code>%1$s</code>. Please review the following error(s):', $plugin->text_domain), esc_html($request_args['ID'])).
                  '<ul class="pmp-list-items"><li>'.implode('</li><li>', $sub_updater->errorsHtml()).'</li></ul>',
                  ['transient' => true, 'for_page' => $plugin->utils_env->current_menu_page()]
                );
            }
        } else { // We are doing a new insertion; i.e. a new subscription is being added here.
            $sub_inserter = new SubInserter($request_args, $args); // Run inserter.

            if ($sub_inserter->didInsert()) { // Inserted successfully?
                $plugin->enqueue_user_notice( // Queue notice.
                  sprintf(__('Subscription ID #<code>%1$s</code> created successfully.', $plugin->text_domain), esc_html($sub_inserter->insertId())),
                  ['transient' => true, 'for_page' => $plugin->utils_env->current_menu_page()]
                );
                $redirect_to = $plugin->utils_url->page_table_nav_vars_only();
            } else { // There were errors; display those errors to the current user.
                $plugin->enqueue_user_error( // Queue error notice.
                  __('Failed to create new subscription. Please review the following error(s):', $plugin->text_domain).
                  '<ul class="pmp-list-items"><li>'.implode('</li><li>', $sub_inserter->errorsHtml()).'</li></ul>',
                  ['transient' => true, 'for_page' => $plugin->utils_env->current_menu_page()]
                );
            }
        }
        if (!empty($redirect_to)) { // If applicable.
            if (headers_sent()) { // Output started already?
                exit('      <script type="text/javascript">'.
                     "         document.getElementsByTagName('body')[0].style.display = 'none';".
                     "         location.href = '".$plugin->utils_string->esc_js_sq($redirect_to)."';".
                     '      </script>'.
                     '   </body>'.
                     '</html>');
            }
            wp_redirect($redirect_to);
            exit();
        }
    }
}
