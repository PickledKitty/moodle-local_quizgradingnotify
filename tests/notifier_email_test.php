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

namespace local_quizgradingnotify;

/**
 * Tests for email notifier.
 *
 * @package   local_quizgradingnotify
 * @copyright 2026 Rebecca Trynes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \local_quizgradingnotify\notifier\email
 */
final class notifier_email_test extends \advanced_testcase {
    /**
     * Ensure email notifications are sent to teachers with grading capability.
     */
    public function test_notify_sends_email_to_grading_teacher(): void {
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user([
            'firstname' => 'Teacher',
            'lastname' => 'One',
            'email' => 'teacher1@example.com',
        ]);

        $editingteacher = $DB->get_record('role', ['shortname' => 'editingteacher'], '*', MUST_EXIST);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $editingteacher->id);

        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'name' => 'Quiz 1',
        ]);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);
        $event = \mod_quiz\event\attempt_submitted::create([
            'context' => \context_module::instance($cm->id),
            'objectid' => 0,
            'relateduserid' => $teacher->id,
            'other' => [
                'submitterid' => $teacher->id,
                'quizid' => $quiz->id,
            ],
        ]);

        $sink = $this->redirectEmails();
        $notifier = new notifier\email();
        $notifier->notify($event, $cm);

        $messages = $sink->get_messages();
        $sink->close();

        self::assertCount(1, $messages);
        $message = reset($messages);
        self::assertSame($teacher->email, $message->to);
        self::assertStringContainsString('Quiz grading required', $message->subject);
        self::assertStringContainsString('one or more questions require manual grading',
            quoted_printable_decode($message->body));
    }

    /**
     * Ensure no emails are sent when there are no enrolled teachers.
     */
    public function test_notify_sends_nothing_without_teachers(): void {
        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'name' => 'Quiz 1',
        ]);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);
        $event = \mod_quiz\event\attempt_submitted::create([
            'context' => \context_module::instance($cm->id),
            'objectid' => 0,
            'relateduserid' => 0,
            'other' => [
                'submitterid' => 0,
                'quizid' => $quiz->id,
            ],
        ]);

        $sink = $this->redirectEmails();
        $notifier = new notifier\email();
        $notifier->notify($event, $cm);

        self::assertCount(0, $sink->get_messages());
        $sink->close();
    }
}
