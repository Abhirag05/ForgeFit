/**
 * Prevent Back Button Navigation After Logout
 * This script prevents users from accessing protected pages via browser back button after logout
 */

(function () {
    'use strict';

    // Method 1: Prevent back button navigation
    history.pushState(null, document.title, location.href);
    window.addEventListener('popstate', function (event) {
        history.pushState(null, document.title, location.href);
    });

    // Method 2: Detect if page is loaded from cache (back/forward navigation)
    window.addEventListener('pageshow', function (event) {
        if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
            // Page was loaded from cache (back/forward button)
            window.location.reload();
        }
    });

    // Method 3: Prevent page from being stored in browser cache
    if (window.performance && window.performance.navigation.type === 2) {
        window.location.reload();
    }

})();
