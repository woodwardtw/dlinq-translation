<?php
/**
 * People Block render template
 *
 * Uses the same people_block repeater as the page-bottom display.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$html = dlinq_people_block();

if ( ! $html ) {
	if ( $is_preview ) {
		echo '<p class="people-block-empty">Add a people group in the block settings.</p>';
	}
	return;
}

$anchor = ! empty( $block['anchor'] ) ? ' id="' . esc_attr( $block['anchor'] ) . '"' : '';
$class  = ! empty( $block['className'] ) ? ' ' . esc_attr( $block['className'] ) : '';

echo "<div class='wp-block-people-block{$class}'{$anchor}>{$html}</div>";
