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

    /** Display as cards only in small blocks. */
    const LAYOUT_ADAPTIVE = 'adaptive';
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
        // Fields for editing Custom report block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_rbreport'));
        $mform->setType('config_title', PARAM_TEXT);

        $options = (new manager())->get_report_options($this->page->pagetype, $this->page->subpage, $this->page->url);
        // Add empty option on first load to avoid autocomplete selecting the first option automatically.
        if (!isset($this->block->config)) {
            $options = ['' => ''] + $options;
        }
        $mform->addElement('autocomplete', 'config_report', get_string('configreport', 'block_rbreport'), $options);
        $mform->addHelpButton('config_report', 'configreport', 'block_rbreport');
        $mform->addRule('config_report', get_string('required'), 'required', null, 'client');

        $options = [
            self::LAYOUT_ADAPTIVE => get_string('displayadaptive', 'block_rbreport'),
            self::LAYOUT_CARDS => get_string('displayascards', 'block_rbreport'),
            self::LAYOUT_TABLE => get_string('displayastable', 'block_rbreport'),
        ];
        $mform->addElement('select', 'config_layout', get_string('configlayout', 'block_rbreport'),
            $options);
        $mform->addHelpButton('config_layout', 'configlayout', 'block_rbreport');

        $cardsarray = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 10 => 10, 25 => 25, 50 => 50];
        $mform->addElement('select', 'config_pagesize', get_string('entriesperpage', 'block_rbreport'), $cardsarray);
        $mform->setDefault('config_pagesize', 5);
        $mform->setType('config_pagesize', PARAM_INT);
    }
}
