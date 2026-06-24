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
 * Tests for popup notifier.
 *
 * @package   local_quizgradingnotify
 * @copyright 2026 Rebecca Trynes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \local_quizgradingnotify\notifier\popup
 */
final class notifier_popup_test extends \advanced_testcase {
    /**
     * Ensure popup notifications are sent to teachers with grading capability.
     */
    public function test_notify_sends_popup_to_grading_teacher(): void {
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

        $sink = $this->redirectMessages();
        $notifier = new notifier\popup();
        $notifier->notify($event, $cm);

        $messages = $sink->get_messages_by_component('local_quizgradingnotify');
        $sink->close();

        $this->assertCount(1, $messages);
        $message = reset($messages);
        $this->assertEquals($teacher->id, $message->useridto);
        $this->assertEquals('local_quizgradingnotify', $message->component);
        $this->assertEquals('grading_required', $message->eventtype);
        $this->assertStringContainsString('Grading required: Quiz 1', $message->subject);
        $this->assertStringContainsString('one or more questions require manual grading', $message->fullmessage);
    }

    /**
     * Ensure no popup notifications are sent when there are no enrolled teachers.
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

        $sink = $this->redirectMessages();
        $notifier = new notifier\popup();
        $notifier->notify($event, $cm);

        $this->assertCount(0, $sink->get_messages_by_component('local_quizgradingnotify'));
        $sink->close();
    }

    /**
     * Ensure pending state suppresses duplicate popup sends.
     */
    public function test_notify_suppresses_duplicate_popup_while_pending(): void {
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();

        $editingteacher = $DB->get_record('role', ['shortname' => 'editingteacher'], '*', MUST_EXIST);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $editingteacher->id);

        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'name' => 'Quiz 1',
        ]);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);
        $event = $this->create_attempt_event($cm, $quiz->id, $teacher->id);

        $sink = $this->redirectMessages();
        $notifier = new notifier\popup();
        $notifier->notify($event, $cm);
        $notifier->notify($event, $cm);

        $this->assertCount(1, $sink->get_messages_by_component('local_quizgradingnotify'));
        $this->assertTrue($DB->record_exists('local_quizgradingnotify_pnd', [
            'cmid' => $cm->id,
            'userid' => $teacher->id,
            'pending' => 1,
        ]));
        $sink->close();
    }

    /**
     * Ensure grading-report acknowledgement still respects cooldown before resend.
     */
    public function test_notify_resends_popup_only_after_cooldown_from_grading_report_view(): void {
        global $DB;

        $this->resetAfterTest(true);

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();

        $editingteacher = $DB->get_record('role', ['shortname' => 'editingteacher'], '*', MUST_EXIST);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $editingteacher->id);

        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $course->id,
            'name' => 'Quiz 1',
        ]);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id, false, MUST_EXIST);
        $event = $this->create_attempt_event($cm, $quiz->id, $teacher->id);

        $sink = $this->redirectMessages();
        $notifier = new notifier\popup();
        $notifier->notify($event, $cm);

        $this->assertTrue($DB->record_exists('local_quizgradingnotify_pnd', [
            'cmid' => $cm->id,
            'userid' => $teacher->id,
            'pending' => 1,
        ]));

        $reportevent = \mod_quiz\event\report_viewed::create([
            'context' => \context_module::instance($cm->id),
            'userid' => $teacher->id,
            'other' => [
                'quizid' => $quiz->id,
                'reportname' => 'grading',
            ],
        ]);
        observer::report_viewed($reportevent);

        $this->assertTrue($DB->record_exists('local_quizgradingnotify_pnd', [
            'cmid' => $cm->id,
            'userid' => $teacher->id,
            'pending' => 0,
        ]));

        // Still suppressed immediately after report view because cooldown is active.
        $notifier->notify($event, $cm);
        $this->assertCount(1, $sink->get_messages_by_component('local_quizgradingnotify'));

        // Expire cooldown and verify a new send is allowed.
        $row = $DB->get_record('local_quizgradingnotify_pnd', [
            'cmid' => $cm->id,
            'userid' => $teacher->id,
        ], '*', MUST_EXIST);
        $row->timesent = time() - pending_state::cooldown_seconds() - 1;
        $row->timemodified = time();
        $DB->update_record('local_quizgradingnotify_pnd', $row);

        $notifier->notify($event, $cm);

        $this->assertCount(2, $sink->get_messages_by_component('local_quizgradingnotify'));
        $sink->close();
    }

    /**
     * Creates an attempt submitted event for tests.
     *
     * @param \stdClass $cm
     * @param int $quizid
     * @param int $submitterid
     * @return \mod_quiz\event\attempt_submitted
     */
    private function create_attempt_event(\stdClass $cm, int $quizid, int $submitterid): \mod_quiz\event\attempt_submitted {
        return \mod_quiz\event\attempt_submitted::create([
            'context' => \context_module::instance($cm->id),
            'objectid' => 0,
            'relateduserid' => $submitterid,
            'other' => [
                'submitterid' => $submitterid,
                'quizid' => $quizid,
            ],
        ]);
    }

}
