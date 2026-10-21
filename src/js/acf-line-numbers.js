/**
 * Translation edit screen: line-number gutters for the ACF original (Maidu)
 * and translation textareas, numbered like the front-end .line counters —
 * blank lines are skipped, and wrapped lines keep a single number.
 */
var LINE_FIELDS = [
	{ key: 'field_67ec0a23a86b4', side: 'right' }, // original_text (right-aligned)
	{ key: 'field_67ec0a3fa86b5', side: 'left' }, // translation
];

// Styles that affect how text wraps, copied onto the measuring mirror.
var MIRROR_STYLES = [
	'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'letterSpacing',
	'lineHeight', 'textTransform', 'wordSpacing', 'tabSize',
	'paddingLeft', 'paddingRight',
];

function initLineNumbers( textarea, side ) {
	var wrap   = document.createElement( 'div' );
	var gutter = document.createElement( 'div' );
	var mirror = document.createElement( 'div' );
	var frame;

	wrap.className   = 'dlinq-ln-wrap dlinq-ln-' + side;
	gutter.className = 'dlinq-ln-gutter';
	gutter.setAttribute( 'aria-hidden', 'true' );
	mirror.className = 'dlinq-ln-mirror';

	textarea.parentNode.insertBefore( wrap, textarea );
	wrap.appendChild( textarea );
	if ( 'left' === side ) {
		wrap.insertBefore( gutter, textarea );
	} else {
		wrap.appendChild( gutter );
	}
	document.body.appendChild( mirror );

	function render() {
		var style   = window.getComputedStyle( textarea );
		var padding = parseFloat( style.paddingLeft ) + parseFloat( style.paddingRight );
		var lines   = textarea.value.split( '\n' );
		var counter = 1;

		MIRROR_STYLES.forEach( function( prop ) {
			mirror.style[ prop ] = style[ prop ];
		} );
		mirror.style.width = ( textarea.clientWidth - padding ) + 'px';

		mirror.innerHTML = '';
		lines.forEach( function( line ) {
			var row = document.createElement( 'div' );
			// A zero-width space gives empty lines their one-line height.
			row.textContent = line || '​';
			mirror.appendChild( row );
		} );

		gutter.innerHTML        = '';
		gutter.style.paddingTop = style.paddingTop;
		Array.prototype.forEach.call( mirror.children, function( row, idx ) {
			var cell = document.createElement( 'div' );
			cell.style.height     = row.offsetHeight + 'px';
			cell.style.lineHeight = style.lineHeight;
			if ( '' !== lines[ idx ] ) {
				cell.textContent = counter;
				counter++;
			}
			gutter.appendChild( cell );
		} );

		gutter.scrollTop = textarea.scrollTop;
	}

	function scheduleRender() {
		window.cancelAnimationFrame( frame );
		frame = window.requestAnimationFrame( render );
	}

	textarea.addEventListener( 'input', scheduleRender );
	textarea.addEventListener( 'scroll', function() {
		gutter.scrollTop = textarea.scrollTop;
	} );
	if ( window.ResizeObserver ) {
		new ResizeObserver( scheduleRender ).observe( textarea );
	} else {
		window.addEventListener( 'resize', scheduleRender );
	}

	render();
}

function initAllLineNumbers() {
	LINE_FIELDS.forEach( function( field ) {
		var textarea = document.querySelector( '.acf-field[data-key="' + field.key + '"] textarea' );
		if ( textarea ) {
			initLineNumbers( textarea, field.side );
		}
	} );
}

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', initAllLineNumbers );
} else {
	initAllLineNumbers();
}
