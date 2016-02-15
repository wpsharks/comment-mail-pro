<?php
namespace WebSharks\CommentMail\Pro;

/*
 * @var Plugin      $plugin Plugin class.
 * @var Template    $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var integer     $post_id The post ID for which to obtain comments.
 * @var FormFields $form_fields Form fields class.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php echo $form_fields->selectRow(
    array(
        'placeholder'         => __('— All Comments/Replies —', $plugin->text_domain),
        'label'               => __('<i class="fa fa-fw fa-comment-o"></i> Comment', $plugin->text_domain),
        'name'                => 'comment_id', 'required' => false, 'options' => '%%comments%%', 'post_id' => $post_id, 'current_value' => null,
        'input_fallback_args' => array('type' => 'number', 'maxlength' => 20, 'other_attrs' => 'min="1" max="18446744073709551615"', 'current_value_empty_on_0' => true),
    )
);
?>
