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

/**
 * Interface for all grading notification strategies.
 */
interface notifier_interface {
    /**
     * Send (or store) the grading-required notification.
     *
     * @param \core\event\base $event  The quiz attempt event.
     * @param \stdClass        $cm     The course-module record for the quiz.
     */
    public function notify(\core\event\base $event, \stdClass $cm): void;
}
