(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollSync);
    } else {
        initScrollSync();
    }

    function initScrollSync() {
        const field1 = document.querySelector('.acf-field-67ec0a23a86b4 textarea, .acf-field-67ec0a23a86b4 input');
        const field2 = document.querySelector('.acf-field-67ec0a3fa86b5 textarea, .acf-field-67ec0a3fa86b5 input');

        if (!field1 || !field2) {
            console.warn('ACF scroll sync: One or both fields not found');
            return;
        }

        let isScrolling = false;

        field1.addEventListener('scroll', function() {
            if (!isScrolling) {
                isScrolling = true;
                field2.scrollTop = field1.scrollTop;
                field2.scrollLeft = field1.scrollLeft;
                setTimeout(function() {
                    isScrolling = false;
                }, 10);
            }
        });

        field2.addEventListener('scroll', function() {
            if (!isScrolling) {
                isScrolling = true;
                field1.scrollTop = field2.scrollTop;
                field1.scrollLeft = field2.scrollLeft;
                setTimeout(function() {
                    isScrolling = false;
                }, 10);
            }
        });
    }
})();
