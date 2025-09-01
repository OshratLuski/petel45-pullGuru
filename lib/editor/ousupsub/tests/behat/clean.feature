@ou @ouvle @editor @editor_ousupsub @_bug_phantomjs
Feature: OU sub sub editor input cleaning test
  In order to have my input easily processed
  As a Moodle user
  I need the supsub editor to clearn up the input as much as possible

  @javascript
  Scenario: Superscript some text
    Given I log in as "admin"
    And I am on the integrated "sup" editor test page

    # Verify superscript applied before entering space and text leads to superscript text
    When I press the superscript key in the "Input" ousupsub editor
    And I enter the text "H" in the "Input" ousupsub editor
    Then I should see "<sup>H</sup>" in the "Input" ousupsub editor

    # Verify subscript applied before entering space and text leads to normal text
    When I press the subscript key in the "Input" ousupsub editor
    And I enter the text " e" in the "Input" ousupsub editor
    Then I should see "<sup>H</sup> e" in the "Input" ousupsub editor

    # Verify subscript applied before entering space and text leads to normal text
    When I press the superscript key in the "Input" ousupsub editor
    #And I enter the text " e" in the "Input" ousupsub editor
    Then I should see "<sup>H</sup> e" in the "Input" ousupsub editor
