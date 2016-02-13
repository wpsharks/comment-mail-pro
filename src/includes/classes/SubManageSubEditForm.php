<?php
/**
 * Sub. Management Sub. Edit Form
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;



		/**
		 * Sub. Management Sub. Edit Form
		 *
		 * @since 141111 First documented version.
		 */
		class sub_manage_sub_edit_form extends SubManageSubFormBase
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $sub_key Unique subscription key.
			 */
			public function __construct($sub_key)
			{
				parent::__construct((string)$sub_key);
			}
		}
