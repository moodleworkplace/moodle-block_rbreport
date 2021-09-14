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
 * Unit tests for block_rbreport.
 *
 * @package     block_rbreport
 * @author      Mikel Martín <mikel@moodle.com>
 * @copyright   2021 Moodle Pty Ltd <support@moodle.com>
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for block_rbreport.
 *
 * @package     block_rbreport
 * @author      Mikel Martín <mikel@moodle.com>
 * @copyright   2021 Moodle Pty Ltd <support@moodle.com>
 * @license     Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class block_rbreport_test extends advanced_testcase {
    /**
     * Set up
     */
    protected function setUp(): void {
        $this->resetAfterTest();
        $this->generator = $this->getDataGenerator()->get_plugin_generator('tool_tenant');
    }

    /**
     * Test for manager::get_report_options
     */
    public function test_get_report_options(): void {
        // TODO: Write test covering all possible options.
        $url = new \moodle_url('/my/indexsys.php');
        $options = \block_rbreport\manager::get_report_options('my-index', null, $url);
        $this->assertEmpty($options);
    }
}
