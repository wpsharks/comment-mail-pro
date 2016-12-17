<?php
namespace WebSharks\CommentMail\Pro;

/**
 * Conflicts.
 *
 * @since 160618
 */
class Conflicts
{
    /**
     * Check for conflicts.
     *
     * @since 160618 Rewrite.
     */
    public static function check()
    {
        if (static::doCheck()) {
            static::maybeEnqueueNotice();
        }
        return $GLOBALS[GLOBAL_NS.'_conflicting_plugin'];
    }

    /**
     * Perform check.
     *
     * @since 160618 Rewrite.
     */
    protected static function doCheck()
    {
        if (!empty($GLOBALS[GLOBAL_NS.'_conflicting_plugin'])) {
            return $GLOBALS[GLOBAL_NS.'_conflicting_plugin'];
        }
        $lite_slug = str_replace('_', '-', GLOBAL_NS);
        $pro_slug  = str_replace('_', '-', GLOBAL_NS).'-pro';

        $conflicting_plugin_slugs = [IS_PRO ? $lite_slug : $pro_slug];

        $active_plugins           = (array) get_option('active_plugins', []);
        $active_sitewide_plugins  = is_multisite() ? array_keys((array) get_site_option('active_sitewide_plugins', [])) : [];
        $active_plugins           = array_unique(array_merge($active_plugins, $active_sitewide_plugins));

        foreach ($active_plugins as $_active_plugin_basename) {
            $_active_plugin_slug = strstr($_active_plugin_basename, '/', true);
            if (in_array($_active_plugin_slug, $conflicting_plugin_slugs, true)) {
                return $GLOBALS[GLOBAL_NS.'_conflicting_plugin'] = $_active_plugin_slug;
            }
        }
        return $GLOBALS[GLOBAL_NS.'_conflicting_plugin'] = ''; // i.e. No conflicting plugins.
    }

    /**
     * Maybe enqueue dashboard notice.
     *
     * @since 160618 Rewrite.
     */
    protected static function maybeEnqueueNotice()
    {
        if (!empty($GLOBALS[GLOBAL_NS.'_uninstalling'])) {
            return; // Not when uninstalling.
        } elseif (empty($GLOBALS[GLOBAL_NS.'_conflicting_plugin'])) {
            return; // Not conflicts.
        } elseif (!empty($GLOBALS[GLOBAL_NS.'_conflicting_plugin_lite_pro'])) {
            return; // Already did this in one plugin or the other.
        }
        add_action('all_admin_notices', function () {
            $lite_slug = str_replace('_', '-', GLOBAL_NS);
            $pro_slug  = str_replace('_', '-', GLOBAL_NS).'-pro';

            if (!empty($GLOBALS[GLOBAL_NS.'_conflicting_plugin_lite_pro'])) {
                return; // Already did this in one plugin or the other.
            }
            if (in_array($GLOBALS[GLOBAL_NS.'_conflicting_plugin'], [$lite_slug, $pro_slug], true)) {
                $GLOBALS[GLOBAL_NS.'_conflicting_plugin_lite_pro'] = true;

                echo '<div class="error">'.// Error notice.
                     '   <p>'.// Running one or more conflicting plugins at the same time.
                     '      '.sprintf(__('<strong>%1$s Lite &amp; %1$s Pro</strong> cannot run at the same time. Please deactivate one to run the other.', SLUG_TD), esc_html(NAME)).
                     '   </p>'.
                     '</div>';
            } else {
                echo '<div class="error">'.// Error notice.
                     '   <p>'.// Running one or more conflicting plugins at the same time.
                     '      '.sprintf(__('<strong>%1$s</strong> is not running. A conflicting plugin, <strong>%2$s</strong>, is currently active at the same time. Please deactivate the <strong>%2$s</strong> plugin to clear this message.', SLUG_TD), esc_html(NAME), esc_html($GLOBALS[GLOBAL_NS.'_conflicting_plugin'])).
                     '   </p>'.
                     '</div>';
            }
        });
    }
}
