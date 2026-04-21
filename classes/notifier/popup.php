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
 * Sends a Moodle notification (appears under the bell icon) to all
 * enrolled users who hold mod/quiz:grade.
 *
 * Delivery is subject to each teacher's own messaging preferences.
 */
class popup implements \local_quizgradingnotify\notifier_interface {

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

        $a = (object) ['quizname' => $cm->name];

        foreach ($teachers as $teacher) {
            $message                    = new \core\message\message();
            $message->component         = 'local_quizgradingnotify';
            $message->name              = 'grading_required';
            $message->userfrom          = \core_user::get_noreply_user();
            $message->userto            = $teacher;
            $message->subject           = get_string('popup_subject',  'local_quizgradingnotify', $a);
            $message->fullmessage       = get_string('popup_body',     'local_quizgradingnotify', $a);
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml   = '';
            $message->smallmessage      = get_string('popup_small',    'local_quizgradingnotify', $a);
            $message->notification      = 1;
            $message->contexturl        = $gradingurl->out(false);
            $message->contexturlname    = get_string('grading_report', 'local_quizgradingnotify');

            message_send($message);
        }
    }
}
