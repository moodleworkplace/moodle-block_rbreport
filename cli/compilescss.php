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
 * TODO describe file compilescss
 *
 * @package    block_rbreport
 * @copyright  2023 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

$inputfile = dirname(__DIR__) . '/scss/styles.scss';
$outputfile = dirname(__DIR__) . '/styles.css';

if (!file_exists($inputfile)) {
    cli_error('File not found: ' . $inputfile);
}

$cacheoptions = [];
$compiler = new core_scss($cacheoptions);

$compiler->set_file($inputfile);

try {
    // Compile!
    $compiled = $compiler->to_css();

} catch (\Exception $e) {
    cli_error('Error while compiling SCSS: ' . $e->getMessage());
}

$prefix = <<<EOF
/* stylelint-disable */
/*
  Do not edit!

  To generate the file make modifications in blocks/rbreport/scss/styles.scss and execute

  /path/to/php blocks/rbreport/cli/compilescss.php
*/

EOF;

file_put_contents($outputfile, $prefix . $compiled);
cli_writeln('File generated: ' . $outputfile);
