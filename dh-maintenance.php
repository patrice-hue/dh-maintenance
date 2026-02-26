<?php
/**
 * Plugin Name: DH Maintenance
 * Plugin URI:  https://github.com/patrice-hue/dh-maintenance
 * Description: A simple maintenance mode plugin with customizable logo, title and content.
 * Version:     1.1.0
 * Author:      Patrice Hue
 * License:     GPL-2.0-or-later
 * Text Domain: dh-maintenance
 */

defined( 'ABSPATH' ) || exit;

define( 'DH_MAINTENANCE_VERSION', '1.1.0' );
define( 'DH_MAINTENANCE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DH_MAINTENANCE_URL', plugin_dir_url( __FILE__ ) );

/* -----------------------------------------------------------------------
 * Core: intercept front-end requests when maintenance mode is active
 * --------------------------------------------------------------------- */
add_action( 'template_redirect', 'dh_maintenance_intercept', 1 );

function dh_maintenance_intercept() {
    $options = get_option( 'dh_maintenance_options', array() );

    // Maintenance mode must be explicitly toggled on.
    if ( empty( $options['enabled'] ) ) {
        return;
    }

    // Let users whose role is in the bypass list through.
    if ( is_user_logged_in() ) {
        $bypass_roles = isset( $options['bypass_roles'] ) ? (array) $options['bypass_roles'] : array( 'administrator' );
        $user         = wp_get_current_user();
        foreach ( $bypass_roles as $role ) {
            if ( in_array( $role, (array) $user->roles, true ) ) {
                return;
            }
        }
    }

    // Allow WP-cron, REST, XML-RPC to function normally.
    if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
        return;
    }
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }
    if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
        return;
    }

    // Send the proper HTTP status and render the maintenance template.
    http_response_code( 503 );
    header( 'Retry-After: 3600' );

    include DH_MAINTENANCE_DIR . 'templates/maintenance.php';
    exit;
}

/* -----------------------------------------------------------------------
 * Admin: settings page
 * --------------------------------------------------------------------- */
add_action( 'admin_menu', 'dh_maintenance_add_menu' );

function dh_maintenance_add_menu() {
    add_options_page(
        __( 'DH Maintenance', 'dh-maintenance' ),
        __( 'DH Maintenance', 'dh-maintenance' ),
        'manage_options',
        'dh-maintenance',
        'dh_maintenance_settings_page'
    );
}

add_action( 'admin_init', 'dh_maintenance_register_settings' );

function dh_maintenance_register_settings() {
    register_setting(
        'dh_maintenance_group',
        'dh_maintenance_options',
        array(
            'sanitize_callback' => 'dh_maintenance_sanitize',
        )
    );
}

function dh_maintenance_sanitize( $input ) {
    $clean = array();

    $clean['enabled'] = ! empty( $input['enabled'] ) ? 1 : 0;

    $clean['logo_url'] = isset( $input['logo_url'] )
        ? esc_url_raw( $input['logo_url'] )
        : '';

    $clean['title'] = isset( $input['title'] )
        ? sanitize_text_field( $input['title'] )
        : '';

    $clean['content'] = isset( $input['content'] )
        ? wp_kses_post( $input['content'] )
        : '';

    $clean['bg_color'] = isset( $input['bg_color'] )
        ? sanitize_hex_color( $input['bg_color'] )
        : '#ffffff';

    $clean['text_color'] = isset( $input['text_color'] )
        ? sanitize_hex_color( $input['text_color'] )
        : '#333333';

    // Roles allowed to bypass maintenance mode. Administrator always bypasses.
    $raw_roles = isset( $input['bypass_roles'] ) && is_array( $input['bypass_roles'] )
        ? array_map( 'sanitize_key', $input['bypass_roles'] )
        : array();
    if ( ! in_array( 'administrator', $raw_roles, true ) ) {
        $raw_roles[] = 'administrator';
    }
    $clean['bypass_roles'] = $raw_roles;

    return $clean;
}

/* -----------------------------------------------------------------------
 * Admin: enqueue media uploader + admin styles
 * --------------------------------------------------------------------- */
add_action( 'admin_enqueue_scripts', 'dh_maintenance_admin_scripts' );

function dh_maintenance_admin_scripts( $hook ) {
    if ( 'settings_page_dh-maintenance' !== $hook ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'dh-maintenance-admin',
        DH_MAINTENANCE_URL . 'assets/css/admin.css',
        array(),
        DH_MAINTENANCE_VERSION
    );
    wp_enqueue_script(
        'dh-maintenance-admin',
        DH_MAINTENANCE_URL . 'assets/js/admin.js',
        array( 'jquery' ),
        DH_MAINTENANCE_VERSION,
        true
    );
}

/* -----------------------------------------------------------------------
 * Admin: settings page HTML
 * --------------------------------------------------------------------- */
function dh_maintenance_settings_page() {
    $options = get_option( 'dh_maintenance_options', array() );
    $enabled    = ! empty( $options['enabled'] );
    $logo_url   = isset( $options['logo_url'] )  ? $options['logo_url']  : '';
    $title      = isset( $options['title'] )     ? $options['title']     : __( 'We\'ll be back soon!', 'dh-maintenance' );
    $content    = isset( $options['content'] )   ? $options['content']   : __( 'Our website is currently undergoing scheduled maintenance. Thank you for your patience.', 'dh-maintenance' );
    $bg_color     = isset( $options['bg_color'] )     ? $options['bg_color']     : '#ffffff';
    $text_color   = isset( $options['text_color'] )   ? $options['text_color']   : '#333333';
    $bypass_roles = isset( $options['bypass_roles'] ) ? (array) $options['bypass_roles'] : array( 'administrator' );
    ?>
    <div class="wrap dh-maintenance-wrap">
        <h1><?php esc_html_e( 'DH Maintenance', 'dh-maintenance' ); ?></h1>

        <?php if ( $enabled ) : ?>
            <div class="dh-status-banner dh-status-active">
                <span class="dh-status-dot"></span>
                <?php esc_html_e( 'Maintenance mode is currently ACTIVE — visitors see the maintenance page.', 'dh-maintenance' ); ?>
            </div>
        <?php else : ?>
            <div class="dh-status-banner dh-status-inactive">
                <span class="dh-status-dot"></span>
                <?php esc_html_e( 'Maintenance mode is currently INACTIVE — your site is visible to everyone.', 'dh-maintenance' ); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'dh_maintenance_group' ); ?>

            <table class="form-table dh-form-table">

                <!-- Toggle -->
                <tr>
                    <th scope="row"><?php esc_html_e( 'Maintenance Mode', 'dh-maintenance' ); ?></th>
                    <td>
                        <label class="dh-toggle">
                            <input type="checkbox"
                                   name="dh_maintenance_options[enabled]"
                                   value="1"
                                   <?php checked( $enabled ); ?>>
                            <span class="dh-toggle-slider"></span>
                        </label>
                        <span class="dh-toggle-label">
                            <?php esc_html_e( 'Enable maintenance mode', 'dh-maintenance' ); ?>
                        </span>
                    </td>
                </tr>

                <!-- Bypass Roles -->
                <tr>
                    <th scope="row"><?php esc_html_e( 'Bypass Roles', 'dh-maintenance' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Bypass Roles', 'dh-maintenance' ); ?></legend>
                            <?php foreach ( wp_roles()->roles as $role_slug => $role_data ) :
                                $is_admin = ( 'administrator' === $role_slug );
                                $checked  = $is_admin || in_array( $role_slug, $bypass_roles, true );
                            ?>
                            <label class="dh-role-label">
                                <input type="checkbox"
                                       name="dh_maintenance_options[bypass_roles][]"
                                       value="<?php echo esc_attr( $role_slug ); ?>"
                                       <?php checked( $checked ); ?>
                                       <?php disabled( $is_admin ); ?>>
                                <?php echo esc_html( translate_user_role( $role_data['name'] ) ); ?>
                            </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'Users with a checked role will see the live site instead of the maintenance page. Administrators always bypass.', 'dh-maintenance' ); ?></p>
                    </td>
                </tr>

                <!-- Logo -->
                <tr>
                    <th scope="row"><?php esc_html_e( 'Logo', 'dh-maintenance' ); ?></th>
                    <td>
                        <div class="dh-logo-preview">
                            <?php if ( $logo_url ) : ?>
                                <img src="<?php echo esc_url( $logo_url ); ?>" id="dh-logo-preview-img" alt="logo preview">
                            <?php else : ?>
                                <img src="" id="dh-logo-preview-img" alt="logo preview" style="display:none;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden"
                               name="dh_maintenance_options[logo_url]"
                               id="dh-logo-url"
                               value="<?php echo esc_attr( $logo_url ); ?>">
                        <button type="button" class="button" id="dh-upload-logo">
                            <?php esc_html_e( 'Upload / Choose Logo', 'dh-maintenance' ); ?>
                        </button>
                        <?php if ( $logo_url ) : ?>
                            <button type="button" class="button" id="dh-remove-logo">
                                <?php esc_html_e( 'Remove Logo', 'dh-maintenance' ); ?>
                            </button>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e( 'Recommended: PNG or SVG with transparent background.', 'dh-maintenance' ); ?></p>
                    </td>
                </tr>

                <!-- Title -->
                <tr>
                    <th scope="row">
                        <label for="dh-title"><?php esc_html_e( 'Title', 'dh-maintenance' ); ?></label>
                    </th>
                    <td>
                        <input type="text"
                               name="dh_maintenance_options[title]"
                               id="dh-title"
                               class="large-text"
                               value="<?php echo esc_attr( $title ); ?>">
                    </td>
                </tr>

                <!-- Content -->
                <tr>
                    <th scope="row">
                        <label for="dh-content"><?php esc_html_e( 'Message', 'dh-maintenance' ); ?></label>
                    </th>
                    <td>
                        <?php
                        wp_editor(
                            $content,
                            'dh_maintenance_content',
                            array(
                                'textarea_name' => 'dh_maintenance_options[content]',
                                'textarea_rows' => 8,
                                'media_buttons' => false,
                            )
                        );
                        ?>
                    </td>
                </tr>

                <!-- Background colour -->
                <tr>
                    <th scope="row">
                        <label for="dh-bg-color"><?php esc_html_e( 'Background Color', 'dh-maintenance' ); ?></label>
                    </th>
                    <td>
                        <input type="color"
                               name="dh_maintenance_options[bg_color]"
                               id="dh-bg-color"
                               value="<?php echo esc_attr( $bg_color ); ?>">
                    </td>
                </tr>

                <!-- Text colour -->
                <tr>
                    <th scope="row">
                        <label for="dh-text-color"><?php esc_html_e( 'Text Color', 'dh-maintenance' ); ?></label>
                    </th>
                    <td>
                        <input type="color"
                               name="dh_maintenance_options[text_color]"
                               id="dh-text-color"
                               value="<?php echo esc_attr( $text_color ); ?>">
                    </td>
                </tr>

            </table>

            <?php submit_button( __( 'Save Settings', 'dh-maintenance' ) ); ?>
        </form>

        <!-- Live preview -->
        <div class="dh-preview-section">
            <h2><?php esc_html_e( 'Live Preview', 'dh-maintenance' ); ?></h2>
            <div class="dh-preview-frame-wrap">
                <iframe id="dh-preview-frame"
                        src="<?php echo esc_url( home_url( '/?dh_preview=1' ) ); ?>"
                        title="<?php esc_attr_e( 'Maintenance page preview', 'dh-maintenance' ); ?>">
                </iframe>
            </div>
        </div>
    </div>
    <?php
}

/* -----------------------------------------------------------------------
 * Preview: allow admins to see the maintenance page while logged in
 * --------------------------------------------------------------------- */
add_action( 'template_redirect', 'dh_maintenance_preview', 0 );

function dh_maintenance_preview() {
    if ( ! isset( $_GET['dh_preview'] ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    include DH_MAINTENANCE_DIR . 'templates/maintenance.php';
    exit;
}
