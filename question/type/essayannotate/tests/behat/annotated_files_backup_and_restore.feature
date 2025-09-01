@qtype @qtype_essayannotate @_file_upload
Feature: Annotated files can be backed up and restored
  As a teacher
  I need to be able to backup and restore the annotated files

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
      | student  |
    And path to gs and covert is set
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext    | defaultmark | template |
      | Test questions   | essayannotate       | TF1   | First question  | 20          | editorfilepicker |
    And the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | grade |
      | quiz       | Quiz 1 | Quiz 1 description | C1     | quiz1    | 20    |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    And the following "blocks" exist:
      | blockname     | contextlevel | reference | pagetypepattern | defaultregion |
      | private_files | System       | 1         | my-index        | side-post     |
    And the following "user private files" exist:
      | user    | filepath                                        | filename  |
      | student | question/type/essayannotate/tests/fixtures/blank.pdf | blank.pdf |

  @javascript
  Scenario: Backup and restore a course with quiz attempt containing essayannotate question and annotated pdf
    When I log in as "student"
    And I am on the "Quiz 1" "quiz activity" page
    And I press "Attempt quiz"
    And I should see "First question"
    And I should see "You can drag and drop files here to add them."
    And I click on "Add..." "button"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "blank.pdf" "link"
    And I click on "Select this file" "button"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit" "button" in the "Submit all your answers and finish?" "dialogue"
    And I log out

    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    And I follow "Make comment or override mark"
    And I wait "3" seconds
    Then The document should open in a new tab
    And I follow "Annotate"
    And I wait "3" seconds
    Then shift focus to the latest tab
    And I annotate the pdf
    And I press "Save"
    And I wait "1" seconds
    And I should see "File has been saved"
    And I switch to main window
    And I follow "Make comment or override mark"
    And I wait "3" seconds
    Then The document should open in a new tab
    And I set the field "Mark" to "10"
    And I wait "3" seconds
    And I press "Save"
    And I switch to main window
    And I wait "5" seconds
    And I reload the page
    And I wait "3" seconds
    And I should see "Corrected Documents"
    And I wait "3" seconds
    And I log out

    When I am on the "Course 1" course page logged in as admin
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | C2       |
    And I wait "5" seconds
    And I follow "Quiz 1"
    And I follow "Attempts: 1"
    And I follow "Review attempt"
    And I should see "Corrected Documents"
