/*!
 * Understrap v1.2.4 (https://understrap.com)
 * Copyright 2013-2026 The Understrap Authors (https://github.com/understrap/understrap/graphs/contributors)
 * Licensed under GPL-3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 */
(function () {
	'use strict';

	/* Translation page search — requires Mark.js loaded as a global */

	var searchInput = document.getElementById('translation-search-input');
	var searchCount = document.getElementById('translation-search-count');
	var searchClear = document.getElementById('translation-search-clear');
	var prevBtn = document.querySelector("button[data-search='prev']");
	var nextBtn = document.querySelector("button[data-search='next']");
	if (searchInput) {
	  var markInstance = new Mark(document.querySelectorAll('.text-box')); // eslint-disable-line no-undef
	  var currentClass = 'current';
	  var offsetTop = 50;
	  var currentIndex = 0;
	  var results = [];
	  function jumpTo() {
	    if (!results.length) {
	      return;
	    }
	    results.forEach(function (el) {
	      el.classList.remove(currentClass);
	    });
	    var current = results[currentIndex];
	    if (current) {
	      current.classList.add(currentClass);
	      window.scrollTo(0, current.getBoundingClientRect().top + window.scrollY - offsetTop);
	    }
	  }
	  function doSearch() {
	    var term = searchInput.value.trim();
	    markInstance.unmark({
	      done: function () {
	        results = [];
	        currentIndex = 0;
	        if (!term) {
	          searchCount.textContent = '';
	          return;
	        }
	        markInstance.mark(term, {
	          separateWordSearch: false,
	          done: function (count) {
	            results = Array.from(document.querySelectorAll('.text-box mark'));
	            searchCount.textContent = count > 0 ? count + ' found' : 'none';
	            jumpTo();
	          }
	        });
	      }
	    });
	  }
	  searchInput.addEventListener('input', doSearch);
	  searchClear.addEventListener('click', function () {
	    searchInput.value = '';
	    searchCount.textContent = '';
	    results = [];
	    currentIndex = 0;
	    markInstance.unmark();
	  });
	  if (nextBtn) {
	    nextBtn.addEventListener('click', function () {
	      if (!results.length) {
	        return;
	      }
	      currentIndex = (currentIndex + 1) % results.length;
	      jumpTo();
	    });
	  }
	  if (prevBtn) {
	    prevBtn.addEventListener('click', function () {
	      if (!results.length) {
	        return;
	      }
	      currentIndex = (currentIndex - 1 + results.length) % results.length;
	      jumpTo();
	    });
	  }
	}

})();
//# sourceMappingURL=translation-search.js.map
