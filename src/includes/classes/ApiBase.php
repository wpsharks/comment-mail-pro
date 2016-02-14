<?php
/**
 * API abstraction.
 *
 * @since 160212 PSR compliance.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

abstract class ApiBase extends AbsBase
{
    public static function subOps()
    {
        new CommentFormAfter(true);
    }
}
