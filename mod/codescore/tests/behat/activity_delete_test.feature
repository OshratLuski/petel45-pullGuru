@mod @mod_codescore
Feature: In an codescore, admin can delete the activity from course
    Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "activities" exist:
      | activity  | name              | intro                       | course | idnumber   |
      | codescore | Codescore1        | Topics forum description    | C1     | codescore1 |

  @javascript
  Scenario Outline: Hide and display page features
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I click on ".codescore .dropdown-toggle" "css_element"
    And I click on ".codescore .editing_delete" "css_element"
    And I click on ".modal-footer .btn-primary" "css_element"
    Then I should not see "Codescore1"

    Examples:
      | example           |
      | 1                 |