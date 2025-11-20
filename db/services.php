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

/**
 * Plugin external service definition
 *
 * @package     block_rbreport
 * @copyright   2025 Moodle Pty Ltd <support@moodle.com>
 * @author      2025 Aleti Vinod Kumar <vinod.aleti@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'block_rbreport_search_reports' => [
        'classname'   => block_rbreport\external\search_reports::class,
        'description' => 'Search Reports',
        'type'        => 'read',
        'ajax'        => true,
    ],
];
