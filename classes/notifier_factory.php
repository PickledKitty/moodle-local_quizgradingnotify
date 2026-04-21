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
 * Factory that returns the correct notifier strategy for a given method string.
 */
class notifier_factory {

    /**
     * Instantiate and return the appropriate notifier.
     *
     * @param  string             $method One of: email | popup
     * @return notifier_interface
     * @throws \coding_exception  If an unknown method is requested.
     */
    public static function make(string $method): notifier_interface {
        return match ($method) {
            'email'    => new notifier\email(),
            'popup'    => new notifier\popup(),
            default    => throw new \coding_exception(
                "local_quizgradingnotify: unknown notification method '$method'"),
        };
    }
}
