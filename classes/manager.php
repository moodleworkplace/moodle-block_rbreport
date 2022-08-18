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
use tool_reportbuilder\permission;

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
        [$tsql, $tparams] = $this->get_tenant_sql('r', $pagetype, $subpage, $pageurl);
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
     * SQL to filter reports based on the page tenant
     *
     * @param string $reporttablealias
     * @param string $pagetype
     * @param string|null $subpage
     * @param moodle_url $pageurl
     * @return array
     */
    protected function get_tenant_sql(string $reporttablealias, string $pagetype, ?string $subpage, moodle_url $pageurl): array {
        global $DB;
        $sharedspaceid = \tool_tenant\sharedspace::get_shared_space_id();
        $mypage = $DB->get_record('my_pages', ['id' => (int)$subpage]);
        if ($pagetype == 'my-index' && $pageurl->compare(new \moodle_url('/my/indexsys.php'), URL_MATCH_BASE)) {
            if (!$sharedspaceid) {
                return ["1=0", []];
            }
            $tenantid = $sharedspaceid;
        } else if (!empty($mypage) && preg_match('/^tenant-([0-9]+)$/', $mypage->name, $matches, PREG_UNMATCHED_AS_NULL)) {
            $tenantid = (int)$matches[1];
        } else {
            $tenantid = \tool_tenant\tenancy::get_actual_tenant_id();
        }

        $sql = "{$reporttablealias}.component=:component";
        $params = ['component' => 'tool_tenant'];
        [$tsql, $tparams] = \tool_tenant\hierarchy::filter_own_or_parent_shared_entities_sql("{$reporttablealias}.itemid",
            "{$reporttablealias}.area = 'shared'", $tenantid);
        return ["$sql AND $tsql", $params + $tparams];
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

    /**
     * List of available reports (tool_reportbuilder)
     *
     * @param string $pagetype
     * @param string|null $subpage
     * @param moodle_url $pageurl
     * @return string[]
     */
    public function get_report_options_tool(string $pagetype, ?string $subpage, moodle_url $pageurl): array {
        global $DB;

        $mypage = $DB->get_record('my_pages', ['id' => (int)$subpage]);

        if ($pagetype == 'my-index' && $pageurl->compare(new \moodle_url('/my/indexsys.php'), URL_MATCH_BASE)) {
            return $this->get_shared_reports_tool();
        } else if (!empty($mypage) && preg_match('/^tenant-([0-9]+)$/', $mypage->name, $matches, PREG_UNMATCHED_AS_NULL)) {
            return $this->get_tenant_reports_tool((int)$matches[1]);
        } else {
            return $this->get_tenant_reports_tool(\tool_tenant\tenancy::get_actual_tenant_id());
        }
    }

    /**
     * Returns user available reports of a tenant (tool_reportbuilder)
     *
     * @param int $tenantid
     * @return string[]
     */
    private function get_tenant_reports_tool(int $tenantid): array {
        global $DB;

        [$select, $selectparams] = \tool_tenant\hierarchy::filter_own_or_parent_shared_entities_sql('tenantid',
            'shared=1', $tenantid);
        if (!permission::can_view_any()) {
            $allowedreports = \tool_reportbuilder\local\helpers\audience::user_reports_list();
            if (empty($allowedreports)) {
                return [];
            }
            [$insql, $inparams] = $DB->get_in_or_equal($allowedreports, SQL_PARAMS_NAMED);
            $select .= " AND id $insql";
            $selectparams = array_merge($selectparams, $inparams);
        }

        $sql = "SELECT id, name FROM {tool_reportbuilder}
                    WHERE type = :type AND $select
                    ORDER BY name, id";
        $selectparams['type'] = \tool_reportbuilder\constants::TYPE_DATASOURCE;
        $reports = $DB->get_records_sql_menu($sql, $selectparams);
        return $reports;
    }

    /**
     * Returns user available shared reports (tool_reportbuilder)
     *
     * @return string[]
     */
    private function get_shared_reports_tool(): array {
        $sharedspaceid = \tool_tenant\sharedspace::get_shared_space_id();

        // If shared space isn't enabled then there are no shared reports.
        if (!$sharedspaceid) {
            return [];
        }

        return $this->get_tenant_reports_tool($sharedspaceid);
    }
}
