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

use core_reportbuilder\external\custom_report_exporter;

/**
 * Custom report block.
 *
 * @package    block_rbreport
 * @author     Marina Glancy
 * @copyright  2021 Moodle Pty Ltd <support@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rbreport extends block_base {

    /** @var stdClass $content */
    public $content = null;

    /** @var \core_reportbuilder\local\report\base */
    protected $corereport = false;

    /** @var tool_reportbuilder\report_base */
    protected $toolreport = false;

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
        $layoutclass = !empty($this->config->layout) ? 'rblayout-' . $this->config->layout : '';

        if ($report = $this->get_core_report()) {
            if (method_exists($report, 'set_default_per_page')) {
                // Method was added in Moodle LMS 4.1 in MDL-73184, it is present in Workplace 4.0 but not LMS 4.0.
                $report->set_default_per_page(((int)$this->config->pagesize) ?: $report->get_default_per_page());
            }
            $outputpage = new \core_reportbuilder\output\custom_report($report->get_report_persistent(), false);
            $output = $this->page->get_renderer('core_reportbuilder');
            $export = $outputpage->export_for_template($output);
            $outputhtml = $output->render_from_template('core_reportbuilder/report', $export);
            $this->content->text = html_writer::div($outputhtml, 'rblayout ' . $layoutclass);
            $fullreporturl = new moodle_url('/reportbuilder/view.php', ['id' => $report->get_report_persistent()->get('id')]);
            $this->content->footer = html_writer::link($fullreporturl, get_string('gotofullreport', 'block_rbreport'));
        } else if ($report = $this->get_tool_report()) {
            $outputpage = new tool_reportbuilder\output\report_view($report, false);
            $output = $this->page->get_renderer('tool_reportbuilder');
            $this->content->text = html_writer::div($output->render($outputpage), 'rblayout ' . $layoutclass);
            $fullreporturl = new moodle_url('/admin/tool/reportbuilder/view.php', ['id' => $report->get_id()]);
            $this->content->footer = html_writer::link($fullreporturl, get_string('gotofullreport', 'block_rbreport'));
        } else {
            $this->content->text = $this->user_can_edit() && $this->page->user_is_editing() ? $this->statusmessage : '';
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
        } else if ($report = $this->get_core_report()) {
            $this->title = $report->get_report_persistent()->get_formatted_name();
        } else if ($report = $this->get_tool_report()) {
            $this->title = format_string($report->get_reportname());
        } else {
            $this->title = get_string('pluginname', 'block_rbreport');
        }

        if ((!empty($this->config->corereport) && !$this->get_core_report()) ||
                (!empty($this->config->report) && !$this->get_tool_report())) {
            $this->statusmessage = html_writer::div(get_string('errormessage', 'block_rbreport'), 'alert alert-danger');
        } else {
            $this->statusmessage = html_writer::div(get_string('reportnotsetmessage', 'block_rbreport'));
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
     * @return \core_reportbuilder\local\report\base|null
     */
    protected function get_core_report(): ?\core_reportbuilder\local\report\base {
        if ($this->corereport === false) {
            $this->corereport = null;
            $reportid = $this->config->corereport ?? 0;
            if (!$reportid && ($oldreportid = $this->config->report ?? 0)) {
                // Check maybe the old report was converted already.
                $reportid = get_config('tool_reportbuilder', 'converted-'.$oldreportid);
            }
            if ($reportid) {
                try {
                    $report = \core_reportbuilder\manager::get_report_from_id($reportid);
                    if (\core_reportbuilder\permission::can_view_report($report->get_report_persistent())) {
                        $this->corereport = $report;
                    }
                } catch (moodle_exception $e) {
                    return null;
                }
            }
        }
        return $this->corereport;
    }

    /**
     * Get current report (tool_reportbuilder)
     *
     * @return tool_reportbuilder\report_base|null
     */
    protected function get_tool_report(): ?\tool_reportbuilder\report_base {
        if ($this->toolreport === false) {
            $this->toolreport = null;
            if ($reportid = $this->config->report ?? 0) {
                $parameters = isset($this->config->pagesize) ? ['defaultpagesize' => (int)$this->config->pagesize] : [];
                try {
                    $report = tool_reportbuilder\manager::get_report($reportid, $parameters);
                    if (tool_reportbuilder\permission::can_view($report)) {
                        $this->toolreport = $report;
                    }
                } catch (moodle_exception $e) {
                    return null;
                }
            }
        }
        return $this->toolreport;
    }
}
