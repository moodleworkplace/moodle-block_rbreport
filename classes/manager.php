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

/**
 * Class manager.
 *
 * @package     block_rbreport
 * @author      Mikel Martín <mikel@moodle.com>
 * @copyright   2021 Moodle Pty Ltd <support@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_rbreport;

use core_reportbuilder\local\helpers\audience;
use core_reportbuilder\local\helpers\database;
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
     * {@see \core_reportbuilder\local\systemreports\reports_list::filter_by_allowed_reports_sql()}
     * {@see \tool_tenant\reportbuilder\local\callbacks::get_reports_list_tenant_fields() }
     * {@see \tool_tenant\reportbuilder\local\callbacks::get_reports_list_tenant_clause() }
     *
     * @param string $pagetype
     * @param string|null $subpage
     * @param moodle_url $pageurl
     * @return string[]
     */
    public function get_report_options(string $pagetype, ?string $subpage, moodle_url $pageurl): array {
        global $DB;
        $sql = 'type=:type';
        $params = ['type' => \core_reportbuilder\local\report\base::TYPE_CUSTOM_REPORT];
        [$tsql, $tparams] = component_class_callback(\tool_tenant\local\block_rbreport::class,
            'get_extra_sql_for_core_reports',
            [$pagetype, $subpage, $pageurl],
            ['1=1', []]);
        [$asql, $aparams] = $this->get_audience_sql('r');

        $records = $DB->get_records_sql(
            'SELECT * FROM {reportbuilder_report} r
            WHERE ' . $sql . ' AND ' . $tsql . ' AND ' . $asql . '
            ORDER BY name, id',
            $params + $aparams + $tparams);
        $res = [];
        foreach ($records as $record) {
            $persistent = new \core_reportbuilder\local\models\report(0, $record);
            $res[$record->id] = $persistent->get_formatted_name();
        };
        return $res;
    }

    /**
     * SQL to filter reports based on the audience
     *
     * @param string $reporttablealias
     * @return array
     */
    protected function get_audience_sql(string $reporttablealias): array {
        global $USER;
        if (has_capability('moodle/reportbuilder:editall', \context_system::instance())) {
            return ['1=1', []];
        }
        [$asql, $aparams] = audience::user_reports_list_sql($reporttablealias);
        if (has_capability('moodle/reportbuilder:edit', \context_system::instance())) {
            // User can always see own reports and also those reports user is in audience of.
            $paramuserid = database::generate_param_name();
            $aparams += [$paramuserid => $USER->id];
            $asql = "({$reporttablealias}.usercreated = :{$paramuserid} OR ($asql))";
        }
        return [$asql, $aparams];
    }
}
