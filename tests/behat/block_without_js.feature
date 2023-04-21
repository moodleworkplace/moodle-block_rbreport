@block @block_rbreport @moodleworkplace
Feature: The Report block without javascript
  Testing report visibility when reprot block is not configured

  Scenario: Block created without configuration is not visible without editing mode
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | user11   | User      | One      | one@example.com |
    # Add the block as user11.
    And I log in as "user11"
    And I switch editing mode on
    And I add the "Report" block
    And I should see "Please configure this block and select which report it should display." in the "Report" "block"
    # Check that block is not shown while not editing the page.
    And I switch editing mode off
    And "Report" "block" should not exist
