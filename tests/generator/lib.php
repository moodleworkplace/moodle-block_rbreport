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

use core_reportbuilder\local\models\report;

/**
 * Generator for tests in block_rbreport
 *
 * @package    block_rbreport
 * @copyright  2023 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rbreport_generator extends component_generator_base {

    /**
     * Proxy the core report generator method, autofilling in component/itemid (tenant ID)
     *
     * @param array|stdClass $record
     * @param int|null $tenantid
     * @return report
     */
    public function create_report($record, ?int $tenantid = null): report {
        $record = (array) $record;

        /** @var \core_reportbuilder_generator $generator */
        $generator = $this->datagenerator->get_plugin_generator('core_reportbuilder');

        if (\core_component::get_component_directory('tool_tenant')) {
            $record += [
                'component' => 'tool_tenant',
                'itemid' => $tenantid ?? \tool_tenant\tenancy::get_tenant_id(),
            ];
        }
        return $generator->create_report($record);
    }
}
