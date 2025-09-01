@ou @ouvle @editor @editor_ousupsub @_bug_phantomjs
Feature: Subscript keyboard interface
  To format text in ousupsub, I need to use the subscript keys. It works a specific way
  when only the subscript button is available.

  @javascript
  Scenario: Apply Subscript some text using the keyboard interface
    Given I log in as "admin"
    And I am on the integrated "sub" editor test page
    Then "ancestor-or-self::button[contains(@title, 'Shift + _ or Down arrow')]" "xpath_element" should exist in the "button.ousupsub_subscript_button_subscript" "css_element"

    # Verify subscript key _ applies subscript
    When I set the field "Input" to "Helicopter"
    And I select the text in the "Input" ousupsub editor
    And I press the subscript key in the "Input" ousupsub editor
    Then I should see "<sub>Helicopter</sub>" in the "Input" ousupsub editor

    # Verify cannot add further subscript
    When I press the subscript key in the "Input" ousupsub editor
    Then I should see "<sub>Helicopter</sub>" in the "Input" ousupsub editor

    # Verify superscript key ^ removes subscript
    When I press the superscript key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor

    # Verify superscript key cannot apply superscript
    When I press the superscript key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor

    # Verify down arrow applies subscript
    When I press the down arrow key in the "Input" ousupsub editor
    Then I should see "<sub>Helicopter</sub>" in the "Input" ousupsub editor

    # Verify pressing down arrow again does nothing
    When I press the down arrow key in the "Input" ousupsub editor
    Then I should see "<sub>Helicopter</sub>" in the "Input" ousupsub editor

    # Verify up arrow removes subscript
    When I press the up arrow key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor

    # Verify pressing up arrow again does nothing
    When I press the up arrow key in the "Input" ousupsub editor
    Then I should see "Helicopter" in the "Input" ousupsub editor
