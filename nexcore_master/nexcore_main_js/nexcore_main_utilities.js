/*
 * ============================================================
 * NxUtils - NexCore Enterprise Utility Functions
 * ============================================================
 *
 * Component:     nexcore_main_utilities
 * Version:       1.0
 * Location:      /public/nexcore/master/nexcore_main_js/nexcore_main_utilities.js
 *
 * Dependencies:
 *   None (standalone utility library)
 *
 * Available Methods:
 *   NxUtils.formatDate(date, format)          - Format date string
 *   NxUtils.formatCurrency(amount, cur, dec)  - Format as currency
 *   NxUtils.formatNumber(num, decimals)       - Format with separators
 *   NxUtils.debounce(fn, delay)               - Debounce a function
 *   NxUtils.throttle(fn, delay)               - Throttle a function
 *   NxUtils.copyToClipboard(text)             - Copy text to clipboard
 *   NxUtils.truncate(text, length)            - Truncate with ellipsis
 *   NxUtils.slugify(text)                     - Convert to URL slug
 *   NxUtils.generateId(prefix)                - Generate unique ID
 *   NxUtils.isEmpty(value)                    - Check if empty
 *   NxUtils.ajax(url, options)                - Fetch with CSRF
 *   NxUtils.showLoader(targetEl)              - Show spinner on element
 *   NxUtils.hideLoader(targetEl)              - Remove spinner
 *   NxUtils.scrollToElement(el)               - Smooth scroll to element
 *   NxUtils.getQueryParam(name)               - Get URL query param
 *   NxUtils.setQueryParam(name, value)        - Set URL query param
 *
 * Usage:
 *   NxUtils.formatCurrency(1500.50);           // 'R 1,500.50'
 *   NxUtils.formatDate(new Date(), 'j M Y');   // '19 Jun 2026'
 *   NxUtils.ajax('/api/persons', { method: 'POST', body: data });
 *
 * NexCore Africa Proprietary Limited
 * www.nexcore.africa
 * ============================================================
 */

var NxUtils = {


    /* =======================================================
       MONTH NAMES
       Short month names for date formatting.
       ======================================================= */
    _months: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],

    /* Full month names */
    _monthsFull: ['January','February','March','April','May','June','July','August','September','October','November','December'],


    /* =======================================================
       FORMAT DATE
       Format a Date object or date string to NexCore display
       format. Default format: 'j M Y' (e.g. '5 Jun 2026').

       Supported tokens:
         j - Day of month (no leading zero)
         d - Day of month (2-digit with leading zero)
         M - Short month name (Jan, Feb, ...)
         F - Full month name (January, February, ...)
         m - Month number (2-digit with leading zero)
         Y - Full year (4-digit)
         y - Short year (2-digit)
         H - Hours (24h, 2-digit)
         i - Minutes (2-digit)
         s - Seconds (2-digit)

       Parameters:
         date   - Date object, date string, or timestamp
         format - Format string (default 'j M Y')

       Returns:
         Formatted date string

       Example:
         NxUtils.formatDate(new Date(), 'j M Y');     // '19 Jun 2026'
         NxUtils.formatDate('2026-01-05', 'd F Y');   // '05 January 2026'
       ======================================================= */
    formatDate: function(date, format) {
        if (!date) return '';

        var d = (date instanceof Date) ? date : new Date(date);
        if (isNaN(d.getTime())) return '';

        var fmt = format || 'j M Y';
        var self = this;

        var day = d.getDate();
        var month = d.getMonth();
        var year = d.getFullYear();
        var hours = d.getHours();
        var minutes = d.getMinutes();
        var seconds = d.getSeconds();

        var pad = function(n) { return n < 10 ? '0' + n : '' + n; };

        var result = fmt
            .replace(/j/g, '\x01')
            .replace(/d/g, '\x02')
            .replace(/M/g, '\x03')
            .replace(/F/g, '\x04')
            .replace(/m/g, '\x05')
            .replace(/Y/g, '\x06')
            .replace(/y/g, '\x07')
            .replace(/H/g, '\x08')
            .replace(/i/g, '\x09')
            .replace(/s/g, '\x0A');

        result = result
            .replace(/\x01/g, '' + day)
            .replace(/\x02/g, pad(day))
            .replace(/\x03/g, self._months[month])
            .replace(/\x04/g, self._monthsFull[month])
            .replace(/\x05/g, pad(month + 1))
            .replace(/\x06/g, '' + year)
            .replace(/\x07/g, ('' + year).slice(-2))
            .replace(/\x08/g, pad(hours))
            .replace(/\x09/g, pad(minutes))
            .replace(/\x0A/g, pad(seconds));

        return result;
    },


    /* =======================================================
       FORMAT CURRENCY
       Format a number as a currency string. Defaults to
       South African Rand (ZAR) with 2 decimal places.

       Parameters:
         amount   - Number to format
         currency - Currency symbol (default 'R')
         decimals - Decimal places (default 2)

       Returns:
         Formatted currency string (e.g. 'R 1,500.00')

       Example:
         NxUtils.formatCurrency(1500.5);          // 'R 1,500.50'
         NxUtils.formatCurrency(250, '$', 2);     // '$ 250.00'
       ======================================================= */
    formatCurrency: function(amount, currency, decimals) {
        var cur = currency || 'R';
        var dec = (decimals !== undefined) ? decimals : 2;
        var num = parseFloat(amount);

        if (isNaN(num)) return cur + ' 0.' + '0'.repeat(dec);

        var isNegative = num < 0;
        num = Math.abs(num);

        var formatted = this.formatNumber(num, dec);
        return (isNegative ? '-' : '') + cur + ' ' + formatted;
    },


    /* =======================================================
       FORMAT NUMBER
       Format a number with thousand separators and optional
       decimal places.

       Parameters:
         num      - Number to format
         decimals - Decimal places (default 0)

       Returns:
         Formatted number string (e.g. '1,500.00')

       Example:
         NxUtils.formatNumber(1500000, 2);   // '1,500,000.00'
         NxUtils.formatNumber(42);           // '42'
       ======================================================= */
    formatNumber: function(num, decimals) {
        var dec = (decimals !== undefined) ? decimals : 0;
        var n = parseFloat(num);
        if (isNaN(n)) return '0';

        var fixed = n.toFixed(dec);
        var parts = fixed.split('.');
        var intPart = parts[0];
        var decPart = parts[1] || '';

        /* Add thousand separators */
        var formatted = '';
        var count = 0;
        for (var i = intPart.length - 1; i >= 0; i--) {
            if (count > 0 && count % 3 === 0 && intPart[i] !== '-') {
                formatted = ',' + formatted;
            }
            formatted = intPart[i] + formatted;
            count++;
        }

        return decPart ? formatted + '.' + decPart : formatted;
    },


    /* =======================================================
       DEBOUNCE
       Returns a function that delays invoking fn until after
       delay milliseconds have elapsed since the last call.

       Parameters:
         fn    - Function to debounce
         delay - Delay in milliseconds (default 300)

       Returns:
         Debounced function

       Example:
         var search = NxUtils.debounce(function(text) {
             NxTables.filter('table', text);
         }, 400);
         input.addEventListener('input', function() { search(this.value); });
       ======================================================= */
    debounce: function(fn, delay) {
        var timer = null;
        var ms = delay || 300;

        return function() {
            var context = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() {
                fn.apply(context, args);
            }, ms);
        };
    },


    /* =======================================================
       THROTTLE
       Returns a function that fires at most once every delay
       milliseconds, regardless of how often it is called.

       Parameters:
         fn    - Function to throttle
         delay - Minimum interval in milliseconds (default 300)

       Returns:
         Throttled function

       Example:
         var onScroll = NxUtils.throttle(function() {
             console.log('scrolled');
         }, 200);
         window.addEventListener('scroll', onScroll);
       ======================================================= */
    throttle: function(fn, delay) {
        var lastCall = 0;
        var ms = delay || 300;

        return function() {
            var now = Date.now();
            if (now - lastCall >= ms) {
                lastCall = now;
                fn.apply(this, arguments);
            }
        };
    },


    /* =======================================================
       COPY TO CLIPBOARD
       Copy a text string to the clipboard. Shows a brief
       visual indicator if NxAlert is available.

       Parameters:
         text - String to copy

       Example:
         NxUtils.copyToClipboard('some-reference-code');
       ======================================================= */
    copyToClipboard: function(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof NxAlert !== 'undefined') {
                    NxAlert.success('Copied', 'Text copied to clipboard.');
                }
            });
        } else {
            /* Fallback for older browsers */
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px;opacity:0;';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                if (typeof NxAlert !== 'undefined') {
                    NxAlert.success('Copied', 'Text copied to clipboard.');
                }
            } catch (e) {
                if (typeof NxAlert !== 'undefined') {
                    NxAlert.error('Error', 'Failed to copy text.');
                }
            }
            document.body.removeChild(textarea);
        }
    },


    /* =======================================================
       TRUNCATE
       Truncate a string to a maximum length and append an
       ellipsis if it was shortened.

       Parameters:
         text   - String to truncate
         length - Maximum length (default 50)

       Returns:
         Truncated string

       Example:
         NxUtils.truncate('A very long name here', 15);  // 'A very long nam...'
       ======================================================= */
    truncate: function(text, length) {
        if (!text) return '';
        var max = length || 50;
        if (text.length <= max) return text;
        return text.substring(0, max) + '...';
    },


    /* =======================================================
       SLUGIFY
       Convert a text string to a URL-safe slug.
       Lowercases, replaces spaces with hyphens, removes
       special characters.

       Parameters:
         text - String to slugify

       Returns:
         URL-safe slug string

       Example:
         NxUtils.slugify('Hello World Test!');  // 'hello-world-test'
       ======================================================= */
    slugify: function(text) {
        if (!text) return '';
        return text
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    },


    /* =======================================================
       GENERATE ID
       Generate a unique identifier string with an optional
       prefix. Uses random hex characters.

       Parameters:
         prefix - Optional prefix (default 'nx')

       Returns:
         ID string (e.g. 'nx-a3f9c1')

       Example:
         NxUtils.generateId();          // 'nx-a3f9c1'
         NxUtils.generateId('field');   // 'field-b7e2d4'
       ======================================================= */
    generateId: function(prefix) {
        var pfx = prefix || 'nx';
        var hex = '';
        for (var i = 0; i < 6; i++) {
            hex += Math.floor(Math.random() * 16).toString(16);
        }
        return pfx + '-' + hex;
    },


    /* =======================================================
       IS EMPTY
       Check if a value is null, undefined, empty string,
       or empty array.

       Parameters:
         value - Value to check

       Returns:
         Boolean

       Example:
         NxUtils.isEmpty('');        // true
         NxUtils.isEmpty(null);      // true
         NxUtils.isEmpty([]);        // true
         NxUtils.isEmpty('hello');   // false
       ======================================================= */
    isEmpty: function(value) {
        if (value === null || value === undefined) return true;
        if (typeof value === 'string' && value.trim() === '') return true;
        if (Array.isArray(value) && value.length === 0) return true;
        return false;
    },


    /* =======================================================
       AJAX
       Fetch wrapper with automatic CSRF token injection,
       JSON defaults, and error handling. Reads the CSRF
       token from the meta tag named 'csrf-token'.

       Parameters:
         url     - Request URL
         options - Optional config:
                   .method  - HTTP method (default 'GET')
                   .body    - Request body (object for JSON)
                   .headers - Additional headers
                   .raw     - If true, return raw Response

       Returns:
         Promise that resolves to parsed JSON

       Example:
         NxUtils.ajax('/api/persons', {
             method: 'POST',
             body: { first_name: 'John', last_name: 'Doe' }
         }).then(function(data) {
             console.log(data);
         });
       ======================================================= */
    ajax: function(url, options) {
        var opts = options || {};
        var method = (opts.method || 'GET').toUpperCase();

        /* Build headers with CSRF token */
        var headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        /* Get CSRF token from meta tag */
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
        }

        /* Merge custom headers */
        if (opts.headers) {
            for (var key in opts.headers) {
                if (opts.headers.hasOwnProperty(key)) {
                    headers[key] = opts.headers[key];
                }
            }
        }

        var fetchConfig = {
            method: method,
            headers: headers,
            credentials: 'same-origin'
        };

        /* Handle body - auto-stringify objects as JSON */
        if (opts.body && method !== 'GET') {
            if (opts.body instanceof FormData) {
                fetchConfig.body = opts.body;
                /* Let browser set Content-Type for FormData */
            } else if (typeof opts.body === 'object') {
                headers['Content-Type'] = 'application/json';
                fetchConfig.body = JSON.stringify(opts.body);
            } else {
                fetchConfig.body = opts.body;
            }
        }

        fetchConfig.headers = headers;

        return fetch(url, fetchConfig).then(function(response) {
            if (opts.raw) return response;

            if (!response.ok) {
                return response.json().then(function(errorData) {
                    var error = new Error(errorData.message || 'Request failed');
                    error.status = response.status;
                    error.data = errorData;
                    throw error;
                }).catch(function(e) {
                    if (e.status) throw e;
                    var error = new Error('Request failed with status ' + response.status);
                    error.status = response.status;
                    throw error;
                });
            }

            return response.json();
        });
    },


    /* =======================================================
       SHOW LOADER
       Display a loading spinner overlay on a target element.
       The element gets position: relative and an overlay
       child with a spinner.

       Parameters:
         targetEl - DOM element to show loader on

       Example:
         NxUtils.showLoader(document.getElementById('formCard'));
       ======================================================= */
    showLoader: function(targetEl) {
        if (!targetEl) return;

        /* Remove existing loader first */
        this.hideLoader(targetEl);

        targetEl.style.position = 'relative';

        var overlay = document.createElement('div');
        overlay.className = 'nx-utils-loader-overlay';
        overlay.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;'
            + 'background:rgba(10,14,26,0.6);display:flex;align-items:center;'
            + 'justify-content:center;z-index:10;border-radius:inherit;';
        overlay.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:24px;color:#00d4ff;"></i>';

        targetEl.appendChild(overlay);
    },


    /* =======================================================
       HIDE LOADER
       Remove the loading spinner overlay from a target element.

       Parameters:
         targetEl - DOM element to remove loader from

       Example:
         NxUtils.hideLoader(document.getElementById('formCard'));
       ======================================================= */
    hideLoader: function(targetEl) {
        if (!targetEl) return;

        var overlays = targetEl.querySelectorAll('.nx-utils-loader-overlay');
        for (var i = 0; i < overlays.length; i++) {
            overlays[i].parentNode.removeChild(overlays[i]);
        }
    },


    /* =======================================================
       SCROLL TO ELEMENT
       Smoothly scroll the page to bring a target element
       into view, with a small offset from the top.

       Parameters:
         el - DOM element or CSS selector string

       Example:
         NxUtils.scrollToElement('#contactSection');
         NxUtils.scrollToElement(document.getElementById('errors'));
       ======================================================= */
    scrollToElement: function(el) {
        var target = (typeof el === 'string') ? document.querySelector(el) : el;
        if (!target) return;

        var offset = 80;
        var top = target.getBoundingClientRect().top + window.pageYOffset - offset;

        window.scrollTo({
            top: top,
            behavior: 'smooth'
        });
    },


    /* =======================================================
       GET QUERY PARAM
       Read a URL query parameter by name from the current
       page URL.

       Parameters:
         name - Parameter name

       Returns:
         Parameter value string, or null if not found

       Example:
         var page = NxUtils.getQueryParam('page');   // '3'
       ======================================================= */
    getQueryParam: function(name) {
        var params = new URLSearchParams(window.location.search);
        return params.get(name);
    },


    /* =======================================================
       SET QUERY PARAM
       Update a URL query parameter without reloading the
       page. Uses history.replaceState to update the URL bar.

       Parameters:
         name  - Parameter name
         value - Parameter value (null to remove)

       Example:
         NxUtils.setQueryParam('page', '3');
         NxUtils.setQueryParam('filter', null);  // removes the param
       ======================================================= */
    setQueryParam: function(name, value) {
        var params = new URLSearchParams(window.location.search);

        if (value === null || value === undefined) {
            params.delete(name);
        } else {
            params.set(name, value);
        }

        var newUrl = window.location.pathname;
        var paramString = params.toString();
        if (paramString) {
            newUrl += '?' + paramString;
        }
        if (window.location.hash) {
            newUrl += window.location.hash;
        }

        window.history.replaceState({}, '', newUrl);
    }
};
