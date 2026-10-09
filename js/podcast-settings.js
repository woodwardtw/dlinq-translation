/*!
 * Understrap v1.2.4 (https://understrap.com)
 * Copyright 2013-2026 The Understrap Authors (https://github.com/understrap/understrap/graphs/contributors)
 * Licensed under GPL-3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 */
(function () {
	'use strict';

	/**
	 * Settings > Podcast: media library picker for the cover art field.
	 */
	document.querySelectorAll('.dlinq-podcast-image').forEach(function (wrap) {
	  var input = wrap.querySelector('input[type="hidden"]');
	  var preview = wrap.querySelector('img');
	  var selectBtn = wrap.querySelector('.dlinq-podcast-image-select');
	  var removeBtn = wrap.querySelector('.dlinq-podcast-image-remove');
	  var frame;
	  selectBtn.addEventListener('click', function () {
	    if (!frame) {
	      frame = wp.media({
	        // eslint-disable-line no-undef
	        title: 'Select cover art',
	        button: {
	          text: 'Use as cover art'
	        },
	        library: {
	          type: 'image'
	        },
	        multiple: false
	      });
	      frame.on('select', function () {
	        var image = frame.state().get('selection').first().toJSON();
	        var size = image.sizes && image.sizes.medium ? image.sizes.medium : image;
	        input.value = image.id;
	        preview.src = size.url;
	        preview.style.display = 'inline-block';
	        removeBtn.style.display = 'inline-block';
	      });
	    }
	    frame.open();
	  });
	  removeBtn.addEventListener('click', function () {
	    input.value = '';
	    preview.src = '';
	    preview.style.display = 'none';
	    removeBtn.style.display = 'none';
	  });
	});

})();
//# sourceMappingURL=podcast-settings.js.map
