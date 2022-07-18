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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps definitions for block_rbreport.
 *
 * @package    block_rbreport
 * @category   test
 * @copyright  2022 Moodle Pty Ltd <support@moodle.com>
 * @author     2022 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_rbreport extends behat_base {

    /**
     * I open the autocomplete suggestions list for legacy custom reports
     *
     * @Given /^I open the autocomplete suggestions list for legacy custom reports$/
     */
    public function i_open_the_autocomplete_suggestions_list_for_legacy_custom_reports() {
        $locator = behat_context_helper::escape(get_string('configreport', 'block_rbreport') . ': ' .
            get_string('reporttypetool', 'block_rbreport'));
        $formselector = <<<XPATH
.//*[contains(concat(' ', @class, ' '), ' col-form-label ')]
    [normalize-space(.)= {$locator}]
    /ancestor::*[contains(concat(' ', @class, ' '), ' fitem ') and position() = 1]
XPATH;
        $csstarget = ".form-autocomplete-downarrow";
        $node = $this->get_selected_node('xpath_element', $formselector);
        $this->ensure_node_is_visible($node);
        $this->execute('behat_general::i_click_on_in_the',
            [$csstarget, 'css_element', $formselector, 'xpath_element']);
    }
}
