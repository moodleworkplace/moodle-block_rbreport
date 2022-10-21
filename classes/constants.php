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

/**
 * Class constants
 *
 * @package   block_rbreport
 * @author    2022 Mikel Martín <mikel@moodle.com>
 * @copyright 2022 Moodle Pty Ltd <support@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class constants {
    /** Display as cards only in small blocks. */
    const LAYOUT_ADAPTIVE = 'adaptive';
    /** Always display as cards. */
    const LAYOUT_CARDS = 'cards';
    /** Always display as table. */
    const LAYOUT_TABLE = 'table';

    /** @var int */
    const REPORTTYPE_CORE = 1;
    /** @var int */
    const REPORTTYPE_TOOL = 0;
}
