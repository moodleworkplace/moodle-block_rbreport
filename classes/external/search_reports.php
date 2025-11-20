<?php
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

namespace block_rbreport\external;

use core_external\external_api;
use core_external\external_multiple_structure;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External method for searching reports
 *
 * @package     block_rbreport
 * @copyright   2025 Moodle Pty Ltd <support@moodle.com>
 * @author      2025 Aleti Vinod Kumar <vinod.aleti@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_reports extends external_api {

    /**
     * External method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query'     => new external_value(PARAM_TEXT, 'Search query'),
            'pagetype'  => new external_value(PARAM_TEXT, 'Page type'),
            'pageurl'   => new external_value(PARAM_LOCALURL, 'Page URL'),
            'subpage'   => new external_value(PARAM_TEXT, 'Page subpage', VALUE_DEFAULT, null),
        ]);
    }

    /**
     * External method execution
     *
     * @param string $query
     * @param string $pagetype
     * @param string $pageurl
     * @param string|null $subpage
     * @return array[]
     */
    public static function execute(
        string $query,
        string $pagetype,
        string $pageurl,
        ?string $subpage,
    ): array {

        $params = self::validate_parameters(self::execute_parameters(), [
            'query' => $query,
            'pagetype' => $pagetype,
            'pageurl' => $pageurl,
            'subpage' => $subpage,
        ]);

        $url = new \moodle_url($params['pageurl']);

        $manager = new \block_rbreport\manager();

        // Fetch one extra row (101) to detect overflow for autocomplete.
        // If > 100 results exist, JS will show "too many to display".
        $reports = $manager->get_report_options(
            $params['pagetype'],
            $params['subpage'],
            $url,
            $params['query'],
            0,
            101,
        );
        $results = [];
        foreach ($reports as $id => $name) {
            $results[] = ['id' => $id, 'name' => $name];
        }

        return $results;
    }

    /**
     * External method return value
     *
     * @return external_value
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Report ID'),
            'name' => new external_value(PARAM_TEXT, 'Report name'),
        ]));
    }
}
