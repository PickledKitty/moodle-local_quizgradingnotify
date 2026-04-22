<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Step definitions for local_quizgradingnotify.
 *
 * @package   local_quizgradingnotify
 * @category  test
 * @copyright 2026 Rebecca Trynes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Step definitions for local_quizgradingnotify.
 *
 * @copyright 2026 Rebecca Trynes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_quizgradingnotify extends behat_base {
    /**
     * Set the grading notification method for a quiz.
     *
     * @Given /^the quiz "(?P<quizname_string>(?:[^"]|\\")*)" has grading notification method "(?P<method_string>(?:[^"]|\\")*)"$/
     * @param string $quizname
     * @param string $method
     */
    public function the_quiz_has_grading_notification_method(string $quizname, string $method): void {
        global $DB;

        $allowed = ['none', 'email', 'popup'];
        if (!in_array($method, $allowed, true)) {
            throw new Exception("Unsupported grading notification method '$method'.");
        }

        $quiz = $DB->get_record('quiz', ['name' => $quizname], 'id,course', MUST_EXIST);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course, false, MUST_EXIST);

        $existing = $DB->get_record('local_quizgradingnotify_cfg', ['cmid' => $cm->id]);
        if ($existing) {
            $existing->method = $method;
            $existing->timemodified = time();
            $DB->update_record('local_quizgradingnotify_cfg', $existing);
            return;
        }

        $DB->insert_record('local_quizgradingnotify_cfg', [
            'cmid' => $cm->id,
            'method' => $method,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * Assert the saved grading notification method for a quiz.
     *
     * @Then /^the quiz "(?P<quizname_str>(?:[^"]|\\")*)" should have grading notification method "(?P<method_str>(?:[^"]|\\")*)"/
     * @param string $quizname
     * @param string $method
     */
    public function the_quiz_should_have_grading_notification_method(string $quizname, string $method): void {
        global $DB;

        $quiz = $DB->get_record('quiz', ['name' => $quizname], 'id,course', MUST_EXIST);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $quiz->course, false, MUST_EXIST);
        $setting = $DB->get_record('local_quizgradingnotify_cfg', ['cmid' => $cm->id], 'method', MUST_EXIST);

        if ($setting->method !== $method) {
            throw new Exception(
                "Expected method '$method' for quiz '$quizname', got '{$setting->method}'."
            );
        }
    }
}
