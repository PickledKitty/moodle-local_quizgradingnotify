# local_quizgradingnotify

A Moodle local plugin (compatible with **Moodle 4.4 – 4.5.x**) that adds a
"Notify when questions require grading" setting to every quiz activity.

---

## Features

| Method | What happens |
|---|---|
| **None** | No notification (default) |
| **Email** | Teachers with `mod/quiz:grade` receive an email when a student submits a quiz containing manually graded questions |
| **Moodle notification (bell)** | A notification appears under the bell icon; respects each teacher's messaging preferences |

---

## Installation

1. Copy or clone the `quizgradingnotify` folder into your Moodle installation's
   `local/` directory so the path becomes:
   ```
   /path/to/moodle/local/quizgradingnotify/
   ```
2. Upgrade via CLI, or Log in as Site Administrator and navigate to **Site administration →
   Notifications** to run the database upgrade.

---

## Usage

1. Open a quiz's **Settings** page (the cog icon, or Edit settings).
2. Expand the **Grading notifications** section near the bottom of the form.
3. Select the desired notification method from the dropdown.
4. Save changes.

From this point, whenever a student submits an attempt that contains at least
one manually graded question (e.g. an Essay question), the configured
notification is dispatched to all teachers enrolled in the course who hold the
`mod/quiz:grade` capability.

---

## Moodle coding standards compliance

- Namespaced classes under `local_quizgradingnotify\*` (PSR-4, `classes/`)
- No direct `$_POST` access; all form data via `$mform->get_data()`
- All DB access via the `$DB` API; raw SQL only where joins require it
- All user-facing strings in `lang/en/local_quizgradingnotify.php`
- Privacy API implemented via `null_provider` (no personal data stored)
- Event observers declared in `db/events.php`; message provider in `db/messages.php`

---

## File structure

```
local/quizgradingnotify/
├── classes/
│   ├── notifier_interface.php
│   ├── notifier_factory.php
│   ├── observer.php
│   ├── notifier/
│   │   ├── email.php
│   │   ├── popup.php
│   └── privacy/
│       └── provider.php
├── db/
│   ├── access.php
│   ├── events.php
│   ├── install.xml
│   └── messages.php
├── lang/
│   └── en/
│       └── local_quizgradingnotify.php
├── lib.php
├── version.php
└── README.md
```

---

## License

GNU GPL v3 or later – https://www.gnu.org/licenses/gpl-3.0.html
