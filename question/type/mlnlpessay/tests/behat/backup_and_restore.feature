@qtype @qtype_mlnlpessay
Feature: Test duplicating a quiz containing an mlnlpessay question
  As a teacher
  In order re-use my courses containing mlnlpessay questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name      | template         |
      | Test questions   | mlnlpessay     | mlnlpessay-001 | editor           |
      | Test questions   | mlnlpessay     | mlnlpessay-002 | editorfilepicker |
      | Test questions   | mlnlpessay     | mlnlpessay-003 | plain            |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | mlnlpessay-001 | 1 |
      | mlnlpessay-002 | 1 |
      | mlnlpessay-003 | 1 |

  @javascript
  Scenario: Backup and restore a course containing 3 mlnlpessay questions
    When I am on the "Course 1" course page logged in as admin
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | C2       |
    And I am on the "Course 2" "core_question > course question bank" page
    Then I should see "mlnlpessay-001"
    And I should see "mlnlpessay-002"
    And I should see "mlnlpessay-003"
    And I choose "Edit question" action for "mlnlpessay-001" in the question bank
    Then the following fields match these values:
      | Question name              | mlnlpessay-001                                               |
      | Question text              | Please write a story about a frog.                      |
      | General feedback           | I hope your story had a beginning, a middle and an end. |
      | Response format            | HTML editor                                             |
      | Require text               | Require the student to enter text                       |
    And I press "Cancel"
    And I choose "Edit question" action for "mlnlpessay-002" in the question bank
    Then the following fields match these values:
      | Question name              | mlnlpessay-002                                               |
      | Question text              | Please write a story about a frog.                      |
      | General feedback           | I hope your story had a beginning, a middle and an end. |
      | Response format            | HTML editor with file picker                            |
      | Require text               | Require the student to enter text                       |
    And I press "Cancel"
    And I choose "Edit question" action for "mlnlpessay-003" in the question bank
    Then the following fields match these values:
      | Question name              | mlnlpessay-003                                               |
      | Question text              | Please write a story about a frog.                      |
      | General feedback           | I hope your story had a beginning, a middle and an end. |
      | Response format            | Plain text                                              |
      | Require text               | Require the student to enter text                       |
