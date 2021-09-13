@block @block_rbreport @javascript
Feature: The 'Custom Report' block allows users to view custom reports
  In order to view report builder custom reports
  As a user
  I can add the 'Custom report' block to show a custom report

  Background:
    Given "2" tenants exist with "10" users and "0" courses in each
    # This will create users: tenantadmin1, user11 .... , user19, tenantadmin2, user21 .... user29.
    And the following "tool_reportbuilder > reports" exist:
      | name    | tenant  | source                                                              |
      | Report1 | Tenant1 | tool_reportbuilder\tool_reportbuilder\datasources\report_users_list |
      | Report2 | Tenant1 | tool_reportbuilder\tool_reportbuilder\datasources\report_users_list |
      | Report3 | Tenant2 | tool_reportbuilder\tool_reportbuilder\datasources\report_users_list |

  Scenario: View a 'Custom report' block added by admin in the dashboard as normal user
    # Add a 'Custom report' block to the default dashboard.
    When I log in as "admin"
    Then I switch to tenant "Tenant1"
    And I navigate to "Appearance > Default site dashboard page" in site administration
    And I press "Blocks editing on"
    And I add the "Custom report" block
    And I should see "This block will not be visible to other users until a report is set."
    And I follow "Configure block"
    And I set the following fields to these values:
      | Select report  | Report1 |
    And I press "Save changes"
    And I log out
    # Check custom Report1 block does not appear in user11 dashboard (not in report audience).
    And I log in as "user11"
    And I should not see "Custom report"
    And I should not see "Report1"
    And I press "Customise this page"
    And I configure the "Custom report" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_suggestions" should not exist
    And I press "Cancel"
    And I log out
    # Now add user11 in Report1 audiences.
    And I log in as "tenantadmin1"
    And I navigate to "Report builder" in workplace launcher
    And I click on "Edit content" "link" in the "Report1" "table_row"
    And I click on "Audience" "link" in the "[role=tablist]" "css_element"
    And I click on "Manually added users" "link"
    And I set the field "Add users manually" to "User11"
    And I press "Save changes"
    And I log out
    # Check custom Report1 block appears in user11 dashboard.
    And I log in as "user11"
    And I should see "User 11" in the "Report1" "block"
    And I press "Customise this page"
    And I configure the "Report1" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_selection" should exist
    And "Report2" "autocomplete_suggestions" should not exist

  Scenario: View a 'Custom report' block as tenantadmin
    When I log in as "tenantadmin1"
    Then I press "Customise this page"
    And I add the "Custom report" block
    And I should see "This block will not be visible to other users until a report is set."
    And I configure the "Custom report" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_suggestions" should exist
    And "Report3" "autocomplete_suggestions" should not exist
    And I click on "Report2" item in the autocomplete list
    And I press "Save changes"
    And I should see "User 11" in the "Report2" "block"
    And I follow "Go to full report"
    And I should see "Report2"
    And I should see "Email address" in the "report-table" "table"
    And I should see "user11@invalid.com" in the "report-table" "table"

  Scenario: Create a 'Custom report' block as normal user
    # First add user11 in Report1 audiences.
    When I log in as "tenantadmin1"
    Then I navigate to "Report builder" in workplace launcher
    And I click on "Edit content" "link" in the "Report1" "table_row"
    And I click on "Audience" "link" in the "[role=tablist]" "css_element"
    And I click on "Manually added users" "link"
    And I set the field "Add users manually" to "User11"
    And I press "Save changes"
    And I log out
    # Add the block as user11.
    And I log in as "user11"
    And I press "Customise this page"
    And I add the "Custom report" block
    And I should see "This block will not be visible to other users until a report is set."
    And I configure the "Custom report" block
    And I open the autocomplete suggestions list
    And I click on "Report1" item in the autocomplete list
    And I press "Save changes"
    And I should see "User 11" in the "Report1" "block"
    And I log out
    # Now remove user11 from Report1 audiences.
    And I log in as "tenantadmin1"
    And I navigate to "Report builder" in workplace launcher
    And I click on "Edit content" "link" in the "Report1" "table_row"
    And I click on "Audience" "link" in the "[role=tablist]" "css_element"
    And I click on "Delete" "link"
    And I click on "Delete" "button" in the "Confirm" "dialogue"
    And I log out
    # Check custom Report1 block does not appear in user11 dashboard (not in report audience anymore).
    And I log in as "user11"
    And I should not see "Custom report"
    And I should not see "Report1"
    And I press "Customise this page"
    And I configure the "Custom report" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_suggestions" should not exist
    And I press "Cancel"

  Scenario: Edit pagination setting in 'Custom Report' block
    # Create two blocks with different pagination setting.
    When I log in as "tenantadmin1"
    Then I press "Customise this page"
    And I add the "Custom report" block
    And I configure the "Custom report" block
    And I set the following fields to these values:
      | Select report     | Report1 |
      | Entries per page  | 5       |
    And I press "Save changes"
    And I add the "Custom report" block
    And I configure the "Custom report" block
    And I set the following fields to these values:
      | Block title       | Report1B |
      | Select report     | Report1  |
      | Entries per page  | 10       |
    And I press "Save changes"
    And I should see "User 11" in the "Report1" "block"
    And I should not see "User 16" in the "Report1" "block"
    And I should see "User 11" in the "Report1B" "block"
    And I should see "User 16" in the "Report1B" "block"
    And I click on "Show all 10" "button" in the "Report1" "block"
    And I should see "User 16" in the "Report1" "block"

  Scenario: Edit layout setting in 'Custom Report' block
    When I log in as "tenantadmin1"
    And I change window size to "large"
    Then I press "Customise this page"
    And I add the "Custom report" block
    And I configure the "Custom report" block
    And I set the following fields to these values:
      | Select report     | Report1 |
      | Layout            | Cards   |
      | Region            | content |
      | Weight            | -1      |
    And I press "Save changes"
    # Forcing Card view show cards also in large screens.
    And I should not see "user11@invalid.com" in the "report-table" "table"
    And I follow "Dashboard"
    And I configure the "Report1" block
    And I set the following fields to these values:
      | Layout            | Table   |
    And I press "Save changes"
    And I change window size to "530x678"
    # Forcing Table view show table also in small screens.
    And I should see "user11@invalid.com" in the "report-table" "table"
    And I follow "Dashboard"
    And I configure the "Report1" block
    And I set the following fields to these values:
      | Layout            | Adaptive   |
    And I press "Save changes"
    # Adaptive view show cards in small screens.
    And I should not see "user11@invalid.com" in the "report-table" "table"
    And I change window size to "large"
    # Adaptive view show table in large screens.
    And I should see "user11@invalid.com" in the "report-table" "table"
