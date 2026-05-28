/*
 * ============================================================
 * NexCore System Master Drawer - Behaviour
 * ============================================================
 *
 * Component:     system_drawer
 * Version:       1.0
 * Location:      /public/nexcore/system_drawer/js/system_master_drawer.js
 *
 * Dependencies:
 *   - system_master_drawer.css (must be loaded first)
 *
 * Paired with:
 *   - /public/nexcore/system_drawer/css/system_master_drawer.css
 *   - /application/resources/views/nexcore/system_master_drawer.blade.php
 *
 * Features:
 *   - Open/close slide-out drawer from the right
 *   - Overlay backdrop that closes drawer on click
 *   - Escape key closes drawer
 *   - Dynamically set title and content
 *
 * Usage:
 *   NxDrawer.open('Title', '<p>Content</p>');
 *   NxDrawer.close();
 *
 * NexCore Africa Proprietary Limited
 * www.nexcore.africa
 * ============================================================
 */

var NxDrawer = {

    /* =======================================================
       ELEMENT REFERENCES
       Cached DOM references set during init.
       ======================================================= */
    _drawer: null,
    _overlay: null,
    _title: null,
    _body: null,


    /* =======================================================
       INITIALISATION
       Called on DOMContentLoaded. Sets up references and
       binds the overlay click and escape key handlers.
       ======================================================= */
    init: function() {
        this._drawer  = document.getElementById('nxDrawer');
        this._overlay = document.getElementById('nxDrawerOverlay');
        this._title   = document.getElementById('nxDrawerTitle');
        this._body    = document.getElementById('nxDrawerBody');

        if (!this._drawer) return;

        /* Close drawer when overlay is clicked */
        if (this._overlay) {
            this._overlay.addEventListener('click', function() {
                NxDrawer.close();
            });
        }

        /* Close drawer with Escape key */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                NxDrawer.close();
            }
        });
    },


    /* =======================================================
       OPEN
       Slides the drawer into view. Optionally sets title
       and HTML content in the body.
       ======================================================= */
    open: function(title, content) {
        if (!this._drawer) return;

        /* Set title if provided */
        if (title && this._title) {
            this._title.textContent = title;
        }

        /* Set body HTML if provided */
        if (content && this._body) {
            this._body.innerHTML = content;
        }

        /* Show drawer and overlay */
        this._drawer.classList.add('nxdw-open');
        if (this._overlay) {
            this._overlay.classList.add('nxdw-overlay-visible');
        }
    },


    /* =======================================================
       CLOSE
       Slides the drawer off-screen and hides the overlay.
       ======================================================= */
    close: function() {
        if (!this._drawer) return;

        this._drawer.classList.remove('nxdw-open');
        if (this._overlay) {
            this._overlay.classList.remove('nxdw-overlay-visible');
        }
    }
};


/* =======================================================
   AUTO-INIT
   Initialise drawer when the DOM is ready.
   ======================================================= */
document.addEventListener('DOMContentLoaded', function() {
    NxDrawer.init();
});
