<?php
/**
 * Subscriber Actions
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;



		/**
		 * Subscriber Actions
		 *
		 * @since 141111 First documented version.
		 */
	class SubActions extends AbsBase
		{
			/**
			 * @var array Valid actions.
			 *
			 * @since 141111 First documented version.
			 */
			protected $valid_actions;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->valid_actions
					= array(
					'confirm',
					'unsubscribe',
					'unsubscribe_all',

					'manage',
				);
				$this->maybe_handle();
			}

			/**
			 * Action handler.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_handle()
			{
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[GLOBAL_NS]))
					return; // Not applicable.

				foreach((array)$_REQUEST[GLOBAL_NS] as $_action => $_request_args)
					if($_action && in_array($_action, $this->valid_actions, TRUE))
						$this->{$_action}($this->plugin->utils_string->trim_strip_deep($_request_args));
				unset($_action, $_request_args); // Housekeeping.
			}

			/**
			 * Confirm handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function confirm($request_args)
			{
				$sub_key             = '';
				$user_initiated      = true;
				$process_list_server = false;

				// Initialize others needed by template.
				$sub = $sub_post = $sub_comment = NULL;

				$error_codes = array(); // Initialize.

				$sub_key = (string)$request_args;
				if(stripos($sub_key, '.pls') !== false)
				{
					list($sub_key)       = explode('.pls', $sub_key, 2);
					$process_list_server = true; // Processing.
				}
				if(!($sub_key = $this->plugin->utils_sub->sanitize_key($sub_key)))
					$error_codes[] = 'missing_sub_key';

				else if(!($sub = $this->plugin->utils_sub->get($sub_key)))
					$error_codes[] = 'invalid_sub_key';

				else if(!($sub_post = get_post($sub->post_id)))
					$error_codes[] = 'sub_post_id_missing';

				else if($sub->comment_id && !($sub_comment = get_comment($sub->comment_id)))
					$error_codes[] = 'sub_comment_id_missing';

				if(!$error_codes) // If no errors; set current email.
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				if(!$error_codes && !($confirmed = $this->plugin->utils_sub->confirm($sub->ID, compact('user_initiated', 'process_list_server'))))
					$error_codes[] = $confirmed === NULL ? 'invalid_sub_key' : 'sub_already_confirmed';

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new Template('site/sub-actions/confirmed.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/**
			 * Unsubscribe handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function unsubscribe($request_args)
			{
				$sub_key        = '';
				$user_initiated = true;

				// Initialize others needed by template.
				$sub = $sub_post = $sub_comment = NULL;

				$error_codes = array(); // Initialize.

				if(!($sub_key = $this->plugin->utils_sub->sanitize_key($request_args)))
					$error_codes[] = 'missing_sub_key';

				else if(!($sub = $this->plugin->utils_sub->get($sub_key)))
					$error_codes[] = 'invalid_sub_key';

				if(!$error_codes) // May not exist!
					$sub_post = get_post($sub->post_id);

				if(!$error_codes && $sub->comment_id) // May not exist!
					$sub_comment = get_comment($sub->comment_id);

				if(!$error_codes) // Note: this MUST come before deletion.
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				if(!$error_codes && !($deleted = $this->plugin->utils_sub->delete($sub->ID, compact('user_initiated'))))
					$error_codes[] = $deleted === NULL ? 'invalid_sub_key' : 'sub_already_unsubscribed';

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new Template('site/sub-actions/unsubscribed.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/**
			 * Unsubscribe ALL handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function unsubscribe_all($request_args)
			{
				$sub_email = ''; // Initialize.

				$error_codes = array(); // Initialize.

				if(!($sub_email = $this->plugin->utils_enc->decrypt($request_args)))
					$error_codes[] = 'missing_sub_email';

				$delete_args = array('user_initiated' => TRUE); // Deletion args.
				if(!$error_codes && !($deleted = $this->plugin->utils_sub->delete_email_user_all($sub_email, $delete_args)))
					$error_codes[] = 'sub_already_unsubscribed_all';

				$template_vars = get_defined_vars(); // Everything above.
				$template      = new Template('site/sub-actions/unsubscribed-all.php');

				status_header(200); // Status header.
				nocache_headers(); // Disallow caching.
				header('Content-Type: text/html; charset=UTF-8');

				exit($template->parse($template_vars));
			}

			/**
			 * Manage handler w/ sub. actions.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function manage($request_args)
			{
				$sub_key = ''; // Initialize.

				if(is_string($request_args)) // Key sanitizer.
					$sub_key = $this->plugin->utils_sub->sanitize_key($request_args);

				if($sub_key && ($sub = $this->plugin->utils_sub->get($sub_key)))
					$this->plugin->utils_sub->set_current_email($sub_key, $sub->email);

				if(!is_array($request_args)) // If NOT a sub action, redirect to one.
					wp_redirect($this->plugin->utils_url->sub_manage_summary_url($sub_key)).exit();

				new SubManageActions(); // Handle sub. manage actions.
			}
		}
