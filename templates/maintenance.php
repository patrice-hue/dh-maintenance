<?php
/**
 * Maintenance page template.
 * Loaded directly – no WP theme involved.
 */
defined( 'ABSPATH' ) || exit;

$options    = get_option( 'dh_maintenance_options', array() );
$logo_url   = isset( $options['logo_url'] )   ? esc_url( $options['logo_url'] )       : '';
$title      = isset( $options['title'] )      ? esc_html( $options['title'] )          : esc_html__( "We'll be back soon!", 'dh-maintenance' );
$content    = isset( $options['content'] )    ? wp_kses_post( $options['content'] )    : esc_html__( 'Our website is currently undergoing scheduled maintenance. Thank you for your patience.', 'dh-maintenance' );
$bg_color   = isset( $options['bg_color'] )   ? esc_attr( $options['bg_color'] )       : '#ffffff';
$text_color = isset( $options['text_color'] ) ? esc_attr( $options['text_color'] )     : '#333333';
$site_name  = get_bloginfo( 'name' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html( $site_name ); ?> — <?php esc_html_e( 'Maintenance', 'dh-maintenance' ); ?></title>
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: <?php echo $bg_color; ?>;
            color: <?php echo $text_color; ?>;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
            text-align: center;
            padding: 2rem;
        }

        .dh-maintenance-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 640px;
        }

        .dh-maintenance-logo {
            margin-bottom: 2.5rem;
        }

        .dh-maintenance-logo img {
            max-width: 200px;
            max-height: 120px;
            width: auto;
            height: auto;
        }

        .dh-maintenance-title {
            font-size: clamp(1.75rem, 5vw, 2.75rem);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1.25rem;
            color: <?php echo $text_color; ?>;
        }

        .dh-maintenance-content {
            font-size: clamp(1rem, 2.5vw, 1.125rem);
            line-height: 1.7;
            color: <?php echo $text_color; ?>;
            opacity: 0.85;
        }

        .dh-maintenance-content p {
            margin-bottom: 1em;
        }

        .dh-maintenance-content p:last-child {
            margin-bottom: 0;
        }

        .dh-maintenance-content a {
            color: <?php echo $text_color; ?>;
        }
    </style>
</head>
<body>
    <div class="dh-maintenance-container">

        <?php if ( $logo_url ) : ?>
            <div class="dh-maintenance-logo">
                <img src="<?php echo esc_url( $logo_url ); ?>"
                     alt="<?php echo esc_attr( $site_name ); ?>">
            </div>
        <?php endif; ?>

        <h1 class="dh-maintenance-title"><?php echo $title; ?></h1>

        <div class="dh-maintenance-content">
            <?php echo $content; ?>
        </div>

    </div>
</body>
</html>
