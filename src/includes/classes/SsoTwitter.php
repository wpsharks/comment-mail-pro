<?php
/**
 * SSO for Twitter
 *
 * @since     141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * SSO for Twitter
 *
 * @since 141111 First documented version.
 */
class SsoTwitter extends SsoServiceBase
{
    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Incoming request args.
     */
    public function __construct(array $request_args)
    {
        parent::__construct('twitter', $request_args);
    }

    /**
     * Handle SSO authorization redirection.
     *
     * @since 141111 First documented version.
     */
    protected function maybeHandleAuthorize()
    {
        try { // Catch exceptions and log them for debugging.
            $service_factory = new \OAuth\ServiceFactory();
            $credentials     = new \OAuth\Common\Consumer\Credentials(
              $this->plugin->options['sso_'.$this->service.'_key'],
              $this->plugin->options['sso_'.$this->service.'_secret'],
              $this->plugin->utils_url->sso_action_url($this->service, 'callback')
            );
            /** @var $service \OAuth\OAuth1\Service\Twitter */
            $service = $service_factory->createService($this->service, $credentials, $this->storage);

            $token = $service->requestRequestToken()->getRequestToken(); // oAuth 1.0 requires a request token.
            $this->processAuthorizationRedirect($service->getAuthorizationUri(['oauth_token' => $token]));
        } catch (\exception $exception) {
            $this->processException($exception);
        }
    }

    /**
     * Handle SSO; i.e. account generation or login.
     *
     * @since 141111 First documented version.
     */
    protected function maybeHandleCallback()
    {
        try { // Catch exceptions and log them for debugging.
            if (!$this->request_args['oauth_token'] || !$this->request_args['oauth_verifier']) {
                throw new \exception(__('Missing oAuth token/verifier.', $this->plugin->text_domain));
            }
            $service_factory = new \OAuth\ServiceFactory();
            $credentials     = new \OAuth\Common\Consumer\Credentials(
              $this->plugin->options['sso_'.$this->service.'_key'],
              $this->plugin->options['sso_'.$this->service.'_secret'],
              $this->plugin->utils_url->sso_action_url($this->service, 'callback')
            );
            /** @var $service \OAuth\OAuth1\Service\Twitter */
            $service = $service_factory->createService($this->service, $credentials, $this->storage);

            # Request access token via oAuth API provided by this service.

            $service->requestAccessToken($this->request_args['oauth_token'], $this->request_args['oauth_verifier']);

            # Acquire and validate data received from this service.

            if (!is_object($service_user = json_decode($service->request('account/verify_credentials.json')))) {
                throw new \exception(__('Failed to acquire user.', $this->plugin->text_domain));
            }
            if (empty($service_user->id) || !($sso_id = (string)$service_user->id)) {
                throw new \exception(__('Failed to obtain user.', $this->plugin->text_domain));
            }
            foreach (['name', 'screen_name'] as $_prop) {
                if (!isset($service_user->{$_prop})) {
                    $service_user->{$_prop} = '';
                }
                if (strcasecmp($service_user->{$_prop}, 'private') === 0) {
                    $service_user->{$_prop} = ''; // If `private`; empty.
                }
            }
            unset($_prop); // Just a little housekeeping.

            if (!($fname = $this->request_args['fname'])) {
                $fname = $this->plugin->utils_string->firstName(
                  $this->coalesce($service_user->name, $service_user->screen_name),
                  $this->request_args['email'] // Twitter does not provide this.
                );
            }
            if (!($lname = $this->request_args['lname'])) {
                $lname = $this->plugin->utils_string->lastName($service_user->name);
            }
            if (!$lname) {
                $lname = $this->plugin->utils_string->lastName($service_user->screen_name);
            }
            $email = $this->request_args['email']; // From request args only.

            $this->processCallbackCompleteRedirect(compact('sso_id', 'fname', 'lname', 'email'));
        } catch (\exception $exception) {
            $this->processException($exception);
        }
    }
}
