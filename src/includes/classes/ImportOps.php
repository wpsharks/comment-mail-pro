<?php
/*[pro exclude-file-from="lite"]*/
/**
 * Options Importer.
 *
 * @since     141111 First documented version.
 *
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license   GNU General Public License, version 3
 */
namespace WebSharks\CommentMail\Pro;

/**
 * Options Importer.
 *
 * @since 141111 First documented version.
 */
class ImportOps extends AbsBase
{
    /**
     * @type string Input data.
     *
     * @since 141111 First documented version.
     */
    protected $data;

    /**
     * @type string Input data file.
     *
     * @since 141111 First documented version.
     */
    protected $data_file;

    /**
     * Class constructor.
     *
     * @since 141111 First documented version.
     *
     * @param array $request_args Arguments to the constructor.
     *                            These should NOT be trusted; they come from a `$_REQUEST` action.
     *
     * @throws \exception If a security flag is triggered on `$this->data_file`.
     */
    public function __construct(array $request_args = [])
    {
        parent::__construct();

        $default_request_args = [
            'data'      => '',
            'data_file' => '',
        ];
        $request_args = array_merge($default_request_args, $request_args);
        $request_args = array_intersect_key($request_args, $default_request_args);

        $this->data      = trim((string) $request_args['data']);
        $this->data_file = trim((string) $request_args['data_file']);

        if ($this->data_file) { // Run security flag checks on the path.
            $this->plugin->utils_fs->checkPathSecurity($this->data_file, true);
        }
        if ($this->data_file) {
            $this->data = ''; // Favor file over raw data.
        }
        $this->maybeImport();
    }

    /**
     * Import processor.
     *
     * @since 141111 First documented version.
     */
    protected function maybeImport()
    {
        if (!current_user_can($this->plugin->cap)) {
            return; // Unauthenticated; ignore.
        }
        if ($this->data_file) { // File takes precedence.
            $options_to_import = json_decode(file_get_contents($this->data_file), true);
        } else {
            $options_to_import = json_decode($this->data, true);
        }
        $options_to_import = (array) $options_to_import; // Force array.
        unset($options_to_import['version'], $options_to_import['crons_setup']);

        $this->plugin->optionsSave($options_to_import);

        $this->enqueueNoticesAndRedirect();
    }

    /**
     * Notices and redirection.
     *
     * @since 141111 First documented version.
     */
    protected function enqueueNoticesAndRedirect()
    {
        $notice_markup = sprintf(__('<strong>Imported %1$s&trade; config. options successfully.</strong>', SLUG_TD), esc_html(NAME));

        $this->plugin->enqueueUserNotice($notice_markup, ['transient' => true, 'for_page' => $this->plugin->utils_env->currentMenuPage()]);

        wp_redirect($this->plugin->utils_url->pageOnly());
        exit();
    }
}
