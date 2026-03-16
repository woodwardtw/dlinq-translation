<?php
/**
 * Partial template for content in mappage.php
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<?php
	if ( ! is_page_template( 'page-templates/no-title.php' ) ) {
		the_title(
			'<header class="entry-header"><h1 class="entry-title">',
			'</h1></header><!-- .entry-header -->'
		);
	}
	?>

	<div class="entry-content">

		<div id="location-map" style="height: 600px; width: 100%;"></div>

		<script>
		(function () {
			const restBase = <?php echo wp_json_encode( rest_url( 'wp/v2/location' ) ); ?>;

			function addMarkers( map, locations ) {
				locations.forEach( function ( location ) {
					let acf = location.acf || {};
					let lat = parseFloat( acf.latitude );
					let lng = parseFloat( acf.longitude );
					if ( isNaN( lat ) || isNaN( lng ) ) {
						return;
					}
					let name = location.title.rendered || ( location.title && location.title.rendered ) || 'Location';
					L.marker( [ lat, lng ] )
						.addTo( map )
						.bindPopup( '<h3>' + name + '</h3><div>' + location.acf.english_name + '</div>' );
				} );
			}

			function fetchPage( map, page ) {
				fetch( restBase + '?per_page=100&page=' + page )
					.then( function ( response ) {
						const totalPages = parseInt( response.headers.get( 'X-WP-TotalPages' ), 10 ) || 1;
						return response.json().then( function ( data ) {
							addMarkers( map, data );
							if ( page < totalPages ) {
								fetchPage( map, page + 1 );
							}
						} );
					} )
					.catch( function ( err ) {
						console.error( 'Error fetching location data:', err );
					} );
			}

			document.addEventListener( 'DOMContentLoaded', function () {
				const map = L.map( 'location-map' ).setView( [ 40, -120 ], 8 );

				L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
					maxZoom: 18,
				} ).addTo( map );
               
				fetchPage( map, 1 );
			} );
		} )();
		</script>

		<?php
		the_content();
		understrap_link_pages();
		?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_edit_post_link(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-<?php the_ID(); ?> -->
