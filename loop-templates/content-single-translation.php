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

	<header class="entry-header">

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="entry-meta">

			<?php understrap_posted_on(); ?>

		</div><!-- .entry-meta -->

	</header><!-- .entry-header -->

	<?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>

	<div class="entry-content">
		
	<!--translation display-->
		<div class="container-fluid">
  			<div class="row">
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
