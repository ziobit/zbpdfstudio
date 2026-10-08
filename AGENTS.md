# PDF Studio development

- Keep the deployable application in the single `index.php` file and compatible with PHP 7.2 and newer.
- Optional dependencies stay optional. Installation help displays commands; it must not execute package installation from the web application.
- Whenever the application version changes, update `PDFSTUDIO_VERSION`, the header comment, `PDFSTUDIO_RELEASE_HISTORY`, `release.json` and `CHANGELOG.md` together.
- Write release notes about behavior users can see or benefit from. Describe actual changes, not implementation details or speculative improvements. Preserve previous release entries.
- The embedded release history keeps upgrade notices working when only `index.php` is deployed. Do not require the publication files at runtime.
- Italian is the default; support Italian, English and German. Add new visible wording to translations.json, preserve placeholders and sync the embedded catalog with scripts/sync_translations.py. Language changes must preserve document content, filenames, commands, input values and action identifiers.
- Do not introduce a database dependency. Browser storage supports preferences and recovery; downloaded projects remain the independent backup.
- Run the checks documented in README.md before opening a pull request, including PHP 7.2 compatibility checks when a 7.2 interpreter is available.
