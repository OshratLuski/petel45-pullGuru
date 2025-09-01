@ou @ouvle @editor @editor_ousupsub @_bug_phantomjs
Feature: ousupsub superscript keyboard interface
  To format text in ousupsub, I need to use the superscript button. It works a specific way
  when only the superscript button is available.

  @javascript
  Scenario: Superscript some text using the keyboard interface
    Given I log in as "admin"
    And I am on the integrated "sup" editor test page
    Then "ancestor-or-self::button[contains(@title, 'Shift + ^ or Up arrow')]" "xpath_element" should exist in the "button.ousupsub_superscript_button_superscript" "css_element"

    # Verify superscript key ^ applies superscript
    When I set the field "Input" to "Helicopter"
    And I select the text in the "Input" ousupsub editor
    And I press the superscript key in the "Input" ousupsub editor
    Then I should see "<sup>Helicopter</sup>" in the "Input" ousupsub editor

    # Verify cannot add further superscript
    When I press the superscript key in the "Input" ousupsub editor
    Then I should see "<sup>Helicopter</sup>" in the "Input" ousupsub editor

    # Verify subscript key _ removes superscript
    When I press the subscript key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor

    # Verify subscript key cannot apply subscript
    When I press the subscript key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor

    # Verify up arrow applies superscript
    When I set the field "Input" to "Helicopter"
    And I select the text in the "Input" ousupsub editor
    And I press the up arrow key in the "Input" ousupsub editor
    Then I should see "<sup>Helicopter</sup>" in the "Input" ousupsub editor

    # Verify pressing up arrow again does nothing
    When I press the up arrow key in the "Input" ousupsub editor
    Then I should see "<sup>Helicopter</sup>" in the "Input" ousupsub editor

    # Verify down arrow removes superscript
    When I press the down arrow key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor

    # Verify pressing down arrow again does nothing
    When I press the down arrow key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor
