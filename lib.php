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

/**
 * Adds the grading notification setting to the quiz mod_form.
 *
 * @param moodleform_mod $formwrapper
 * @param MoodleQuickForm $mform
 */
function local_quizgradingnotify_coursemodule_standard_elements($formwrapper, $mform): void {
    global $DB;

    if ($formwrapper->get_current()->modulename !== 'quiz') {
        return;
    }

    $cmid = $formwrapper->get_coursemodule()->id ?? 0;
    if ($cmid) {
        $context = \context_module::instance($cmid);
        if (!has_capability('local/quizgradingnotify:configure', $context)) {
            return;
        }
    }

    $mform->addElement(
        'header',
        'gradingnotifyheader',
        get_string('gradingnotifications', 'local_quizgradingnotify')
    );

    $options = [
        'none' => get_string('notify_none', 'local_quizgradingnotify'),
        'email' => get_string('notify_email', 'local_quizgradingnotify'),
        'popup' => get_string('notify_popup', 'local_quizgradingnotify'),
    ];

    $mform->addElement(
        'select',
        'gradingnotifymethod',
        get_string('gradingnotifymethod', 'local_quizgradingnotify'),
        $options
    );
    $mform->setDefault('gradingnotifymethod', 'none');
    $mform->setType('gradingnotifymethod', PARAM_ALPHA);
    $mform->addHelpButton(
        'gradingnotifymethod',
        'gradingnotifymethod',
        'local_quizgradingnotify'
    );

    $delayoptions = [
        0 => get_string('notifydelay_none', 'local_quizgradingnotify'),
        3600 => get_string('notifydelay_1hour', 'local_quizgradingnotify'),
        7200 => get_string('notifydelay_2hours', 'local_quizgradingnotify'),
    ];

    $mform->addElement(
        'select',
        'gradingnotifydelay',
        get_string('gradingnotifydelay', 'local_quizgradingnotify'),
        $delayoptions
    );
    $mform->setDefault('gradingnotifydelay', 0);
    $mform->setType('gradingnotifydelay', PARAM_INT);
    $mform->addHelpButton(
        'gradingnotifydelay',
        'gradingnotifydelay',
        'local_quizgradingnotify'
    );

    // Pre-populate if editing an existing quiz.
    if ($cmid) {
        $setting = $DB->get_record('local_quizgradingnotify_cfg', ['cmid' => $cmid]);
        if ($setting) {
            $mform->setDefault('gradingnotifymethod', $setting->method);
            $mform->setDefault('gradingnotifydelay', (int) ($setting->delayseconds ?? 0));
        }
    }
}

/**
 * Saves the grading notification setting after the quiz form is submitted.
 *
 * @param stdClass $data
 * @param stdClass $course
 * @return stdClass
 */
function local_quizgradingnotify_coursemodule_edit_post_actions($data, $course): stdClass {
    global $DB;

    // Only act on quiz modules – other module types must pass through untouched.
    $cm = get_coursemodule_from_id('quiz', $data->coursemodule, 0, false, IGNORE_MISSING);
    if (!$cm) {
        return $data;
    }

    $method  = isset($data->gradingnotifymethod) ? $data->gradingnotifymethod : 'none';
    $delayseconds = isset($data->gradingnotifydelay) ? (int) $data->gradingnotifydelay : 0;
    $allowed = ['none', 'email', 'popup'];
    $alloweddelays = [0, 3600, 7200];
    if (!in_array($method, $allowed, true)) {
        $method = 'none';
    }
    if (!in_array($delayseconds, $alloweddelays, true)) {
        $delayseconds = 0;
    }

    $existing = $DB->get_record('local_quizgradingnotify_cfg', ['cmid' => $cm->id]);
    if ($existing) {
        $existing->method       = $method;
        $existing->delayseconds = $delayseconds;
        $existing->timemodified = time();
        $DB->update_record('local_quizgradingnotify_cfg', $existing);
    } else {
        $DB->insert_record('local_quizgradingnotify_cfg', [
            'cmid'         => $cm->id,
            'method'       => $method,
            'delayseconds' => $delayseconds,
            'timecreated'  => time(),
            'timemodified' => time(),
        ]);
    }

    return $data;
}
