@qtype @qtype_essayrubric
Feature: Test creating an Essayrubric question
  As a teacher
  In order to test my students
  I need to be able to create an Essayrubric question

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |

  Scenario: Create an Essayrubric question with Response format set to 'HTML editor'
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Essayrubric" question filling the form with:
      | Question name            | essayrubric-001                      |
      | Question text            | Write an essayrubric with 500 words. |
      | General feedback         | This is general feedback       |
      | Response format          | HTML editor                    |
    Then I should see "essayrubric-001"

  Scenario: Create an Essayrubric question with Response format set to 'HTML editor with the file picker'
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Essayrubric" question filling the form with:
      | Question name            | essayrubric-002                      |
      | Question text            | Write an essayrubric with 500 words. |
      | General feedback         | This is general feedback       |
      | id_responseformat        | editorfilepicker               |
    Then I should see "essayrubric-002"

  @javascript
  Scenario: Create an Essayrubric question for testing some default options
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Essayrubric" question filling the form with:
      | Question name          | essayrubric-003                      |
      | Question text          | Write an essayrubric with 500 words. |
      | General feedback       | This is general feedback       |
      | id_responseformat      | editorfilepicker               |
      | id_responserequired    | 0                              |
      | id_responsefieldlines  | 15                             |
      | id_attachments         | 2                              |
      | id_attachmentsrequired | 2                              |
      | id_maxbytes            | 10240                          |
    Then I should see "essayrubric-003"
    # Checking that the next new question form displays user preferences settings.
    And I press "Create a new question ..."
    And I set the field "item_qtype_essayrubric" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And the following fields match these values:
      | id_responseformat      | editorfilepicker |
      | id_responserequired    | 0                |
      | id_responsefieldlines  | 15               |
      | id_attachments         | 2                |
      | id_attachmentsrequired | 2                |
      | id_maxbytes            | 10240            |
