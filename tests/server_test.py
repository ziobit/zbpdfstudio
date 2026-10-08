"""Check the application over HTTP with the selected PHP interpreter."""
import argparse
import http.cookiejar
import json
from pathlib import Path
import re
import socket
import subprocess
import tempfile
import time
import urllib.request

parser = argparse.ArgumentParser()
parser.add_argument('--php', default='php')
args = parser.parse_args()
root = Path(__file__).resolve().parent.parent
with tempfile.TemporaryDirectory() as work:
  session_dir = Path(work) / 'sessions'
  session_dir.mkdir(mode=0o700)
  with open(Path(work) / 'server.log', 'w') as log:
    server = subprocess.Popen([args.php, '-d', 'session.save_path=' + str(session_dir), '-d', 'error_reporting=-1', '-d', 'log_errors=1', '-d', 'error_log=' + str(Path(work) / 'errors.log'), '-S', '127.0.0.1:18723', '-t', str(root)], stdout=log, stderr=log)
    try:
      for attempt in range(100):
        try:
          with socket.create_connection(('127.0.0.1', 18723), timeout=.1):
            break
        except OSError:
          if server.poll() is not None:
            raise RuntimeError('PHP server failed to start.')
          time.sleep(.05)
      client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
      with client.open('http://127.0.0.1:18723/index.php') as response:
        text = response.read().decode()
        cookie = response.headers.get('Set-Cookie', '')
      boot = json.loads(re.search(r'window\.PDF_STUDIO_BOOT = (.*?);</script>', text).group(1))
      expected = json.loads((root / 'release.json').read_text())
      assert boot['release'] == expected and boot['version'] == expected['version']
      assert boot['language'] == 'it' and '<html lang="it"' in text
      assert boot['translations'] == json.loads((root / 'translations.json').read_text())
      for lang in ['it', 'en', 'de', 'unknown']:
        with client.open('http://127.0.0.1:18723/index.php?lang=' + lang) as response:
          localized = response.read().decode()
        localized_boot = json.loads(re.search(r'window\.PDF_STUDIO_BOOT = (.*?);</script>', localized).group(1))
        assert localized_boot['language'] == (lang if lang in ['it', 'en', 'de'] else 'it')
      assert 'HttpOnly' in cookie and 'SameSite=Strict' in cookie
      with client.open('http://127.0.0.1:18723/index.php') as response:
        second = json.loads(re.search(r'window\.PDF_STUDIO_BOOT = (.*?);</script>', response.read().decode()).group(1))
      assert second['csrf'] == boot['csrf'], 'The session must persist between requests.'
      with client.open('http://127.0.0.1:18723/index.php?action=capabilities') as response:
        caps = json.loads(response.read())
        assert response.headers['Content-Type'].startswith('application/json')
      assert caps['version'] == expected['version']
      assert all(tool['source'] == 'server' and tool['install']['commands'] for tool in caps['tools'].values())
      assert len(boot['csrf']) == 64
      print('HTTP page, upgrade metadata, session cookies and capability JSON passed on PHP ' + caps['php'] + '.')
    finally:
      server.terminate()
      server.wait(timeout=5)
  errors = Path(work) / 'errors.log'
  if errors.exists():
    text = errors.read_text()
    assert not any(message in text for message in ['PHP Warning:', 'PHP Notice:', 'PHP Fatal error:', 'PHP Parse error:']), text
