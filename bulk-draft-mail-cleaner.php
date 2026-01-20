<?php
/**
 * Plugin Name: Bulk Draft Mail Cleaner
 * Description: Bulk delete draft emails/posts from WordPress admin.
 * Version: 1.0.0
 * Author: Gaurav Shere
 */

if (!defined('ABSPATH')) {
    exit;
}

class Bulk_Draft_Mail_Cleaner {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'load_assets']);
        add_action('wp_ajax_bdmc_delete_drafts', [$this, 'delete_drafts']);
    }

    public function register_menu() {
        add_menu_page(
            'Draft Mail Cleaner',
            'Draft Mail Cleaner',
            'manage_options',
            'draft-mail-cleaner',
            [$this, 'admin_page'],
            'dashicons-email-alt2'
        );
    }

    public function load_assets($hook) {
        if ($hook !== 'toplevel_page_draft-mail-cleaner') {
            return;
        }

        wp_enqueue_style(
            'bdmc-admin-css',
            plugin_dir_url(__FILE__) . 'assets/css/admin.css'
        );

        wp_enqueue_script(
            'bdmc-admin-js',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            ['jquery'],
            null,
            true
        );

        wp_localize_script('bdmc-admin-js', 'bdmcData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('bdmc_nonce')
        ]);
    }

    public function admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $drafts = get_posts([
            'post_status' => 'draft',
            'post_type'   => 'post',
            'numberposts' => -1
        ]);
        ?>

        <div class="wrap">
            <h1>Bulk Draft Mail Cleaner</h1>

            <?php if (empty($drafts)) : ?>
                <p>No draft mails found.</p>
            <?php else : ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Title</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($drafts as $draft) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="draft-checkbox" value="<?= esc_attr($draft->ID); ?>">
                                </td>
                                <td><?= esc_html($draft->post_title ?: '(No Title)'); ?></td>
                                <td><?= esc_html($draft->post_date); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button id="delete-selected" class="button button-primary">
                    Delete Selected Drafts
                </button>
            <?php endif; ?>
        </div>
        <?php
    }

    public function delete_drafts() {
        check_ajax_referer('bdmc_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $ids = isset($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];

        if (empty($ids)) {
            wp_send_json_error('No drafts selected');
        }

        foreach ($ids as $id) {
            wp_delete_post($id, true);
        }

        wp_send_json_success('Drafts deleted successfully');
    }
}

new Bulk_Draft_Mail_Cleaner();