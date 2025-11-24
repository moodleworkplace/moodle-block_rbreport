// This file is part of the block_rbreport plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Module implementing form autocomplete element for report selection
 *
 * @module      block_rbreport/form_report_selector
 * @copyright   2025 Moodle Pty Ltd <support@moodle.com>
 * @author      2025 Aleti Vinod Kumar <vinod.aleti@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import Ajax from 'core/ajax';
import {getString} from 'core/str';

// Maximum number of suggestions to show.
const maxSuggestions = 100;

/**
 * Perform AJAX search for reports.
 *
 * @param {String} query
 * @param {String} pagetype
 * @param {String} pageurl
 * @param {String} subpage
 * @return {Promise}
 */
const searchReports = (query, pagetype, pageurl, subpage) => {
    return Ajax.call([{
        methodname: 'block_rbreport_search_reports',
        args: {query, pagetype, pageurl, subpage}
    }])[0];
};

/**
 * Perform search request for reports
 *
 * @param {String} selector
 * @param {String} query
 * @param {Function} success
 * @param {Function} failure
 */
export const transport = async(selector, query, success, failure) => {
    const input = document.querySelector(selector);
    const {pagetype, pageurl, subpage} = input.dataset;

    try {
        const results = await searchReports(query, pagetype, pageurl, subpage);
        if (results.length <= maxSuggestions) {
            success(results);
        } else {
            const strTooManyReports = await getString('toomanyreportstoshow', 'block_rbreport', `>${maxSuggestions}`);
            success(strTooManyReports);
        }
    } catch (e) {
        failure(e);
    }
};

/**
 * Process results from search request for reports
 *
 * @param {String} selector
 * @param {String|String[]} results
 * @return {String|String[]}
 */
export const processResults = (selector, results) => {
    if (!Array.isArray(results)) {
        return results;
    } else {
        return results.map(result => ({value: result.id, label: result.name}));
    }
};
