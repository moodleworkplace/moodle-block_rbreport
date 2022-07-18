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
use tool_reportbuilder\test\mock_report;
use tool_reportbuilder\tool_reportbuilder\audiences\manual;

/**
 * Unit tests for manager class.
 *
 * @package     block_rbreport
 * @author      Marina Glancy
 * @covers      \block_rbreport\manager
 * @copyright   2022 Moodle Pty Ltd <support@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager_tool_test extends advanced_testcase {
    /**
     * Set up
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test for manager::get_report_options
     */
    public function test_get_report_options_tool(): void {
        global $DB;

        $sharedspaceid = \tool_tenant\sharedspace::enable_shared_space();
        $tenantgenerator = $this->getDataGenerator()->get_plugin_generator('tool_tenant');
        $rbgenerator = $this->getDataGenerator()->get_plugin_generator('tool_reportbuilder');

        // Create tenants and users.
        [$tenant1, [$user1]] = $tenantgenerator->create_tenant_and_users(1,
            ['dashboardlinked' => 0]);
        [$tenant2, [$user2]] = $tenantgenerator->create_tenant_and_users(1,
            ['dashboardlinked' => 0]);

        // Create an admin for tenant1.
        $manager = new \tool_tenant\manager();
        $tenantadmin1 = $this->getDataGenerator()->create_user();
        $manager->allocate_user($tenantadmin1->id, $tenant1->id, 'tool_tenant', 'testing');
        $manager->assign_tenant_admin_roles([$tenantadmin1->id], $tenant1->id);

        // Create a shared report.
        $sharedreport = $rbgenerator->create_report([
            'name' => 'Shared Report',
            'source' => mock_report::class,
            'tenantid' => $sharedspaceid,
            'shared' => true,
        ]);
        // Create a report in each tenant.
        $report1 = $rbgenerator->create_report(['source' => mock_report::class, 'tenantid' => $tenant1->id]);
        $report2 = $rbgenerator->create_report(['source' => mock_report::class, 'tenantid' => $tenant1->id]);
        $report3 = $rbgenerator->create_report(['source' => mock_report::class, 'tenantid' => $tenant2->id]);

        // Create audiences.
        manual::create($report3->get_id(), ['users' => [$user2->id]]);

        // Create 'my' pages.
        $sitedefaultpage = $DB->insert_record('my_pages', ['userid' => null, 'name' => '__default', 'private' => 1,
            'sortorder' => 0]);
        $tenant1page = $DB->insert_record('my_pages', ['userid' => null, 'name' => 'tenant-' . $tenant1->id, 'private' => 1,
            'sortorder' => 0]);
        $user1page = $DB->insert_record('my_pages', ['userid' => $user1->id, 'name' => '__default', 'private' => 1,
            'sortorder' => 0]);
        $user2page = $DB->insert_record('my_pages', ['userid' => $user2->id, 'name' => '__default', 'private' => 1,
            'sortorder' => 0]);

        $manager = new manager();

        $this->setUser($tenantadmin1);

        // System default dashboard.
        $options = $manager->get_report_options_tool('my-index', $sitedefaultpage, new \moodle_url('/my/indexsys.php'));
        $expected = [$sharedreport->get_id() => $sharedreport->get_reportname()];
        $this->assertEquals($expected, $options);

        // Tenant dashboard.
        $options = $manager->get_report_options_tool('my-index',
            $tenant1page, new \moodle_url('/admin/tool/tenant/editdashboard.php'));
        $expected = [
            $sharedreport->get_id() => $sharedreport->get_reportname(),
            $report1->get_id() => $report1->get_reportname(),
            $report2->get_id() => $report2->get_reportname(),
        ];
        $this->assertEquals($expected, $options);

        $this->setUser($user1);
        // User1 dashboard.
        $options = $manager->get_report_options_tool('my-index', $user1page, new \moodle_url('/my/index.php'));
        $this->assertEmpty($options);

        $this->setUser($user2);
        // User2 dashboard.
        $options = $manager->get_report_options_tool('my-index', $user2page, new \moodle_url('/my/index.php'));
        $expected = [$report3->get_id() => $report3->get_reportname()];
        $this->assertEquals($expected, $options);
    }

    /**
     * Test for manager::get_report_options with disabled shared space
     */
    public function test_get_report_options_tool_disabled_shared_space(): void {
        global $DB;

        $tenantgenerator = $this->getDataGenerator()->get_plugin_generator('tool_tenant');
        $rbgenerator = $this->getDataGenerator()->get_plugin_generator('tool_reportbuilder');

        // Create tenants and users.
        [$tenant1, [$user1]] = $tenantgenerator->create_tenant_and_users(1,
            ['dashboardlinked' => 0]);

        // Create an admin for tenant1.
        $manager = new \tool_tenant\manager();
        $tenantadmin1 = $this->getDataGenerator()->create_user();
        $manager->allocate_user($tenantadmin1->id, $tenant1->id, 'tool_tenant', 'testing');
        $manager->assign_tenant_admin_roles([$tenantadmin1->id], $tenant1->id);

        // Create a report.
        $report1 = $rbgenerator->create_report(['source' => mock_report::class, 'tenantid' => $tenant1->id]);

        // Create audience.
        manual::create($report1->get_id(), ['users' => [$user1->id]]);

        // Create 'my' pages.
        $sitedefaultpage = $DB->insert_record('my_pages', ['userid' => null, 'name' => '__default', 'private' => 1,
            'sortorder' => 0]);
        $user1page = $DB->insert_record('my_pages', ['userid' => $user1->id, 'name' => '__default', 'private' => 1,
            'sortorder' => 0]);

        $manager = new manager();

        $this->setUser($tenantadmin1);

        // System default dashboard.
        $options = $manager->get_report_options_tool('my-index', $sitedefaultpage, new \moodle_url('/my/indexsys.php'));
        $this->assertEmpty($options);

        $this->setUser($user1);
        // User1 dashboard.
        $options = $manager->get_report_options_tool('my-index', $user1page, new \moodle_url('/my/index.php'));
        $expected = [$report1->get_id() => $report1->get_reportname()];
        $this->assertEquals($expected, $options);
    }
}
