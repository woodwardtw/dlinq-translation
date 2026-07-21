<?php
/**
 * Single post partial template
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$audio_url = get_field( 'audio_file' );
$vtt_url   = get_field( 'vtt_file' );
$has_audio = ! empty( $audio_url );
$has_vtt   = $has_audio && ! empty( $vtt_url );
$logged_in = is_user_logged_in();
$pad = $logged_in ? 'wp-pad' : '';
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header d-flex justify-content-between align-items-start flex-wrap gap-2">

		<div>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			<div class="entry-meta">
				<?php //understrap_posted_on(); ?>
			</div><!-- .entry-meta -->
		</div>

		<div class="translation-search <?php echo $pad;?>" id="translation-search">
			<div class="input-group">
				<input type="search" id="translation-search-input" class="form-control" placeholder="Search this story . . . " aria-label="Search translation text">
				<span class="input-group-text" id="translation-search-count" aria-live="polite"></span>
				<button data-search="next">&darr;</button>
  				<button data-search="prev">&uarr;</button>
				<button class="btn btn-outline-secondary" type="button" id="translation-search-clear" aria-label="Clear search">&#x2715;</button>
			</div>
		</div>

	</header><!-- .entry-header -->

	<?php
	$thumbnail_id  = get_post_thumbnail_id( $post->ID );
	$thumbnail_url = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
	$thumbnail_alt = $thumbnail_id ? get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) : '';
	?>
	<?php if ( $thumbnail_url ) : ?>
		<?php if ( $thumbnail_alt ) : ?>
			<div class="entry-thumbnail-banner" style="background-image: url('<?php echo esc_url( $thumbnail_url ); ?>');" role="img" aria-label="<?php echo esc_attr( $thumbnail_alt ); ?>"></div>
		<?php else : ?>
			<div class="entry-thumbnail-banner" style="background-image: url('<?php echo esc_url( $thumbnail_url ); ?>');" role="presentation"></div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="entry-content">

	<?php if ( $has_audio ) : ?>
		<audio id="tt-audio" preload="metadata" src="<?php echo esc_url( $audio_url ); ?>">
			<?php if ( $has_vtt ) : ?>
				<track kind="metadata" src="<?php echo esc_url( $vtt_url ); ?>" label="Phrase timing">
			<?php endif; ?>
		</audio>
		<div id="tt-player">
			<div id="waveform"></div>
			<div id="player-controls">
				<button id="play-btn" aria-label="Play">&#9654;</button>
				<span id="player-time">0:00 / 0:00</span>
			</div>
		</div>
	<?php endif; ?>

	<!--translation display-->
		<div class="container-fluid">
  			<div class="row">
  				<div class="col-md-12">
  					<?php echo dlinq_translation_legend();?>
  				</div>
  				<div class="col-md-6">
      				<div class="original text-box<?php echo $has_vtt ? ' tt-transcript' : ''; ?>"<?php echo $has_vtt ? ' data-tt-media-urls="' . esc_url( $audio_url ) . '"' : ''; ?>>
      					<?php echo dlinq_translation( 'original_text', $has_vtt ? ' tt-phrase' : '' );?>
      				</div>
      			</div>
      			<div class="col-md-6">
      				<div class="translated text-box">
      					<?php echo dlinq_translation('translation');?>
      				</div>
      			</div>
				<div class="col-md-6 offset-md-3 ">
					<?php $speakers = get_field( 'speaker' ); ?>
					<?php if ( $speakers ) : ?>
						<div class="speaker-box">
							<h2>Speakers</h2>
							<?php foreach ( $speakers as $speaker ) : ?>
								<?php
								$post_id = $speaker->ID;
								$image   = get_field( 'bio_photo', $post_id );
								?>
								<div class="speaker row">
									<?php if ( $image ) : ?>
										<div class="col-md-3">
											<img class="img-fluid" src="<?php echo esc_url( $image['sizes']['thumbnail'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>">
										</div>
									<?php endif; ?>
									<div class="col-md-9">
										<a href="<?php echo get_permalink( $post_id ); ?>"><?php echo get_the_title( $post_id ); ?></a>
										<?php echo get_field( 'biography', $post_id ); ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p>No speakers.</p>
					<?php endif; ?>
				</div>
				<?php $trajectory = get_field( 'text_trajectory' ); ?>
				<?php if ( $trajectory ) : ?>
					<div class="col-md-6 offset-md-3">
						<div class="trajectory-box">
							<h2>Text Trajectory</h2>
							<?php echo $trajectory; ?>
						</div>
					</div>
				<?php endif; ?>

      		</div>
      	</div>
    <!--end translation display-->
		<?php
		the_content();
		understrap_link_pages();
		?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
