Feature: Test the filter method of filter.php
  As a developer
  I want to test the filter method of filter.php
  So that I can ensure it works correctly
  Scenario: Test filter method with test cases 30 to 70
    Given I am on the homepage
    When I fill in "text" with "This is a {questionanswer:30, correct, Correct Answer} and this is a {questionanswer:40, incorrect, Wrong Answer}"
    And I press "filter"
    Then I should see "This is a Correct Answer and this is a "
    When I fill in "text" with "This is a {questionanswer:50, incorrect, Wrong Answer} and this is a {questionanswer:60, correct, Correct Answer}"
    And I press "filter"
    Then I should see "This is a  and this is a Correct Answer"
    When I fill in "text" with "This is a {questionanswer:70, correct, Correct Answer} and this is a {questionanswer:80, incorrect, Wrong Answer}"
    And I press "filter"
    Then I should see "This is a Correct Answer and this is a "