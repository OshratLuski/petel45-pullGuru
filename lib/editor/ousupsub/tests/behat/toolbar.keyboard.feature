@ou @ouvle @editor @editor_ousupsub @_bug_phantomjs
Feature: ousupsub superscript toolbar
  To undo a redo text changes in ousupsub, I need to use the undo and redo key combinations.

  @javascript
  Scenario: Verify toolbar buttons are accessible
    Given I log in as "admin"
    And I am on the integrated "both" editor test page
    # Set the initial text
    And I set the field "Input" to "Helicopter"
    Then I should see "Helicopter" in the "Input" ousupsub editor

    Then I select the text in the "Input" ousupsub editor

    # Verify toolbar superscript button can be selected by tab index
    And I select and click the first button in the "Input" ousupsub editor
    Then I should see "<sup>Helicopter</sup>" in the "Input" ousupsub editor
