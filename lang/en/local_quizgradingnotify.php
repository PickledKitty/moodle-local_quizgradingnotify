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
defined('MOODLE_INTERNAL') || die();

// Plugin name.
$string['email_body'] = 'A student has submitted the quiz "{$a->quizname}" in course {$a->courseshortname} and one or more questions require manual grading.

Please visit the grading report to review and mark the submission:
{$a->url}';
$string['email_bodyhtml'] = '<p>A student has submitted the quiz <strong>{$a->quizname}</strong> in course <strong>{$a->courseshortname}</strong> and one or more questions require manual grading.</p><p><a href="{$a->url}">Open the grading report</a></p>';
$string['email_subject'] = 'Quiz grading required: {$a->quizname}';
$string['grading_report'] = 'Open grading report';
$string['gradingnotifications'] = 'Grading notifications';
$string['gradingnotifymethod'] = 'Notify when questions require grading';
$string['gradingnotifymethod_help'] = 'Choose how teachers are alerted when a student submits a quiz that contains manually graded questions (e.g. essay questions).

**None** – No notification is sent.

**Email** – An email is sent to all teachers who can grade the quiz.

**Moodle notification** – A notification appears under the bell icon in Moodle. Delivery respects each teacher\'s messaging preferences.';
$string['gradingnotifydelay'] = 'Notification delay';
$string['gradingnotifydelay_help'] = 'Choose how long to wait before another notification can be sent for the same quiz after you have acknowledged the previous one by opening the Overview report, the Manual grading report, or an individual attempt review page.

The first notification is sent immediately when a submission requires manual grading.

While a notification is pending, repeats are suppressed.

After pending is cleared, this delay controls when another notification may be sent.';
$string['messageprovider:grading_required'] = 'Quiz grading required';
$string['notifydelay_1hour'] = 'Delay 1 hour';
$string['notifydelay_2hours'] = 'Delay 2 hours';
$string['notifydelay_none'] = 'No delay';
$string['notify_email'] = 'Email';
$string['notify_none'] = 'None';
$string['notify_popup'] = 'Moodle notification (bell)';
$string['pluginname'] = 'Quiz grading notifications';
$string['popup_body'] = 'A student has submitted "{$a->quizname}" and one or more questions require manual grading.';
$string['popup_small'] = 'Quiz grading required';
$string['popup_subject'] = 'Grading required: {$a->quizname}';
$string['privacy:metadata'] = 'The Quiz grading notifications plugin stores only quiz configuration settings. It does not store any personal user data.';
$string['quizgradingnotify:configure'] = 'Configure quiz grading notifications';
