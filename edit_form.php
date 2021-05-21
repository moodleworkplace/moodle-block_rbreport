<?php
// This file is part of Moodle Workplace https://moodle.com/workplace based on Moodle
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
//
// Moodle Workplace Code is dual-licensed under the terms of both the
// single GNU General Public Licence version 3.0, dated 29 June 2007
// and the terms of the proprietary Moodle Workplace Licence strictly
// controlled by Moodle Pty Ltd and its certified premium partners.
// Wherever conflicting terms exist, the terms of the MWL are binding
// and shall prevail.

/**
 * Form for editing HTML block instances.
 *
 * @package    block_rbreport
 * @author     Marina Glancy
 * @copyright  2021 Moodle Pty Ltd <support@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rbreport_edit_form extends block_edit_form {

    /** @var block_rbreport */
    public $block;

    /**
     * Block settings definitions
     *
     * @param object $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform) {
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_rbreport'));
        $mform->setType('config_title', PARAM_TEXT);

        $mform->addElement('select', 'config_report', get_string('configreport', 'block_rbreport'),
            $this->get_report_options());
    }

    /**
     * List of available reports
     *
     * @return array|string[]
     * @throws dml_exception
     */
    protected function get_report_options() {
        global $DB;
        $params = [
            'systemreport' => 0,
            'sharedtenantid' => \tool_tenant\sharedspace::get_shared_space_id(),
        ];
        $reports = $DB->get_records_select_menu('tool_reportbuilder',
            'shared=1 AND tenantid = :sharedtenantid',
            $params,
            'name, id',
            'id, name');
        return ['' => ''] + $reports;
    }
}
