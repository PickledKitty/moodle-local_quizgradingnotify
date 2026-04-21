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

namespace local_quizgradingnotify\notifier;

defined('MOODLE_INTERNAL') || die();

/**
 * Sends a plain email to all enrolled users who hold mod/quiz:grade.
 */
class email implements \local_quizgradingnotify\notifier_interface {

    public function notify(\core\event\base $event, \stdClass $cm): void {
        $context  = \context_module::instance($cm->id);
        $teachers = get_enrolled_users($context, 'mod/quiz:grade');

        if (empty($teachers)) {
            return;
        }

        $gradingurl = new \moodle_url('/mod/quiz/report.php', [
            'id'   => $cm->id,
            'mode' => 'grading',
        ]);

        $course = get_course($cm->course);

        $a = (object) [
            'quizname'        => $cm->name,
            'courseshortname' => $course->shortname,
            'url'             => $gradingurl->out(false),
        ];

        $subject  = get_string('email_subject', 'local_quizgradingnotify', $a);
        $bodytext = get_string('email_body',    'local_quizgradingnotify', $a);
        $bodyhtml = get_string('email_bodyhtml','local_quizgradingnotify', $a);
        $noreply  = \core_user::get_noreply_user();

        foreach ($teachers as $teacher) {
            email_to_user($teacher, $noreply, $subject, $bodytext, $bodyhtml);
        }
    }
}
