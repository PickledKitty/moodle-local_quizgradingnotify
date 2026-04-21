@local @local_quizgradingnotify
Feature: Configure quiz grading notification method
  In order to choose how teachers are alerted for manual quiz grading
  As a teacher
  I need to save a grading notification method on quiz settings

  Scenario Outline: Teacher saves grading notification method for a quiz
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | idnumber | name   | intro     | section |
      | quiz     | C1     | quiz1    | Quiz 1 | Quiz intro | 1      |
    When I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > Edit" page
    And I expand all fieldsets
    And I set the field "gradingnotifymethod" to "<methodlabel>"
    And I press "Save and display"
    Then the quiz "Quiz 1" should have grading notification method "<methodvalue>"

    Examples:
      | methodlabel                | methodvalue |
      | None                       | none        |
      | Email                      | email       |
      | Moodle notification (bell) | popup       |
