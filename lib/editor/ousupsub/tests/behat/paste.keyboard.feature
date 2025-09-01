@ou @ouvle @editor @editor_ousupsub @_bug_phantomjs
Feature: Pasting into the Sup/Sub editor
  To undo a redo text changes in ousupsub, I need to use the undo and redo key combinations.

  @javascript
  Scenario: Verify Paste by adding text to the editor via paste events
    Given I log in as "admin"
    And I am on the integrated "both" editor test page
    # Set the initial text
    And I paste the text "Helicopter" in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor

    #Verify paste events can be undone
    When I press the undo key in the "Input" ousupsub editor
    Then I should see "" in the "Input" ousupsub editor

    # Verify redo key works
    When I press the redo key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor
