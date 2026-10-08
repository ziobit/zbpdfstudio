<?php
declare(strict_types=1);

ob_start();
require dirname(__DIR__) . '/index.php';
ob_end_clean();

function verify($condition, string $message): void {
  if (!$condition) throw new RuntimeException($message);
}

$release = json_decode(file_get_contents(dirname(__DIR__) . '/release.json'), true);
verify(json_last_error() === JSON_ERROR_NONE && $release === studio_release_info(), 'Published and embedded release information must agree.');
$changelog = file_get_contents(dirname(__DIR__) . '/CHANGELOG.md');
foreach ($release['releases'] as $entry) {
  verify(strpos($changelog, '## ' . $entry['version'] . ' — ' . $entry['date']) !== false, 'Changelog release heading is missing.');
  foreach ($entry['changes'] as $change) verify(strpos($changelog, '- ' . $change) !== false, 'Changelog and in-app release notes differ.');
}

$caps = studio_capabilities();
verify($caps['version'] === PDFSTUDIO_VERSION && count($caps['tools']) === 16, 'Capability response must include the current version and all server dependencies.');
foreach ($caps['tools'] as $name => $tool) {
  verify($tool['source'] === 'server', 'Server capability location is missing: ' . $name);
  verify(!empty($tool['label']) && !empty($tool['install']['commands']) && is_array($tool['install']['notes']), 'Ubuntu installation help is missing: ' . $name);
}
verify(strpos(implode("\n", $caps['tools']['zip']['install']['commands']), 'php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '-zip') !== false, 'Extension installation must match the serving PHP version.');
verify(strpos(implode("\n", $caps['tools']['chromium']['install']['notes']), 'sandbox') !== false, 'Chromium configuration help is missing.');
if (!studio_enabled('proc_open')) {
  foreach (['qpdf', 'gs', 'pdfinfo', 'pdftotext', 'pdfimages', 'pdftoppm', 'tesseract', 'libreoffice', 'chromium'] as $name) {
    verify(!$caps['tools'][$name]['available'] && strpos($caps['tools'][$name]['reason'], 'proc_open') !== false, 'Blocked execution needs actionable help.');
  }
}
verify(studio_contains('abc', '') && studio_starts_with('abc', '') && studio_ends_with('abc', ''), 'Compatibility helpers must accept empty strings.');
verify((new StudioError('test', 422))->status === 422, 'HTTP error statuses must be preserved.');
echo 'PHP ' . PHP_VERSION . ': release consistency and server capability checks passed.' . PHP_EOL;
