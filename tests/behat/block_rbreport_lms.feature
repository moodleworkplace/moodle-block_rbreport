@block @block_rbreport @moodleworkplace @javascript
Feature: The Report block allows users to view custom reports in Moodle LMS
  In order to view report builder custom reports
  As a user
  I can add the Report block to show a custom report

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | manager  | Max       | Manager  | manager@example.com |
      | user01   | User01    | User01   | user01@example.com  |
      | user02   | User02    | User02   | user02@example.com  |
      | user03   | User03    | User03   | user03@example.com  |
      | user04   | User04    | User04   | user04@example.com  |
      | user05   | User05    | User05   | user05@example.com  |
      | user06   | User06    | User06   | user06@example.com  |
      | user07   | User07    | User07   | user07@example.com  |
      | user08   | User08    | User08   | user08@example.com  |
      | user09   | User09    | User09   | user09@example.com  |
      | user10   | User10    | User10   | user10@example.com  |
      | user11   | User11    | User11   | user11@example.com  |
    Given the following "block_rbreport > reports" exist:
      | name    | source                                   |
      | Report1 | core_user\reportbuilder\datasource\users |
      | Report2 | core_user\reportbuilder\datasource\users |
      | Report3 | core_user\reportbuilder\datasource\users |
    # Report1 is visible to all users, Report2 - only to site managers
    And the following "core_reportbuilder > Audiences" exist:
      | report  | configdata    | classname                                            |
      | Report1 |               | core_reportbuilder\reportbuilder\audience\allusers   |
      | Report2 | {"roles":[1]} | core_reportbuilder\reportbuilder\audience\systemrole |
    And the following "role assigns" exist:
      | user    | role    | contextlevel | reference |
      | manager | manager | System       |           |

  Scenario: Configure a Report block added by admin in a default dashboard as manager
    # Add a Report block to the default dashboard.
    When I log in as "admin"
    And I visit "/my/indexsys.php"
    And I turn editing mode on
    And I add the "Report..." block
    And I set the field "Entries per page" to "10"
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_suggestions" should exist
    And "Report3" "autocomplete_suggestions" should exist
    And I click on "Report2" item in the autocomplete list
    And I click on "Save changes" "button" in the "Add Report block" "dialogue"
    # Check custom Report2 block appears in manager dashboard.
    And I log in as "manager"
    And I should see "User03" in the "Report2" "block"
    And I switch editing mode on
    And I configure the "Report2" block
    And I open the autocomplete suggestions list
    And "Report2" "autocomplete_selection" should exist
    And "Report1" "autocomplete_suggestions" should exist
    And "Report3" "autocomplete_suggestions" should not exist

  Scenario: Configure a Report block added by admin in a default dashboard as normal user
    # Add a Report block to the default dashboard.
    When I log in as "admin"
    And I visit "/my/indexsys.php"
    And I turn editing mode on
    And I add the "Report" block to the content region with:
      | Select report     | Report1 |
    And I log in as "user01"
    And I should see "User03" in the "Report1" "block"
    And I switch editing mode on
    And I configure the "Report1" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_selection" should exist
    And "Report2" "autocomplete_suggestions" should not exist
    And "Report3" "autocomplete_suggestions" should not exist

  Scenario: Configure a Report block added by admin in a default dashboard as user who can not see it
    # Add a Report block to the default dashboard.
    When I log in as "admin"
    And I visit "/my/indexsys.php"
    And I turn editing mode on
    And I add the "Report" block to the content region with:
      | Select report     | Report2 |
    And I log in as "user01"
    And "Report2" "block" should not exist
    And "Report" "block" should not exist
    And I switch editing mode on
    And I should see "Error occurred while retrieving the report" in the "Report" "block"
    And I configure the "Report" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_selection" should exist
    And "Report2" "autocomplete_suggestions" should not exist
    And "Report3" "autocomplete_suggestions" should not exist

  Scenario: Create a Report block as normal user
    When I log in as "user01"
    And I switch editing mode on
    And I add the "Report..." block
    And I open the autocomplete suggestions list
    And "Report2" "autocomplete_suggestions" should not exist
    And "Report3" "autocomplete_suggestions" should not exist
    And I click on "Report1" item in the autocomplete list
    And I click on "Save changes" "button" in the "Add Report block" "dialogue"
    And I should see "User02" in the "Report1" "block"

  Scenario: Edit pagination setting in Report block
    # Create two blocks with different pagination setting.
    When I log in as "manager"
    Then I switch editing mode on
    And I add the "Report" block to the content region with:
      | Block title       | RepA |
      | Select report     | Report1  |
      | Entries per page  | 5        |
    And I add the "Report" block to the content region with:
      | Block title       | RepB |
      | Select report     | Report1  |
      | Entries per page  | 10       |
    And I should see "User01" in the "RepA" "block"
    And I should not see "User06" in the "RepA" "block"
    And I should see "User01" in the "RepB" "block"
    And I should see "User06" in the "RepB" "block"
    # Change to page 2.
    And I click on "2" "link" in the "RepA" "block"
    And I should see "User06" in the "RepA" "block"

  Scenario: Edit layout setting in Report block
    When I log in as "manager"
    And I change window size to "large"
    Then I switch editing mode on
    And I add the "Report" block to the content region with:
      | Select report     | Report1 |
      | Layout            | Cards   |
    # Forcing Card view show cards also in large screens.
    # In card view only the first column is visible (name), the email will not be visible
    And I should see "User01" in the "Report1" "block"
    And I should not see "user01@example.com" in the "Report1" "block"
    And I am on homepage
    And I configure the "Report1" block
    And I set the following fields to these values:
      | Layout            | Table   |
    And I press "Save changes"
    And I change window size to "530x678"
    # Forcing Table view show table also in small screens.
    And I should see "User01" in the "Report1" "block"
    And I should see "user01@example.com" in the "Report1" "block"
    And I am on homepage
    And I configure the "Report1" block
    And I set the following fields to these values:
      | Layout            | Adaptive   |
    And I press "Save changes"
    # Adaptive view show cards in small screens.
    And I should see "User01" in the "Report1" "block"
    And I should not see "user01@example.com" in the "Report1" "block"
    And I change window size to "large"
    # Adaptive view show table in large screens.
    And I should see "User01" in the "Report1" "block"
    And I should see "user01@example.com" in the "Report1" "block"

  Scenario: Add a Report block in a page with a system report
    When I log in as "admin"
    # Go to 'Report Builder' page because it has a system report.
    And I navigate to "Reports > Report builder > Custom reports" in site administration
    And I should see "Report1" in the "[data-region='core_reportbuilder/report']" "css_element"
    And I should not see "User01" in the "[data-region='core_reportbuilder/report']" "css_element"
    # Now add a Report block.
    And I switch editing mode on
    And I add the "Report" block to the default region with:
      | Block title   | Users report |
      | Select report | Report1      |
    And I should see "User01" in the "Users report" "block"
    And I should not see "Report1" in the "Users report" "block"
    # Filter and reset the system report.
    And I click on "Filters" "button" in the "region-main" "region"
    And I set the field "report:source_operator" to "Is not equal to"
    And I click on "Apply" "button" in the "[data-region='core_reportbuilder/report'][data-report-type='1']" "css_element"
    And I click on "Reset all" "button" in the "region-main" "region"
    # Check that both reports are showing the correct data.
    And I should see "Report1" in the "[data-region='core_reportbuilder/report'][data-report-type='1']" "css_element"
    And I should not see "User01" in the "[data-region='core_reportbuilder/report'][data-report-type='1']" "css_element"
    And I should see "User01" in the "Users report" "block"
    And I should not see "Report1" in the "Users report" "block"
