<?php
// This file is part of Moodle - http://moodle.org/
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
 * Questionnaire module capability definition
 *
 * @package    block_rbreport
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$addons = array(
    "block_rbreport" => array(
        "handlers" => array( // Different places where the add-on will display content.
            'rbreport' => array( // Handler unique name (can be anything)
                'delegate' => 'CoreBlockDelegate',
                'method' => 'mobile_course_view'
            )
        )
    )
);
