<?php
/**
 * SSO Actions
 *
 * @since     141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * SSO Actions
 *
 * @since 141111 First documented version.
 */
class SsoActions extends AbsBase
{
    /**
     * @var array Valid actions.
     *
     * @since 141111 First documented version.
     */
    protected $valid_actions;

    /**
     * @var array Valid services.
     *
     * @since 141111 First documented version.
     */
    public static $valid_services = [
        'twitter',
        'facebook',
        'google',
        'linkedin',
    ];

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        parent::__construct();

        $this->valid_actions = [
            'sso',
        ];
        $this->maybeHandle();
    }

    /**
     * Action handler.
     *
     * @since 141111 First documented version.
     */
    protected function maybeHandle()
    {
        if (is_admin()) {
            return; // Not applicable.
        }
        if (empty($_REQUEST[GLOBAL_NS])) {
            return; // Not applicable.
        }
        $cb_r_args = []; // Initialize callback request args.
        $_r        = $this->plugin->utils_string->trimStripDeep($_REQUEST);

        foreach (['oauth_token', 'oauth_verifier', 'oauth_problem', 'code', 'state'] as $_cb_r_arg_key) {
            if (isset($_r[$_cb_r_arg_key])) {
                $cb_r_args[$_cb_r_arg_key] = $_r[$_cb_r_arg_key];
            }
        }
        unset($_cb_r_arg_key); // Housekeeping.

        foreach ((array)$_REQUEST[GLOBAL_NS] as $_action => $_request_args) {
            if ($_action && in_array($_action, $this->valid_actions, true) && is_array($_request_args)) {
                $_method = preg_replace_callback('/_(.)/', function ($m) { return strtoupper($m[1]); }, strtolower($_action));
                $this->{$_method}(array_merge($cb_r_args, $this->plugin->utils_string->trimStripDeep($_request_args)));
            }
        }
        unset($_action, $_method, $_request_args); // Housekeeping.
    }

    /**
     * SSO actions for various services.
     *
     * @since 141111 First documented version.
     *
     * @param mixed $request_args Input argument(s).
     */
    protected function sso(array $request_args)
    {
        if (empty($request_args['service'])) {
            return; // Empty service identifier.
        }
        if (!in_array($request_args['service'], static::$valid_services, true)) {
            return; // Invalid import type.
        }
        if (!class_exists($class = '\\'.__NAMESPACE__.'\\Sso'.ucfirst($request_args['service']))) {
            return; // Invalid service identifier.
        }
        new $class($request_args);

        exit(); // Stop; always.
    }
}
	
