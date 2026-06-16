<?php
/**
 * Single post partial template
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header d-flex justify-content-between align-items-start flex-wrap gap-2">

		<div>
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			<div class="entry-meta">
				<?php //understrap_posted_on(); ?>
			</div><!-- .entry-meta -->
		</div>

		<div class="translation-search mt-2" id="translation-search">
			<div class="input-group">
				<input type="search" id="translation-search-input" class="form-control" placeholder="Search this story . . . " aria-label="Search translation text">
				<span class="input-group-text" id="translation-search-count" aria-live="polite"></span>
				<button data-search="next">&darr;</button>
  				<button data-search="prev">&uarr;</button>
				<button class="btn btn-outline-secondary" type="button" id="translation-search-clear" aria-label="Clear search">&#x2715;</button>
			</div>
		</div>

	</header><!-- .entry-header -->

	<?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>

	<div class="entry-content">
		
	<!--translation display-->
		<div class="container-fluid">
  			<div class="row">
  				<div class="col-md-12">
  					<?php echo dlinq_translation_legend();?>
  				</div>
  				<div class="col-md-6">
      				<div class="original text-box">
      					<?php echo dlinq_translation('original_text');?>
      				</div>
      			</div>
      			<div class="col-md-6">
      				<div class="translated text-box">
      					<?php echo dlinq_translation('translation');?>
      				</div>
      			</div>
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
