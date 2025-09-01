@editor @editor_ousupsub @_bug_phantomjs
Feature: Sup/Sub standalone editor
  To format text in ousupsub, I need to use both the superscript and subscript buttons.
  The tests must be built initially in a specific order because we are relying on CSS selectors to select
  specific pieces of text. It is very easy to get into a situation where pieces of text cannot be selected.

  We are using Rangy to create a selection range between specific nodes https://code.google.com/p/rangy/wiki/RangySelection
  and document.querySelector() https://developer.mozilla.org/en-US/docs/Web/API/document.querySelector
  to select the specific nodes. This requires css selector syntax http://www.w3schools.com/cssref/css_selectors.asp

  Many of these tests highlight specific bugs in the existing atto/ousupsub editor implementation while also
  demonstrating how to use the text selection behat method created for this plugin.

  @javascript
  Scenario: Applying Subscript and Superscript on in the standalone editor
    Given I am on the stand-alone supsub editor test page
    And I set the "Both Superscript and Subscript allowed" stand-alone ousupsub editor to "Superscript and Subscript"

    # Apply subscript
    When I select the range "'',16,'',25" in the "Both Superscript and Subscript allowed" ousupsub editor
    And I click on "Subscript" "button"
    Then I should see "Superscript and <sub>Subscript</sub>" in the "Both Superscript and Subscript allowed" ousupsub editor

    # Apply superscript
    When I select the range "'',0,'',11" in the "Both Superscript and Subscript allowed" ousupsub editor
    And I click on "Superscript" "button"
    Then I should see "<sup>Superscript</sup> and <sub>Subscript</sub>" in the "Both Superscript and Subscript allowed" ousupsub editor

    # Return superscript to normal
    When I select the range "'sup',0,'sup',11" in the "Both Superscript and Subscript allowed" ousupsub editor
    And I click on "Superscript" "button"
    Then I should see "Superscript and <sub>Subscript</sub>" in the "Both Superscript and Subscript allowed" ousupsub editor

    # Return subscript to normal
    When I select the range "'sub',0,'sub',9" in the "Both Superscript and Subscript allowed" ousupsub editor
    And I click on "Subscript" "button"
    Then I should see "Superscript and Subscript" in the "Both Superscript and Subscript allowed" ousupsub editor

    # Apply subscript across existing superscript
    And I set the "Both Superscript and Subscript allowed" stand-alone ousupsub editor to "Super<sup>script</sup> and Subscript"
    And I select the range "'sup',3,2,8" in the "Both Superscript and Subscript allowed" ousupsub editor
    And I click on "Subscript" "button"
    Then I should see "Super<sup>scr</sup><sub>ipt and Sub</sub>script" in the "Both Superscript and Subscript allowed" ousupsub editor

    # Apply superscript across existing subscript
    And I select the range "'sup',2,'sub',3" in the "Both Superscript and Subscript allowed" ousupsub editor
    And I click on "Superscript" "button"
    Then I should see "Super<sup>scr</sup>ipt <sub>and Sub</sub>script" in the "Both Superscript and Subscript allowed" ousupsub editor
