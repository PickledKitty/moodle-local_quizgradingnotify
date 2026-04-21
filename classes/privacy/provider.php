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

namespace local_quizgradingnotify\privacy;

/**
 * Privacy API implementation.
 *
 * This plugin stores only course-module configuration (method setting) and
 * course-page notification messages. Neither table stores personal user data,
 * so this plugin has no user data to export or delete.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Get the reason why this plugin does not store personal data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return get_string('privacy:metadata', 'local_quizgradingnotify');
    }
}
