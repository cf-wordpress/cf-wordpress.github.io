<?php
/**
 * Admin settings page template
 *
 * @package Rhubarb\RedisCache
 */

namespace Rhubarb\RedisCache\UI;

use Rhubarb\RedisCache\UI;

defined( '\\ABSPATH' ) || exit;

?>
<div id="rediscache" class="wrap">

    <h1>
        <?php esc_html_e( 'Redis Object Cache', 'redis-cache' ); ?>
    </h1>

    <?php is_network_admin() && settings_errors(); ?>

    <div class="columns">

        <div class="content-column">

            <h2 class="nav-tab-wrapper">
                <?php foreach ( UI::get_tabs() as $ui_tab ) : ?>
                    <a class="nav-tab <?php echo $ui_tab->default ? 'nav-tab-active' : ''; ?>"
                        id="<?php echo esc_attr( $ui_tab->slug ); ?>-tab"
                        data-toggle="<?php echo esc_attr( $ui_tab->slug ); ?>"
                        href="<?php echo esc_attr( $ui_tab->slug ); ?>"
                    >
                        <?php echo esc_html( $ui_tab->label ); ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <div class="tab-content">
                <?php foreach ( UI::get_tabs() as $ui_tab ) : ?>
                    <div id="<?php echo esc_attr( $ui_tab->slug ); ?>-pane"
                        class="tab-pane tab-pane-<?php echo esc_attr( $ui_tab->slug ); ?> <?php echo $ui_tab->default ? 'active' : ''; ?>"
                    >
                        <?php include $ui_tab->file; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <div class="sidebar-column">

            <h6>
                <?php esc_html_e( 'Resources', 'redis-cache' ); ?>
            </h6>

            <div class="section-pro">

                <div class="card">
                    <h2 class="title">
                        Redis Cache Pro
                    </h2>
                    <p>
                        <b>A business class object cache backend.</b> Truly reliable, highly-optimized and fully customizable, with a <u>dedicated engineer</u> when you most need it.
                    </p>
                    <ul>
                        <li>Rewritten for raw performance</li>
                        <li>100% WordPress API compliant</li>
                        <li>Faster serialization and compression</li>
                        <li>Easy debugging &amp; logging</li>
                        <li>Cache analytics and preloading</li>
                        <li>Fully unit tested (100% code coverage)</li>
                        <li>Secure connections with TLS</li>
                        <li>Health checks via WordPress &amp; WP CLI</li>
                        <li>Optimized for WooCommerce, Jetpack &amp; Yoast SEO</li>
                    </ul>
                    <p>
                        <a class="button button-primary" target="_blank" rel="noopener" href="https://wprediscache.com/?utm_source=wp-plugin&amp;utm_medium=settings">
                            <?php esc_html_e( 'Learn more', 'redis-cache' ); ?>
                        </a>
                    </p>
                </div>

                <?php $is_php7 = version_compare( phpversion(), '7.0', '>=' ); ?>
                <?php $is_phpredis311 = version_compare( phpversion( 'redis' ), '3.1.1', '>=' ); ?>
                <?php $phpredis_installed = (bool) phpversion( 'redis' ); ?>

                <?php if ( $is_php7 && $is_phpredis311 ) : ?>

                    <p class="compatiblity">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php esc_html_e( 'Your site meets the system requirements for the Pro version.', 'redis-cache' ); ?></span>
                    </p>

                <?php else : ?>

                    <p class="compatiblity">
                        <span class="dashicons dashicons-no"></span>
                        <span><?php echo wp_kses_post( __( 'Your site <i>does not</i> meet the requirements for the Pro version:', 'redis-cache' ) ); ?></span>
                    </p>

                    <ul>
                        <?php if ( ! $is_php7 ) : ?>
                            <li>
                                <?php
                                    printf(
                                        // translators: %s = PHP Version.
                                        esc_html__( 'The current version of PHP (%s) is too old. PHP 7.0 or newer is required.', 'redis-cache' ),
                                        esc_html( phpversion() )
                                    );
                                ?>
                            </li>
                        <?php endif; ?>

                        <?php if ( ! $phpredis_installed ) : ?>
                            <li>
                                <?php esc_html_e( 'The PhpRedis extension is not installed.', 'redis-cache' ); ?>
                            </li>
                        <?php elseif ( ! $is_phpredis311 ) : ?>
                            <li>
                                <?php
                                    printf(
                                        // translators: %s = Version of the PhpRedis extension.
                                        esc_html__( 'The current version of the PhpRedis extension (%s) is too old. PhpRedis 3.1.1 or newer is required.', 'redis-cache' ),
                                        esc_html( phpversion( 'redis' ) )
                                    );
                                ?>
                            </li>
                        <?php endif; ?>
                    </ul>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>
