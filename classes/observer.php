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
 * moodle-local_quizgradingnotify settings.
 *
 * @package   local_quizgradingnotify
 * @copyright 2026 Rebecca Trynes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quizgradingnotify;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for quiz attempt events.
 */
class observer {

    /**
     * Fired on attempt_submitted (and attempt_becameoverdue).
     * Checks whether the quiz has a notification setting and whether any
     * questions in the attempt require manual grading, then delegates to
     * the appropriate notifier strategy.
     *
     * @param \core\event\base $event
     */
    public static function attempt_submitted(\core\event\base $event): void {
        global $DB;

        // $event->contextinstanceid is the course-module ID for quiz events.
        $cmid = $event->contextinstanceid;

        $setting = $DB->get_record('local_quizgradingnotify_cfg', ['cmid' => $cmid]);
        if (!$setting || $setting->method === 'none') {
            return;
        }

        if (!self::has_manual_questions((int) $event->objectid)) {
            return;
        }

        $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);

        $notifier = notifier_factory::make($setting->method);
        $notifier->notify($event, $cm);
    }

    /**
     * Returns true if the given attempt has at least one step in the
     * 'needsgrading' state (i.e. a manually graded question awaiting a mark).
     *
     * @param  int  $attemptid
     * @return bool
     */
    private static function has_manual_questions(int $attemptid): bool {
        global $DB;

        return $DB->record_exists_sql(
            "SELECT 1
               FROM {question_attempt_steps} qas
               JOIN {question_attempts} qa ON qa.id = qas.questionattemptid
              WHERE qa.questionusageid = (SELECT uniqueid FROM {quiz_attempts} WHERE id = :attemptid)
                AND qas.state = 'needsgrading'",
            ['attemptid' => $attemptid]
        );
    }
}
