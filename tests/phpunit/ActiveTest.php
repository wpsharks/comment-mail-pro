<?php
namespace WebSharks\CommentMail\Pro;

class ActiveTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->plugin = $GLOBALS[GLOBAL_NS];
    }

    public function testActive()
    {
        $this->assertSame(true, (bool) $this->plugin->options['enable']);
    }
}
