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
// Moodle Workplace™ Code is the collection of software scripts
// (plugins and modifications, and any derivations thereof) that are
// exclusively owned and licensed by Moodle under the terms of this
// proprietary Moodle Workplace License ("MWL") alongside Moodle's open
// software package offering which itself is freely downloadable at
// "download.moodle.org" and which is provided by Moodle under a single
// GNU General Public License version 3.0, dated 29 June 2007 ("GPL").
// MWL is strictly controlled by Moodle Pty Ltd and its certified
// premium partners. Wherever conflicting terms exist, the terms of the
// MWL are binding and shall prevail.

/**
 * Form for editing HTML block instances.
 *
 * @package    block_rbreport
 * @author     Marina Glancy
 * @copyright  2021 Moodle Pty Ltd <support@moodle.com>
 * @license    Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class block_rbreport_edit_form extends block_edit_form {

    /** Display as cards only in small blocks. */
    const LAYOUT_DYNAMIC = 'dynamic';
    /** Always display as cards. */
    const LAYOUT_CARDS = 'cards';
    /** Always display as table. */
    const LAYOUT_TABLE = 'table';

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

        $mform->addElement('autocomplete', 'config_report', get_string('configreport', 'block_rbreport'),
            $this->get_report_options());
        $mform->addHelpButton('config_report', 'configreport', 'block_rbreport');
        $mform->addRule('config_report', get_string('required'), 'required', null, 'client');

        $options = [
            self::LAYOUT_DYNAMIC => get_string('displayadaptative', 'block_rbreport'),
            self::LAYOUT_CARDS => get_string('displayascards', 'block_rbreport'),
            self::LAYOUT_TABLE => get_string('displayastable', 'block_rbreport'),
        ];
        $mform->addElement('select', 'config_layout', get_string('configlayout', 'block_rbreport'),
            $options);
        $mform->addHelpButton('config_layout', 'configlayout', 'block_rbreport');

        $cardsarray = [
            5 => 5,
            10 => 10,
            25 => 25,
            50 => 50,
        ];
        $mform->addElement('select', 'config_pagesize', get_string('entriesperpage', 'block_rbreport'), $cardsarray);
        $mform->setType('config_pagesize', PARAM_INT);
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
            'customreport' => 0,
            'sharedtenantid' => \tool_tenant\sharedspace::get_shared_space_id(),
            'currenttenantid' => \tool_tenant\tenancy::get_tenant_id()
        ];

        if (\tool_reportbuilder\permission::can_view_any()) {
            // Return all tenant and shared reports.
            $select = 'type = :customreport AND (tenantid = :currenttenantid OR (shared = 1 AND tenantid = :sharedtenantid))';
            $reports = $DB->get_records_select_menu('tool_reportbuilder', $select, $params, 'name, id', 'id, name');
        } else {
            // If user can't view all reports, limit the returned list to those they can see.
            $allowedreports = \tool_reportbuilder\local\helpers\audience::user_reports_list();
            if (empty($allowedreports)) {
                return [];
            }
            [$insql, $inparams] = $DB->get_in_or_equal($allowedreports);
            $reports = $DB->get_records_sql_menu("SELECT id, name FROM {tool_reportbuilder} WHERE id $insql", $inparams);
        }

        // Add empty option on first load to avoid autocomplete selecting the first option automatically.
        if (!isset($this->block->config)) {
            $reports = ['' => ''] + $reports;
        }
        return $reports;
    }
}
