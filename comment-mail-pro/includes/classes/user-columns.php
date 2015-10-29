<?php
/**
* User Columns
*
* @since 15xxxx Adding custom user columns.
* @copyright WebSharks, Inc. <http://www.websharks-inc.com>
* @license GNU General Public License, version 3
*/
namespace comment_mail // Root namespace.
{
  if(!defined('WPINC')) // MUST have WordPress.
    exit('Do NOT access this file directly: '.basename(__FILE__));

  if(!class_exists('\\'.__NAMESPACE__.'\\user_columns'))
  {
    /**
     * User Columns
     *
     * @since 15xxxx Adding custom user columns.
     */
    class user_columns extends abs_base
    {
      /**
       * Class constructor.
       *
       * @since 15xxxx Adding custom user columns.
       */
      public function __construct()
      {
        parent::__construct();
      }

      /**
       * Filter (and add) user columns.
       *
       * @since 15xxxx Adding custom user columns.
       *
       * @param array $columns Existing columns.
       *
       * @return array Filtered columns.
       */
      public function filter(array $columns)
      {
        if(!$this->plugin->options['enable'])
          return $columns; // Not applicable.

        if($this->plugin->options['sso_enable'])
          $columns[__NAMESPACE__.'_sso_services'] = __('SSO Service', $this->plugin->text_domain);

        return $columns;
      }

      /**
       * Maybe fill custom user columns.
       *
       * @since 15xxxx Adding custom user columns.
       *
       * @param mixed $value Existing column value.
       * @param string $column Column name.
       * @param int|string $user_id User ID.
       *
       * @return mixed Filtered value.
       */
      public function maybe_fill($value, $column, $user_id)
      {
        if ($column === __NAMESPACE__.'_sso_services') {
          $user_sso_services = get_user_option(__NAMESPACE__.'_sso_services', $user_id);
          $user_sso_services = is_array($user_sso_services) ? $user_sso_services : array();
          $value = $user_sso_services ? implode(', ', $user_sso_services) : '—';
        }
        return $value;
      }
    }
  }
}
