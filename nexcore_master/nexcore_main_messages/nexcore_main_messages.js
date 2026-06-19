/*
 * ============================================================
 * NxAlert - NexCore Enterprise Message Box System
 * ============================================================
 *
 * Component:     nexcore_main_messages
 * Version:       2.0
 * Location:      /public/nexcore/master/nexcore_main_messages/nexcore_main_messages.js
 *
 * Dependencies:
 *   - SweetAlert2 v11 JS (must be loaded BEFORE this file)
 *   - nexcore_main_messages.css (must be loaded in the <head>)
 *
 * Available Methods:
 *   NxAlert.success(title, message)    - M1: Record saved, action completed
 *   NxAlert.error(title, message)      - M2: Operation failed, server error
 *   NxAlert.warning(title, message)    - M3: Caution notice, review required
 *   NxAlert.info(title, message)       - M4: General notice, information
 *   NxAlert.confirm(title, message)    - M5: Yes/No confirmation (2 buttons)
 *   NxAlert.delete(title, recordName)  - M6: Typed DELETE confirmation (2 buttons)
 *
 * Image Fallback Logic:
 *   On load, the system checks the master images folder first:
 *     /public/nexcore/master/nexcore_main_images/nexcore-icon.png
 *   If the icon is found there, it uses the master version.
 *   If not found, it falls back to the central branding folder:
 *     /public/nexcore/branding/nexcore-icon.png
 *   This allows master-level icon overrides while keeping
 *   a clean central brand default.
 *
 * Quick Start:
 *   1. Load SweetAlert2 CSS + JS from CDN
 *   2. Load nexcore_main_messages.css
 *   3. Load nexcore_main_messages.js (this file)
 *   4. Call NxAlert.success('Done', 'Your record was saved.')
 *
 * NexCore Africa Proprietary Limited
 * www.nexcore.africa
 * ============================================================
 */

var NxAlert = {

    /* =======================================================
       IMAGE PATHS
       Component folder is checked first, branding is fallback.
       Change _logoFile to use a different icon filename.
       ======================================================= */

    /* Path to the master images folder (checked first) */
    _componentImages: '/public/nexcore/master/nexcore_main_images/',

    /* Path to the central branding folder (fallback) */
    _brandingImages: '/public/nexcore/branding/',

    /* Icon filename - same name used in both locations */
    _logoFile: 'nexcore-icon.png',

    /* Resolved logo path - defaults to branding, upgraded if master icon exists */
    _logo: '/public/nexcore/branding/nexcore-icon.png',

    /* Icon display size in pixels (width and height) */
    _logoSize: 160,


    /* =======================================================
       COLOUR DEFINITIONS
       Each message type has three properties:
         btn    - Confirm button background colour
         pastel - Light tint (available for custom use)
         cls    - CSS class appended to the popup
       ======================================================= */
    _colors: {
        success: { btn: '#059669', pastel: '#d1fae5', cls: 'nx-swal-success' },  /* Green  */
        warning: { btn: '#e11d48', pastel: '#ffe4e6', cls: 'nx-swal-warning' },  /* Rose   */
        info:    { btn: '#2563eb', pastel: '#dbeafe', cls: 'nx-swal-info'    },  /* Blue   */
        confirm: { btn: '#0891b2', pastel: '#cffafe', cls: 'nx-swal-confirm' },  /* Cyan   */
        error:   { btn: '#d97706', pastel: '#fef3c7', cls: 'nx-swal-error'   },  /* Amber  */
        delete:  { btn: '#dc2626', pastel: '#fee2e2', cls: 'nx-swal-delete'  }   /* Red    */
    },


    /* =======================================================
       INITIALISATION
       Runs on page load. Checks if a component-level icon
       exists by preloading it as an image. If found, overrides
       the default branding path. If not found (404), the
       branding fallback remains in place. Silent - no errors.
       ======================================================= */
    _init: function() {
        var self = this;
        var img = new Image();
        var componentPath = this._componentImages + this._logoFile;

        /* If the component icon loads successfully, use it */
        img.onload = function() {
            self._logo = componentPath;
        };

        /* If it fails (404), _logo stays as the branding default */
        /* No action needed on error - fallback is already set */

        img.src = componentPath;
    },


    /* =======================================================
       CORE FIRE METHOD
       Builds the SweetAlert2 configuration and displays the
       popup. All six public methods route through here
       (except delete, which handles its own input field).

       Parameters:
         type    - Message type key (success/error/warning/info/confirm/delete)
         title   - Popup heading text (T2 standard)
         message - Popup body text (T5 standard)
         opts    - Optional overrides:
                   .confirmText - Custom confirm button label
                   .cancelText  - Custom cancel button label
                   .showCancel  - Show the cancel button (true/false)
       ======================================================= */
    _fire: function(type, title, message, opts) {
        var c = this._colors[type] || this._colors.info;

        var config = {
            /* Brand icon */
            imageUrl: this._logo,
            imageWidth: this._logoSize,
            imageHeight: this._logoSize,

            /* Content */
            title: title,
            html: message,

            /* Buttons */
            confirmButtonText: (opts && opts.confirmText) || 'OK',
            confirmButtonColor: c.btn,
            showCancelButton: !!(opts && opts.showCancel),
            cancelButtonText: (opts && opts.cancelText) || 'CANCEL',

            /* Layout */
            width: 'auto',
            background: '#ffffff',

            /* Footer - company branding */
            footer: 'NexCore Africa Proprietary Limited<br>'
                  + '<a href="https://www.nexcore.africa" target="_blank" class="nx-swal-link">'
                  + 'www.nexcore.africa</a>',

            /* CSS classes for NexCore styling */
            customClass: {
                popup:         'nx-swal-popup ' + c.cls,
                title:         'nx-swal-title',
                htmlContainer: 'nx-swal-html',
                actions:       'nx-swal-actions',
                footer:        'nx-swal-footer'
            }
        };

        return Swal.fire(config);
    },


    /* =======================================================
       M1: SUCCESS
       Single green confirm button.
       Use for: record saved, action completed, operation successful.

       Example:
         NxAlert.success('Success', 'Record has been saved successfully.');
       ======================================================= */
    success: function(title, message) {
        return this._fire('success', title, message);
    },


    /* =======================================================
       M2: ERROR
       Single amber confirm button.
       Use for: operation failed, server error, validation failure.

       Example:
         NxAlert.error('Error', 'Something went wrong. Please try again.');
       ======================================================= */
    error: function(title, message) {
        return this._fire('error', title, message);
    },


    /* =======================================================
       M3: WARNING
       Single rose confirm button.
       Use for: caution notices, review required, potential issues.

       Example:
         NxAlert.warning('Warning', 'Please review before proceeding.');
       ======================================================= */
    warning: function(title, message) {
        return this._fire('warning', title, message);
    },


    /* =======================================================
       M4: INFO
       Single blue confirm button.
       Use for: general notices, information, tips.

       Example:
         NxAlert.info('Information', 'This is a general notice.');
       ======================================================= */
    info: function(title, message) {
        return this._fire('info', title, message);
    },


    /* =======================================================
       M5: CONFIRM
       Two buttons: pink confirm + cyan cancel.
       Use for: confirm before proceeding, yes/no decisions.
       Returns a promise - check result.isConfirmed for the answer.

       Example:
         NxAlert.confirm('Confirm Action', 'Are you sure?').then(function(result) {
             if (result.isConfirmed) {
                 // User clicked YES, CONFIRM
             }
         });
       ======================================================= */
    confirm: function(title, message, confirmText) {
        return this._fire('confirm', title, message, {
            showCancel: true,
            confirmText: confirmText || 'YES, CONFIRM',
            cancelText: 'CANCEL'
        });
    },


    /* =======================================================
       M6: DELETE
       Two buttons: red confirm + cyan cancel with typed confirmation.
       The user MUST type "DELETE" in the input field before
       the action is allowed. Validation prevents submission
       with incorrect text.

       Note: This method builds its own SweetAlert2 config
       (does not use _fire) because it requires the input field.

       Example:
         NxAlert.delete('Delete Record', 'Krish Moodley').then(function(result) {
             if (result.isConfirmed) {
                 // User typed DELETE and clicked YES, DELETE
             }
         });
       ======================================================= */
    delete: function(title, name) {
        var c = this._colors['delete'];

        return Swal.fire({
            /* Brand icon */
            imageUrl: this._logo,
            imageWidth: this._logoSize,
            imageHeight: this._logoSize,

            /* Content - includes the record name in bold */
            title: title,
            html: 'You are about to delete <strong>' + name + '</strong>.<br>'
                + 'To confirm, please type <strong>DELETE</strong> in the box below.',

            /* Typed confirmation input */
            input: 'text',
            inputPlaceholder: 'Type DELETE to confirm',
            inputValidator: function(value) {
                if (value !== 'DELETE') {
                    return 'Please type DELETE to confirm';
                }
            },

            /* Buttons */
            confirmButtonText: 'YES, DELETE',
            confirmButtonColor: c.btn,
            showCancelButton: true,
            cancelButtonText: 'CANCEL',

            /* Layout */
            width: 'auto',
            background: '#ffffff',

            /* Footer - company branding */
            footer: 'NexCore Africa Proprietary Limited<br>'
                  + '<a href="https://www.nexcore.africa" target="_blank" class="nx-swal-link">'
                  + 'www.nexcore.africa</a>',

            /* CSS classes for NexCore styling */
            customClass: {
                popup:         'nx-swal-popup ' + c.cls,
                title:         'nx-swal-title',
                htmlContainer: 'nx-swal-html',
                actions:       'nx-swal-actions',
                footer:        'nx-swal-footer',
                input:         'nx-swal-input'
            }
        });
    }
};


/* =======================================================
   AUTO-INIT
   Resolve the logo path on page load.
   Checks component images folder first, falls back to
   central branding if not found.
   ======================================================= */
NxAlert._init();
