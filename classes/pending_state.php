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
 * Storage helper for pending per-teacher per-quiz notification state.
 */
class pending_state {
    /**
     * Returns true when a teacher should be suppressed from receiving
     * another notification for this quiz module.
     *
     * Suppression is active when there is a pending notification, or when
     * a notification was sent recently within the configured delay window.
     *
     * @param int $cmid
     * @param int $userid
     * @param int $delayseconds
     * @return bool
     */
    public static function has_pending(int $cmid, int $userid, int $delayseconds = 0): bool {
        global $DB;

        $record = $DB->get_record('local_quizgradingnotify_pnd', [
            'cmid' => $cmid,
            'userid' => $userid,
        ], 'id, pending, timesent');

        if (!$record) {
            return false;
        }

        if (!empty($record->pending)) {
            return true;
        }

        $timesent = (int) $record->timesent;
        if ($timesent <= 0) {
            return false;
        }

        $delayseconds = max(0, $delayseconds);
        if ($delayseconds === 0) {
            return false;
        }

        return (time() - $timesent) < $delayseconds;
    }

    /**
     * Marks a teacher as having a pending notification for a quiz module.
     *
     * @param int $cmid
     * @param int $userid
     * @return void
     */
    public static function mark_pending(int $cmid, int $userid): void {
        global $DB;

        $now = time();

        $existing = $DB->get_record('local_quizgradingnotify_pnd', [
            'cmid' => $cmid,
            'userid' => $userid,
        ]);

        if ($existing) {
            $existing->pending = 1;
            $existing->timesent = $now;
            $existing->timemodified = $now;
            $DB->update_record('local_quizgradingnotify_pnd', $existing);
            return;
        }

        $DB->insert_record('local_quizgradingnotify_pnd', [
            'cmid' => $cmid,
            'userid' => $userid,
            'pending' => 1,
            'timesent' => $now,
            'timeacked' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }

    /**
     * Clears pending state after teacher acknowledgement.
     *
     * @param int $cmid
     * @param int $userid
     * @return void
     */
    public static function clear_pending(int $cmid, int $userid): void {
        global $DB;

        $existing = $DB->get_record('local_quizgradingnotify_pnd', [
            'cmid' => $cmid,
            'userid' => $userid,
        ]);

        if (!$existing || !$existing->pending) {
            return;
        }

        $existing->pending = 0;
        $existing->timeacked = time();
        $existing->timemodified = time();
        $DB->update_record('local_quizgradingnotify_pnd', $existing);
    }
}
