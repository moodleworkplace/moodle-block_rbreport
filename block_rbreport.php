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
 * Custom report block.
 *
 * @package    block_rbreport
 * @author     Marina Glancy
 * @copyright  2021 Moodle Pty Ltd <support@moodle.com>
 * @license    Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class block_rbreport extends block_base {

    /** @var stdClass $content */
    public $content = null;

    /** @var tool_reportbuilder\report_base */
    protected $report = false;

    /** @var string */
    protected $statusmessage = '';

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
        $this->content->items = [];
        $this->content->icons = [];

        if ($report = $this->get_report()) {
            $outputpage = new tool_reportbuilder\output\report_view($report, false);
            $output = $this->page->get_renderer('tool_reportbuilder');
            $layoutclass = !empty($this->config->layout) ? 'rblayout-' . $this->config->layout : '';
            $this->content->text = html_writer::div($output->render($outputpage), 'rblayout ' . $layoutclass);
            $fullreporturl = new moodle_url('/admin/tool/reportbuilder/view.php', ['id' => $report->get_id()]);
            $this->content->footer = html_writer::link($fullreporturl, get_string('gotofullreport', 'block_rbreport'));
        } else {
            $this->content->text = $this->user_can_edit() ? $this->statusmessage : '';
            $this->content->footer = '';
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
     * @return tool_reportbuilder\report_base|null
     */
    protected function get_report(): ?\tool_reportbuilder\report_base {
        if (empty($this->config)) {
            $this->statusmessage = html_writer::div(get_string('reportnotsetmessage', 'block_rbreport'));
            return null;
        }
        if ($this->report === false) {
            $this->report = null;
            if ($reportid = $this->config->report) {
                $parameters = isset($this->config->pagesize) ? ['defaultpagesize' => (int)$this->config->pagesize] : [];
                try {
                    $report = tool_reportbuilder\manager::get_report($reportid, $parameters);
                    if ($report && tool_reportbuilder\permission::can_view($report)) {
                        $this->report = $report;
                    }
                } catch (moodle_exception $e) {
                    $this->statusmessage = html_writer::div($e->getMessage(), 'alert alert-danger');
                    return null;
                }
            }
        }
        return $this->report;
    }
}
