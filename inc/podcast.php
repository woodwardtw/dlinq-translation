<?php
/**
 * Podcast feed for translations.
 *
 * Feed:        /feed/podcast/  (also /category/<slug>/feed/podcast/, /tag/<slug>/feed/podcast/)
 * Transcripts: /?dlinq_transcript=<post_id>&dlinq_variant=<original|translation|bilingual>
 *
 * Each published translation with an audio file becomes an episode. When a VTT
 * file is attached, the feed advertises Podcasting 2.0 transcripts in the
 * original language, the translation language, and both combined. The
 * translated/bilingual VTTs reuse the original cue timings: cue N maps to
 * line N of the text fields, the same mapping vtt-player.js uses on the page.
 *
 * @package dlinq-translation
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

define( 'DLINQ_PODCAST_FEED', 'podcast' );

// Podcasting 2.0 namespace GUID used to derive podcast:guid (UUIDv5).
define( 'DLINQ_PODCAST_GUID_NAMESPACE', 'ead4c236-bf58-58c6-a2c6-a6b28d128cb6' );

/*
 * Feed registration.
 * Runs before priority 0 because create_translation_cpt() flushes rewrite
 * rules at init:0, and the feed name must be known by then.
 */
add_action( 'init', 'dlinq_podcast_register_feed', -1 );
function dlinq_podcast_register_feed() {
	add_feed( DLINQ_PODCAST_FEED, 'dlinq_podcast_render_feed' );
}

// Limit podcast feeds to translations that have audio.
add_action( 'pre_get_posts', 'dlinq_podcast_feed_query' );
function dlinq_podcast_feed_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_feed( DLINQ_PODCAST_FEED ) || $query->is_comment_feed() ) {
		return;
	}

	$query->set( 'post_type', 'translation' );
	$query->set( 'post_status', 'publish' );
	$query->set( 'posts_per_page', 500 );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );
	$query->set( 'ignore_sticky_posts', true );
	$query->set(
		'meta_query',
		array(
			array(
				'key'     => 'audio_file',
				'value'   => '',
				'compare' => '!=',
			),
		)
	);
}

// Advertise the feed so podcast apps and browsers can discover it.
add_action( 'wp_head', 'dlinq_podcast_feed_link' );
function dlinq_podcast_feed_link() {
	printf(
		'<link rel="alternate" type="application/rss+xml" title="%s" href="%s">' . "\n",
		esc_attr( dlinq_podcast_setting( 'title' ) ),
		esc_url( get_feed_link( DLINQ_PODCAST_FEED ) )
	);
}

/*
 * Show-level settings (Appearance > Customize > Podcast).
 */
function dlinq_podcast_setting( $key ) {
	$defaults = array(
		'title'                => get_bloginfo( 'name' ),
		'description'          => get_bloginfo( 'description' ),
		'author'               => get_bloginfo( 'name' ),
		'owner_name'           => get_bloginfo( 'name' ),
		'owner_email'          => '',
		'cover'                => 0,
		'category'             => 'Society & Culture > Documentary',
		'explicit'             => false,
		'original_language'    => '',
		'translation_language' => 'en',
	);

	$value = get_theme_mod( 'dlinq_podcast_' . $key, $defaults[ $key ] );

	// Empty text settings fall back to the default rather than going blank.
	if ( '' === $value && '' !== $defaults[ $key ] ) {
		$value = $defaults[ $key ];
	}

	return $value;
}

add_action( 'customize_register', 'dlinq_podcast_customize_register' );
function dlinq_podcast_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'dlinq_podcast',
		array(
			'title'       => 'Podcast',
			'priority'    => 160,
			'description' => sprintf(
				'Settings for the translations podcast feed: <a href="%1$s" target="_blank">%1$s</a>. Episodes are published translations with an audio file.',
				esc_url( get_feed_link( DLINQ_PODCAST_FEED ) )
			),
		)
	);

	$text_fields = array(
		'title'                => array( 'Podcast title', 'Defaults to the site title.', 'sanitize_text_field' ),
		'description'          => array( 'Podcast description', 'Defaults to the site tagline.', 'sanitize_textarea_field' ),
		'author'               => array( 'Author', 'Shown as the podcast creator in apps.', 'sanitize_text_field' ),
		'owner_name'           => array( 'Owner name', 'Used by directories (Apple, etc.) for ownership verification.', 'sanitize_text_field' ),
		'owner_email'          => array( 'Owner email', 'Published in the feed. Directories send verification emails here.', 'sanitize_email' ),
		'category'             => array( 'Apple Podcasts category', 'Use "Category > Subcategory", e.g. "Society & Culture > Documentary".', 'sanitize_text_field' ),
		'original_language'    => array( 'Default original language', 'Language code (e.g. "fr") used when a translation has no "lang" custom field.', 'sanitize_text_field' ),
		'translation_language' => array( 'Translation language', 'Language code of the translated text, e.g. "en".', 'sanitize_text_field' ),
	);

	foreach ( $text_fields as $key => $field ) {
		$wp_customize->add_setting(
			'dlinq_podcast_' . $key,
			array(
				'default'           => '',
				'sanitize_callback' => $field[2],
			)
		);
		$wp_customize->add_control(
			'dlinq_podcast_' . $key,
			array(
				'label'       => $field[0],
				'description' => $field[1],
				'section'     => 'dlinq_podcast',
				'type'        => 'description' === $key ? 'textarea' : ( 'owner_email' === $key ? 'email' : 'text' ),
			)
		);
	}

	$wp_customize->add_setting(
		'dlinq_podcast_cover',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'dlinq_podcast_cover',
			array(
				'label'       => 'Cover art',
				'description' => 'Square JPG or PNG, 1400–3000px (required by Apple Podcasts).',
				'section'     => 'dlinq_podcast',
				'mime_type'   => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'dlinq_podcast_explicit',
		array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'dlinq_podcast_explicit',
		array(
			'label'   => 'Contains explicit content',
			'section' => 'dlinq_podcast',
			'type'    => 'checkbox',
		)
	);
}

/*
 * Data helpers.
 */

/**
 * Text lines for a translation text field, counted the same way
 * dlinq_translation() numbers data-line on the page (bare "\r" lines skipped).
 * Returned lines are plain text.
 */
function dlinq_podcast_text_lines( $field, $post_id ) {
	$content = (string) get_field( $field, $post_id );
	$content = str_replace( array( '<br>', '<br/>', '<br />' ), "\n", $content );

	$lines = array();
	foreach ( explode( "\n", $content ) as $line ) {
		if ( "\r" === $line ) {
			continue;
		}
		$lines[] = trim( html_entity_decode( wp_strip_all_tags( $line ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	}

	return $lines;
}

function dlinq_podcast_original_language( $post_id ) {
	$lang = get_post_meta( $post_id, 'lang', true );
	return $lang ? $lang : dlinq_podcast_setting( 'original_language' );
}

function dlinq_podcast_vtt_path( $post_id ) {
	$attachment_id = absint( get_post_meta( $post_id, 'vtt_file', true ) );
	$path          = $attachment_id ? get_attached_file( $attachment_id ) : false;
	return $path && file_exists( $path ) ? $path : false;
}

/**
 * Transcript URL. The version changes whenever the VTT file or the post's
 * text changes, so podcast apps and caches pick up edits.
 */
function dlinq_podcast_transcript_url( $post_id, $variant ) {
	$path    = dlinq_podcast_vtt_path( $post_id );
	$version = ( $path ? filemtime( $path ) : 0 ) . '-' . get_post_modified_time( 'U', true, $post_id );

	return add_query_arg(
		array(
			'dlinq_transcript' => $post_id,
			'dlinq_variant'    => $variant,
			'v'                => $version,
		),
		home_url( '/' )
	);
}

// Podcasting 2.0 person roles, keyed by lowercase role => group.
function dlinq_podcast_person_roles() {
	return array(
		'host'        => 'cast',
		'co-host'     => 'cast',
		'guest host'  => 'cast',
		'guest'       => 'cast',
		'narrator'    => 'cast',
		'announcer'   => 'cast',
		'voice actor' => 'cast',
		'reporter'    => 'cast',
		'writer'      => 'writing',
		'translator'  => 'writing',
		'transcriber' => 'writing',
		'researcher'  => 'writing',
		'editor'      => 'writing',
	);
}

/**
 * Participants as podcast:person entries. A participant's tags that match a
 * Podcasting 2.0 role become their roles; otherwise they are listed as a guest.
 */
function dlinq_podcast_people( $post_id ) {
	$speakers = get_field( 'speaker', $post_id );
	if ( ! $speakers ) {
		return array();
	}

	$known  = dlinq_podcast_person_roles();
	$people = array();

	foreach ( $speakers as $speaker ) {
		$photo = get_field( 'bio_photo', $speaker->ID );
		$tags  = get_the_terms( $speaker->ID, 'post_tag' );
		$tags  = ( $tags && ! is_wp_error( $tags ) ) ? wp_list_pluck( $tags, 'name' ) : array();

		$roles = array();
		foreach ( $tags as $tag ) {
			$role = strtolower( trim( $tag ) );
			if ( isset( $known[ $role ] ) ) {
				$roles[ $role ] = $known[ $role ];
			}
		}
		if ( ! $roles ) {
			$roles = array( 'guest' => 'cast' );
		}

		$people[] = array(
			'name'  => get_the_title( $speaker->ID ),
			'href'  => get_permalink( $speaker->ID ),
			'img'   => is_array( $photo ) ? ( $photo['url'] ?? '' ) : '',
			'tags'  => $tags,
			'roles' => $roles,
		);
	}

	return $people;
}

// First Location post linked to this translation via its text_link field.
function dlinq_podcast_location( $post_id ) {
	$locations = get_posts(
		array(
			'post_type'      => 'location',
			'posts_per_page' => 1,
			'meta_key'       => 'text_link', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => $post_id,    // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	if ( ! $locations ) {
		return null;
	}

	$location = $locations[0];
	$lat      = get_field( 'latitude', $location->ID );
	$lng      = get_field( 'longitude', $location->ID );
	$name     = get_field( 'english_name', $location->ID );

	return array(
		'name' => $name ? $name : get_the_title( $location->ID ),
		'geo'  => ( '' !== (string) $lat && '' !== (string) $lng ) ? 'geo:' . (float) $lat . ',' . (float) $lng : '',
	);
}

// HTML show notes: link to the interactive page, participants, paired text, glossary, trajectory.
function dlinq_podcast_show_notes( $post_id, $people ) {
	$html = sprintf(
		'<p>Read along with the interactive side-by-side text: <a href="%s">%s</a></p>',
		esc_url( get_permalink( $post_id ) ),
		esc_html( get_the_title( $post_id ) )
	);

	if ( $people ) {
		$html .= '<h3>' . ( count( $people ) === 1 ? 'Participant' : 'Participants' ) . '</h3><ul>';
		foreach ( $people as $person ) {
			$html .= sprintf( '<li><a href="%s">%s</a>', esc_url( $person['href'] ), esc_html( $person['name'] ) );
			if ( $person['tags'] ) {
				$html .= ' (' . esc_html( implode( ', ', $person['tags'] ) ) . ')';
			}
			$html .= '</li>';
		}
		$html .= '</ul>';
	}

	$original    = dlinq_podcast_text_lines( 'original_text', $post_id );
	$translation = dlinq_podcast_text_lines( 'translation', $post_id );
	$count       = max( count( $original ), count( $translation ) );
	if ( $count ) {
		$html .= '<h3>Text</h3>';
		for ( $i = 0; $i < $count; $i++ ) {
			$orig  = $original[ $i ] ?? '';
			$trans = $translation[ $i ] ?? '';
			if ( '' === $orig && '' === $trans ) {
				continue;
			}
			$html .= '<p>' . esc_html( $orig );
			if ( '' !== $trans ) {
				$html .= '<br><em>' . esc_html( $trans ) . '</em>';
			}
			$html .= '</p>';
		}
	}

	if ( have_rows( 'highlight_words', $post_id ) ) {
		$html .= '<h3>Glossary</h3><ul>';
		while ( have_rows( 'highlight_words', $post_id ) ) {
			the_row();
			$html .= sprintf(
				'<li><strong>%s</strong> — %s</li>',
				esc_html( get_sub_field( 'word' ) ),
				esc_html( get_sub_field( 'meaning' ) )
			);
		}
		$html .= '</ul>';
	}

	$trajectory = get_field( 'text_trajectory', $post_id );
	if ( $trajectory ) {
		$html .= '<h3>Text Trajectory</h3>' . wp_kses_post( $trajectory );
	}

	return $html;
}

// Plain-text episode summary.
function dlinq_podcast_summary( $post_id ) {
	$speakers   = dlinq_translation_speaker_primary( $post_id );
	$trajectory = wp_strip_all_tags( (string) get_field( 'text_trajectory', $post_id ) );
	$body       = $trajectory ? $trajectory : implode( ' ', dlinq_podcast_text_lines( 'translation', $post_id ) );

	return trim( ( $speakers ? $speakers . '. ' : '' ) . wp_trim_words( $body, 55 ) );
}

function dlinq_podcast_uuid5( $namespace, $name ) {
	$hash = sha1( hex2bin( str_replace( '-', '', $namespace ) ) . $name );

	return sprintf(
		'%s-%s-%04x-%04x-%s',
		substr( $hash, 0, 8 ),
		substr( $hash, 8, 4 ),
		( hexdec( substr( $hash, 12, 4 ) ) & 0x0fff ) | 0x5000,
		( hexdec( substr( $hash, 16, 4 ) ) & 0x3fff ) | 0x8000,
		substr( $hash, 20, 12 )
	);
}

function dlinq_podcast_cdata( $html ) {
	return '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $html ) . ']]>';
}

/*
 * Feed output.
 */
function dlinq_podcast_render_feed() {
	header( 'Content-Type: application/rss+xml; charset=' . get_option( 'blog_charset' ), true );

	$self_url    = get_self_link();
	$cover_id    = absint( dlinq_podcast_setting( 'cover' ) );
	$cover_url   = $cover_id ? wp_get_attachment_url( $cover_id ) : '';
	$explicit    = dlinq_podcast_setting( 'explicit' ) ? 'true' : 'false';
	$owner_email = dlinq_podcast_setting( 'owner_email' );
	$category    = array_map( 'trim', explode( '>', dlinq_podcast_setting( 'category' ) ) );
	$feed_lang   = dlinq_podcast_setting( 'translation_language' );
	$feed_guid   = dlinq_podcast_uuid5( DLINQ_PODCAST_GUID_NAMESPACE, untrailingslashit( preg_replace( '#^[a-z]+://#i', '', $self_url ) ) );

	echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . '"?>' . "\n";
	?>
<rss version="2.0"
	xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
	xmlns:podcast="https://podcastindex.org/namespace/1.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
	<title><?php echo esc_xml( dlinq_podcast_setting( 'title' ) ); ?></title>
	<link><?php echo esc_url( home_url( '/' ) ); ?></link>
	<atom:link href="<?php echo esc_url( $self_url ); ?>" rel="self" type="application/rss+xml" />
	<?php // Directories require a non-empty description; the site tagline is often blank. ?>
	<description><?php echo esc_xml( dlinq_podcast_setting( 'description' ) ? dlinq_podcast_setting( 'description' ) : dlinq_podcast_setting( 'title' ) ); ?></description>
	<language><?php echo esc_xml( $feed_lang ); ?></language>
	<lastBuildDate><?php echo esc_xml( get_feed_build_date( 'r' ) ); ?></lastBuildDate>
	<generator>https://wordpress.org/?v=<?php echo esc_xml( get_bloginfo( 'version' ) ); ?></generator>
	<podcast:guid><?php echo esc_xml( $feed_guid ); ?></podcast:guid>
	<itunes:author><?php echo esc_xml( dlinq_podcast_setting( 'author' ) ); ?></itunes:author>
	<itunes:type>episodic</itunes:type>
	<itunes:explicit><?php echo esc_xml( $explicit ); ?></itunes:explicit>
	<itunes:owner>
		<itunes:name><?php echo esc_xml( dlinq_podcast_setting( 'owner_name' ) ); ?></itunes:name>
		<?php if ( $owner_email ) : ?>
		<itunes:email><?php echo esc_xml( $owner_email ); ?></itunes:email>
		<?php endif; ?>
	</itunes:owner>
	<?php if ( $category[0] ) : ?>
	<itunes:category text="<?php echo esc_attr( $category[0] ); ?>">
		<?php if ( ! empty( $category[1] ) ) : ?>
		<itunes:category text="<?php echo esc_attr( $category[1] ); ?>" />
		<?php endif; ?>
	</itunes:category>
	<?php endif; ?>
	<?php if ( $cover_url ) : ?>
	<itunes:image href="<?php echo esc_url( $cover_url ); ?>" />
	<image>
		<url><?php echo esc_url( $cover_url ); ?></url>
		<title><?php echo esc_xml( dlinq_podcast_setting( 'title' ) ); ?></title>
		<link><?php echo esc_url( home_url( '/' ) ); ?></link>
	</image>
	<?php endif; ?>
	<?php
	while ( have_posts() ) :
		the_post();
		dlinq_podcast_render_item( get_the_ID(), $explicit );
	endwhile;
	?>
</channel>
</rss>
	<?php
}

function dlinq_podcast_render_item( $post_id, $explicit ) {
	$audio_id   = absint( get_post_meta( $post_id, 'audio_file', true ) );
	$audio_url  = $audio_id ? wp_get_attachment_url( $audio_id ) : '';
	$audio_path = $audio_id ? get_attached_file( $audio_id ) : '';
	if ( ! $audio_url ) {
		return;
	}

	$audio_meta = wp_get_attachment_metadata( $audio_id );
	$duration   = ! empty( $audio_meta['length'] ) ? (int) $audio_meta['length'] : 0;
	$filesize   = ! empty( $audio_meta['filesize'] ) ? (int) $audio_meta['filesize'] : ( $audio_path && file_exists( $audio_path ) ? filesize( $audio_path ) : 0 );
	$mime       = get_post_mime_type( $audio_id );
	$image      = get_the_post_thumbnail_url( $post_id, 'full' );
	$people     = dlinq_podcast_people( $post_id );
	$location   = dlinq_podcast_location( $post_id );
	$has_vtt    = (bool) dlinq_podcast_vtt_path( $post_id );
	$orig_lang  = dlinq_podcast_original_language( $post_id );
	$trans_lang = dlinq_podcast_setting( 'translation_language' );

	$transcripts = array();
	if ( $has_vtt ) {
		$transcripts = array(
			'original'    => $orig_lang,
			'translation' => $trans_lang,
			'bilingual'   => $orig_lang && $trans_lang ? $orig_lang . ',' . $trans_lang : '',
		);
	}
	?>
	<item>
		<title><?php echo esc_xml( get_the_title( $post_id ) ); ?></title>
		<link><?php echo esc_url( get_permalink( $post_id ) ); ?></link>
		<guid isPermaLink="false"><?php echo esc_xml( get_the_guid( $post_id ) ); ?></guid>
		<pubDate><?php echo esc_xml( get_post_time( 'r', true, $post_id ) ); ?></pubDate>
		<description><?php echo esc_xml( dlinq_podcast_summary( $post_id ) ); ?></description>
		<content:encoded><?php echo dlinq_podcast_cdata( dlinq_podcast_show_notes( $post_id, $people ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped while built. ?></content:encoded>
		<enclosure url="<?php echo esc_url( $audio_url ); ?>" length="<?php echo (int) $filesize; ?>" type="<?php echo esc_attr( $mime ); ?>" />
		<itunes:episodeType>full</itunes:episodeType>
		<itunes:explicit><?php echo esc_xml( $explicit ); ?></itunes:explicit>
		<?php if ( $duration ) : ?>
		<itunes:duration><?php echo (int) $duration; ?></itunes:duration>
		<?php endif; ?>
		<?php if ( $image ) : ?>
		<itunes:image href="<?php echo esc_url( $image ); ?>" />
		<?php endif; ?>
		<?php foreach ( $transcripts as $variant => $lang ) : ?>
		<podcast:transcript url="<?php echo esc_url( dlinq_podcast_transcript_url( $post_id, $variant ) ); ?>" type="text/vtt"<?php echo $lang ? ' language="' . esc_attr( $lang ) . '"' : ''; ?><?php echo 'original' === $variant ? ' rel="captions"' : ''; ?> />
		<?php endforeach; ?>
		<?php foreach ( $people as $person ) : ?>
			<?php foreach ( $person['roles'] as $role => $group ) : ?>
		<podcast:person role="<?php echo esc_attr( $role ); ?>" group="<?php echo esc_attr( $group ); ?>" href="<?php echo esc_url( $person['href'] ); ?>"<?php echo $person['img'] ? ' img="' . esc_url( $person['img'] ) . '"' : ''; ?>><?php echo esc_xml( $person['name'] ); ?></podcast:person>
			<?php endforeach; ?>
		<?php endforeach; ?>
		<?php if ( $location ) : ?>
		<podcast:location<?php echo $location['geo'] ? ' geo="' . esc_attr( $location['geo'] ) . '"' : ''; ?>><?php echo esc_xml( $location['name'] ); ?></podcast:location>
		<?php endif; ?>
	</item>
	<?php
}

/*
 * Transcript endpoint.
 */
add_filter( 'query_vars', 'dlinq_podcast_query_vars' );
function dlinq_podcast_query_vars( $vars ) {
	$vars[] = 'dlinq_transcript';
	$vars[] = 'dlinq_variant';
	return $vars;
}

// Runs before redirect_canonical (priority 10).
add_action( 'template_redirect', 'dlinq_podcast_serve_transcript', 1 );
function dlinq_podcast_serve_transcript() {
	$post_id = absint( get_query_var( 'dlinq_transcript' ) );
	if ( ! $post_id ) {
		return;
	}

	$variant = get_query_var( 'dlinq_variant' );
	if ( ! in_array( $variant, array( 'original', 'translation', 'bilingual' ), true ) ) {
		$variant = 'original';
	}

	$post = get_post( $post_id );
	$path = ( $post && 'translation' === $post->post_type && 'publish' === $post->post_status ) ? dlinq_podcast_vtt_path( $post_id ) : false;

	if ( ! $path ) {
		status_header( 404 );
		nocache_headers();
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo 'Transcript not found.';
		exit;
	}

	$vtt = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$vtt = preg_replace( '/^\xEF\xBB\xBF/', '', str_replace( array( "\r\n", "\r" ), "\n", (string) $vtt ) );

	if ( 'translation' === $variant ) {
		$vtt = dlinq_podcast_build_vtt( $vtt, array( dlinq_podcast_text_lines( 'translation', $post_id ) ) );
	} elseif ( 'bilingual' === $variant ) {
		$vtt = dlinq_podcast_build_vtt(
			$vtt,
			array(
				dlinq_podcast_text_lines( 'original_text', $post_id ),
				dlinq_podcast_text_lines( 'translation', $post_id ),
			)
		);
	}

	status_header( 200 );
	header( 'Content-Type: text/vtt; charset=utf-8' );
	// Web-based podcast players fetch transcripts cross-origin.
	header( 'Access-Control-Allow-Origin: *' );
	header( 'Cache-Control: public, max-age=3600' );
	header( 'Content-Disposition: inline; filename="' . sanitize_file_name( $post->post_name . '-' . $variant . '.vtt' ) . '"' );
	echo $vtt; // phpcs:ignore WordPress.Security.EscapeOutput -- VTT payload, cue text escaped in dlinq_podcast_build_vtt().
	exit;
}

/**
 * Rebuild a VTT with cue text taken from the text fields, keeping the original
 * cue ids, timings and settings. Blocks are split and cues counted the same
 * way as parseVTT() in vtt-player.js, so cue N gets line N of each field in
 * $line_sets (one cue text line per field) — the same pairing the page shows.
 */
function dlinq_podcast_build_vtt( $vtt, $line_sets ) {
	$out   = array( 'WEBVTT' );
	$index = 0;

	foreach ( preg_split( "/\n{2,}/", trim( $vtt ) ) as $block ) {
		$lines  = explode( "\n", trim( $block ) );
		$timing = null;
		foreach ( $lines as $i => $line ) {
			if ( false !== strpos( $line, '-->' ) ) {
				$timing = $i;
				break;
			}
		}
		if ( null === $timing ) {
			continue;
		}

		$text = array();
		foreach ( $line_sets as $set ) {
			if ( isset( $set[ $index ] ) && '' !== $set[ $index ] ) {
				$text[] = dlinq_podcast_vtt_escape( $set[ $index ] );
			}
		}
		$index++;

		if ( ! $text ) {
			continue;
		}

		$out[] = implode( "\n", array_merge( array_slice( $lines, 0, $timing + 1 ), $text ) );
	}

	return implode( "\n\n", $out ) . "\n";
}

function dlinq_podcast_vtt_escape( $text ) {
	return str_replace( array( '&', '<', '>' ), array( '&amp;', '&lt;', '&gt;' ), $text );
}
