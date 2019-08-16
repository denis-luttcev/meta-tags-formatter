<?php
/**
 * Plugin Name: Meta Tags Formatter
 * Description: Add Title, Description, Keywords and Open Graph meta-tags into HEAD (for articles).
 * Author: Denis Luttcev
 * Version:     1.0.0
 * Text Domain: meta-tag-formatter
 */

// Need add namespace attributes into <html> tag in header.php of theme:
// (itemscope itemtype="https://schema.org/Article" prefix="article: https://ogp.me/ns/article# og: https://ogp.me/ns# fb: https://ogp.me/ns/fb#")
// and add (itemprop="name" ) into <h1> tag in content-single.php of theme.
// define('FB_ADMINS', '100003904247343'); // Use actual FB-page ID

// h1 formatter
function h1_formatter($title) {
	if ( is_single() ) {
		return '<span itemprop="headline">'.$title.'</span>';
	}
	return $title;
}
add_filter ( 'the_title', 'h1_formatter' );
// Title formatter (from h1)
add_filter( 'document_title_separator', function(){ return '|'; } );
function title_formatter($title) {
	if ( is_single() && has_secondary_title() ) {
		$secondary_title = html_entity_decode(stripslashes(get_secondary_title()));
		return $secondary_title.' | '.get_bloginfo();
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'title_formatter' );
add_filter( 'pre_get_document_title', 'typoFilterHeader' );
function og_title_formatter($title) {
	if ( is_single() ) {
		$title = str_replace('<span itemprop="headline">', '', $title);
		$title = str_replace('</span>', '', $title);
		if ( has_secondary_title() ) {
			return substr(str_replace("<span class='unvisible'> | <span itemprop='alternativeHeadline' class='subhead'>", ' | ', $title), 0);
		}
	}
	return $title;
}
// Meta-tags formatter
function my_meta_tags( ) {
	if ( is_single() ) {
		if (have_posts()) : while (have_posts()) : the_post();
			// Description from Acticle Lead
			$description = wp_trim_excerpt();
			$description = substr( $description, 26, strpos( $description, '</p>' ) - 26);
			if ($description != '') {	
				echo '<meta property="og:description" name="description" content="'.$description.'" />';
				//echo '<meta property="og:description" content="'.$description.'" />';
			}
			// Keywords (Tags+Brends)
			$keywords = '';
			$posttags = get_the_tags();
			if ($posttags) {
				foreach($posttags as $tag) {
					$keywords = $keywords.', '.typoFilterHeader($tag->name);
					echo '<meta itemprop="about" property="article:tag" content="'.typoFilterHeader($tag->name).'" />';
				}
			}
			$postbrends = get_the_terms( $post->ID, 'brends' );
			if ($postbrends) {
				foreach($postbrends as $brend) {
					$keywords = $keywords.', '.typoFilterHeader($brend->name);
					echo '<meta itemprop="mentions" property="article:tag" content="'.typoFilterHeader($brend->name).'" />';
				}
			}
			if ($keywords != '') {	
				$keywords = substr( $keywords, 2 );
				echo '<meta itemprop="keywords" name="keywords" content="'.$keywords.'" />';
			}
		endwhile; endif;
		echo '<meta name="twitter:card" content="summary_large_image">';
		echo '<meta property="og:title" content="'.typoFilterHeader(og_title_formatter(get_the_title())).'" />';
		echo '<meta property="og:type" content="article" />';
		echo '<meta property="og:locale" content="'.get_locale().'" />';
		foreach ( get_the_category() as $category ) {
			foreach ( get_ancestors( $category->cat_ID, 'category' ) as $ancestor) {
				echo '<meta itemprop="articleSection" property="article:section" content="'.typoFilterHeader(get_cat_name($ancestor)).'" />';
			}
			echo '<meta itemprop="articleSection" property="article:section" content="'.typoFilterHeader($category->cat_name).'" />';
		}
		echo '<meta property="og:url" content="'.get_permalink().'" />';
		echo '<link itemprop="url" rel="bookmark" href="'.get_permalink().'" />';
		if ( get_the_post_thumbnail_url() ) {
			echo '<meta property="og:image" content="'.get_the_post_thumbnail_url($post->ID, 'full').'" />';
		}
		echo '<meta itemprop="copyrightHolder" property="og:site_name" content="'.get_bloginfo().'" />';
		//echo '<meta property="fb:admins" content="'.FB_ADMINS.'" />';
		echo '<meta itemprop="datePublished" property="article:published_time" content="'.get_post_time('Y-m-d').'" />';
		echo '<meta itemprop="dateModified" property="article:modified_time" content="'.get_the_modified_date('Y-m-d').'" />';
	}
	else {
		return;
	}
}
add_action( 'wp_head', 'my_meta_tags' );
 ?>