<?php
/**
 * Plugin functions.
 *
 * @since 160212 PSR compliance.
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Core {@link Plugin} class instance.
 *
 * @since 141111 First documented version.
 *
 * @return plugin Class instance.
 */
function plugin()
{
    return $GLOBALS[GLOBAL_NS];
}
