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
 * Custom report block.
 *
 * @package    block_rbreport
 * @author     Marina Glancy
 * @copyright  2021 Moodle Pty Ltd <support@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class block_rbreport extends block_base {

    /** @var stdClass $content */
    public $content = null;

    /** @var \tool_reportbuilder\report_base */
    protected $report = false;

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_rbreport');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else if ($report = $this->get_report()) {
            $outputpage = new \tool_reportbuilder\output\report_view($report, false);
            $output = $this->page->get_renderer('tool_reportbuilder');
            $this->content->text = $output->render($outputpage);
        } else {
            $this->content->text = '';
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediatly after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else if ($report = $this->get_report()) {
            $this->title = format_string($report->get_reportname());
        } else {
            $this->title = get_string('pluginname', 'block_rbreport');
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Allow multiple instances
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Get current report
     *
     * @return \tool_reportbuilder\report_base|null
     */
    protected function get_report(): ?\tool_reportbuilder\report_base {
        if (empty($this->config)) {
            return null;
        }
        if ($this->report === false) {
            $this->report = null;
            if ($reportid = $this->config->report) {
                $parameters = []; // TODO?
                try {
                    $report = \tool_reportbuilder\manager::get_report($reportid, $parameters);
                    if ($report && \tool_reportbuilder\permission::can_view($report)) {
                        $this->report = $report;
                    }
                } catch (moodle_exception $e) {
                    null;
                }
            }
        }
        return $this->report;
    }
}
