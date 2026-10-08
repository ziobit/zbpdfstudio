# PDF Studio

A single-file PDF and document workbench. Serve `index.php` with PHP 7.2 or newer and open it in a modern browser.

Browser tools edit, organize, annotate and visually sign PDFs without uploading the documents. Optional server tools add compression, password protection, text recognition and Office conversion.

## System capabilities

Open the sliders button in the toolbar to see each dependency's **Browser** or **Server** location, availability, version and features. An information button beside each unavailable server dependency opens Ubuntu installation commands and setup advice. The commands can be copied; run them yourself in an SSH terminal.

PHP extension commands match the PHP version serving the page. That version must be available in your configured Ubuntu repositories. Restart the applicable PHP-FPM or Apache service after adding extensions, then reload the page. Composer commands belong in the directory containing `index.php` and must run using the same PHP version as the application.

## Updates and release notes

Use the cloud download button or **System capabilities → Check for updates**. The browser also checks GitHub at most once every 24 hours, provided browser storage is available. A badge highlights a newer version. Failed checks leave the editor usable and can be retried manually.

The update panel shows the installed version, latest published version and changes since your version. To upgrade:

1. Save your open document or editable project.
2. Back up the existing `index.php` outside the public web directory.
3. Download the new `index.php` and replace it on your server. Keep your optional `vendor` directory and ensure its packages support your PHP version.
4. Reload PDF Studio. The changes since the last version opened in that browser are shown once. **What's new** reopens the history at any time.

Update checks download only release information from this repository. No document content or credentials are sent to GitHub. The application does not replace files on your server automatically. Deploying only `index.php` remains sufficient; `release.json` and `CHANGELOG.md` are publication files in this repository.

See [CHANGELOG.md](CHANGELOG.md) for the user-facing release history.

## Development checks

```sh
php -l index.php
php tests/runtime.php
python3 tests/server_test.py --php php
npm install --no-audit --no-fund
npm test
```

Node dependencies are used only for development tests. Run PHP checks with PHP 7.2 as well as a current PHP version before publishing changes. Keep the application version, embedded release history, `release.json` and `CHANGELOG.md` in agreement.
