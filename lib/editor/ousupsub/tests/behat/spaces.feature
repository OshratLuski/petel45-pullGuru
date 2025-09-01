@ou @ouvle @editor @editor_ousupsub @_bug_phantomjs
Feature: Entering spaces into the Sup/Sub editor
  To format text in ousupsub, I need to use the superscript button.

  @javascript
  Scenario: Enter text containing a space
    Given I log in as "admin"
    And I am on the integrated "sup" editor test page

    When I set the field "Input" to "H"
    And I select the text in the "Input" ousupsub editor
    And I click on "Superscript" "button"
    And I select the range "'sup',1,'sup',1" in the "Input" ousupsub editor
    And I click on "Superscript" "button"
    Then I should see "<sup>H</sup>" in the "Input" ousupsub editor

    When I enter the text " " in the "Input" ousupsub editor
    Then I should see "<sup>H</sup> " in the "Input" ousupsub editor

    When I enter the text "e" in the "Input" ousupsub editor
    Then I should see "<sup>H</sup> e" in the "Input" ousupsub editor
