<?php
/**
 * Stats Pinger
 *
 * @since     150708 Adding stats pinger.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Stats Pinger
 *
 * @since 150708 Adding stats pinger.
 */
class StatsPinger extends AbsBase
{
    /**
     * Class constructor.
     *
     * @since 150708 Adding stats pinger.
     */
    public function __construct()
    {
        parent::__construct();

        $this->maybePing();
    }

    /**
     * Maybe ping stats logger.
     *
     * @since 150708 Adding stats pinger.
     */
    protected function maybePing()
    {
        if (!apply_filters(__CLASS__.'_enable', true)) {
            return; // Stats collection off.
        }
        if ($this->plugin->options['last_pro_stats_log'] >= strtotime('-1 week')) {
            return; // No reason to keep pinging.
        }
        $this->plugin->optionsQuickSave(['last_pro_stats_log' => (string)time()]);

        $stats_api_url      = 'https://stats.wpsharks.io/log';
        $stats_api_url_args = [
          'os'              => PHP_OS,
          'php_version'     => PHP_VERSION,
          'mysql_version'   => $this->plugin->utils_db->wp->db_version(),
          'wp_version'      => get_bloginfo('version'),
          'product_version' => $this->plugin->version,
          'product'         => $this->plugin->slug.($this->plugin->is_pro ? '-pro' : ''),
        ];
        $stats_api_url      = add_query_arg(urlencode_deep($stats_api_url_args), $stats_api_url);

        wp_remote_get(
          $stats_api_url,
          [
            'blocking'  => false,
            'sslverify' => false,
          ]
        );
    }
}
	
