<div class="notice notice-warning fade is-dismissible">
	<p>
    <strong><?php _e('XML Sitemap & Google News','xml-sitemap-feed'); ?></strong>
  </p>
  <p>
		<?php printf( /* translators: Conflicting Plugn name, Plugin name */
			__( 'The %1$s XML Sitemap is not compatible with %2$s.', 'xml-sitemap-feed'),
			translate('SEOPress','wp-seopress'),
			__('XML Sitemap & Google News','xml-sitemap-feed')
		); ?>
		<?php printf( /* translators: Sitemap page name (linked to SEOPress plugin settings), XML Sitemap Index, Reading Settings admin page (linked to Reading settings) */
			__( 'Please either disable the XML Sitemap under %1$s in your SEO settings or disable the option %2$s on %3$s.', 'xml-sitemap-feed'),
			'<a href="' . admin_url('admin.php') . '?page=seopress-xml-sitemap">' . translate('XML / HTML Sitemap','wp-seopress') . '</a>',
			__('XML Sitemap Index','xml-sitemap-feed'),
			'<a href="' . admin_url('options-reading.php') . '#xmlsf_sitemaps">' . translate('Reading Settings') . '</a>'
		); ?>
  </p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME.'-notice', '_xmlsf_notice_nonce' ); ?>
		<p>
			<input type="hidden" name="xmlsf-dismiss" value="seopress_sitemap" />
			<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo translate('Dismiss'); ?>" />
		</p>
	</form>
</div>
