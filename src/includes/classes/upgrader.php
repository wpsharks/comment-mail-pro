<?php
/**
 * Upgrade Routines
 *
 * @since     141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Upgrade Routines
 *
 * @since 141111 First documented version.
 */
class Upgrader extends AbsBase
{
    /**
     * @var string Previous version.
     *
     * @since 141111 First documented version.
     */
    protected $prev_version;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->prev_version = $this->plugin->options['version'];

        $this->maybeUpgrade();
    }

    /**
     * Upgrade routine(s).
     *
     * @since 141111 First documented version.
     */
    protected function maybeUpgrade()
    {
        if (version_compare($this->prev_version, $this->plugin->version, '>=')) {
            return; // Nothing to do; already @ latest version.
        }
        $this->plugin->options['version'] = $this->plugin->version;
        update_option(GLOBAL_NS.'_options', $this->plugin->options);

        new UpgraderVs($this->prev_version); // Run version-specific upgrader(s).

        $this->plugin->enqueueNotice( // Notify site owner about this upgrade process.
          sprintf(__('<strong>%1$s&trade;</strong> was automatically recompiled upon detecting an upgrade to v%2$s. Your existing configuration remains :-)', $this->plugin->text_domain), esc_html($this->plugin->name), esc_html($this->plugin->version)),
          ['requires_cap' => $this->plugin->auto_recompile_cap, 'push_to_top' => true]
        );
    }
}
