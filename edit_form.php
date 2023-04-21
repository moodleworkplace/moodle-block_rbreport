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

use block_rbreport\constants;
use block_rbreport\manager;

/**
 * Form for editing Custom report block instances.
 *
 * @package    block_rbreport
 * @author     Marina Glancy
 * @copyright  2021 Moodle Pty Ltd <support@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rbreport_edit_form extends block_edit_form {

    /**
     * Block settings definitions
     *
     * @param MoodleQuickForm $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform) {
        // Fields for editing Custom report block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_rbreport'));
        $mform->setType('config_title', PARAM_TEXT);

        // Check if there are any legacy reports present, if there are, display a report type selector.
        $optionstool = component_class_callback(\tool_tenant\local\block_rbreport::class,
            'get_report_options_tool',
            [$this->page->pagetype, $this->page->subpage, $this->page->url], []);
        $optionscore = (new manager())->get_report_options($this->page->pagetype, $this->page->subpage, $this->page->url);
        // Add empty option on first load to avoid autocomplete selecting the first option automatically.
        if (!isset($this->block->config->corereport)) {
            $optionscore = ['' => ''] + $optionscore;
        }
        if ($optionstool && !isset($this->block->config->report)) {
            $optionstool = ['' => ''] + $optionstool;
        }

        if ($optionstool) {
            $group = [];
            $group[] = $mform->createElement('radio', 'config_reporttype', '',
                get_string('reporttypecore', 'block_rbreport'), constants::REPORTTYPE_CORE);
            $group[] = $mform->createElement('autocomplete', 'config_corereport',
                get_string('configreport', 'block_rbreport'), $optionscore);
            $mform->hideIf('config_corereport', 'config_reporttype', 'ne', constants::REPORTTYPE_CORE);

            $group[] = $mform->createElement('radio', 'config_reporttype', '',
                get_string('reporttypetool', 'block_rbreport'), constants::REPORTTYPE_TOOL);
            $group[] = $mform->createElement('autocomplete', 'config_report',
                get_string('configreport', 'block_rbreport') . ': '.get_string('reporttypetool', 'block_rbreport'),
                $optionstool);
            $mform->hideIf('config_report', 'config_reporttype', 'ne', constants::REPORTTYPE_TOOL);

            $mform->addGroup($group, 'config_grp', get_string('configreport', 'block_rbreport'),
                '<div class="w-100 mdl-left"></div>', false);
            $mform->addHelpButton('config_grp', 'configreport', 'block_rbreport');
        } else {
            $mform->addElement('hidden', 'config_reporttype', constants::REPORTTYPE_CORE);
            $mform->setType('config_reporttype', PARAM_INT);
            $mform->addElement('autocomplete', 'config_corereport', get_string('configreport', 'block_rbreport'), $optionscore);
            $mform->addHelpButton('config_corereport', 'configreport', 'block_rbreport');
        }

        $options = [
            constants::LAYOUT_ADAPTIVE => get_string('displayadaptive', 'block_rbreport'),
            constants::LAYOUT_CARDS => get_string('displayascards', 'block_rbreport'),
            constants::LAYOUT_TABLE => get_string('displayastable', 'block_rbreport'),
        ];
        $mform->addElement('select', 'config_layout', get_string('configlayout', 'block_rbreport'),
            $options);
        $mform->addHelpButton('config_layout', 'configlayout', 'block_rbreport');

        $cardsarray = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 10 => 10, 25 => 25, 50 => 50];
        $mform->addElement('select', 'config_pagesize', get_string('entriesperpage', 'block_rbreport'), $cardsarray);
        $mform->setDefault('config_pagesize', 5);
        $mform->setType('config_pagesize', PARAM_INT);
    }

    /**
     * Form validation
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errorelement = $this->_form->elementExists('config_grp') ? 'config_grp' : 'config_corereport';
        $errors = [];
        $reporttype = $data['config_reporttype'] ?? 0;
        if ($reporttype == constants::REPORTTYPE_TOOL) {
            if (empty($data['config_report'])) {
                $errors[$errorelement] = get_string('required');
            }
        } else {
            if (empty($data['config_corereport'])) {
                $errors[$errorelement] = get_string('required');
            }
        }
        return $errors;
    }

    /**
     * Set data
     *
     * @param array|stdClass $defaults
     */
    public function set_data($defaults) {
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        if (!isset($this->block->config->reporttype)) {
            // Setting 'config_reporttype' must be always set, so there is always one radio button selected.
            if (!empty($this->block->config->report)) {
                // This is most likely a report with old configuration from Workplace 3.11.
                $this->block->config->reporttype = constants::REPORTTYPE_TOOL;
            } else {
                $this->block->config->reporttype = constants::REPORTTYPE_CORE;
            }
        }
        // If it's an old report check if it may have been converted already.
        if ($reportid = component_class_callback(\tool_tenant\local\block_rbreport::class,
                'get_converted_report_id', [$this->block->config], 0)) {
            $this->block->config->corereport = $reportid;
            $this->block->config->report = null;
            $this->block->config->reporttype = constants::REPORTTYPE_CORE;
        }
        parent::set_data($defaults);
    }

    /**
     * Get data
     *
     * @return stdClass
     */
    public function get_data() {
        if ($data = parent::get_data()) {
            // Make sure we only save one report id - either for the tool or for the core.
            $reporttype = $data->config_reporttype ?? -1;
            if ($reporttype == constants::REPORTTYPE_CORE) {
                $data->config_report = null;
            } else if ($reporttype == constants::REPORTTYPE_TOOL) {
                $data->config_corereport = null;
            }
        }
        return $data;
    }

    /**
     * Display the configuration form when block is being added to the page
     *
     * @return bool
     */
    public static function display_form_when_adding(): bool {
        return true;
    }
}
