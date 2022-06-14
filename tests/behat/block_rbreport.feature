@block @block_rbreport @moodleworkplace @javascript
Feature: The Report block allows users to view custom reports
  In order to view report builder custom reports
  As a user
  I can add the Report block to show a custom report

  Background:
    # This will create users: tenantadmin1, user11 .... , user19, tenantadmin2, user21 .... user29.
    Given "2" tenants exist with "10" users and "0" courses in each
    And the following users allocations to tenants exist:
      | user  | tenant  |
      | admin | Tenant1 |
    And the following "tool_reportbuilder > reports" exist:
      | name    | tenant  | source                                                              |
      | Report1 | Tenant1 | tool_reportbuilder\tool_reportbuilder\datasources\report_users_list |
      | Report2 | Tenant1 | tool_reportbuilder\tool_reportbuilder\datasources\report_users_list |
      | Report3 | Tenant2 | tool_reportbuilder\tool_reportbuilder\datasources\report_users_list |

  Scenario: Configure a Report block added by tenantadmin in the tenant dashboard as normal user
    # Add a Report block to the tenant dashboard.
    When I log in as "tenantadmin1"
    And I navigate to "Appearance" in workplace launcher
    And I click on "Dashboard" "link" in the "[role=tablist]" "css_element"
    And I press "Create personalised dashboard..."
    And I click on "Proceed" "button" in the "Confirmation" "dialogue"
    And I press "Edit dashboard"
    And I switch editing mode on
    And I add the "Report" block
    And I should see "Please configure this block and select which report it should display." in the "Report" "block"
    And I configure the "Report" block
    And I open the autocomplete suggestions list
    And "Report2" "autocomplete_suggestions" should exist
    And "Report3" "autocomplete_suggestions" should not exist
    And I click on "Report1" item in the autocomplete list
    And I press "Save changes"
    And I log out
    # Check custom Report1 block does not appear in user11 dashboard (not in report audience).
    And I log in as "user11"
    And "Report" "block" should not exist
    And I should not see "Report1"
    And I switch editing mode on
    And I should see "Error occurred while retrieving the report" in the "Report" "block"
    And I configure the "Report" block
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
    And I switch editing mode on
    And I configure the "Report1" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_selection" should exist
    And "Report2" "autocomplete_suggestions" should not exist
    And I log out
    # Now remove Report1.
    And I log in as "tenantadmin1"
    And I navigate to "Report builder" in workplace launcher
    And I click on "Delete report" "link" in the "Report1" "table_row"
    And I click on "Delete" "button" in the "Confirm" "dialogue"
    And I log out
    # Check Report1 block does not appear for user.
    And I log in as "user11"
    And "Report" "block" should not exist
    And I switch editing mode on
    And I should see "Error occurred while retrieving the report" in the "Report" "block"

  Scenario: View a Report block added by tenantadmin in the tenant dashboard as normal user (no block editing permissions)
    Given the following "permission overrides" exist:
      | capability                   | permission     | role                  | contextlevel | reference |
      | moodle/my:manageblocks       | Prevent        | user                  | System       |           |
      | moodle/my:manageblocks       | Allow          | tool_tenant_admin     | System       |           |
    # Add a Report block to the tenant dashboard.
    When I log in as "tenantadmin1"
    And I navigate to "Appearance" in workplace launcher
    And I click on "Dashboard" "link" in the "[role=tablist]" "css_element"
    And I press "Create personalised dashboard..."
    And I click on "Proceed" "button" in the "Confirmation" "dialogue"
    And I press "Edit dashboard"
    And I switch editing mode on
    And I add the "Report" block
    And I configure the "Report" block
    And I set the following fields to these values:
      | Select report     | Report1 |
    And I press "Save changes"
    And I log out
    # Check custom Report1 block does not appear in user11 dashboard (not in report audience).
    And I log in as "user11"
    And "Report" "block" should not exist
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
    And I log out
    # Now remove Report1.
    And I log in as "tenantadmin1"
    And I navigate to "Report builder" in workplace launcher
    And I click on "Delete report" "link" in the "Report1" "table_row"
    And I click on "Delete" "button" in the "Confirm" "dialogue"
    And I log out
    # Check Report1 block does not appear for user.
    And I log in as "user11"
    And "Report" "block" should not exist

  Scenario: Configure a Report block added by admin in a tenant dashboard as normal user
    # Add a Report block to the tenant1 dashboard.
    When I log in as "admin"
    And I navigate to "All tenants" in workplace launcher
    And I click on "Tenant2" "link" in the "Tenant2" "tool_wp > Table tree node"
    And I click on "Dashboard" "link" in the "[role=tablist]" "css_element"
    And I press "Create personalised dashboard..."
    And I click on "Proceed" "button" in the "Confirmation" "dialogue"
    And I press "Edit dashboard"
    And I switch editing mode on
    And I add the "Report" block
    And I configure the "Report" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_suggestions" should not exist
    And "Report2" "autocomplete_suggestions" should not exist
    And I click on "Report3" item in the autocomplete list
    And I press "Save changes"
    # Now add user21 in Report3 audiences.
    And I switch to tenant "Tenant2"
    And I navigate to "Report builder" in workplace launcher
    And I click on "Edit content" "link" in the "Report3" "table_row"
    And I click on "Audience" "link" in the "[role=tablist]" "css_element"
    And I click on "Manually added users" "link"
    And I set the field "Add users manually" to "User21"
    And I press "Save changes"
    And I log out
    # Check custom Report3 block appears in user11 dashboard.
    And I log in as "user21"
    And I should see "User 21" in the "Report3" "block"
    And I switch editing mode on
    And I configure the "Report3" block
    And I open the autocomplete suggestions list
    And "Report3" "autocomplete_selection" should exist
    And "Report1" "autocomplete_suggestions" should not exist
    And "Report2" "autocomplete_suggestions" should not exist

  Scenario: View a Report block as tenantadmin
    When I log in as "tenantadmin1"
    Then I switch editing mode on
    And I add the "Report" block
    And I should see "Please configure this block and select which report it should display." in the "Report" "block"
    And I configure the "Report" block
    And I open the autocomplete suggestions list
    And "Report1" "autocomplete_suggestions" should exist
    And "Report3" "autocomplete_suggestions" should not exist
    And I click on "Report2" item in the autocomplete list
    And I press "Save changes"
    And I should see "User 11" in the "Report2" "block"
    And I follow "Go to full report"
    And I should see "Report builder" in the ".breadcrumb" "css_element"
    And I should see "Report2" in the ".breadcrumb" "css_element"

  Scenario: Create a Report block as normal user
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
    And I switch editing mode on
    And I add the "Report" block
    And I should see "Please configure this block and select which report it should display." in the "Report" "block"
    # Check that block is not shown while not editing the page.
    And I switch editing mode off
    And "Report" "block" should not exist
    And I switch editing mode on
    And I configure the "Report" block
    And I open the autocomplete suggestions list
    And "Report2" "autocomplete_suggestions" should not exist
    And "Report3" "autocomplete_suggestions" should not exist
    And I click on "Report1" item in the autocomplete list
    And I press "Save changes"
    And I should see "User 11" in the "Report1" "block"

  Scenario: Edit pagination setting in Report block
    # Create two blocks with different pagination setting.
    When I log in as "tenantadmin1"
    Then I switch editing mode on
    And I add the "Report" block
    And I configure the "Report" block
    And I set the following fields to these values:
      | Select report     | Report1 |
      | Entries per page  | 5       |
    And I press "Save changes"
    And I add the "Report" block
    And I configure the "Report" block
    And I set the following fields to these values:
      | Block title       | Report1B |
      | Select report     | Report1  |
      | Entries per page  | 10       |
    And I press "Save changes"
    And I should see "User 11" in the "Report1" "block"
    And I should not see "User 16" in the "Report1" "block"
    And I should see "User 11" in the "Report1B" "block"
    And I should see "User 16" in the "Report1B" "block"
    # Change to page 2.
    And I click on "2" "link" in the "Report1" "block"
    And I should see "User 16" in the "Report1" "block"

  Scenario: Edit layout setting in Report block
    When I log in as "tenantadmin1"
    And I change window size to "large"
    Then I switch editing mode on
    And I add the "Report" block
    And I configure the "Report" block
    And I set the following fields to these values:
      | Select report     | Report1 |
      | Layout            | Cards   |
      | Region            | content |
      | Weight            | -1      |
    And I press "Save changes"
    # Forcing Card view show cards also in large screens.
    # In card view only the first column is visible (name), the email will not be visible
    And I should see "User 11" in the "Report1" "block"
    And I should not see "user11@invalid.com" in the "Report1" "block"
    And I am on homepage
    And I configure the "Report1" block
    And I set the following fields to these values:
      | Layout            | Table   |
    And I press "Save changes"
    And I change window size to "530x678"
    # Forcing Table view show table also in small screens.
    And I should see "User 11" in the "Report1" "block"
    And I should see "user11@invalid.com" in the "Report1" "block"
    And I am on homepage
    And I configure the "Report1" block
    And I set the following fields to these values:
      | Layout            | Adaptive   |
    And I press "Save changes"
    # Adaptive view show cards in small screens.
    And I should see "User 11" in the "Report1" "block"
    And I should not see "user11@invalid.com" in the "Report1" "block"
    And I change window size to "large"
    # Adaptive view show table in large screens.
    And I should see "User 11" in the "Report1" "block"
    And I should see "user11@invalid.com" in the "Report1" "block"

  Scenario: Add a Report block in a page with a system report
    When I log in as "admin"
    # Go to 'Report Builder' page because it has a system report.
    And I navigate to "Report builder" in workplace launcher
    And I should see "Report1" in the ".system-report" "css_element"
    And I should not see "User 11" in the ".system-report" "css_element"
    # Now add a Report block.
    And I switch editing mode on
    And I add the "Report" block
    And I configure the "Report" block
    And I set the following fields to these values:
      | Block title        | Users report |
      | Select report     | Report1      |
    And I press "Save changes"
    And I should see "User 11" in the "Users report" "block"
    And I should not see "Report1" in the "Users report" "block"
    # Filter and reset the system report.
    And I click on "Show/hide filters sidebar" "button" in the "region-main" "region"
    And I set the field "tool_reportbuilder:source_op" to "isn't equal to"
    And I click on "Reset table" "button" in the "region-main" "region"
    # Check that both reports are showing the correct data.
    And I should see "Report1" in the ".system-report" "css_element"
    And I should not see "User 11" in the ".system-report" "css_element"
    And I should see "User 11" in the "Users report" "block"
    And I should not see "Report1" in the "Users report" "block"
