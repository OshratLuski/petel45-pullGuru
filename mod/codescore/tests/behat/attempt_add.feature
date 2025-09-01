@mod @mod_codescore
Feature: In an codescore, we can add attempt in module
    Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |

  @javascript
  Scenario Outline: Hide and display page features
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I press "Add an activity or resource"
    And I click on "Add a new Codescore" "link"
    And I set the field "Name" to "Codescore name"
    And I set the field "Write task" to "Codescore task"
    And I set the field "Select programming language" to "1"
    And I press "Save and display"
    And I click on "Start attempt" "link"
    And I click on "#submitattemptbutton" "css_element"
    Then I should see "Your attempt:"

    Examples:
      | example           |
      | 1                 |