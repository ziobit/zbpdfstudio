<img src="brand/pdfstudio-logo.svg" width="56" height="56" alt="PDF Studio logo">

# PDF Studio

A single-file PDF and document workbench. Serve `index.php` with PHP 7.2 or newer and open it in a modern browser.

Browser tools edit, organize, annotate and visually sign PDFs without uploading the documents. Optional server tools add compression, password protection, text recognition and Office conversion.

## Presentation website and branding

Open **website.html** for the single-page presentation website. It is a self-contained HTML file with its own CSS, JavaScript, SVG logo and icons; no build, database, external fonts or libraries are required. Italian is the default. The language buttons switch to English or German and preserve the interactive preview's text and state. A language can also be selected with `?lang=it`, `?lang=en` or `?lang=de`.

Deploy `website.html` beside `index.php`. The **Open PDF Studio** links open the application in the selected language. Deploying only `index.php` remains supported.

The three manual links are placeholders. Upload the previously created Word manuals to `manuals/PDFStudio_Manual_Italian.docx`, `manuals/PDFStudio_Manual_English.docx` and `manuals/PDFStudio_Manual_German.docx`, or replace their URLs in `CONFIG.manuals` near the bottom of `website.html`. Change `CONFIG.app` if the editor is hosted elsewhere. If you want the presentation page at your site's root, configure your web server to serve `website.html` first or copy it as `index.html`; the editor keeps its `index.php` URL.

The reusable vector mark is in `brand/pdfstudio-logo.svg`. The same mark is embedded throughout the presentation page and in its favicon. Presentation wording is maintained in the page's `COPY` catalog, with Italian, English and German entries in that order.

The pricing section is promotional: Base is free for PDFs up to 5 pages, Plus is €19.99 per month for PDFs up to 49 pages, and Pro is priced on request for generally unlimited PDFs, subject to server configuration and resources. It does not implement subscriptions, billing or plan-based restrictions. The application, its version and its existing limits remain unchanged.

## Languages and storage

Italian is the default. Use the language selector in the toolbar to choose Italiano, English or Deutsch without closing your document. Labels, dialogs, messages and release notes change together; document text, filenames, commands, field values and action identifiers are preserved. Tool search accepts translated names. Page ranges accept `all`, `odd`, `even`, Italian `tutte`, `dispari`, `pari`, and German `alle`, `ungerade`, `gerade`.

No database is required, including IndexedDB. Language preferences and automatic recovery use browser storage, with a cookie fallback for the language. Preferences and recovery are scoped to the installation URL. Automatic recovery links to the original PDFs; keep those files and download an editable project for a lasting backup. Recovery can be unavailable when storage is blocked or full. Save a project before upgrading an older installation if you relied on its previous automatic recovery.

PHP sessions and private temporary files support server processing. Deploying only `index.php` is sufficient. Browser libraries and optional OCR language data are downloaded from their configured CDNs; the app is not an offline package.

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

Edit translations in `translations.json` and run `python3 scripts/sync_translations.py` to embed them in the single application file. Keep dynamic placeholders unchanged. The tests check the embedded catalog, translated dialog labels, language switching, file and content preservation, page-range keywords and recovery without a database.
