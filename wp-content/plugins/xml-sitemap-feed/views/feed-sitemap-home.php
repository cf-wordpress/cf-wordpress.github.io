<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

if ( ! defined( 'WPINC' ) ) die;

// do xml tag via echo or SVN parser is going to freak out
echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . '"?>
<?xml-stylesheet type="text/xsl" href="' . wp_make_link_relative( plugins_url('assets/styles/sitemap.xsl',XMLSF_BASENAME) ) . '?ver=' . XMLSF_VERSION . '"?>
'; ?>
<?php xmlsf_generator(); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
<?php do_action('xmlsf_urlset', 'home'); ?>
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
		http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php
foreach ( xmlsf_get_root_data() as $url => $data ) {
?>
	<url>
		<loc><?php echo esc_url( $url ); ?></loc>
		<priority><?php echo $data['priority']; ?></priority>
		<lastmod><?php echo $data['lastmod']; ?></lastmod>
<?php do_action( 'xmlsf_tags_after', 'home' ); ?>
	</url>
<?php
	do_action( 'xmlsf_url_after', 'home' );
}
?>
</urlset>
<?php xmlsf_usage(); ?>
