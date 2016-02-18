<?php
/**
 * SSO Storage.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;

/**
 * SSO Storage.
 *
 * @since 141111 First documented version.
 */
class SsoStorage implements \OAuth\Common\Storage\TokenStorageInterface
{
    /**
     * @type Plugin Plugin class reference.
     *
     * @since 141111 First documented version.
     */
    protected $plugin;

    /**
     * @type int Time to live.
     *
     * @since 141111 First documented version.
     */
    protected $ttl;

    /**
     * @type string SSO cookie key.
     *
     * @since 141111 First documented version.
     */
    protected $key;

    /**
     * @type string Transient key.
     *
     * @since 141111 First documented version.
     */
    protected $transient;

    /**
     * @type array Transient SSO data.
     *
     * @since 141111 First documented version.
     */
    protected $data;

    /*
     * Constructor.
     */

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     */
    public function __construct()
    {
        $this->plugin = plugin();

        $this->ttl = apply_filters(__CLASS__.'_ttl', 31556926);

        if (!($this->key = $this->plugin->utils_enc->getCookie(GLOBAL_NS.'_sso_key'))) {
            $this->key = $this->plugin->utils_enc->uunnciKey20Max();
            $this->plugin->utils_enc->setCookie(GLOBAL_NS.'_sso_key', $this->key, $this->ttl);
        }
        $this->key       = preg_replace('/[^a-z0-9]/i', '', $this->key);
        $this->key       = substr($this->key, 0, 20); // 20 chars max.
        $this->transient = TRANSIENT_PREFIX.'sso_key_'.$this->key;

        if (!($this->data = get_transient($this->transient))) {
            $this->data = []; // Initialize.
        }
    }

    /*
     * Access tokens.
     */

    /**
     * {@inheritdoc}
     */
    public function hasAccessToken($service)
    {
        $service = trim(strtolower((string) $service));

        return !empty($this->data['tokens'][$service]);
    }

    /**
     * {@inheritdoc}
     *
     * @return \OAuth\oAuth1\Token\StdOAuth1Token
     *                                            |\OAuth\oAuth2\Token\StdOAuth2Token
     */
    public function retrieveAccessToken($service)
    {
        $service = trim(strtolower((string) $service));

        if ($this->hasAccessToken($service)) {
            return unserialize($this->data['tokens'][$service]);
        }
        throw new TokenNotFoundException(__('Token not found.', SLUG_TD));
    }

    /**
     * {@inheritdoc}
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $service = trim(strtolower((string) $service));

        $this->data['tokens'][$service] = serialize($token);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /**
     * {@inheritdoc}
     */
    public function clearToken($service)
    {
        $service = trim(strtolower((string) $service));

        unset($this->data['tokens'][$service]);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllTokens()
    {
        unset($this->data['tokens']);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /*
     * Authorization states.
     */

    /**
     * {@inheritdoc}
     */
    public function hasAuthorizationState($service)
    {
        $service = trim(strtolower((string) $service));

        return !empty($this->data['states'][$service]);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveAuthorizationState($service)
    {
        $service = trim(strtolower((string) $service));

        if ($this->hasAuthorizationState($service)) {
            return unserialize($this->data['states'][$service]);
        }
        throw new AuthorizationStateNotFoundException(__('State not found.', SLUG_TD));
    }

    /**
     * {@inheritdoc}
     */
    public function storeAuthorizationState($service, $state)
    {
        $service = trim(strtolower((string) $service));

        $this->data['states'][$service] = serialize($state);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /**
     * {@inheritdoc}
     */
    public function clearAuthorizationState($service)
    {
        $service = trim(strtolower((string) $service));

        unset($this->data['states'][$service]);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllAuthorizationStates()
    {
        unset($this->data['states']);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /*
     * Extras; custom implementation.
     */

    /**
     * {@inheritdoc}
     */
    public function hasExtra($service)
    {
        $service = trim(strtolower((string) $service));

        return !empty($this->data['extras'][$service]);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveExtra($service)
    {
        $service = trim(strtolower((string) $service));

        if ($this->hasExtra($service)) {
            return unserialize($this->data['extras'][$service]);
        }
        throw new \Exception(__('Extra data not found.', SLUG_TD));
    }

    /**
     * {@inheritdoc}
     */
    public function storeExtra($service, $extra)
    {
        $service = trim(strtolower((string) $service));

        $this->data['extras'][$service] = serialize($extra);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /**
     * {@inheritdoc}
     */
    public function clearExtra($service)
    {
        $service = trim(strtolower((string) $service));

        unset($this->data['extras'][$service]);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllExtras()
    {
        unset($this->data['extras']);
        set_transient($this->transient, $this->data, $this->ttl);

        return $this; // Allow chaining.
    }
}
