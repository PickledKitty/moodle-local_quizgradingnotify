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
 * Tests for notifier factory.
 *
 * @package   local_quizgradingnotify
 * @copyright 2026 Rebecca Trynes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \local_quizgradingnotify\notifier_factory
 */
final class notifier_factory_test extends \advanced_testcase {
    /**
     * Test that the email notifier strategy is returned.
     */
    public function test_make_returns_email_notifier(): void {
        $notifier = notifier_factory::make('email');

        $this->assertInstanceOf(notifier_interface::class, $notifier);
        $this->assertInstanceOf(\local_quizgradingnotify\notifier\email::class, $notifier);
    }

    /**
     * Test that the popup notifier strategy is returned.
     */
    public function test_make_returns_popup_notifier(): void {
        $notifier = notifier_factory::make('popup');

        $this->assertInstanceOf(notifier_interface::class, $notifier);
        $this->assertInstanceOf(\local_quizgradingnotify\notifier\popup::class, $notifier);
    }

    /**
     * Test that unknown notifier methods throw an exception.
     */
    public function test_make_throws_for_unknown_method(): void {
        $this->expectException(\coding_exception::class);
        $this->expectExceptionMessage("local_quizgradingnotify: unknown notification method 'sms'");

        notifier_factory::make('sms');
    }
}
