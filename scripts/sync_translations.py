"""Embed the reviewed catalog so index.php remains the only deployed file."""
import json
from pathlib import Path
root=Path(__file__).resolve().parent.parent
catalog=json.loads((root/'translations.json').read_text())
quote=lambda value:"'"+value.replace('\\','\\\\').replace("'","\\'")+"'"
body='function studio_translations(): array {\n  return [\n'+''.join('    '+quote(k)+" => ['it' => "+quote(v['it'])+", 'de' => "+quote(v['de'])+"],\n" for k,v in catalog.items())+'  ];\n}\n'
p=root/'index.php';s=p.read_text();start=s.index('function studio_translations(): array {');end=s.index('\nfunction studio_language()',start);p.write_text(s[:start]+body+s[end:])
print('Embedded',len(catalog),'phrases.')
