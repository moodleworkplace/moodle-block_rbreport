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

namespace block_rbreport;

use advanced_testcase;
use core_user\reportbuilder\datasource\users;

/**
 * Unit tests for block_rbreport class.
 *
 * @package     block_rbreport
 * @author      Ruslan Kabalin
 * @covers      \block_rbreport
 * @copyright   2023 Moodle Pty Ltd <support@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class rbreport_test extends advanced_testcase {
    public static function setUpBeforeClass(): void {
        global $CFG; // Required for CFG availability in require files.
        require_once(__DIR__ . '/../../moodleblock.class.php');
        require_once(__DIR__ . '/../../edit_form.php');
        require_once(__DIR__ . '/../block_rbreport.php');
        require_once(__DIR__ . '/../edit_form.php');
    }

    /**
     * Test get_config_for_external method.
     */
    public function test_get_config_for_external(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        /** @var \core_reportbuilder_generator $rbgenerator */
        $rbgenerator = $this->getDataGenerator()->get_plugin_generator('core_reportbuilder');

        // Create course and add rbreport block.
        $course = $this->getDataGenerator()->create_course();
        $block = $this->create_block($course);

        // Change block instance settings and save.
        if (\core_component::get_component_directory('tool_tenant')) {
            $defaulttenantid = \tool_tenant\tenancy::get_default_tenant_id();
            $report = $rbgenerator->create_report(['source' => users::class,
                'component' => 'tool_tenant', 'itemid' => $defaulttenantid, 'name' => 'R1', ]);
        } else {
            $report = $rbgenerator->create_report(['source' => users::class, 'name' => 'R1']);
        }
        $data = (object)[
            'title' => 'Block title',
            'reporttype' => constants::REPORTTYPE_CORE,
            'corereport' => $report->get('id'),
            'layout' => constants::LAYOUT_CARDS,
            'pagesize' => 10,
        ];
        $block->instance_config_save($data);

        // Load the block.
        $page = self::construct_page($course);
        $page->blocks->load_blocks();
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        $block = end($blocks);

        // Test values.
        $config = $block->get_config_for_external();
        $this->assertEquals($data->title, $config->instance->title);
        $this->assertEquals($data->reporttype, $config->instance->reporttype);
        $this->assertEquals($data->corereport, $config->instance->corereport);
        $this->assertEquals($data->layout, $config->instance->layout);
        $this->assertEquals($data->pagesize, $config->instance->pagesize);
    }

    /**
     * Creates an HTML block on a course.
     *
     * @param \stdClass $course Course object
     * @return \block_rbreport Block instance object
     */
    protected function create_block(\stdClass $course): \block_rbreport {
        $page = self::construct_page($course);
        $page->blocks->add_block_at_end_of_default_region('rbreport');

        // Load the block.
        $page = self::construct_page($course);
        $page->blocks->load_blocks();
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        $block = end($blocks);
        return $block;
    }

    /**
     * Constructs a page object for the test course.
     *
     * @param \stdClass $course Moodle course object
     * @return \moodle_page Page object representing course view
     */
    protected static function construct_page(\stdClass $course): \moodle_page {
        $context = \context_course::instance($course->id);
        $page = new \moodle_page();
        $page->set_context($context);
        $page->set_course($course);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->blocks->load_blocks();
        return $page;
    }
}
