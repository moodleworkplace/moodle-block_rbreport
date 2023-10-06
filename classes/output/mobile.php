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

namespace block_rbreport\output;

/**
 * Mobile output class.
 *
 * @package    block_rbreport
 * @author     Juan Leyva
 * @copyright  2023 Moodle Pty Ltd <support@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {

    /**
     * Returns the view for the mobile app.
     *
     * @param  array $args Arguments from tool_mobile_get_content WS
     * @return array HTML, javascript and otherdata
     */
    public static function mobile_course_view($args) {
        global $OUTPUT, $CFG;
        require_once($CFG->libdir . '/blocklib.php');

        $blockinstance = block_instance_by_id($args['blockid']);

        $data = [
            'url' => '',
            'title' => '',
        ];

        $reportid = $blockinstance->config->corereport;
        try {
            $report = \core_reportbuilder\manager::get_report_from_id($reportid);
            if (\core_reportbuilder\permission::can_view_report($report->get_report_persistent())) {

                $url = new \moodle_url('/reportbuilder/view.php', ['id' => $reportid]);
                $data['url'] = $url->out(false);
                $data['title'] = $blockinstance->title;

            }
        // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        } catch (\moodle_exception $e) {
            // Do nothing.
        }

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template('block_rbreport/mobile_view_page', $data),
                ],
            ],
            'javascript' => '',
        ];
    }
}
