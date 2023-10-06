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
 * Behat data generator for tool_tenant block_rbreport
 *
 * @package    block_rbreport
 * @copyright  2023 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_rbreport_generator extends behat_generator_base {

    /**
     * Get a list of the entities that can be created for this component.
     *
     * See {@see behat_core_generator::get_creatable_entities} for an example.
     *
     * @return array entity name => information about how to generate.
     */
    protected function get_creatable_entities(): array {
        return [
            'reports' => [
                'singular' => 'report',
                'datagenerator' => 'report',
                'required' => ['name', 'source'],
                'switchids' => ['tenant' => 'itemid'],
            ],
        ];
    }

    /**
     * Look up the id of a tenant from its name
     *
     * @param string $tenantname
     * @return int corresponding id.
     */
    protected function get_tenant_id(string $tenantname): int {
        global $DB;
        if (\core_component::get_component_directory('tool_tenant')) {
            if (!$id = $DB->get_field('tool_tenant', 'id', ['name' => $tenantname])) {
                throw new Exception('The specified tenant with name "' . $tenantname . '" does not exist');
            }
            return $id;
        } else {
            return 0;
        }
    }

}
