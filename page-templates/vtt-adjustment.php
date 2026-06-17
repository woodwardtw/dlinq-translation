<?php
/**
 * Template Name: VTT Adjustment
 *
 * Tool for adjusting VTT cue timestamps on a translation post.
 * Requires Author role or above.
 *
 * Usage: add ?id=<translation_post_id> to the page URL.
 *
 * @package dlinq-translation
 */

defined( 'ABSPATH' ) || exit;

// Authors and above only.
if ( ! is_user_logged_in() || ! current_user_can( 'publish_posts' ) ) {
	wp_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$translation_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
$translation    = $translation_id ? get_post( $translation_id ) : null;
$error          = '';

if ( ! $translation || 'translation' !== $translation->post_type || 'publish' !== $translation->post_status ) {
	$error = $translation_id
		? 'No published translation found for that ID.'
		: 'No translation ID provided — add <code>?id=&lt;post_id&gt;</code> to the URL.';
}

$audio_url  = '';
$vtt_url    = '';
$lang       = '';
$post_title = '';

if ( ! $error ) {
	$audio_url  = get_field( 'audio_file', $translation_id ) ?: '';
	$vtt_url    = get_field( 'vtt_file', $translation_id )   ?: '';
	$lang       = get_post_meta( $translation_id, 'lang', true );
	$post_title = get_the_title( $translation_id );

	if ( empty( $audio_url ) || empty( $vtt_url ) ) {
		$error = 'This translation is missing an audio file or a VTT file. Add both in the translation editor.';
	}
}

get_header();
$container = get_theme_mod( 'understrap_container_type' );
?>

<div class="wrapper" id="vtt-adjustment-wrapper">
	<div class="<?php echo esc_attr( $container ); ?>" id="content">
		<div class="row justify-content-center">
			<div class="col-lg-9 col-xl-8" id="primary">
				<main class="site-main" id="main" role="main">

					<?php if ( $error ) : ?>

						<h1>VTT Adjustment</h1>
						<p class="vtt-adj-error-msg"><?php echo wp_kses_post( $error ); ?></p>

					<?php else : ?>

						<h1>
							<?php echo esc_html( $post_title ); ?>
							<small>(VTT adjustment)</small>
						</h1>

						<p class="vtt-adj-meta">
							<a href="<?php echo esc_url( get_permalink( $translation_id ) ); ?>">&#8592; View translation</a>
						</p>

						<p class="vtt-adj-meta">
							Edit timestamps next to each phrase, or click <strong>Mark Start</strong> / <strong>Mark End</strong> while the audio plays to capture the current position. Download the revised VTT when done.
						</p>

						<div id="vtt-adj-player">
							<audio id="vtt-adj-audio" controls preload="metadata"
								src="<?php echo esc_url( $audio_url ); ?>"></audio>
						</div>

						<div id="vtt-adj-toolbar">
							<span>Now: <span id="vtt-adj-current-time">00:00:00.000</span></span>
							<span class="vtt-adj-sep">|</span>
							<label>
								Shift all by
								<input type="number" id="vtt-adj-shift-input" value="0" step="0.1"> s
							</label>
							<button class="vtt-adj-btn" id="vtt-adj-apply-shift">Apply Shift</button>
							<span class="vtt-adj-sep">|</span>
							<button class="vtt-adj-btn" id="vtt-adj-download">&#11015; Download VTT</button>
							<button class="vtt-adj-btn" id="vtt-adj-reset">Reset</button>
							<span id="vtt-adj-status"></span>
						</div>

						<div class="vtt-adj-transcript"
							id="vtt-adj-transcript"
							<?php if ( $lang ) : ?>lang="<?php echo esc_attr( $lang ); ?>"<?php endif; ?>></div>

					<?php endif; ?>

				</main>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
