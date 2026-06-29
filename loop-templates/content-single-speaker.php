<?php
/**
 * Single speaker post partial template
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header speaker">

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="entry-meta">

			<?php //understrap_posted_on(); ?>

		</div><!-- .entry-meta -->

	</header><!-- .entry-header -->



	<div class="entry-content">
        <div class="row">
            <div class="col-md-4">
                <?php 
                    $image = get_field('bio_photo');
                    echo '<img class="img-fluid" src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '" />';
                ?>
            </div>
            <div class="col-md-8">
                <?php
                the_content();
                echo the_field('biography');
                $translations = get_field('translations');
                if ($translations) {
                    echo '<h2>Stories</h2>';
                    echo '<ul>';
                    foreach ($translations as $translation) {
                        echo '<li><a href="' . get_permalink($translation->ID) . '">' . get_the_title($translation->ID) . '</a></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No stories available.</p>';   
                }
                //var_dump(get_field('translations'));
                understrap_link_pages();
                ?>
            </div>
        </div>


		<?php
		the_content();
		understrap_link_pages();
		?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
