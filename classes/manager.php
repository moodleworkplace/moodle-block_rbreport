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

namespace block_rbreport;

use core_reportbuilder\local\helpers\audience;
use moodle_url;

/**
 * Class manager.
 *
 * @package     block_rbreport
 * @author      Mikel Martín <mikel@moodle.com>
 * @copyright   2021 Moodle Pty Ltd <support@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * List of available reports
     *
     * @uses \tool_tenant\local\block_rbreport::get_extra_sql_for_core_reports
     *
     * @param string $pagetype
     * @param string|null $subpage
     * @param moodle_url $pageurl
     * @param string|null $search
     * @param int $limitfrom
     * @param int $limitnum
     * @return string[]
     */
    public function get_report_options(
        string $pagetype,
        ?string $subpage,
        moodle_url $pageurl,
        ?string $search = null,
        int $limitfrom = 0,
        int $limitnum = 0,
    ): array {
        global $DB;
        $sql = 'type=:type';
        $params = ['type' => \core_reportbuilder\local\report\base::TYPE_CUSTOM_REPORT];

        [$tsql, $tparams] = component_class_callback(
            \tool_tenant\local\block_rbreport::class,
            'get_extra_sql_for_core_reports',
            [$pagetype, $subpage, $pageurl],
            ['1=1', []],
        );

        [$asql, $aparams] = audience::user_reports_list_access_sql('r');

        // If searching, add LIKE condition.
        $searchsql = '';
        if ((string) $search !== '') {
            $searchsql = ' AND ' . $DB->sql_like('r.name', ':search', false, false);
            $params['search'] = '%' . $DB->sql_like_escape($search) . '%';
        }

        $records = $DB->get_records_sql(
            'SELECT * FROM {reportbuilder_report} r
            WHERE ' . $sql . ' AND ' . $tsql . ' AND ' . $asql . $searchsql . '
            ORDER BY name, id',
            $params + $aparams + $tparams,
            $limitfrom,
            $limitnum,
        );
        $res = [];
        foreach ($records as $record) {
            $persistent = new \core_reportbuilder\local\models\report(0, $record);
            $res[$record->id] = $persistent->get_formatted_name();
        };
        return $res;
    }
}
