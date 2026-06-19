/*
 * ============================================================
 * NxTables - NexCore Enterprise Table Utilities
 * ============================================================
 *
 * Component:     nexcore_main_tables
 * Version:       1.0
 * Location:      /public/nexcore/master/nexcore_main_js/nexcore_main_tables.js
 *
 * Dependencies:
 *   - nexcore_main_tables.css (must be loaded in the <head>)
 *
 * Available Methods:
 *   NxTables.init(tableId, options)              - Initialise a table
 *   NxTables.sort(tableId, columnIndex, dir)     - Sort by column
 *   NxTables.filter(tableId, searchText)         - Filter rows by text
 *   NxTables.paginate(tableId, page, perPage)    - Show a specific page
 *   NxTables.getSelectedRows(tableId)            - Get checked rows
 *   NxTables.selectAll(tableId, checked)         - Toggle all checkboxes
 *   NxTables.exportCsv(tableId, filename)        - Export as CSV
 *   NxTables.showLoading(tableId)                - Show loading overlay
 *   NxTables.hideLoading(tableId)                - Hide loading overlay
 *   NxTables.showEmpty(tableId, message)         - Show empty state
 *   NxTables.refresh(tableId)                    - Re-apply sort/filter
 *
 * Usage:
 *   NxTables.init('personsTable', { perPage: 25, sortable: true });
 *   NxTables.filter('personsTable', 'John');
 *   NxTables.sort('personsTable', 2, 'asc');
 *
 * NexCore Africa Proprietary Limited
 * www.nexcore.africa
 * ============================================================
 */

var NxTables = {


    /* =======================================================
       INTERNAL STATE
       Stores configuration and state for each initialised
       table, keyed by table ID.
       ======================================================= */
    _tables: {},


    /* =======================================================
       INIT
       Initialise a table with sorting, filtering, and
       pagination support. Stores all rows internally for
       fast client-side operations.

       Parameters:
         tableId - ID attribute of the <table> element
         options - Optional config:
                   .perPage   - Rows per page (default 25)
                   .sortable  - Enable column sort (default true)
                   .filterable - Enable filter (default true)

       Example:
         NxTables.init('personsTable', { perPage: 25 });
       ======================================================= */
    init: function(tableId, options) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var opts = options || {};
        var tbody = table.querySelector('tbody');
        var rows = tbody ? Array.prototype.slice.call(tbody.rows) : [];

        this._tables[tableId] = {
            table: table,
            tbody: tbody,
            allRows: rows,
            filteredRows: rows.slice(),
            perPage: opts.perPage || 25,
            currentPage: 1,
            sortColumn: -1,
            sortDirection: 'asc',
            sortable: opts.sortable !== false,
            filterable: opts.filterable !== false,
            searchText: ''
        };

        /* Bind sortable headers */
        if (this._tables[tableId].sortable) {
            this._bindSortHeaders(tableId);
        }

        /* Apply initial pagination */
        this._applyView(tableId);
    },


    /* =======================================================
       BIND SORT HEADERS (INTERNAL)
       Attach click handlers to <th> elements in the thead.
       Clicking toggles sort direction on that column.
       ======================================================= */
    _bindSortHeaders: function(tableId) {
        var state = this._tables[tableId];
        if (!state) return;

        var headers = state.table.querySelectorAll('thead th');
        var self = this;

        for (var i = 0; i < headers.length; i++) {
            (function(colIndex) {
                headers[colIndex].style.cursor = 'pointer';
                headers[colIndex].addEventListener('click', function() {
                    var dir = 'asc';
                    if (state.sortColumn === colIndex && state.sortDirection === 'asc') {
                        dir = 'desc';
                    }
                    self.sort(tableId, colIndex, dir);
                });
            })(i);
        }
    },


    /* =======================================================
       SORT
       Sort the filtered rows by a specific column index.
       Supports text and numeric comparison.

       Parameters:
         tableId     - ID of the table
         columnIndex - Zero-based column index
         direction   - 'asc' or 'desc'

       Example:
         NxTables.sort('personsTable', 1, 'asc');
       ======================================================= */
    sort: function(tableId, columnIndex, direction) {
        var state = this._tables[tableId];
        if (!state) return;

        state.sortColumn = columnIndex;
        state.sortDirection = direction || 'asc';

        state.filteredRows.sort(function(a, b) {
            var cellA = a.cells[columnIndex] ? a.cells[columnIndex].textContent.trim().toLowerCase() : '';
            var cellB = b.cells[columnIndex] ? b.cells[columnIndex].textContent.trim().toLowerCase() : '';

            /* Try numeric comparison first */
            var numA = parseFloat(cellA.replace(/[^0-9.\-]/g, ''));
            var numB = parseFloat(cellB.replace(/[^0-9.\-]/g, ''));

            if (!isNaN(numA) && !isNaN(numB)) {
                return direction === 'asc' ? numA - numB : numB - numA;
            }

            /* Fall back to text comparison */
            if (cellA < cellB) return direction === 'asc' ? -1 : 1;
            if (cellA > cellB) return direction === 'asc' ? 1 : -1;
            return 0;
        });

        state.currentPage = 1;
        this._applyView(tableId);
    },


    /* =======================================================
       FILTER
       Filter table rows by a search string. Matches against
       all cell text in each row (case-insensitive).

       Parameters:
         tableId    - ID of the table
         searchText - Text to search for (empty = show all)

       Example:
         NxTables.filter('personsTable', 'John');
       ======================================================= */
    filter: function(tableId, searchText) {
        var state = this._tables[tableId];
        if (!state) return;

        state.searchText = (searchText || '').toLowerCase().trim();

        if (state.searchText === '') {
            state.filteredRows = state.allRows.slice();
        } else {
            state.filteredRows = [];
            for (var i = 0; i < state.allRows.length; i++) {
                var rowText = state.allRows[i].textContent.toLowerCase();
                if (rowText.indexOf(state.searchText) !== -1) {
                    state.filteredRows.push(state.allRows[i]);
                }
            }
        }

        /* Re-apply sort if active */
        if (state.sortColumn >= 0) {
            this.sort(tableId, state.sortColumn, state.sortDirection);
            return;
        }

        state.currentPage = 1;
        this._applyView(tableId);
    },


    /* =======================================================
       PAGINATE
       Jump to a specific page of results.

       Parameters:
         tableId - ID of the table
         page    - Page number (1-based)
         perPage - Optional override for rows per page

       Example:
         NxTables.paginate('personsTable', 3);
       ======================================================= */
    paginate: function(tableId, page, perPage) {
        var state = this._tables[tableId];
        if (!state) return;

        if (perPage) {
            state.perPage = perPage;
        }

        var totalPages = Math.ceil(state.filteredRows.length / state.perPage) || 1;
        state.currentPage = Math.max(1, Math.min(page, totalPages));

        this._applyView(tableId);
    },


    /* =======================================================
       APPLY VIEW (INTERNAL)
       Renders the current page of filtered/sorted rows
       into the table body. Hides/shows rows accordingly.
       ======================================================= */
    _applyView: function(tableId) {
        var state = this._tables[tableId];
        if (!state || !state.tbody) return;

        var start = (state.currentPage - 1) * state.perPage;
        var end = start + state.perPage;

        /* Remove all rows from tbody */
        while (state.tbody.firstChild) {
            state.tbody.removeChild(state.tbody.firstChild);
        }

        /* Show empty state if no rows */
        if (state.filteredRows.length === 0) {
            this.showEmpty(tableId, 'No records found');
            return;
        }

        /* Append only the rows for the current page */
        for (var i = start; i < end && i < state.filteredRows.length; i++) {
            state.tbody.appendChild(state.filteredRows[i]);
        }
    },


    /* =======================================================
       GET SELECTED ROWS
       Return an array of row elements that have a checked
       checkbox in the first column.

       Parameters:
         tableId - ID of the table

       Returns:
         Array of <tr> DOM elements

       Example:
         var selected = NxTables.getSelectedRows('personsTable');
         console.log(selected.length + ' rows selected');
       ======================================================= */
    getSelectedRows: function(tableId) {
        var state = this._tables[tableId];
        if (!state) return [];

        var selected = [];
        var checkboxes = state.table.querySelectorAll('tbody input[type="checkbox"]');

        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                var row = checkboxes[i].closest('tr');
                if (row) {
                    selected.push(row);
                }
            }
        }

        return selected;
    },


    /* =======================================================
       SELECT ALL
       Toggle all checkboxes in the table body.

       Parameters:
         tableId - ID of the table
         checked - Boolean: true to check all, false to uncheck

       Example:
         NxTables.selectAll('personsTable', true);
       ======================================================= */
    selectAll: function(tableId, checked) {
        var state = this._tables[tableId];
        if (!state) return;

        var checkboxes = state.table.querySelectorAll('tbody input[type="checkbox"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = !!checked;
        }

        /* Also update the header checkbox if one exists */
        var headerCb = state.table.querySelector('thead input[type="checkbox"]');
        if (headerCb) {
            headerCb.checked = !!checked;
        }
    },


    /* =======================================================
       EXPORT CSV
       Export the currently visible (filtered) rows as a
       CSV file download.

       Parameters:
         tableId  - ID of the table
         filename - Download filename (default 'export.csv')

       Example:
         NxTables.exportCsv('personsTable', 'persons_export.csv');
       ======================================================= */
    exportCsv: function(tableId, filename) {
        var state = this._tables[tableId];
        if (!state) return;

        var csv = [];

        /* Header row */
        var headers = state.table.querySelectorAll('thead th');
        var headerRow = [];
        for (var h = 0; h < headers.length; h++) {
            var headerText = headers[h].textContent.trim();
            headerRow.push('"' + headerText.replace(/"/g, '""') + '"');
        }
        csv.push(headerRow.join(','));

        /* Data rows (filtered set) */
        for (var i = 0; i < state.filteredRows.length; i++) {
            var cells = state.filteredRows[i].cells;
            var dataRow = [];
            for (var c = 0; c < cells.length; c++) {
                var cellText = cells[c].textContent.trim();
                dataRow.push('"' + cellText.replace(/"/g, '""') + '"');
            }
            csv.push(dataRow.join(','));
        }

        /* Trigger download */
        var csvContent = csv.join('\n');
        var blob = new Blob(['﻿' + csvContent], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename || 'export.csv';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    },


    /* =======================================================
       SHOW LOADING
       Display a loading overlay on the table. Creates an
       overlay element with a spinner if one does not exist.

       Parameters:
         tableId - ID of the table

       Example:
         NxTables.showLoading('personsTable');
       ======================================================= */
    showLoading: function(tableId) {
        var state = this._tables[tableId];
        var table = state ? state.table : document.getElementById(tableId);
        if (!table) return;

        /* Remove existing overlay */
        this.hideLoading(tableId);

        var wrap = table.closest('.nx-table-wrap') || table.parentNode;

        var overlay = document.createElement('div');
        overlay.className = 'nx-table-loading-overlay';
        overlay.setAttribute('data-nx-table-id', tableId);
        overlay.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;'
            + 'background:rgba(10,14,26,0.7);display:flex;align-items:center;'
            + 'justify-content:center;z-index:10;border-radius:12px;';
        overlay.innerHTML = '<div style="text-align:center;">'
            + '<i class="fas fa-spinner fa-spin" style="font-size:28px;color:#00d4ff;"></i>'
            + '<div style="color:rgba(255,255,255,0.5);font-size:12px;margin-top:10px;'
            + 'font-family:Montserrat,sans-serif;font-weight:600;">Loading...</div></div>';

        wrap.style.position = 'relative';
        wrap.appendChild(overlay);
    },


    /* =======================================================
       HIDE LOADING
       Remove the loading overlay from a table.

       Parameters:
         tableId - ID of the table

       Example:
         NxTables.hideLoading('personsTable');
       ======================================================= */
    hideLoading: function(tableId) {
        var overlays = document.querySelectorAll('.nx-table-loading-overlay[data-nx-table-id="' + tableId + '"]');
        for (var i = 0; i < overlays.length; i++) {
            overlays[i].parentNode.removeChild(overlays[i]);
        }
    },


    /* =======================================================
       SHOW EMPTY
       Display a centred empty state message in the table
       body area. Used when filter returns zero results or
       the table has no data.

       Parameters:
         tableId - ID of the table
         message - Message to display (default 'No records found')

       Example:
         NxTables.showEmpty('personsTable', 'No matching persons found');
       ======================================================= */
    showEmpty: function(tableId, message) {
        var state = this._tables[tableId];
        var table = state ? state.table : document.getElementById(tableId);
        if (!table) return;

        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        var colCount = table.querySelectorAll('thead th').length || 1;
        var msg = message || 'No records found';

        var tr = document.createElement('tr');
        tr.className = 'nx-table-empty-row';
        var td = document.createElement('td');
        td.colSpan = colCount;
        td.style.cssText = 'text-align:center;padding:48px 20px;color:rgba(255,255,255,0.3);'
            + 'font-size:13px;font-weight:500;font-family:Montserrat,sans-serif;';
        td.innerHTML = '<i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;'
            + 'color:rgba(255,255,255,0.15);"></i>' + msg;
        tr.appendChild(td);
        tbody.appendChild(tr);
    },


    /* =======================================================
       REFRESH
       Re-apply the current sort and filter state. Useful
       after dynamically adding or removing rows.

       Parameters:
         tableId - ID of the table

       Example:
         NxTables.refresh('personsTable');
       ======================================================= */
    refresh: function(tableId) {
        var state = this._tables[tableId];
        if (!state) return;

        /* Re-collect rows from the original set */
        if (state.searchText) {
            this.filter(tableId, state.searchText);
        } else if (state.sortColumn >= 0) {
            this.sort(tableId, state.sortColumn, state.sortDirection);
        } else {
            this._applyView(tableId);
        }
    }
};
