/*
 * ============================================================
 * NexCore System Master Sidebar - Behaviour
 * ============================================================
 *
 * Component:     system_sidebar
 * Version:       2.1
 * Location:      /public/nexcore/system_sidebar/js/system_master_sidebar.js
 *
 * Features:
 *   - Collapse/expand with localStorage persistence
 *   - 3-level menu with accordion (auto-close siblings)
 *   - Mobile slide-in with overlay backdrop
 *   - Active menu item highlighting based on current URL
 *   - Keyboard shortcut: Ctrl+B to toggle sidebar
 *
 * NexCore Africa Proprietary Limited
 * www.nexcore.africa
 * ============================================================
 */

var NxSidebar = {

    _sidebar: null,
    _content: null,
    _overlay: null,
    _toggleIcon: null,
    _storageKey: 'nxsb_collapsed',

    init: function() {
        this._sidebar = document.getElementById('nxSidebar');
        this._content = document.querySelector('.nxsb-content');
        this._overlay = document.querySelector('.nxsb-overlay');
        this._toggleIcon = document.getElementById('nxSbToggleIcon');

        if (!this._sidebar) return;

        var saved = localStorage.getItem(this._storageKey);
        if (saved === 'true') {
            this._sidebar.classList.add('nxsb-collapsed');
            if (this._content) this._content.classList.add('nxsb-content-collapsed');
        }

        this._setActiveItem();
        this._initSections();

        if (this._overlay) {
            this._overlay.addEventListener('click', function() {
                NxSidebar.closeMobile();
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                NxSidebar.toggle();
            }
        });
    },

    toggle: function() {
        if (!this._sidebar) return;

        this._sidebar.classList.toggle('nxsb-collapsed');

        if (this._content) {
            this._content.classList.toggle('nxsb-content-collapsed');
        }

        var isCollapsed = this._sidebar.classList.contains('nxsb-collapsed');
        localStorage.setItem(this._storageKey, isCollapsed);
    },

    toggleGroup: function(btn) {
        if (!this._sidebar) return;
        if (this._sidebar.classList.contains('nxsb-collapsed')) return;

        var group = btn.closest('.nxsb-group');
        var items = group.querySelector('.nxsb-group-items');
        var isOpen = group.classList.contains('nxsb-group-open');

        var allGroups = this._sidebar.querySelectorAll('.nxsb-group.nxsb-group-open');
        for (var i = 0; i < allGroups.length; i++) {
            allGroups[i].classList.remove('nxsb-group-open');
            var gi = allGroups[i].querySelector('.nxsb-group-items');
            if (gi) gi.style.maxHeight = '0px';
            var openSections = allGroups[i].querySelectorAll('.nxsb-section.nxsb-open');
            for (var j = 0; j < openSections.length; j++) {
                openSections[j].classList.remove('nxsb-open');
                var ch = openSections[j].querySelector('.nxsb-children');
                if (ch) ch.style.maxHeight = '0px';
            }
        }

        if (!isOpen && items) {
            group.classList.add('nxsb-group-open');
            items.style.maxHeight = items.scrollHeight + 'px';
        }
    },

    toggleSection: function(btn) {
        if (!this._sidebar) return;
        if (this._sidebar.classList.contains('nxsb-collapsed')) return;

        var section = btn.closest('.nxsb-section');
        var children = section.querySelector('.nxsb-children');
        var isOpen = section.classList.contains('nxsb-open');
        var parentGroup = section.closest('.nxsb-group-items');

        var allSections = this._sidebar.querySelectorAll('.nxsb-section.nxsb-open');
        for (var i = 0; i < allSections.length; i++) {
            allSections[i].classList.remove('nxsb-open');
            var ch = allSections[i].querySelector('.nxsb-children');
            if (ch) ch.style.maxHeight = '0px';
        }

        if (!isOpen && children) {
            section.classList.add('nxsb-open');
            children.style.maxHeight = children.scrollHeight + 'px';
        }

        if (parentGroup) {
            setTimeout(function() {
                parentGroup.style.maxHeight = parentGroup.scrollHeight + 'px';
            }, 50);
        }
    },

    openMobile: function() {
        if (!this._sidebar) return;
        this._sidebar.classList.add('nxsb-mobile-open');
    },

    closeMobile: function() {
        if (!this._sidebar) return;
        this._sidebar.classList.remove('nxsb-mobile-open');
    },

    _initSections: function() {
        var groups = this._sidebar.querySelectorAll('.nxsb-group');
        var activeGroupFound = false;

        for (var g = 0; g < groups.length; g++) {
            var groupItems = groups[g].querySelector('.nxsb-group-items');
            if (!groupItems) continue;

            var hasActiveInGroup = groupItems.querySelector('.nxsb-active');
            if (hasActiveInGroup) {
                groups[g].classList.add('nxsb-group-open');
                groupItems.style.maxHeight = groupItems.scrollHeight + 'px';
                activeGroupFound = true;
            } else {
                groupItems.style.maxHeight = '0px';
            }
        }

        if (!activeGroupFound && groups.length > 0) {
            var firstItems = groups[0].querySelector('.nxsb-group-items');
            if (firstItems) {
                groups[0].classList.add('nxsb-group-open');
                firstItems.style.maxHeight = firstItems.scrollHeight + 'px';
            }
        }

        var sections = this._sidebar.querySelectorAll('.nxsb-section');
        for (var i = 0; i < sections.length; i++) {
            var children = sections[i].querySelector('.nxsb-children');
            if (!children) continue;

            var hasActive = children.querySelector('.nxsb-active');
            if (hasActive) {
                sections[i].classList.add('nxsb-open');
                children.style.maxHeight = children.scrollHeight + 'px';
            } else {
                children.style.maxHeight = '0px';
            }
        }
    },

    _setActiveItem: function() {
        var currentPath = window.location.pathname;
        var items = this._sidebar.querySelectorAll('.nxsb-item[href], .nxsb-child[href]');
        var bestMatch = null;
        var bestLength = 0;

        for (var i = 0; i < items.length; i++) {
            var href = items[i].getAttribute('href');
            if (!href || href === '#') continue;

            var linkPath = href;
            try {
                var url = new URL(href, window.location.origin);
                linkPath = url.pathname;
            } catch(e) {}

            if (currentPath.indexOf(linkPath) === 0 && linkPath.length > bestLength) {
                bestMatch = items[i];
                bestLength = linkPath.length;
            }
        }

        if (bestMatch) {
            bestMatch.classList.add('nxsb-active');
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    NxSidebar.init();
});
