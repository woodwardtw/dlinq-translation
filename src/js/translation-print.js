/* Translation PDF download — opens the browser print dialog (Save as PDF) with a QR code back to the page */

import qrcode from 'qrcode-generator';

var printBtn   = document.getElementById( 'download-pdf' );
var qrBox      = document.getElementById( 'print-qr-code' );
var printTable = document.getElementById( 'print-translation' );

if ( qrBox && qrBox.dataset.url ) {
	var qr = qrcode( 0, 'M' );
	qr.addData( qrBox.dataset.url );
	qr.make();
	qrBox.innerHTML = qr.createSvgTag( { cellSize: 4, margin: 0, alt: 'QR code linking to this translation' } );
}

// Collect a column's lines by line number, noting which ones start a new stanza.
function collectLines( selector ) {
	var lines = {};
	document.querySelectorAll( selector + ' .line' ).forEach( function( line ) {
		var prev = line.previousElementSibling;
		lines[ line.dataset.line ] = {
			html:      line.innerHTML,
			highlight: line.classList.contains( 'highlight' ),
			stanza:    !! ( prev && 'BR' === prev.tagName ),
		};
	} );
	return lines;
}

function makeCell( line, className ) {
	var cell = document.createElement( 'td' );
	cell.className = className + ( line && line.highlight ? ' highlight' : '' );
	cell.innerHTML = line ? line.html : '';
	return cell;
}

// Print layout pairs original/translation lines in table rows, which paginate
// cleanly and stay aligned (the on-screen flex columns get clipped by Chrome).
function buildPrintTable() {
	if ( ! printTable ) {
		return;
	}
	var original   = collectLines( '.original' );
	var translated = collectLines( '.translated' );
	var count      = Math.max.apply( null, [ 0 ].concat( Object.keys( original ), Object.keys( translated ) ).map( Number ) );
	var body       = document.createElement( 'tbody' );

	for ( var i = 1; i <= count; i++ ) {
		var row = document.createElement( 'tr' );
		var num = document.createElement( 'td' );
		num.className   = 'print-num';
		num.textContent = i;
		if ( ( original[ i ] && original[ i ].stanza ) || ( translated[ i ] && translated[ i ].stanza ) ) {
			row.className = 'stanza-start';
		}
		row.appendChild( makeCell( original[ i ], 'print-original' ) );
		row.appendChild( num );
		row.appendChild( makeCell( translated[ i ], 'print-translated' ) );
		body.appendChild( row );
	}

	printTable.replaceChildren( body );
}

buildPrintTable();
// Rebuild so lines highlighted after page load are carried into the PDF.
window.addEventListener( 'beforeprint', buildPrintTable );

if ( printBtn ) {
	printBtn.addEventListener( 'click', function() {
		window.print();
	} );
}
