@local @local_quizgradingnotify @javascript
Feature: Deliver quiz grading notifications on attempt submission
  In order to know when manual grading is required
  As a teacher
  I need a notification after students submit quizzes with manually graded questions

  Background:
    Given the following config values are set as admin:
      | sendcoursewelcomemessage | 0 | enrol_manual |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "user preferences" exist:
      | user     | preference                                                            | value |
      | teacher1 | message_provider_local_quizgradingnotify_grading_required_enabled   | popup |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name  | questiontext   | defaultmark |
      | Test questions   | essay | Essay | Essay question | 10          |
    And the following "activities" exist:
      | activity | course | idnumber | name   | intro     | section |
      | quiz     | C1     | quiz1    | Quiz 1 | Quiz intro | 1      |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | Essay    | 1    |

  Scenario Outline: Notification delivery depends on configured method
    Given the quiz "Quiz 1" has grading notification method "<method>"
    And I log in as "teacher1"
    And I open the notification popover
    And I should see "You have no notifications"
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I follow "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I log in as "teacher1"
    And I open the notification popover
    Then I should see "<expectedtext>"

    Examples:
      | method | expectedtext                                  |
      | popup  | one or more questions require manual grading. |
      | none   | You have no notifications                      |
