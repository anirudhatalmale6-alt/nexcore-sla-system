/*
 * ============================================================
 * NxForms - NexCore Enterprise Form Utilities
 * ============================================================
 *
 * Component:     nexcore_main_forms
 * Version:       1.0
 * Location:      /public/nexcore/master/nexcore_main_js/nexcore_main_forms.js
 *
 * Dependencies:
 *   - nexcore_main_forms.css (must be loaded in the <head>)
 *   - Select2 v4 (optional, for initSelect2)
 *   - flatpickr (optional, for initFlatpickr)
 *
 * Available Methods:
 *   NxForms.initSelect2(selector, options)    - Initialise Select2 with dark theme
 *   NxForms.initFlatpickr(selector, options)  - Initialise flatpickr with NexCore defaults
 *   NxForms.initPhoneInput(selector)          - Format phone number inputs
 *   NxForms.validate(formEl)                  - Validate required fields
 *   NxForms.clearForm(formEl)                 - Reset all form inputs
 *   NxForms.setLoading(btn, loading)          - Toggle button loading state
 *   NxForms.getFormData(formEl)               - Get form values as object
 *   NxForms.showFieldError(inputEl, message)  - Show inline error on a field
 *   NxForms.clearFieldError(inputEl)          - Remove inline error from a field
 *
 * Usage:
 *   NxForms.initSelect2('.nx-select2');
 *   NxForms.initFlatpickr('.nx-datepicker');
 *   var result = NxForms.validate(document.getElementById('myForm'));
 *   if (result.valid) { ... }
 *
 * NexCore Africa Proprietary Limited
 * www.nexcore.africa
 * ============================================================
 */

var NxForms = {


    /* =======================================================
       INIT SELECT2
       Initialise Select2 on matching elements with NexCore
       dark theme defaults. Pass options to override.

       Parameters:
         selector - CSS selector string (e.g. '.nx-select2')
         options  - Optional Select2 configuration overrides

       Example:
         NxForms.initSelect2('.nx-select2', { allowClear: true });
       ======================================================= */
    initSelect2: function(selector, options) {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            return;
        }

        var defaults = {
            theme: 'default',
            width: '100%',
            placeholder: 'Select an option...',
            allowClear: false,
            minimumResultsForSearch: 10,
            dropdownCssClass: 'nx-select2-dropdown'
        };

        var config = {};
        var key;
        for (key in defaults) {
            if (defaults.hasOwnProperty(key)) {
                config[key] = defaults[key];
            }
        }
        if (options) {
            for (key in options) {
                if (options.hasOwnProperty(key)) {
                    config[key] = options[key];
                }
            }
        }

        jQuery(selector).each(function() {
            if (!jQuery(this).data('select2')) {
                jQuery(this).select2(config);
            }
        });
    },


    /* =======================================================
       INIT FLATPICKR
       Initialise flatpickr date pickers with NexCore defaults.
       Default format: 'j M Y' (e.g. 5 Jun 2026).

       Parameters:
         selector - CSS selector string (e.g. '.nx-datepicker')
         options  - Optional flatpickr configuration overrides

       Example:
         NxForms.initFlatpickr('.nx-datepicker');
         NxForms.initFlatpickr('#startDate', { minDate: 'today' });
       ======================================================= */
    initFlatpickr: function(selector, options) {
        if (typeof flatpickr === 'undefined') {
            return;
        }

        var defaults = {
            dateFormat: 'j M Y',
            altInput: true,
            altFormat: 'j M Y',
            disableMobile: true,
            allowInput: false
        };

        var config = {};
        var key;
        for (key in defaults) {
            if (defaults.hasOwnProperty(key)) {
                config[key] = defaults[key];
            }
        }
        if (options) {
            for (key in options) {
                if (options.hasOwnProperty(key)) {
                    config[key] = options[key];
                }
            }
        }

        var elements = document.querySelectorAll(selector);
        for (var i = 0; i < elements.length; i++) {
            if (!elements[i]._flatpickr) {
                flatpickr(elements[i], config);
            }
        }
    },


    /* =======================================================
       INIT PHONE INPUT
       Auto-format phone number inputs as the user types.
       Strips non-numeric characters and applies spacing.

       Parameters:
         selector - CSS selector string (e.g. '.nx-phone')

       Example:
         NxForms.initPhoneInput('.nx-phone');
       ======================================================= */
    initPhoneInput: function(selector) {
        var elements = document.querySelectorAll(selector);

        for (var i = 0; i < elements.length; i++) {
            elements[i].addEventListener('input', function() {
                var digits = this.value.replace(/\D/g, '');

                /* Format as 3-3-4 for 10-digit numbers */
                if (digits.length <= 3) {
                    this.value = digits;
                } else if (digits.length <= 6) {
                    this.value = digits.slice(0, 3) + ' ' + digits.slice(3);
                } else {
                    this.value = digits.slice(0, 3) + ' ' + digits.slice(3, 6) + ' ' + digits.slice(6, 10);
                }
            });

            /* Only allow digits, spaces, plus, and dashes */
            elements[i].addEventListener('keypress', function(e) {
                var char = String.fromCharCode(e.which || e.keyCode);
                if (!/[\d\s\+\-]/.test(char)) {
                    e.preventDefault();
                }
            });
        }
    },


    /* =======================================================
       VALIDATE
       Check all required fields in a form. Returns an object
       with valid (boolean) and errors (array of messages).
       Also highlights invalid fields visually.

       Parameters:
         formEl - DOM element of the form to validate

       Returns:
         { valid: true/false, errors: ['Field X is required', ...] }

       Example:
         var result = NxForms.validate(document.getElementById('myForm'));
         if (!result.valid) { NxAlert.warning('Validation', result.errors.join('<br>')); }
       ======================================================= */
    validate: function(formEl) {
        var errors = [];
        var fields = formEl.querySelectorAll('[required]');

        /* Clear previous errors first */
        for (var i = 0; i < fields.length; i++) {
            this.clearFieldError(fields[i]);
        }

        /* Check each required field */
        for (var j = 0; j < fields.length; j++) {
            var field = fields[j];
            var value = (field.value || '').trim();
            var label = '';

            /* Try to find a label for the field */
            var labelEl = formEl.querySelector('label[for="' + field.id + '"]');
            if (labelEl) {
                label = labelEl.textContent.trim();
            } else if (field.getAttribute('placeholder')) {
                label = field.getAttribute('placeholder');
            } else if (field.name) {
                label = field.name.replace(/[_-]/g, ' ');
            } else {
                label = 'Field ' + (j + 1);
            }

            /* Check if empty */
            if (value === '') {
                errors.push(label + ' is required');
                this.showFieldError(field, label + ' is required');
                continue;
            }

            /* Check email format */
            if (field.type === 'email' && value !== '') {
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(value)) {
                    errors.push(label + ' must be a valid email address');
                    this.showFieldError(field, 'Must be a valid email address');
                }
            }

            /* Check min length */
            var minLen = field.getAttribute('minlength');
            if (minLen && value.length < parseInt(minLen, 10)) {
                errors.push(label + ' must be at least ' + minLen + ' characters');
                this.showFieldError(field, 'Must be at least ' + minLen + ' characters');
            }
        }

        return {
            valid: errors.length === 0,
            errors: errors
        };
    },


    /* =======================================================
       CLEAR FORM
       Reset all input fields in a form to their default state.
       Also clears Select2 and flatpickr instances.

       Parameters:
         formEl - DOM element of the form to clear

       Example:
         NxForms.clearForm(document.getElementById('myForm'));
       ======================================================= */
    clearForm: function(formEl) {
        /* Reset native form */
        formEl.reset();

        /* Clear all field errors */
        var errorFields = formEl.querySelectorAll('.nx-field-has-error');
        for (var i = 0; i < errorFields.length; i++) {
            this.clearFieldError(errorFields[i]);
        }

        /* Reset Select2 instances */
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
            jQuery(formEl).find('select').each(function() {
                if (jQuery(this).data('select2')) {
                    jQuery(this).val(null).trigger('change');
                }
            });
        }

        /* Clear flatpickr instances */
        var datePickers = formEl.querySelectorAll('input[type="text"]');
        for (var j = 0; j < datePickers.length; j++) {
            if (datePickers[j]._flatpickr) {
                datePickers[j]._flatpickr.clear();
            }
        }
    },


    /* =======================================================
       SET LOADING
       Toggle the loading state of a button. When loading,
       the button is disabled and shows a spinner icon.
       When not loading, the original content is restored.

       Parameters:
         btn     - DOM element of the button
         loading - Boolean: true to show loading, false to restore

       Example:
         NxForms.setLoading(saveBtn, true);
         // ... after ajax completes ...
         NxForms.setLoading(saveBtn, false);
       ======================================================= */
    setLoading: function(btn, loading) {
        if (!btn) return;

        if (loading) {
            /* Store original content */
            btn.setAttribute('data-nx-original-html', btn.innerHTML);
            btn.setAttribute('data-nx-original-width', btn.style.width || '');
            btn.style.width = btn.offsetWidth + 'px';

            /* Replace with spinner */
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
        } else {
            /* Restore original content */
            var originalHtml = btn.getAttribute('data-nx-original-html');
            if (originalHtml !== null) {
                btn.innerHTML = originalHtml;
            }
            btn.style.width = btn.getAttribute('data-nx-original-width') || '';
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.pointerEvents = '';

            btn.removeAttribute('data-nx-original-html');
            btn.removeAttribute('data-nx-original-width');
        }
    },


    /* =======================================================
       GET FORM DATA
       Collect all named input values from a form and return
       as a plain JavaScript object. Handles text, select,
       checkbox, radio, and textarea elements.

       Parameters:
         formEl - DOM element of the form

       Returns:
         { fieldName: 'value', ... }

       Example:
         var data = NxForms.getFormData(document.getElementById('myForm'));
         console.log(data.first_name);
       ======================================================= */
    getFormData: function(formEl) {
        var data = {};
        var elements = formEl.elements;

        for (var i = 0; i < elements.length; i++) {
            var el = elements[i];
            var name = el.name;

            if (!name) continue;

            if (el.type === 'checkbox') {
                data[name] = el.checked ? 1 : 0;
            } else if (el.type === 'radio') {
                if (el.checked) {
                    data[name] = el.value;
                }
            } else if (el.tagName === 'SELECT' && el.multiple) {
                var selected = [];
                for (var j = 0; j < el.options.length; j++) {
                    if (el.options[j].selected) {
                        selected.push(el.options[j].value);
                    }
                }
                data[name] = selected;
            } else {
                data[name] = el.value;
            }
        }

        return data;
    },


    /* =======================================================
       SHOW FIELD ERROR
       Display an inline error message below an input field.
       Adds a red border to the input and a small error label
       beneath it.

       Parameters:
         inputEl - DOM element of the input field
         message - Error message string to display

       Example:
         NxForms.showFieldError(emailInput, 'Please enter a valid email');
       ======================================================= */
    showFieldError: function(inputEl, message) {
        if (!inputEl) return;

        /* Mark field as having an error */
        inputEl.classList.add('nx-field-has-error');
        inputEl.style.borderColor = '#dc2626';

        /* Remove existing error message if any */
        this.clearFieldError(inputEl);
        inputEl.classList.add('nx-field-has-error');
        inputEl.style.borderColor = '#dc2626';

        /* Create error message element */
        var errorEl = document.createElement('div');
        errorEl.className = 'nx-field-error-msg';
        errorEl.style.cssText = 'color:#dc2626;font-size:11px;font-weight:600;'
            + 'font-family:Montserrat,sans-serif;margin-top:4px;letter-spacing:0.3px;';
        errorEl.textContent = message;

        /* Insert after the input (or after its parent if wrapped) */
        var parent = inputEl.parentNode;
        if (inputEl.nextSibling) {
            parent.insertBefore(errorEl, inputEl.nextSibling);
        } else {
            parent.appendChild(errorEl);
        }
    },


    /* =======================================================
       CLEAR FIELD ERROR
       Remove the inline error message and red border from
       an input field.

       Parameters:
         inputEl - DOM element of the input field

       Example:
         NxForms.clearFieldError(emailInput);
       ======================================================= */
    clearFieldError: function(inputEl) {
        if (!inputEl) return;

        inputEl.classList.remove('nx-field-has-error');
        inputEl.style.borderColor = '';

        /* Remove error message element */
        var parent = inputEl.parentNode;
        if (parent) {
            var errorMsgs = parent.querySelectorAll('.nx-field-error-msg');
            for (var i = 0; i < errorMsgs.length; i++) {
                errorMsgs[i].parentNode.removeChild(errorMsgs[i]);
            }
        }
    }
};
