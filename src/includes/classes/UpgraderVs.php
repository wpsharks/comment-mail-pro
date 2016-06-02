<?php
/**
 * Upgrader (Version-Specific).
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Upgrader (Version-Specific).
 *
 * @since 141111 First documented version.
 */
class UpgraderVs extends AbsBase
{
    /**
     * @type string Previous version.
     *
     * @since 141111 First documented version.
     */
    protected $prev_version;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param string $prev_version Version they are upgrading from.
     */
    public function __construct($prev_version)
    {
        parent::__construct();

        $this->prev_version = (string) $prev_version;

        $this->runHandlers(); // Run upgrade(s).
    }

    /**
     * Runs upgrade handlers in the proper order.
     *
     * @since 141111 First documented version.
     */
    protected function runHandlers()
    {
        $this->fromLtV141115();
        $this->fromLteV160213();
    }

    /**
     * Upgrading from a version prior to our rewrite.
     *
     * @since 141111 First documented version.
     */
    protected function fromLtV141115()
    {
        if (version_compare($this->prev_version, '141115', '<')) {
            $sql1 = 'ALTER TABLE `'.esc_sql($this->plugin->utils_db->prefix().'subs').'`'.

                    " ADD `insertion_region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region code at time of insertion.',".
                    " ADD `insertion_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country code at time of insertion.',".

                    " ADD `last_region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last known geographic region code.',".
                    " ADD `last_country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Last known geographic country code.'";

            $sql2 = 'ALTER TABLE `'.esc_sql($this->plugin->utils_db->prefix().'sub_event_log').'`'.

                    " ADD `region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; at the time of the event.',".
                    " ADD `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; at the time of the event.',".

                    " ADD `region_before` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; before the event, if applicable.',".
                    " ADD `country_before` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; before the event, if applicable.'";

            $sql3 = 'ALTER TABLE `'.esc_sql($this->plugin->utils_db->prefix().'queue_event_log').'`'.

                    " ADD `region` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic region; at the time of the event.',".
                    " ADD `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Geographic country; at the time of the event.'";

            if ($this->plugin->utils_db->wp->query($sql1) === false
                || $this->plugin->utils_db->wp->query($sql2) === false
                || $this->plugin->utils_db->wp->query($sql3) === false
            ) {
                throw new \exception(__('Query failure.', SLUG_TD));
            }
        }
    }

    /**
     * Upgrading from a version prior to changes that broke back. compat. w/ Advanced Templates
     *
     * @since 16xxxx First documented version.
     */
    protected function fromLteV160213()
    {
        if (version_compare($this->prev_version, '160213', '<=')) {

            $_marker = '<?php /* --------------------------- Legacy Template Backup ---------------------------
 * Comment Mail v16xxxx included changes that were not backwards compatible with your 
 * customized Advanced Template. To prevent problems with the upgrade to v16xxxx, we reset
 * the Advanced Templates to their (new) default and backed up your customized template, a
 * copy of which is included below. You can reference your original template below to reapply
 * those changes to the new default template above. When you are ready to discard this backup,
 * simply delete this comment, and everything below it, and save the template. If you leave 
 * this comment here and save your the options, your backup below will be also saved.
 * 
 * Note: Everything below this comment is not parsed by Comment Mail; it is only here for
 * your reference so that you can re-apply your modifications to the new template above.
 */ ?>';

            foreach ($this->plugin->options as $_key => &$_value) {
                if (strpos($_key, 'template__type_a__') === 0) {
                    $_key_data                  = Template::optionKeyData($_key);
                    $_default_template          = new Template($_key_data->file, $_key_data->type, true);
                    $_option_template_contents  = $_value; // A copy of the option value (potentially incompatible, modified template).
                    $_default_template_contents = $_default_template->fileContents(); // New (safe) default template
                    $_option_template_nws       = preg_replace('/\s+/', '', $_option_template_contents); // Strip whitespace for comparison
                    $_default_template_nws      = preg_replace('/\s+/', '', $_default_template_contents); // Strip whitespace for comparison

                    if (!$_option_template_nws || $_option_template_nws === $_default_template_nws) {
                        continue; // Skip this one, because it's empty, or it's no different from the default template.
                    }

                    // Add note and append the modified (incompatible) template to the bottom of the new default template
                    $_value = $_default_template_contents."\n\n".$_marker."\n\n".$_option_template_contents;
                }
            }
            unset($_marker, $_key, $_key_data, $_value); // Housekeeping.
            unset($_default_template, $_default_template_nws, $_new_and_old_template);


            $this->plugin->optionsSave($this->plugin->options);

        }
    }
}
