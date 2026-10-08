<?php
/**
 * PDF Studio 1.2.0 — single-file PDF and document workbench.
 * Minimum PHP: 7.2. Recommended extensions: fileinfo, mbstring, dom, xml,
 * zip, openssl, gd; optional imagick. CLI tools require proc_open.
 * Optional Composer packages (run beside index.php, never from this app):
 * composer require setasign/fpdi setasign/fpdf mpdf/mpdf dompdf/dompdf
 * Run Composer with the server's PHP version so it selects compatible releases.
 * Do not use --ignore-platform-reqs. On PHP 8.2+, optionally also install:
 * composer require tecnickcom/tc-lib-pdf
 * Ubuntu/Debian optional packages:
 * sudo apt install php-xml php-mbstring php-zip php-gd qpdf ghostscript poppler-utils \
 *   tesseract-ocr tesseract-ocr-eng libreoffice chromium imagemagick php-imagick
 * Optional dependencies unlock the corresponding server tools. Browser editing
 * works without them. Keep document parsers patched; serve this file over HTTPS.
 * Configure PHP upload_max_filesize/post_max_size to match the limits below.
 * No packages are installed automatically. Uploaded files are never retained.
 */
declare(strict_types=1);

const PDFSTUDIO_VERSION = '1.2.0';
const PDFSTUDIO_MAX_FILE = 104857600;
const PDFSTUDIO_MAX_PAGES = 500;
const PDFSTUDIO_MAX_FILES = 30;
const PDFSTUDIO_MAX_JOB_BYTES = 536870912;
const PDFSTUDIO_PROCESS_SECONDS = 180;
const PDFSTUDIO_OCR_SECONDS = 600;
const PDFSTUDIO_MAX_IMAGE_PIXELS = 40000000;
const PDFSTUDIO_MAX_HTML = 16777216;
const PDFSTUDIO_STALE_SECONDS = 7200;
const PDFSTUDIO_RELEASE_HISTORY = [
  [
    'version' => '1.2.0',
    'date' => '2026-10-08',
    'changes' => [
      'Choose Italian, English or German without closing your document. Italian is the default language.',
      'Work without a database. The language choice and automatic recovery use browser storage; downloadable projects keep an independent copy.',
      'Save an editable project before upgrading; older automatic recovery copies are not transferred.',
    ],
  ],
  [
    'version' => '1.1.1',
    'date' => '2026-10-08',
    'changes' => [
      'Update downloads now save index.php directly instead of opening its source in a new tab.',
      'Retry a failed update download without leaving your open document.',
    ],
  ],
  [
    'version' => '1.1.0',
    'date' => '2026-10-08',
    'changes' => [
      'See which capabilities run in your browser and which need your server.',
      'Click the information icon beside a missing server capability for Ubuntu installation commands, setup advice and a copy button.',
      'Check for new versions from the toolbar or System capabilities. A daily background check also highlights available updates.',
      'Read what changed before downloading an update, and see the release notes when you open an upgraded installation.',
      'Open What\'s new at any time to review the release history.',
      'Run PDF Studio on PHP 7.2 and newer servers.',
    ],
  ],
  [
    'version' => '1.0.0',
    'date' => '2026-10-08',
    'changes' => [
      'Edit, organize, annotate and visually sign PDFs in your browser.',
      'Create documents with a Word-like editor and export them as PDF.',
      'Add optional server tools for compression, passwords, text recognition and Office conversion.',
    ],
  ],
];


// Embedded to preserve single-file deployment. translations.json is the editable catalog.
function studio_translations(): array {
  return [
    '-fpm for PHP-FPM, or sudo systemctl restart apache2 for Apache. Then refresh System capabilities.' => ['it' => '-fpm per PHP-FPM oppure sudo systemctl restart apache2 per Apache. Quindi aggiorna le capacità del sistema.', 'de' => '-fpm für PHP-FPM oder sudo systemctl restart apache2 für Apache. Aktualisieren Sie danach die Systemfunktionen.'],
    ', which was not detected on this server.' => ['it' => ', non rilevato su questo server.', 'de' => ', das auf diesem Server nicht erkannt wurde.'],
    ', which was not detected.' => ['it' => ', non rilevato.', 'de' => ', das nicht erkannt wurde.'],
    '. Check your Internet connection and CDN access.' => ['it' => '. Controlla la connessione Internet e l\'accesso alla CDN.', 'de' => '. Prüfen Sie Ihre Internetverbindung und den CDN-Zugriff.'],
    '. Check your network or CDN policy.' => ['it' => '. Controlla la connessione o le regole di accesso alla CDN.', 'de' => '. Prüfen Sie Ihre Verbindung oder die CDN-Zugriffsregeln.'],
    '. Example: Page {{page}} of {{pages}}. Chrome and Chromium support margin counters; browser support varies.' => ['it' => '. Esempio: Pagina {{page}} di {{pages}}. Chrome e Chromium supportano i contatori nei margini; il supporto varia in base al browser.', 'de' => '. Beispiel: Seite {{page}} von {{pages}}. Chrome und Chromium unterstützen Seitenzähler im Randbereich; die Unterstützung hängt vom Browser ab.'],
    '. Increase PHP upload_max_filesize/post_max_size to process larger files on the server. Local PDF editing is still available.' => ['it' => '. Aumenta PHP upload_max_filesize/post_max_size per elaborare file più grandi sul server. La modifica locale dei PDF resta disponibile.', 'de' => '. Erhöhen Sie PHP upload_max_filesize/post_max_size, um größere Dateien auf dem Server zu verarbeiten. Die lokale PDF-Bearbeitung bleibt verfügbar.'],
    '. Local tools remain available.' => ['it' => '. Gli strumenti locali restano disponibili.', 'de' => '. Die lokalen Werkzeuge bleiben verfügbar.'],
    '. The SHA-256 hash must match the original PDF.' => ['it' => '. L\'hash SHA-256 deve corrispondere al PDF originale.', 'de' => '. Der SHA-256-Hash muss mit dem ursprünglichen PDF übereinstimmen.'],
    '· about' => ['it' => '· circa', 'de' => '· etwa'],
    '· Latest published version:' => ['it' => '· Ultima versione pubblicata:', 'de' => '· Neueste veröffentlichte Version:'],
    '· XFA present, editing unsupported' => ['it' => '· XFA presente, modifica non supportata', 'de' => '· XFA vorhanden, Bearbeitung nicht unterstützt'],
    '(likely scanned or outlined text)' => ['it' => '(probabilmente una scansione o testo convertito in tracciati)', 'de' => '(wahrscheinlich gescannt oder Text in Pfade umgewandelt)'],
    '(No selection)' => ['it' => '(Nessuna selezione)', 'de' => '(Keine Auswahl)'],
    '); do not ignore its PHP requirements.' => ['it' => '); non ignorare i requisiti PHP.', 'de' => '); ignorieren Sie die PHP-Anforderungen nicht.'],
    '). These commands use matching versioned package names; that PHP version must be available in your configured Ubuntu repositories.' => ['it' => '). Questi comandi usano pacchetti corrispondenti alla versione PHP; la versione deve essere disponibile nei repository Ubuntu configurati.', 'de' => '). Diese Befehle verwenden Pakete für die passende PHP-Version; diese Version muss in Ihren eingerichteten Ubuntu-Paketquellen verfügbar sein.'],
    '). Try again later.' => ['it' => '). Riprova più tardi.', 'de' => '). Versuchen Sie es später erneut.'],
    '{0} · {1} × {2} mm · {3} · {4} column{5}' => ['it' => '{0} · {1} × {2} mm · {3} · colonne {4}{5}', 'de' => '{0} · {1} × {2} mm · {3} · Spalten {4}{5}'],
    '{0} (source pixels)' => ['it' => '{0} (pixel originali)', 'de' => '{0} (Originalpixel)'],
    '{0} bytes' => ['it' => '{0} byte', 'de' => '{0} Bytes'],
    '{0} must be between {1} and {2}.' => ['it' => '{0} deve essere compreso tra {1} e {2}.', 'de' => '{0} muss zwischen {1} und {2} liegen.'],
    '{13} ({14} pages)' => ['it' => '{13} ({14} pagine)', 'de' => '{13} ({14} Seiten)'],
    '{3}Runs locally on your device.' => ['it' => '{3}Elaborazione locale sul tuo dispositivo.', 'de' => '{3}Wird lokal auf Ihrem Gerät ausgeführt.'],
    '{4} layer' => ['it' => '{4} livello', 'de' => '{4} Ebene'],
    '{4} of {5} pages have embedded text{6}' => ['it' => '{4} di {5} pagine contengono testo incorporato{6}', 'de' => '{4} von {5} Seiten enthalten eingebetteten Text{6}'],
    '{8} (repeated uses may count more than once)' => ['it' => '{8} (gli utilizzi ripetuti possono essere contati più volte)', 'de' => '{8} (mehrfache Verwendung kann mehrfach gezählt werden)'],
    '{9} ({10} pages)' => ['it' => '{9} ({10} pagine)', 'de' => '{9} ({10} Seiten)'],
    '% larger' => ['it' => '% più grande', 'de' => '% größer'],
    '% smaller' => ['it' => '% più piccolo', 'de' => '% kleiner'],
    '── Page {0} ──' => ['it' => '── Pagina {0} ──', 'de' => '── Seite {0} ──'],
    '── Page {0} ── {1}' => ['it' => '── Pagina {0} ── {1}', 'de' => '── Seite {0} ── {1}'],
    '0 words · 0 characters' => ['it' => '0 parole · 0 caratteri', 'de' => '0 Wörter · 0 Zeichen'],
    '50% overlay' => ['it' => 'Sovrapposizione 50%', 'de' => '50 % Überlagerung'],
    'A field with this name already exists.' => ['it' => 'Esiste già un campo con questo nome.', 'de' => 'Ein Feld mit diesem Namen ist bereits vorhanden.'],
    'A PDF must retain at least one page.' => ['it' => 'Un PDF deve mantenere almeno una pagina.', 'de' => 'Ein PDF muss mindestens eine Seite behalten.'],
    'A processing operation is already running. Wait or cancel it first.' => ['it' => 'È già in corso un\'operazione. Attendi oppure annullala.', 'de' => 'Ein Vorgang läuft bereits. Warten Sie oder brechen Sie ihn ab.'],
    'a required library' => ['it' => 'una libreria necessaria', 'de' => 'eine benötigte Bibliothek'],
    'A section applies its own column count to the selected text. Use a page break to begin a section on a new page.' => ['it' => 'Una sezione applica un proprio numero di colonne al testo selezionato. Usa un\'interruzione di pagina per iniziarla su una nuova pagina.', 'de' => 'Ein Abschnitt wendet eine eigene Spaltenanzahl auf den ausgewählten Text an. Verwenden Sie einen Seitenumbruch, um ihn auf einer neuen Seite zu beginnen.'],
    'a secure redaction region' => ['it' => 'un\'area di oscuramento sicuro', 'de' => 'ein Bereich für sichere Schwärzung'],
    'A selected page is outside this document.' => ['it' => 'Una pagina selezionata è fuori dal documento.', 'de' => 'Eine ausgewählte Seite liegt außerhalb des Dokuments.'],
    'A3 (297 × 420 mm)' => ['it' => 'A3 (297 × 420 mm)', 'de' => 'A3 (297 × 420 mm)'],
    'A4 · portrait · 20 mm margins' => ['it' => 'A4 · verticale · margini 20 mm', 'de' => 'A4 · Hochformat · 20 mm Ränder'],
    'A4 (210 × 297 mm)' => ['it' => 'A4 (210 × 297 mm)', 'de' => 'A4 (210 × 297 mm)'],
    'A5 (148 × 210 mm)' => ['it' => 'A5 (148 × 210 mm)', 'de' => 'A5 (148 × 210 mm)'],
    'Across rows' => ['it' => 'Per righe', 'de' => 'Zeilenweise'],
    'Action' => ['it' => 'Azione', 'de' => 'Aktion'],
    'Actual size' => ['it' => 'Dimensioni effettive', 'de' => 'Tatsächliche Größe'],
    'Actual size (96 DPI)' => ['it' => 'Dimensioni effettive (96 DPI)', 'de' => 'Tatsächliche Größe (96 DPI)'],
    'Actual-size image does not fit its cell. Use Fit or a larger page.' => ['it' => 'L\'immagine a dimensioni effettive non entra nella cella. Usa Adatta o una pagina più grande.', 'de' => 'Das Bild passt in Originalgröße nicht in die Zelle. Verwenden Sie Anpassen oder eine größere Seite.'],
    'Add Bates numbering' => ['it' => 'Aggiungi numerazione Bates', 'de' => 'Bates-Nummerierung hinzufügen'],
    'Add filename bookmarks (browser engine)' => ['it' => 'Aggiungi segnalibri con i nomi dei file (motore browser)', 'de' => 'Lesezeichen mit Dateinamen hinzufügen (Browser-Engine)'],
    'Add header, footer or page numbers' => ['it' => 'Aggiungi intestazioni, piè di pagina o numeri di pagina', 'de' => 'Kopfzeilen, Fußzeilen oder Seitenzahlen hinzufügen'],
    'Add headings before inserting a table of contents.' => ['it' => 'Aggiungi titoli prima di inserire un indice.', 'de' => 'Fügen Sie Überschriften hinzu, bevor Sie ein Inhaltsverzeichnis einfügen.'],
    'Add optional server tools for compression, passwords, text recognition and Office conversion.' => ['it' => 'Aggiungi strumenti server facoltativi per compressione, password, riconoscimento del testo e conversione Office.', 'de' => 'Ergänzen Sie optionale Serverwerkzeuge für Komprimierung, Passwörter, Texterkennung und Office-Konvertierung.'],
    'Add watermark' => ['it' => 'Aggiungi filigrana', 'de' => 'Wasserzeichen hinzufügen'],
    'Adding watermark' => ['it' => 'Aggiunta filigrana', 'de' => 'Wasserzeichen wird hinzugefügt'],
    'Advanced' => ['it' => 'Avanzate', 'de' => 'Erweitert'],
    'AES password protection' => ['it' => 'Protezione con password AES', 'de' => 'AES-Passwortschutz'],
    'After current page' => ['it' => 'Dopo la pagina corrente', 'de' => 'Nach der aktuellen Seite'],
    'After installing PHP extensions, restart your PHP handler: sudo systemctl restart' => ['it' => 'Dopo aver installato le estensioni PHP, riavvia il gestore PHP: sudo systemctl restart', 'de' => 'Starten Sie nach der Installation der PHP-Erweiterungen den PHP-Dienst neu: sudo systemctl restart'],
    'Align bottom' => ['it' => 'Allinea in basso', 'de' => 'Unten ausrichten'],
    'Align left' => ['it' => 'Allinea a sinistra', 'de' => 'Links ausrichten'],
    'Align right' => ['it' => 'Allinea a destra', 'de' => 'Rechts ausrichten'],
    'Align top' => ['it' => 'Allinea in alto', 'de' => 'Oben ausrichten'],
    'Alignment / wrapping' => ['it' => 'Allineamento e disposizione del testo', 'de' => 'Ausrichtung und Textumbruch'],
    'All' => ['it' => 'Tutte', 'de' => 'Alle'],
    'Allow annotations' => ['it' => 'Consenti annotazioni', 'de' => 'Anmerkungen zulassen'],
    'Allow copying' => ['it' => 'Consenti copia', 'de' => 'Kopieren zulassen'],
    'Allow form filling' => ['it' => 'Consenti compilazione moduli', 'de' => 'Ausfüllen von Formularen zulassen'],
    'Allow modifying document' => ['it' => 'Consenti modifica documento', 'de' => 'Dokumentänderungen zulassen'],
    'Allow page breaks' => ['it' => 'Consenti interruzioni di pagina', 'de' => 'Seitenumbrüche zulassen'],
    'Alternate pages (two PDFs)' => ['it' => 'Alterna pagine (due PDF)', 'de' => 'Seiten abwechselnd einfügen (zwei PDFs)'],
    'Alternative PDF merge' => ['it' => 'Unione PDF alternativa', 'de' => 'Alternative PDF-Zusammenführung'],
    'Alternative text' => ['it' => 'Testo alternativo', 'de' => 'Alternativtext'],
    'An embedded image exceeds the 8 MB export limit.' => ['it' => 'Un\'immagine incorporata supera il limite di esportazione di 8 MB.', 'de' => 'Ein eingebettetes Bild überschreitet die Exportgrenze von 8 MB.'],
    'An embedded image is invalid or has too many pixels.' => ['it' => 'Un\'immagine incorporata non è valida o contiene troppi pixel.', 'de' => 'Ein eingebettetes Bild ist ungültig oder enthält zu viele Pixel.'],
    'Anchor' => ['it' => 'Ancora', 'de' => 'Textmarke'],
    'Anchor name' => ['it' => 'Nome ancora', 'de' => 'Name der Textmarke'],
    'Annot' => ['it' => 'Annotazione', 'de' => 'Anmerkung'],
    'Annotate' => ['it' => 'Annota', 'de' => 'Kommentieren'],
    'Annotations' => ['it' => 'Annotazioni', 'de' => 'Anmerkungen'],
    'Apply' => ['it' => 'Applica', 'de' => 'Anwenden'],
    'Apply cell styling to the whole table' => ['it' => 'Applica lo stile delle celle all\'intera tabella', 'de' => 'Zellenformatierung auf die ganze Tabelle anwenden'],
    'Approximate maximum size' => ['it' => 'Dimensione massima approssimativa', 'de' => 'Ungefähre maximale Größe'],
    'Arrange' => ['it' => 'Disponi', 'de' => 'Anordnen'],
    'Arrange objects' => ['it' => 'Disponi oggetti', 'de' => 'Objekte anordnen'],
    'Arrange photos on PDF pages' => ['it' => 'Disponi le foto sulle pagine PDF', 'de' => 'Fotos auf PDF-Seiten anordnen'],
    'Arrow' => ['it' => 'Freccia', 'de' => 'Pfeil'],
    'Attempt structural repair' => ['it' => 'Tenta riparazione della struttura', 'de' => 'Strukturreparatur versuchen'],
    'Author' => ['it' => 'Autore', 'de' => 'Autor'],
    'Auto · server renderer' => ['it' => 'Automatico · motore server', 'de' => 'Automatisch · Server-Engine'],
    'Auto / Browser (pdf-lib)' => ['it' => 'Automatico / Browser (pdf-lib)', 'de' => 'Automatisch / Browser (pdf-lib)'],
    'Automatic crop padding (points)' => ['it' => 'Margine ritaglio automatico (punti)', 'de' => 'Rand für automatischen Zuschnitt (Punkte)'],
    'Available' => ['it' => 'Disponibile', 'de' => 'Verfügbar'],
    'Back to System capabilities' => ['it' => 'Torna alle capacità del sistema', 'de' => 'Zurück zu den Systemfunktionen'],
    'Back up your current index.php outside the public web directory.' => ['it' => 'Salva una copia dell\'attuale index.php fuori dalla cartella web pubblica.', 'de' => 'Sichern Sie die bisherige index.php außerhalb des öffentlichen Webverzeichnisses.'],
    'Background' => ['it' => 'Sfondo', 'de' => 'Hintergrund'],
    'Barcode' => ['it' => 'Codice a barre', 'de' => 'Strichcode'],
    'Barcode value' => ['it' => 'Valore codice a barre', 'de' => 'Strichcodewert'],
    'Bates numbering' => ['it' => 'Numerazione Bates', 'de' => 'Bates-Nummerierung'],
    'Before current page' => ['it' => 'Prima della pagina corrente', 'de' => 'Vor der aktuellen Seite'],
    'beside an unavailable server capability for Ubuntu installation commands. Some installed tools also need permissions, extensions or server configuration before they can run.' => ['it' => 'accanto a una capacità server non disponibile per vedere i comandi Ubuntu. Alcuni strumenti installati richiedono anche permessi, estensioni o configurazione del server.', 'de' => 'neben einer nicht verfügbaren Serverfunktion, um Ubuntu-Installationsbefehle anzuzeigen. Manche installierten Werkzeuge benötigen auch Berechtigungen, Erweiterungen oder eine Serverkonfiguration.'],
    'Black and white' => ['it' => 'Bianco e nero', 'de' => 'Schwarzweiß'],
    'Blank page' => ['it' => 'Pagina vuota', 'de' => 'Leere Seite'],
    'Block quote' => ['it' => 'Citazione', 'de' => 'Blockzitat'],
    'Bold' => ['it' => 'Grassetto', 'de' => 'Fett'],
    'Booklet imposition' => ['it' => 'Impaginazione opuscolo', 'de' => 'Broschürenmontage'],
    'Bookmark split requires one PDF in its original page order.' => ['it' => 'La divisione per segnalibri richiede un solo PDF nell\'ordine originale delle pagine.', 'de' => 'Die Aufteilung nach Lesezeichen erfordert ein einzelnes PDF in seiner ursprünglichen Seitenreihenfolge.'],
    'Border color' => ['it' => 'Colore bordo', 'de' => 'Rahmenfarbe'],
    'Border width (px)' => ['it' => 'Spessore bordo (px)', 'de' => 'Rahmenbreite (px)'],
    'Brightness (%)' => ['it' => 'Luminosità (%)', 'de' => 'Helligkeit (%)'],
    'Bring forward' => ['it' => 'Porta avanti', 'de' => 'Eine Ebene nach vorne'],
    'Bring to front' => ['it' => 'Porta in primo piano', 'de' => 'In den Vordergrund'],
    'Browser' => ['it' => 'Browser', 'de' => 'Browser'],
    'Browser capabilities process documents on your device. Server capabilities use this installation; files are uploaded only when you choose a server operation.' => ['it' => 'Le capacità browser elaborano i documenti sul tuo dispositivo. Le capacità server usano questa installazione; i file vengono caricati solo quando scegli un\'operazione server.', 'de' => 'Browserfunktionen verarbeiten Dokumente auf Ihrem Gerät. Serverfunktionen verwenden diese Installation; Dateien werden nur bei einer von Ihnen gewählten Serveroperation hochgeladen.'],
    'Browser first.' => ['it' => 'Prima il browser.', 'de' => 'Zuerst im Browser.'],
    'Browser OCR renders selected pages to images before recognition. Language data is downloaded when first used. Existing PDF text is unchanged. Searchable PDF output uses the server engine.' => ['it' => 'L\'OCR nel browser converte le pagine selezionate in immagini prima del riconoscimento. I dati delle lingue vengono scaricati al primo utilizzo. Il testo PDF esistente non cambia. Per un PDF ricercabile si usa il motore server.', 'de' => 'Browser-OCR wandelt ausgewählte Seiten vor der Erkennung in Bilder um. Sprachdaten werden bei der ersten Verwendung heruntergeladen. Vorhandener PDF-Text bleibt erhalten. Ein durchsuchbares PDF erfordert die Server-Engine.'],
    'Browser print · local' => ['it' => 'Stampa browser · locale', 'de' => 'Browserdruck · lokal'],
    'By default source PDFs are linked by SHA-256 hash and must be selected again when reopening. Including them increases project size.' => ['it' => 'Per impostazione predefinita i PDF originali vengono collegati tramite hash SHA-256 e devono essere selezionati di nuovo alla riapertura. Includerli aumenta la dimensione del progetto.', 'de' => 'Standardmäßig werden die Original-PDFs über ihren SHA-256-Hash verknüpft und müssen beim erneuten Öffnen wieder ausgewählt werden. Das Einbetten vergrößert die Projektdatei.'],
    'Camera / scan' => ['it' => 'Fotocamera / scansione', 'de' => 'Kamera / Scan'],
    'Camera capture depends on your device. After creating the PDF, choose OCR to recognize text.' => ['it' => 'L\'acquisizione dalla fotocamera dipende dal dispositivo. Dopo aver creato il PDF, scegli OCR per riconoscere il testo.', 'de' => 'Die Kameraaufnahme hängt von Ihrem Gerät ab. Wählen Sie nach dem Erstellen des PDFs OCR, um den Text zu erkennen.'],
    'Cancel' => ['it' => 'Annulla', 'de' => 'Abbrechen'],
    'Cancelled' => ['it' => 'Annullato', 'de' => 'Abgebrochen'],
    'Cancelling…' => ['it' => 'Annullamento…', 'de' => 'Wird abgebrochen…'],
    'Cannot read' => ['it' => 'Impossibile leggere', 'de' => 'Kann nicht gelesen werden'],
    'Capability / dependency' => ['it' => 'Capacità / dipendenza', 'de' => 'Funktion / Abhängigkeit'],
    'Caption' => ['it' => 'Didascalia', 'de' => 'Bildunterschrift'],
    'Capture and preprocess an image' => ['it' => 'Acquisisci e prepara un\'immagine', 'de' => 'Bild aufnehmen und vorbereiten'],
    'Capture or upload an image' => ['it' => 'Acquisisci o carica un\'immagine', 'de' => 'Bild aufnehmen oder hochladen'],
    'Capture or upload an image first.' => ['it' => 'Prima acquisisci o carica un\'immagine.', 'de' => 'Nehmen Sie zuerst ein Bild auf oder laden Sie eines hoch.'],
    'Cell alignment' => ['it' => 'Allineamento cella', 'de' => 'Zellenausrichtung'],
    'Cell background' => ['it' => 'Sfondo cella', 'de' => 'Zellenhintergrund'],
    'Cell padding (px)' => ['it' => 'Spaziatura interna cella (px)', 'de' => 'Zellenabstand innen (px)'],
    'Center' => ['it' => 'Centro', 'de' => 'Zentriert'],
    'Center content' => ['it' => 'Centra contenuto', 'de' => 'Inhalt zentrieren'],
    'Centered block' => ['it' => 'Blocco centrato', 'de' => 'Zentrierter Block'],
    'Change page size' => ['it' => 'Cambia dimensioni pagina', 'de' => 'Seitengröße ändern'],
    'Check' => ['it' => 'Spunta', 'de' => 'Häkchen'],
    'Check again' => ['it' => 'Controlla di nuovo', 'de' => 'Erneut prüfen'],
    'Check for new versions from the toolbar or System capabilities. A daily background check also highlights available updates.' => ['it' => 'Controlla le nuove versioni dalla barra degli strumenti o dalle capacità del sistema. Un controllo giornaliero segnala anche gli aggiornamenti disponibili.', 'de' => 'Prüfen Sie neue Versionen über die Symbolleiste oder die Systemfunktionen. Eine tägliche Prüfung zeigt verfügbare Updates an.'],
    'Check for updates' => ['it' => 'Controlla aggiornamenti', 'de' => 'Nach Updates suchen'],
    'Checkbox' => ['it' => 'Casella di controllo', 'de' => 'Kontrollkästchen'],
    'Checking GitHub for a newer version…' => ['it' => 'Controllo di una nuova versione su GitHub…', 'de' => 'GitHub wird auf eine neuere Version geprüft…'],
    'Checking PDF structure' => ['it' => 'Controllo struttura PDF', 'de' => 'PDF-Struktur wird geprüft'],
    'Checking…' => ['it' => 'Controllo…', 'de' => 'Wird geprüft…'],
    'Checklist' => ['it' => 'Lista di controllo', 'de' => 'Checkliste'],
    'Checklist item' => ['it' => 'Voce lista di controllo', 'de' => 'Checklisteneintrag'],
    'Checks contact GitHub for version information only. No documents are sent. Automatic checks run at most once a day.{8}' => ['it' => 'I controlli contattano GitHub solo per le versioni. Nessun documento viene inviato. Il controllo automatico viene eseguito al massimo una volta al giorno.{8}', 'de' => 'Prüfungen kontaktieren GitHub nur für Versionsinformationen. Es werden keine Dokumente übertragen. Automatische Prüfungen erfolgen höchstens einmal täglich.{8}'],
    'Choose a file to process. The PHP upload limit may also be smaller than this file.' => ['it' => 'Scegli un file da elaborare. Anche il limite di caricamento PHP potrebbe essere inferiore alla dimensione del file.', 'de' => 'Wählen Sie eine Datei zur Verarbeitung. Auch das PHP-Uploadlimit kann kleiner als die Datei sein.'],
    'Choose a PNG or JPEG watermark image.' => ['it' => 'Scegli un\'immagine PNG o JPEG per la filigrana.', 'de' => 'Wählen Sie ein PNG- oder JPEG-Bild als Wasserzeichen.'],
    'Choose a six-digit hexadecimal color.' => ['it' => 'Scegli un colore esadecimale a sei cifre.', 'de' => 'Wählen Sie eine sechsstellige hexadezimale Farbe.'],
    'Choose one document for this operation.' => ['it' => 'Scegli un solo documento per questa operazione.', 'de' => 'Wählen Sie für diesen Vorgang genau ein Dokument.'],
    'Chromium · best modern CSS' => ['it' => 'Chromium · migliore CSS moderno', 'de' => 'Chromium · beste Unterstützung für modernes CSS'],
    'Chromium rendering requires PHP to run as an unprivileged user with browser sandbox support.' => ['it' => 'Il rendering Chromium richiede PHP eseguito da un utente non privilegiato con supporto alla sandbox del browser.', 'de' => 'Chromium-Rendering erfordert PHP unter einem unprivilegierten Benutzer und Unterstützung für die Browser-Sandbox.'],
    'Chromium requires a working server sandbox. Run PHP as an unprivileged user and enable Chromium sandboxing.' => ['it' => 'Chromium richiede una sandbox funzionante sul server. Esegui PHP come utente non privilegiato e abilita la sandbox Chromium.', 'de' => 'Chromium benötigt eine funktionierende Server-Sandbox. Führen Sie PHP als unprivilegierten Benutzer aus und aktivieren Sie die Chromium-Sandbox.'],
    'Clear' => ['it' => 'Cancella', 'de' => 'Leeren'],
    'Clear drawing' => ['it' => 'Cancella disegno', 'de' => 'Zeichnung löschen'],
    'Clear list' => ['it' => 'Cancella elenco', 'de' => 'Liste löschen'],
    'Clear local autosave' => ['it' => 'Cancella salvataggio automatico locale', 'de' => 'Lokale automatische Sicherung löschen'],
    'Click' => ['it' => 'Fai clic', 'de' => 'Klicken Sie'],
    'Click an image in the document first.' => ['it' => 'Prima fai clic su un\'immagine nel documento.', 'de' => 'Klicken Sie zuerst auf ein Bild im Dokument.'],
    'Click the information icon beside a missing server capability for Ubuntu installation commands, setup advice and a copy button.' => ['it' => 'Fai clic sull\'icona informativa accanto a una capacità server mancante per i comandi Ubuntu, i consigli di configurazione e il pulsante di copia.', 'de' => 'Klicken Sie auf das Infosymbol neben einer fehlenden Serverfunktion, um Ubuntu-Befehle, Einrichtungshinweise und eine Kopierschaltfläche zu sehen.'],
    'Clockwise rotation' => ['it' => 'Rotazione in senso orario', 'de' => 'Drehung im Uhrzeigersinn'],
    'Close' => ['it' => 'Chiudi', 'de' => 'Schließen'],
    'Code' => ['it' => 'Codice', 'de' => 'Code'],
    'Collapse tools' => ['it' => 'Comprimi strumenti', 'de' => 'Werkzeugleiste einklappen'],
    'Color' => ['it' => 'Colore', 'de' => 'Farbe'],
    'Column gap (mm)' => ['it' => 'Spazio tra colonne (mm)', 'de' => 'Spaltenabstand (mm)'],
    'Columns' => ['it' => 'Colonne', 'de' => 'Spalten'],
    'Combine PDFs and images' => ['it' => 'Combina PDF e immagini', 'de' => 'PDFs und Bilder zusammenführen'],
    'Commands selected. Press Ctrl+C or Command+C to copy.' => ['it' => 'Comandi selezionati. Premi Ctrl+C o Command+C per copiarli.', 'de' => 'Befehle ausgewählt. Drücken Sie zum Kopieren Strg+C oder Command+C.'],
    'Compare PDFs' => ['it' => 'Confronta PDF', 'de' => 'PDFs vergleichen'],
    'Compare two PDFs' => ['it' => 'Confronta due PDF', 'de' => 'Zwei PDFs vergleichen'],
    'Comparison view' => ['it' => 'Vista di confronto', 'de' => 'Vergleichsansicht'],
    'Complex paint resources are not accepted in imported projects.' => ['it' => 'Le risorse grafiche complesse non sono accettate nei progetti importati.', 'de' => 'Komplexe Grafikressourcen sind in importierten Projekten nicht zulässig.'],
    'Compress / optimize PDF on server' => ['it' => 'Comprimi / ottimizza PDF sul server', 'de' => 'PDF auf dem Server komprimieren / optimieren'],
    'Compress PDF' => ['it' => 'Comprimi PDF', 'de' => 'PDF komprimieren'],
    'Compressing PDF on server' => ['it' => 'Compressione PDF sul server', 'de' => 'PDF wird auf dem Server komprimiert'],
    'Compression preset' => ['it' => 'Profilo di compressione', 'de' => 'Komprimierungsprofil'],
    'Compression requires qpdf for structural optimization or Ghostscript for image recompression.' => ['it' => 'La compressione richiede qpdf per ottimizzare la struttura oppure Ghostscript per ricomprimere le immagini.', 'de' => 'Die Komprimierung erfordert qpdf für die Strukturoptimierung oder Ghostscript für die Bildkomprimierung.'],
    'Compression: {0} → {1} bytes ({2}).' => ['it' => 'Compressione: {0} → {1} byte ({2}).', 'de' => 'Komprimierung: {0} → {1} Bytes ({2}).'],
    'CONFIDENTIAL' => ['it' => 'RISERVATO', 'de' => 'VERTRAULICH'],
    'Content sizing' => ['it' => 'Dimensionamento contenuto', 'de' => 'Inhaltsgröße'],
    'Contents' => ['it' => 'Indice', 'de' => 'Inhaltsverzeichnis'],
    'Contrast (%)' => ['it' => 'Contrasto (%)', 'de' => 'Kontrast (%)'],
    'Convert' => ['it' => 'Converti', 'de' => 'Konvertieren'],
    'Convert to grayscale (Ghostscript)' => ['it' => 'Converti in scala di grigi (Ghostscript)', 'de' => 'In Graustufen umwandeln (Ghostscript)'],
    'Converting Office document to PDF' => ['it' => 'Conversione documento Office in PDF', 'de' => 'Office-Dokument wird in PDF umgewandelt'],
    'Copy' => ['it' => 'Copia', 'de' => 'Kopieren'],
    'Copy commands' => ['it' => 'Copia comandi', 'de' => 'Befehle kopieren'],
    'Could not check for updates: {0} You can continue using PDF Studio.' => ['it' => 'Impossibile controllare gli aggiornamenti: {0} Puoi continuare a usare PDF Studio.', 'de' => 'Updates konnten nicht geprüft werden: {0} Sie können PDF Studio weiter verwenden.'],
    'Could not download the update: {0} Your open document is unchanged.' => ['it' => 'Impossibile scaricare l\'aggiornamento: {0} Il documento aperto non è stato modificato.', 'de' => 'Das Update konnte nicht heruntergeladen werden: {0} Ihr geöffnetes Dokument bleibt unverändert.'],
    'Could not load' => ['it' => 'Impossibile caricare', 'de' => 'Konnte nicht geladen werden'],
    'Could not read image.' => ['it' => 'Impossibile leggere l\'immagine.', 'de' => 'Das Bild konnte nicht gelesen werden.'],
    'Could not relink' => ['it' => 'Impossibile ricollegare', 'de' => 'Konnte nicht erneut verknüpft werden'],
    'Create' => ['it' => 'Crea', 'de' => 'Erstellen'],
    'Create AcroForm field' => ['it' => 'Crea campo AcroForm', 'de' => 'AcroForm-Feld erstellen'],
    'Create document' => ['it' => 'Crea documento', 'de' => 'Dokument erstellen'],
    'Create Document' => ['it' => 'Crea documento', 'de' => 'Dokument erstellen'],
    'Create documents with a Word-like editor and export them as PDF.' => ['it' => 'Crea documenti con un editor simile a Word ed esportali in PDF.', 'de' => 'Erstellen Sie Dokumente mit einem Word-ähnlichen Editor und exportieren Sie sie als PDF.'],
    'Create field' => ['it' => 'Crea campo', 'de' => 'Feld erstellen'],
    'Create form field' => ['it' => 'Crea campo modulo', 'de' => 'Formularfeld erstellen'],
    'Create N-up contact sheet' => ['it' => 'Crea foglio di contatto N-up', 'de' => 'N-up-Kontaktbogen erstellen'],
    'Create new document' => ['it' => 'Crea nuovo documento', 'de' => 'Neues Dokument erstellen'],
    'Create PDF' => ['it' => 'Crea PDF', 'de' => 'PDF erstellen'],
    'Create printable booklet' => ['it' => 'Crea opuscolo stampabile', 'de' => 'Druckbare Broschüre erstellen'],
    'Created {0}. Export time is used for the PDF modification date.' => ['it' => 'Creato il {0}. Per la data di modifica PDF si usa il momento dell\'esportazione.', 'de' => 'Erstellt am {0}. Als PDF-Änderungsdatum wird der Exportzeitpunkt verwendet.'],
    'Creates left-bound booklet sheets in duplex order. Print two-sided with short-edge binding at actual size. Blank pages are added to a multiple of four. Interactive fields are flattened.' => ['it' => 'Crea fogli per opuscoli rilegati a sinistra nell\'ordine fronte-retro. Stampa su entrambi i lati con rilegatura sul bordo corto e dimensioni effettive. Le pagine vuote portano il totale a un multiplo di quattro. I campi interattivi diventano permanenti.', 'de' => 'Erstellt Bögen für linksgebundene Broschüren in Duplexreihenfolge. Drucken Sie beidseitig mit Bindung an der kurzen Kante und tatsächlicher Größe. Leerseiten ergänzen die Gesamtzahl auf ein Vielfaches von vier. Interaktive Felder werden abgeflacht.'],
    'Creating contact sheet' => ['it' => 'Creazione foglio di contatto', 'de' => 'Kontaktbogen wird erstellt'],
    'Creating PDF from images' => ['it' => 'Creazione PDF da immagini', 'de' => 'PDF wird aus Bildern erstellt'],
    'Creating scanned PDF' => ['it' => 'Creazione PDF da scansione', 'de' => 'Scan-PDF wird erstellt'],
    'Creation date (UTC)' => ['it' => 'Data di creazione (UTC)', 'de' => 'Erstellungsdatum (UTC)'],
    'Creator' => ['it' => 'Creatore', 'de' => 'Ersteller'],
    'Crop' => ['it' => 'Ritaglia', 'de' => 'Zuschneiden'],
    'Crop extends beyond the image.' => ['it' => 'Il ritaglio supera i bordi dell\'immagine.', 'de' => 'Der Zuschnitt liegt außerhalb des Bildes.'],
    'Crop image' => ['it' => 'Ritaglia immagine', 'de' => 'Bild zuschneiden'],
    'Crop leaves no usable area on page {0}.' => ['it' => 'Il ritaglio non lascia un\'area utilizzabile sulla pagina {0}.', 'de' => 'Der Zuschnitt lässt auf Seite {0} keine nutzbare Fläche übrig.'],
    'Crop left / top (%)' => ['it' => 'Ritaglio da sinistra / alto (%)', 'de' => 'Zuschnitt links / oben (%)'],
    'Crop pages' => ['it' => 'Ritaglia pagine', 'de' => 'Seiten zuschneiden'],
    'Crop selected image' => ['it' => 'Ritaglia immagine selezionata', 'de' => 'Ausgewähltes Bild zuschneiden'],
    'Crop selected pages' => ['it' => 'Ritaglia pagine selezionate', 'de' => 'Ausgewählte Seiten zuschneiden'],
    'Crop the original image using percentage coordinates. The original image is replaced with the cropped bitmap.' => ['it' => 'Ritaglia l\'immagine originale usando coordinate percentuali. L\'originale viene sostituito con l\'immagine ritagliata.', 'de' => 'Schneiden Sie das Originalbild mit Prozentangaben zu. Das Original wird durch die zugeschnittene Bilddatei ersetzt.'],
    'Crop width / height (%)' => ['it' => 'Larghezza / altezza ritaglio (%)', 'de' => 'Zuschnittbreite / -höhe (%)'],
    'Cropping adjusts the visible portion of this image without changing the original PDF.' => ['it' => 'Il ritaglio modifica la parte visibile dell\'immagine senza cambiare il PDF originale.', 'de' => 'Der Zuschnitt ändert den sichtbaren Bildbereich, ohne das ursprüngliche PDF zu verändern.'],
    'Cropping changes the PDF CropBox. Hidden content remains in the file; use secure redaction to remove sensitive content. Automatic detection samples pages at 72 DPI.' => ['it' => 'Il ritaglio modifica il CropBox del PDF. Il contenuto nascosto resta nel file; per rimuovere informazioni sensibili usa l\'oscuramento sicuro. Il rilevamento automatico campiona le pagine a 72 DPI.', 'de' => 'Der Zuschnitt ändert die PDF-CropBox. Verdeckter Inhalt bleibt in der Datei; entfernen Sie vertrauliche Inhalte mit sicherer Schwärzung. Die automatische Erkennung untersucht Seiten mit 72 DPI.'],
    'Cropping pages' => ['it' => 'Ritaglio pagine', 'de' => 'Seiten werden zugeschnitten'],
    'Current cell width (%) · 0 = automatic' => ['it' => 'Larghezza cella corrente (%) · 0 = automatica', 'de' => 'Breite der aktuellen Zelle (%) · 0 = automatisch'],
    'Current date' => ['it' => 'Data corrente', 'de' => 'Aktuelles Datum'],
    'Current edited document' => ['it' => 'Documento corrente modificato', 'de' => 'Aktuell bearbeitetes Dokument'],
    'Current tc-lib-pdf releases require PHP 8.2 or newer. On PHP 7.2, use mPDF or Dompdf for document export. This optional library is detected only; no additional PDF Studio tool depends on it.' => ['it' => 'Le versioni attuali di tc-lib-pdf richiedono PHP 8.2 o successivo. Con PHP 7.2 usa mPDF o Dompdf per esportare i documenti. Questa libreria facoltativa viene solo rilevata; nessuno strumento aggiuntivo di PDF Studio dipende da essa.', 'de' => 'Aktuelle tc-lib-pdf-Versionen benötigen PHP 8.2 oder neuer. Verwenden Sie unter PHP 7.2 mPDF oder Dompdf für den Dokumentexport. Diese optionale Bibliothek wird nur erkannt; kein zusätzliches PDF-Studio-Werkzeug benötigt sie.'],
    'Current time' => ['it' => 'Ora corrente', 'de' => 'Aktuelle Uhrzeit'],
    'Custom' => ['it' => 'Personalizzato', 'de' => 'Benutzerdefiniert'],
    'Custom (PDF points)' => ['it' => 'Personalizzato (punti PDF)', 'de' => 'Benutzerdefiniert (PDF-Punkte)'],
    'Custom groups' => ['it' => 'Gruppi personalizzati', 'de' => 'Benutzerdefinierte Gruppen'],
    'Custom groups accept the same page ranges as the organizer. A ZIP is downloaded for multiple outputs. Maximum size is measured after saving each group; a single page may exceed your target.' => ['it' => 'I gruppi personalizzati accettano gli stessi intervalli dell\'organizzatore. Per più risultati viene scaricato uno ZIP. La dimensione massima viene misurata dopo il salvataggio di ogni gruppo; una singola pagina può superare il limite scelto.', 'de' => 'Benutzerdefinierte Gruppen verwenden dieselben Seitenbereiche wie die Seitenverwaltung. Mehrere Ergebnisse werden als ZIP heruntergeladen. Die maximale Größe wird nach dem Speichern jeder Gruppe gemessen; eine einzelne Seite kann die Zielgröße überschreiten.'],
    'Custom groups, separated by semicolons' => ['it' => 'Gruppi personalizzati separati da punto e virgola', 'de' => 'Benutzerdefinierte Gruppen mit Semikolon trennen'],
    'Custom height' => ['it' => 'Altezza personalizzata', 'de' => 'Benutzerdefinierte Höhe'],
    'Custom height (mm)' => ['it' => 'Altezza personalizzata (mm)', 'de' => 'Benutzerdefinierte Höhe (mm)'],
    'Custom page dimensions must be between 30 and 1000 mm.' => ['it' => 'Le dimensioni personalizzate devono essere comprese tra 30 e 1000 mm.', 'de' => 'Benutzerdefinierte Seitengrößen müssen zwischen 30 und 1000 mm liegen.'],
    'Custom sheet height' => ['it' => 'Altezza foglio personalizzata', 'de' => 'Benutzerdefinierte Bogenhöhe'],
    'Custom sheet width (PDF points)' => ['it' => 'Larghezza foglio personalizzata (punti PDF)', 'de' => 'Benutzerdefinierte Bogenbreite (PDF-Punkte)'],
    'Custom stamp' => ['it' => 'Timbro personalizzato', 'de' => 'Benutzerdefinierter Stempel'],
    'Custom width (mm)' => ['it' => 'Larghezza personalizzata (mm)', 'de' => 'Benutzerdefinierte Breite (mm)'],
    'Custom width (PDF points)' => ['it' => 'Larghezza personalizzata (punti PDF)', 'de' => 'Benutzerdefinierte Breite (PDF-Punkte)'],
    'Date' => ['it' => 'Data', 'de' => 'Datum'],
    'Decryption' => ['it' => 'Decrittazione', 'de' => 'Entschlüsselung'],
    'Default text / selected option' => ['it' => 'Testo predefinito / opzione selezionata', 'de' => 'Standardtext / ausgewählte Option'],
    'Default value must match an option.' => ['it' => 'Il valore predefinito deve corrispondere a un\'opzione.', 'de' => 'Der Standardwert muss einer Option entsprechen.'],
    'Delete' => ['it' => 'Elimina', 'de' => 'Löschen'],
    'Delete object' => ['it' => 'Elimina oggetto', 'de' => 'Objekt löschen'],
    'Detect whitespace automatically' => ['it' => 'Rileva automaticamente gli spazi vuoti', 'de' => 'Leerräume automatisch erkennen'],
    'Detected on an input document' => ['it' => 'Rilevato in un documento di origine', 'de' => 'In einem Quelldokument erkannt'],
    'Difference intensity' => ['it' => 'Intensità differenze', 'de' => 'Differenzintensität'],
    'Different first page' => ['it' => 'Prima pagina diversa', 'de' => 'Abweichende erste Seite'],
    'Direction' => ['it' => 'Direzione', 'de' => 'Richtung'],
    'Disallow printing' => ['it' => 'Vieta stampa', 'de' => 'Drucken verbieten'],
    'Distribute horizontally' => ['it' => 'Distribuisci orizzontalmente', 'de' => 'Horizontal verteilen'],
    'Distribute vertically' => ['it' => 'Distribuisci verticalmente', 'de' => 'Vertikal verteilen'],
    'Document editor could not load:' => ['it' => 'Impossibile caricare l\'editor documenti:', 'de' => 'Der Dokumenteditor konnte nicht geladen werden:'],
    'Document files' => ['it' => 'File documento', 'de' => 'Dokumentdateien'],
    'Document inspector' => ['it' => 'Ispezione documento', 'de' => 'Dokumentprüfung'],
    'Document metadata' => ['it' => 'Metadati documento', 'de' => 'Dokumentmetadaten'],
    'Document page layout' => ['it' => 'Layout delle pagine del documento', 'de' => 'Dokumentseitenlayout'],
    'Document paper preview' => ['it' => 'Anteprima foglio documento', 'de' => 'Dokumentpapier-Vorschau'],
    'Document PDF rendering engine' => ['it' => 'Motore di rendering PDF del documento', 'de' => 'PDF-Rendering-Engine für Dokumente'],
    'Document print' => ['it' => 'Stampa documento', 'de' => 'Dokument drucken'],
    'Document print preview' => ['it' => 'Anteprima stampa documento', 'de' => 'Dokumentdruck-Vorschau'],
    'Document properties' => ['it' => 'Proprietà documento', 'de' => 'Dokumenteigenschaften'],
    'Document safety' => ['it' => 'Sicurezza documento', 'de' => 'Dokumentsicherheit'],
    'Document source' => ['it' => 'Sorgente documento', 'de' => 'Dokumentquelle'],
    'Document source exceeds the 50 MB browser project limit.' => ['it' => 'La sorgente del documento supera il limite di 50 MB per i progetti nel browser.', 'de' => 'Die Dokumentquelle überschreitet das Browser-Projektlimit von 50 MB.'],
    'DOCUMENT WORKSPACE' => ['it' => 'AREA DOCUMENTI', 'de' => 'DOKUMENTARBEITSBEREICH'],
    'DOCX, ODT, XLSX and PPTX to PDF' => ['it' => 'Da DOCX, ODT, XLSX e PPTX a PDF', 'de' => 'DOCX, ODT, XLSX und PPTX in PDF'],
    'does not have a valid PDF signature.' => ['it' => 'non ha una firma PDF valida.', 'de' => 'hat keine gültige PDF-Dateikennung.'],
    'Dompdf · basic HTML/CSS' => ['it' => 'Dompdf · HTML/CSS di base', 'de' => 'Dompdf · grundlegendes HTML/CSS'],
    'Double-click to edit' => ['it' => 'Doppio clic per modificare', 'de' => 'Doppelklicken zum Bearbeiten'],
    'Down columns' => ['it' => 'Per colonne', 'de' => 'Spaltenweise'],
    'Download HTML' => ['it' => 'Scarica HTML', 'de' => 'HTML herunterladen'],
    'Download index.php' => ['it' => 'Scarica index.php', 'de' => 'index.php herunterladen'],
    'Download source' => ['it' => 'Scarica sorgente', 'de' => 'Quelldatei herunterladen'],
    'Download the new index.php and replace it on your server, keeping your optional vendor directory.' => ['it' => 'Scarica il nuovo index.php e sostituiscilo sul server, mantenendo la cartella vendor facoltativa.', 'de' => 'Laden Sie die neue index.php herunter und ersetzen Sie sie auf Ihrem Server. Behalten Sie das optionale vendor-Verzeichnis bei.'],
    'Download TXT' => ['it' => 'Scarica TXT', 'de' => 'TXT herunterladen'],
    'Downloading…' => ['it' => 'Download…', 'de' => 'Wird heruntergeladen…'],
    'Downloads contain your selected changes. The opened source remains local.' => ['it' => 'I download contengono le modifiche selezionate. Il file originale aperto resta locale.', 'de' => 'Downloads enthalten Ihre ausgewählten Änderungen. Die geöffnete Originaldatei bleibt lokal.'],
    'DPI exceeds the memory limit for this page. Select a lower DPI.' => ['it' => 'I DPI superano il limite di memoria per questa pagina. Scegli un valore inferiore.', 'de' => 'Der DPI-Wert überschreitet das Speicherlimit für diese Seite. Wählen Sie einen niedrigeren Wert.'],
    'Drag a crop rectangle on the current page, then adjust its corner handles. The same trim measurements apply to the selected pages.' => ['it' => 'Trascina un rettangolo di ritaglio sulla pagina corrente, poi regola gli angoli. Le stesse misure si applicano alle pagine selezionate.', 'de' => 'Ziehen Sie auf der aktuellen Seite ein Zuschnittrechteck auf und passen Sie die Eckpunkte an. Dieselben Zuschnittmaße gelten für alle ausgewählten Seiten.'],
    'Drag files to arrange them. Page range applies to PDFs.' => ['it' => 'Trascina i file per ordinarli. L\'intervallo di pagine si applica ai PDF.', 'de' => 'Ziehen Sie Dateien, um sie anzuordnen. Seitenbereiche gelten für PDFs.'],
    'Drag on the page to add' => ['it' => 'Trascina sulla pagina per aggiungere', 'de' => 'Ziehen Sie auf der Seite, um Folgendes hinzuzufügen:'],
    'Drag to arrange images' => ['it' => 'Trascina per ordinare le immagini', 'de' => 'Bilder zum Anordnen ziehen'],
    'Draw' => ['it' => 'Disegna', 'de' => 'Zeichnen'],
    'Draw a rectangle over the area you want to make clickable.' => ['it' => 'Disegna un rettangolo sull\'area da rendere cliccabile.', 'de' => 'Zeichnen Sie ein Rechteck über den Bereich, der anklickbar werden soll.'],
    'Draw a signature' => ['it' => 'Disegna una firma', 'de' => 'Unterschrift zeichnen'],
    'Draw below or type your signature. This is a visual mark, with no cryptographic signing.' => ['it' => 'Disegna qui sotto oppure digita la firma. È un segno visivo senza firma crittografica.', 'de' => 'Zeichnen Sie unten oder tippen Sie Ihre Unterschrift. Dies ist eine sichtbare Markierung ohne kryptografische Signatur.'],
    'Draw cell borders' => ['it' => 'Disegna bordi celle', 'de' => 'Zellenrahmen zeichnen'],
    'Draw or type a signature first.' => ['it' => 'Prima disegna o digita una firma.', 'de' => 'Zeichnen oder tippen Sie zuerst eine Unterschrift.'],
    'Draw secure region' => ['it' => 'Disegna area sicura', 'de' => 'Sicheren Bereich zeichnen'],
    'Draw with your mouse or finger' => ['it' => 'Disegna con il mouse o il dito', 'de' => 'Mit Maus oder Finger zeichnen'],
    'Draw, type or upload a signature' => ['it' => 'Disegna, digita o carica una firma', 'de' => 'Unterschrift zeichnen, tippen oder hochladen'],
    'Draw, type, or upload a signature first.' => ['it' => 'Prima disegna, digita o carica una firma.', 'de' => 'Zeichnen, tippen oder laden Sie zuerst eine Unterschrift hoch.'],
    'Drop PDFs or images here' => ['it' => 'Trascina qui PDF o immagini', 'de' => 'PDFs oder Bilder hier ablegen'],
    'Dropdown' => ['it' => 'Menu a discesa', 'de' => 'Auswahlliste'],
    'Duplicate' => ['it' => 'Duplica', 'de' => 'Duplizieren'],
    'Edit metadata / privacy clean' => ['it' => 'Modifica metadati / pulizia privacy', 'de' => 'Metadaten bearbeiten / Datenschutzbereinigung'],
    'Edit PDF' => ['it' => 'Modifica PDF', 'de' => 'PDF bearbeiten'],
    'Edit, organize, annotate and visually sign PDFs in your browser.' => ['it' => 'Modifica, organizza, annota e firma visivamente i PDF nel browser.', 'de' => 'Bearbeiten, ordnen, kommentieren und unterschreiben Sie PDFs sichtbar im Browser.'],
    'Edit, organize, sign, convert, or create a document from scratch.' => ['it' => 'Modifica, organizza, firma, converti oppure crea un documento da zero.', 'de' => 'Bearbeiten, ordnen, unterschreiben, konvertieren oder erstellen Sie ein neues Dokument.'],
    'Editable overlay export requires Fabric.js, which could not load.' => ['it' => 'L\'esportazione delle sovrapposizioni modificabili richiede Fabric.js, che non è stato caricato.', 'de' => 'Der Export bearbeitbarer Überlagerungen erfordert Fabric.js, das nicht geladen werden konnte.'],
    'Editable project files' => ['it' => 'File di progetto modificabili', 'de' => 'Bearbeitbare Projektdateien'],
    'Editing is continuous. Print preview uses the selected paper size and shows final pagination. Table cell selection, merging, splitting and column resizing are available in Jodit\'s table popup. Browser print preserves modern HTML/CSS most faithfully; page counters and margin headers depend on your browser.' => ['it' => 'La modifica è continua. L\'anteprima di stampa usa il formato carta selezionato e mostra la paginazione finale. Nel menu tabella di Jodit puoi selezionare, unire, dividere celle e ridimensionare colonne. La stampa browser riproduce meglio HTML/CSS moderni; contatori di pagina e intestazioni nei margini dipendono dal browser.', 'de' => 'Die Bearbeitung erfolgt fortlaufend. Die Druckvorschau verwendet das gewählte Papierformat und zeigt die endgültige Seiteneinteilung. Im Jodit-Tabellenmenü können Sie Zellen auswählen, verbinden, teilen und Spaltenbreiten ändern. Browserdruck bildet modernes HTML/CSS am zuverlässigsten ab; Seitenzähler und Randkopfzeilen hängen vom Browser ab.'],
    'Ellipse' => ['it' => 'Ellisse', 'de' => 'Ellipse'],
    'Embedded PDF exceeds the configured file limit.' => ['it' => 'Il PDF incorporato supera il limite di dimensione configurato.', 'de' => 'Das eingebettete PDF überschreitet die eingerichtete Dateigrößengrenze.'],
    'Embedded source hash does not match' => ['it' => 'L\'hash dell\'originale incorporato non corrisponde', 'de' => 'Der Hash der eingebetteten Quelldatei stimmt nicht überein'],
    'Embedded text differences (deleted / inserted words)' => ['it' => 'Differenze del testo incorporato (parole eliminate / inserite)', 'de' => 'Unterschiede im eingebetteten Text (gelöschte / eingefügte Wörter)'],
    'Encrypt PDF on server' => ['it' => 'Cifra PDF sul server', 'de' => 'PDF auf dem Server verschlüsseln'],
    'Encrypted server processing requires qpdf so passwords can be supplied privately. Decrypt the document first.' => ['it' => 'L\'elaborazione di PDF cifrati sul server richiede qpdf per fornire le password in modo privato. Prima decifra il documento.', 'de' => 'Die Verarbeitung verschlüsselter PDFs auf dem Server benötigt qpdf für die vertrauliche Passwortübergabe. Entschlüsseln Sie zuerst das Dokument.'],
    'Encryption' => ['it' => 'Cifratura', 'de' => 'Verschlüsselung'],
    'Engine' => ['it' => 'Motore', 'de' => 'Engine'],
    'Enter a positive number.' => ['it' => 'Inserisci un numero positivo.', 'de' => 'Geben Sie eine positive Zahl ein.'],
    'Enter a unique field name of 1–200 characters.' => ['it' => 'Inserisci un nome di campo univoco da 1 a 200 caratteri.', 'de' => 'Geben Sie einen eindeutigen Feldnamen mit 1 bis 200 Zeichen ein.'],
    'Enter a valid UTC date.' => ['it' => 'Inserisci una data UTC valida.', 'de' => 'Geben Sie ein gültiges UTC-Datum ein.'],
    'Enter an opening password or owner password.' => ['it' => 'Inserisci una password di apertura o proprietario.', 'de' => 'Geben Sie ein Öffnungs- oder Eigentümerpasswort ein.'],
    'Enter at least one dropdown option.' => ['it' => 'Inserisci almeno un\'opzione per il menu a discesa.', 'de' => 'Geben Sie mindestens einen Eintrag für die Auswahlliste ein.'],
    'Enter at least one radio option.' => ['it' => 'Inserisci almeno un\'opzione per il gruppo radio.', 'de' => 'Geben Sie mindestens eine Option für die Optionsfelder ein.'],
    'Enter valid Tesseract language codes, for example eng+ita.' => ['it' => 'Inserisci codici lingua Tesseract validi, per esempio eng+ita.', 'de' => 'Geben Sie gültige Tesseract-Sprachcodes ein, zum Beispiel eng+ita.'],
    'Entire files in order' => ['it' => 'File interi in ordine', 'de' => 'Ganze Dateien in Reihenfolge'],
    'Every N pages' => ['it' => 'Ogni N pagine', 'de' => 'Alle N Seiten'],
    'Every page' => ['it' => 'Ogni pagina', 'de' => 'Jede Seite'],
    'Export PDF' => ['it' => 'Esporta PDF', 'de' => 'PDF exportieren'],
    'Export PDF pages as images' => ['it' => 'Esporta le pagine PDF come immagini', 'de' => 'PDF-Seiten als Bilder exportieren'],
    'Exporting images' => ['it' => 'Esportazione immagini', 'de' => 'Bilder werden exportiert'],
    'Exporting page' => ['it' => 'Esportazione pagina', 'de' => 'Seite wird exportiert'],
    'Exporting PDF' => ['it' => 'Esportazione PDF', 'de' => 'PDF wird exportiert'],
    'External XML entities are not permitted.' => ['it' => 'Le entità XML esterne non sono consentite.', 'de' => 'Externe XML-Entitäten sind nicht zulässig.'],
    'Extract' => ['it' => 'Estrai', 'de' => 'Extrahieren'],
    'Extract embedded images' => ['it' => 'Estrai immagini incorporate', 'de' => 'Eingebettete Bilder extrahieren'],
    'Extract embedded images on server' => ['it' => 'Estrai immagini incorporate sul server', 'de' => 'Eingebettete Bilder auf dem Server extrahieren'],
    'Extract embedded text' => ['it' => 'Estrai testo incorporato', 'de' => 'Eingebetteten Text extrahieren'],
    'Extract original embedded images' => ['it' => 'Estrai immagini originali incorporate', 'de' => 'Eingebettete Originalbilder extrahieren'],
    'Extract text' => ['it' => 'Estrai testo', 'de' => 'Text extrahieren'],
    'Extracted PDF text' => ['it' => 'Testo estratto dal PDF', 'de' => 'Extrahierter PDF-Text'],
    'Extracted text' => ['it' => 'Testo estratto', 'de' => 'Extrahierter Text'],
    'Extracting embedded images' => ['it' => 'Estrazione immagini incorporate', 'de' => 'Eingebettete Bilder werden extrahiert'],
    'Extracting page text' => ['it' => 'Estrazione testo pagina', 'de' => 'Seitentext wird extrahiert'],
    'Extracting pages' => ['it' => 'Estrazione pagine', 'de' => 'Seiten werden extrahiert'],
    'Extracting text' => ['it' => 'Estrazione testo', 'de' => 'Text wird extrahiert'],
    'Fast Web View' => ['it' => 'Visualizzazione web rapida', 'de' => 'Schnelle Webanzeige'],
    'Fast Web View / linearize' => ['it' => 'Visualizzazione web rapida / linearizza', 'de' => 'Schnelle Webanzeige / linearisieren'],
    'Fast Web View / linearize output' => ['it' => 'Visualizzazione web rapida / linearizza risultato', 'de' => 'Schnelle Webanzeige / Ergebnis linearisieren'],
    'Features / details' => ['it' => 'Funzioni / dettagli', 'de' => 'Funktionen / Details'],
    'Field' => ['it' => 'Campo', 'de' => 'Feld'],
    'Field type' => ['it' => 'Tipo campo', 'de' => 'Feldtyp'],
    'Field values are saved in the PDF. Unsupported button and cryptographic signature fields are shown as read-only. Standard Latin characters are supported by the appearance font.' => ['it' => 'I valori dei campi vengono salvati nel PDF. I campi pulsante e firma crittografica non supportati sono in sola lettura. Il carattere di visualizzazione supporta i caratteri latini standard.', 'de' => 'Feldwerte werden im PDF gespeichert. Nicht unterstützte Schaltflächen und kryptografische Signaturfelder werden schreibgeschützt angezeigt. Die Darstellungsschrift unterstützt Standard-Lateinzeichen.'],
    'Files' => ['it' => 'File', 'de' => 'Dateien'],
    'Files are processed in private temporary directories and removed after the response.' => ['it' => 'I file vengono elaborati in cartelle temporanee private ed eliminati dopo la risposta.', 'de' => 'Dateien werden in privaten temporären Verzeichnissen verarbeitet und nach der Antwort entfernt.'],
    'Fill / crop' => ['it' => 'Riempi / ritaglia', 'de' => 'Füllen / zuschneiden'],
    'Fill & Sign' => ['it' => 'Compila e firma', 'de' => 'Ausfüllen und unterschreiben'],
    'Fill form' => ['it' => 'Compila modulo', 'de' => 'Formular ausfüllen'],
    'Fill PDF form' => ['it' => 'Compila modulo PDF', 'de' => 'PDF-Formular ausfüllen'],
    'Fill PDF forms' => ['it' => 'Compila moduli PDF', 'de' => 'PDF-Formulare ausfüllen'],
    'Find' => ['it' => 'Trova', 'de' => 'Suchen'],
    'Find in extracted text' => ['it' => 'Cerca nel testo estratto', 'de' => 'Im extrahierten Text suchen'],
    'Find text' => ['it' => 'Trova testo', 'de' => 'Text suchen'],
    'First' => ['it' => 'Prima', 'de' => 'Erste'],
    'First row is a repeating header' => ['it' => 'La prima riga è un\'intestazione ripetuta', 'de' => 'Erste Zeile als wiederholte Kopfzeile'],
    'First-line indent (mm)' => ['it' => 'Rientro prima riga (mm)', 'de' => 'Erstzeileneinzug (mm)'],
    'First-page footer' => ['it' => 'Piè di pagina della prima pagina', 'de' => 'Fußzeile der ersten Seite'],
    'First-page header' => ['it' => 'Intestazione della prima pagina', 'de' => 'Kopfzeile der ersten Seite'],
    'Fit' => ['it' => 'Adatta', 'de' => 'Anpassen'],
    'Fit / preserve aspect ratio' => ['it' => 'Adatta / mantieni proporzioni', 'de' => 'Anpassen / Seitenverhältnis erhalten'],
    'Fit inside' => ['it' => 'Adatta all\'interno', 'de' => 'Einpassen'],
    'Fit page' => ['it' => 'Adatta pagina', 'de' => 'Seite einpassen'],
    'Fit width' => ['it' => 'Adatta larghezza', 'de' => 'Breite einpassen'],
    'Flatten fields after filling (cannot edit later)' => ['it' => 'Rendi permanenti i campi dopo la compilazione (non più modificabili)', 'de' => 'Felder nach dem Ausfüllen abflachen (nicht mehr bearbeitbar)'],
    'Flatten form' => ['it' => 'Rendi modulo permanente', 'de' => 'Formular abflachen'],
    'Flatten form fields' => ['it' => 'Rendi permanenti i campi modulo', 'de' => 'Formularfelder abflachen'],
    'Flatten forms' => ['it' => 'Rendi moduli permanenti', 'de' => 'Formulare abflachen'],
    'Flip X' => ['it' => 'Rifletti X', 'de' => 'Horizontal spiegeln'],
    'Flip Y' => ['it' => 'Rifletti Y', 'de' => 'Vertikal spiegeln'],
    'Float left · text wraps right' => ['it' => 'Allinea a sinistra · testo a destra', 'de' => 'Links umflossen · Text rechts'],
    'Float right · text wraps left' => ['it' => 'Allinea a destra · testo a sinistra', 'de' => 'Rechts umflossen · Text links'],
    'Font' => ['it' => 'Carattere', 'de' => 'Schriftart'],
    'Font families' => ['it' => 'Famiglie di caratteri', 'de' => 'Schriftfamilien'],
    'Font size' => ['it' => 'Dimensione carattere', 'de' => 'Schriftgröße'],
    'Font size / image width (PDF points)' => ['it' => 'Dimensione carattere / larghezza immagine (punti PDF)', 'de' => 'Schriftgröße / Bildbreite (PDF-Punkte)'],
    'Footer' => ['it' => 'Piè di pagina', 'de' => 'Fußzeile'],
    'For safe removal of shared resources, this exporter rebuilds the entire output as page images. Original text, vectors, forms, attachments and metadata are discarded. Searchability is lost. Draw black redaction regions, then export.' => ['it' => 'Per eliminare in sicurezza le risorse condivise, l\'esportazione ricrea l\'intero documento come immagini delle pagine. Testo originale, vettori, moduli, allegati e metadati vengono rimossi. Non sarà più possibile cercare il testo. Disegna le aree nere di oscuramento, poi esporta.', 'de' => 'Für die sichere Entfernung gemeinsam genutzter Ressourcen wird das gesamte Ergebnis als Seitenbilder neu aufgebaut. Originaltext, Vektoren, Formulare, Anhänge und Metadaten werden verworfen. Die Textsuche geht verloren. Zeichnen Sie schwarze Schwärzungsbereiche und exportieren Sie anschließend.'],
    'For Thai recognition, also install: sudo apt install -y tesseract-ocr-tha. Other languages have their own tesseract-ocr language packages.' => ['it' => 'Per riconoscere il thailandese installa anche: sudo apt install -y tesseract-ocr-tha. Le altre lingue hanno propri pacchetti tesseract-ocr.', 'de' => 'Für die Erkennung von Thai installieren Sie zusätzlich: sudo apt install -y tesseract-ocr-tha. Andere Sprachen haben eigene tesseract-ocr-Sprachpakete.'],
    'Form fields / annotations' => ['it' => 'Campi modulo / annotazioni', 'de' => 'Formularfelder / Anmerkungen'],
    'Form fields are flattened. PDF comments, highlight annotations and link annotations are omitted from imposed sheets.' => ['it' => 'I campi modulo diventano permanenti. Commenti PDF, evidenziazioni e collegamenti vengono omessi dai fogli impaginati.', 'de' => 'Formularfelder werden abgeflacht. PDF-Kommentare, Hervorhebungen und Verknüpfungen werden auf montierten Bögen weggelassen.'],
    'Form values will become permanent page content. Fields will no longer be interactive. Undo can restore the previous document.' => ['it' => 'I valori dei moduli diventano contenuto permanente della pagina. I campi non saranno più interattivi. Annulla può ripristinare il documento precedente.', 'de' => 'Formularwerte werden zu dauerhaftem Seiteninhalt. Die Felder sind nicht mehr interaktiv. Rückgängig kann das vorherige Dokument wiederherstellen.'],
    'Format' => ['it' => 'Formato', 'de' => 'Format'],
    'Forms' => ['it' => 'Moduli', 'de' => 'Formulare'],
    'Full screen' => ['it' => 'Schermo intero', 'de' => 'Vollbild'],
    'Full-quality printing' => ['it' => 'Stampa a qualità completa', 'de' => 'Drucken in voller Qualität'],
    'Full-screen mode is unavailable in this browser.' => ['it' => 'La modalità schermo intero non è disponibile in questo browser.', 'de' => 'Der Vollbildmodus ist in diesem Browser nicht verfügbar.'],
    'GIF uses its first frame. Transparent images are composited onto the selected background.' => ['it' => 'Per i GIF si usa il primo fotogramma. Le immagini trasparenti vengono composte sullo sfondo scelto.', 'de' => 'Bei GIFs wird das erste Bild verwendet. Transparente Bilder werden mit dem gewählten Hintergrund zusammengesetzt.'],
    'GitHub could not provide index.php (HTTP' => ['it' => 'GitHub non ha fornito index.php (HTTP', 'de' => 'GitHub konnte index.php nicht bereitstellen (HTTP'],
    'GitHub could not provide update information (HTTP' => ['it' => 'GitHub non ha fornito le informazioni di aggiornamento (HTTP', 'de' => 'GitHub konnte keine Updateinformationen bereitstellen (HTTP'],
    'GitHub did not return a valid PDF Studio file. Try again later.' => ['it' => 'GitHub non ha restituito un file PDF Studio valido. Riprova più tardi.', 'de' => 'GitHub hat keine gültige PDF-Studio-Datei geliefert. Versuchen Sie es später erneut.'],
    'Grayscale' => ['it' => 'Scala di grigi', 'de' => 'Graustufen'],
    'Header' => ['it' => 'Intestazione', 'de' => 'Kopfzeile'],
    'Header and footer' => ['it' => 'Intestazione e piè di pagina', 'de' => 'Kopf- und Fußzeile'],
    'Headers and footers' => ['it' => 'Intestazioni e piè di pagina', 'de' => 'Kopf- und Fußzeilen'],
    'Headers, footers & page numbers' => ['it' => 'Intestazioni, piè di pagina e numeri di pagina', 'de' => 'Kopfzeilen, Fußzeilen und Seitenzahlen'],
    'Headers, footers and page numbering' => ['it' => 'Intestazioni, piè di pagina e numerazione pagine', 'de' => 'Kopfzeilen, Fußzeilen und Seitennummerierung'],
    'Headers, footers, page numbering' => ['it' => 'Intestazioni, piè di pagina, numerazione pagine', 'de' => 'Kopfzeilen, Fußzeilen, Seitennummerierung'],
    'Heading 1' => ['it' => 'Titolo 1', 'de' => 'Überschrift 1'],
    'Heading 2' => ['it' => 'Titolo 2', 'de' => 'Überschrift 2'],
    'Heading 3' => ['it' => 'Titolo 3', 'de' => 'Überschrift 3'],
    'Heading 4' => ['it' => 'Titolo 4', 'de' => 'Überschrift 4'],
    'Heading 5' => ['it' => 'Titolo 5', 'de' => 'Überschrift 5'],
    'Heading 6' => ['it' => 'Titolo 6', 'de' => 'Überschrift 6'],
    'Height' => ['it' => 'Altezza', 'de' => 'Höhe'],
    'Height (px)' => ['it' => 'Altezza (px)', 'de' => 'Höhe (px)'],
    'Helvetica / Arial' => ['it' => 'Helvetica / Arial', 'de' => 'Helvetica / Arial'],
    'Highlight' => ['it' => 'Evidenzia', 'de' => 'Hervorheben'],
    'Home' => ['it' => 'Home', 'de' => 'Start'],
    'Horizontal center' => ['it' => 'Centro orizzontale', 'de' => 'Horizontal zentrieren'],
    'How to upgrade' => ['it' => 'Come aggiornare', 'de' => 'So aktualisieren Sie'],
    'HTML · standalone document with embedded settings' => ['it' => 'HTML · documento autonomo con impostazioni incorporate', 'de' => 'HTML · eigenständiges Dokument mit eingebetteten Einstellungen'],
    'HTML export requires the PHP DOM extension.' => ['it' => 'L\'esportazione HTML richiede l\'estensione PHP DOM.', 'de' => 'Der HTML-Export benötigt die PHP-DOM-Erweiterung.'],
    'HTML imported. Remote images and executable markup were removed.' => ['it' => 'HTML importato. Immagini remote e codice eseguibile sono stati rimossi.', 'de' => 'HTML importiert. Externe Bilder und ausführbarer Code wurden entfernt.'],
    'HTML to PDF' => ['it' => 'Da HTML a PDF', 'de' => 'HTML in PDF'],
    'HTML-to-PDF requires mPDF, Dompdf or sandboxed Chromium, plus the PHP DOM extension. Browser print remains available.' => ['it' => 'La conversione HTML in PDF richiede mPDF, Dompdf o Chromium con sandbox e l\'estensione PHP DOM. La stampa browser resta disponibile.', 'de' => 'HTML-zu-PDF benötigt mPDF, Dompdf oder Chromium mit Sandbox sowie die PHP-DOM-Erweiterung. Browserdruck bleibt verfügbar.'],
    'HTML/CSS print rendering' => ['it' => 'Rendering di stampa HTML/CSS', 'de' => 'HTML/CSS-Druckrendering'],
    'Hyperlink region' => ['it' => 'Area collegamento', 'de' => 'Linkbereich'],
    'Image' => ['it' => 'Immagine', 'de' => 'Bild'],
    'Image caption / alt / wrapping' => ['it' => 'Didascalia / testo alternativo / disposizione', 'de' => 'Bildunterschrift / Alternativtext / Textumbruch'],
    'Image DPI (Ghostscript presets)' => ['it' => 'DPI immagini (profili Ghostscript)', 'de' => 'Bild-DPI (Ghostscript-Profile)'],
    'Image drawing operations' => ['it' => 'Operazioni di disegno immagini', 'de' => 'Bildzeichenoperationen'],
    'Image exceeds the 42 megapixel safety limit.' => ['it' => 'L\'immagine supera il limite di sicurezza di 42 megapixel.', 'de' => 'Das Bild überschreitet die Sicherheitsgrenze von 42 Megapixeln.'],
    'Image exceeds the file size limit.' => ['it' => 'L\'immagine supera il limite di dimensione del file.', 'de' => 'Das Bild überschreitet die Dateigrößengrenze.'],
    'Image file (PNG / JPEG)' => ['it' => 'File immagine (PNG / JPEG)', 'de' => 'Bilddatei (PNG / JPEG)'],
    'Image format' => ['it' => 'Formato immagine', 'de' => 'Bildformat'],
    'Image processing extension detected' => ['it' => 'Estensione per elaborare immagini rilevata', 'de' => 'Erweiterung zur Bildverarbeitung erkannt'],
    'Image properties' => ['it' => 'Proprietà immagine', 'de' => 'Bildeigenschaften'],
    'Image width (px)' => ['it' => 'Larghezza immagine (px)', 'de' => 'Bildbreite (px)'],
    'Images' => ['it' => 'Immagini', 'de' => 'Bilder'],
    'Images per page' => ['it' => 'Immagini per pagina', 'de' => 'Bilder pro Seite'],
    'Images to PDF' => ['it' => 'Da immagini a PDF', 'de' => 'Bilder in PDF'],
    'Import' => ['it' => 'Importa', 'de' => 'Importieren'],
    'Imposing booklet' => ['it' => 'Impaginazione opuscolo', 'de' => 'Broschüre wird montiert'],
    'Include page separators' => ['it' => 'Includi separatori di pagina', 'de' => 'Seitentrenner einfügen'],
    'Include source PDF files for a self-contained project' => ['it' => 'Includi i PDF originali per un progetto autonomo', 'de' => 'Original-PDFs für ein eigenständiges Projekt einbetten'],
    'Includes page order, editable overlays, document source, settings and metadata.' => ['it' => 'Include ordine delle pagine, sovrapposizioni modificabili, sorgente documento, impostazioni e metadati.', 'de' => 'Enthält Seitenreihenfolge, bearbeitbare Überlagerungen, Dokumentquelle, Einstellungen und Metadaten.'],
    'IndexedDB unavailable.' => ['it' => 'IndexedDB non disponibile.', 'de' => 'IndexedDB ist nicht verfügbar.'],
    'Individual document images must be smaller than 20 MB.' => ['it' => 'Ogni immagine del documento deve essere inferiore a 20 MB.', 'de' => 'Einzelne Dokumentbilder müssen kleiner als 20 MB sein.'],
    'Info' => ['it' => 'Informazioni', 'de' => 'Info'],
    'Initials' => ['it' => 'Iniziali', 'de' => 'Initialen'],
    'Inline' => ['it' => 'In linea', 'de' => 'Inline'],
    'Insert' => ['it' => 'Inserisci', 'de' => 'Einfügen'],
    'Insert a column section' => ['it' => 'Inserisci una sezione a colonne', 'de' => 'Spaltenabschnitt einfügen'],
    'Insert a local image' => ['it' => 'Inserisci un\'immagine locale', 'de' => 'Lokales Bild einfügen'],
    'Insert barcode' => ['it' => 'Inserisci codice a barre', 'de' => 'Strichcode einfügen'],
    'Insert blank page' => ['it' => 'Inserisci pagina vuota', 'de' => 'Leere Seite einfügen'],
    'Insert column section' => ['it' => 'Inserisci sezione a colonne', 'de' => 'Spaltenabschnitt einfügen'],
    'Insert files' => ['it' => 'Inserisci file', 'de' => 'Dateien einfügen'],
    'Insert page break' => ['it' => 'Inserisci interruzione di pagina', 'de' => 'Seitenumbruch einfügen'],
    'Insert QR code' => ['it' => 'Inserisci codice QR', 'de' => 'QR-Code einfügen'],
    'Insert signature' => ['it' => 'Inserisci firma', 'de' => 'Unterschrift einfügen'],
    'Insert visual signature' => ['it' => 'Inserisci firma visiva', 'de' => 'Sichtbare Unterschrift einfügen'],
    'Inserting pages' => ['it' => 'Inserimento pagine', 'de' => 'Seiten werden eingefügt'],
    'Inspecting pages' => ['it' => 'Ispezione pagine', 'de' => 'Seiten werden geprüft'],
    'Inspecting PDF' => ['it' => 'Ispezione PDF', 'de' => 'PDF wird geprüft'],
    'Install extensions for the PHP version serving this page (' => ['it' => 'Installa le estensioni per la versione PHP che gestisce questa pagina (', 'de' => 'Installieren Sie Erweiterungen für die PHP-Version dieser Seite ('],
    'Installation commands copied.' => ['it' => 'Comandi di installazione copiati.', 'de' => 'Installationsbefehle kopiert.'],
    'Installed version:' => ['it' => 'Versione installata:', 'de' => 'Installierte Version:'],
    'Interactive fields are flattened when pages are reorganized or extracted. Their visible appearances are retained.' => ['it' => 'I campi interattivi diventano permanenti quando riorganizzi o estrai le pagine. Il loro aspetto visibile viene mantenuto.', 'de' => 'Interaktive Felder werden beim Neuordnen oder Extrahieren von Seiten abgeflacht. Ihr sichtbares Erscheinungsbild bleibt erhalten.'],
    'Interface' => ['it' => 'Interfaccia', 'de' => 'Oberfläche'],
    'Interleaving and bookmarks require the browser engine.' => ['it' => 'L\'alternanza e i segnalibri richiedono il motore browser.', 'de' => 'Abwechselnde Seiten und Lesezeichen benötigen die Browser-Engine.'],
    'Interleaving requires exactly two PDF files.' => ['it' => 'Per alternare le pagine servono esattamente due file PDF.', 'de' => 'Für abwechselnde Seiten sind genau zwei PDF-Dateien erforderlich.'],
    'Invalid drawing path.' => ['it' => 'Tracciato di disegno non valido.', 'de' => 'Ungültiger Zeichenpfad.'],
    'Invalid polygon geometry.' => ['it' => 'Geometria del poligono non valida.', 'de' => 'Ungültige Polygongeometrie.'],
    'Invalid processing parameter.' => ['it' => 'Parametro di elaborazione non valido.', 'de' => 'Ungültiger Verarbeitungsparameter.'],
    'Invalid project page dimensions.' => ['it' => 'Dimensioni pagina del progetto non valide.', 'de' => 'Ungültige Projektseitengröße.'],
    'Invalid project source.' => ['it' => 'Originale del progetto non valido.', 'de' => 'Ungültige Projektquelle.'],
    'Invert' => ['it' => 'Inverti', 'de' => 'Umkehren'],
    'Invert selection' => ['it' => 'Inverti selezione', 'de' => 'Auswahl umkehren'],
    'is available' => ['it' => 'è disponibile', 'de' => 'ist verfügbar'],
    'is available. Open Check for updates to read what changed.' => ['it' => 'è disponibile. Apri Controlla aggiornamenti per leggere le novità.', 'de' => 'ist verfügbar. Öffnen Sie Nach Updates suchen, um die Änderungen zu lesen.'],
    'Italic' => ['it' => 'Corsivo', 'de' => 'Kursiv'],
    'JPEG / WebP quality (1–100)' => ['it' => 'Qualità JPEG / WebP (1–100)', 'de' => 'JPEG- / WebP-Qualität (1–100)'],
    'JPEG quality' => ['it' => 'Qualità JPEG', 'de' => 'JPEG-Qualität'],
    'JPEG quality (10–100; Ghostscript)' => ['it' => 'Qualità JPEG (10–100; Ghostscript)', 'de' => 'JPEG-Qualität (10–100; Ghostscript)'],
    'JSON encoding failed:' => ['it' => 'Codifica JSON non riuscita:', 'de' => 'JSON-Kodierung fehlgeschlagen:'],
    'Justify' => ['it' => 'Giustifica', 'de' => 'Blocksatz'],
    'Keep paragraph' => ['it' => 'Mantieni paragrafo', 'de' => 'Absatz zusammenhalten'],
    'Keep paragraph on one page' => ['it' => 'Mantieni il paragrafo su una pagina', 'de' => 'Absatz auf einer Seite zusammenhalten'],
    'Keywords' => ['it' => 'Parole chiave', 'de' => 'Schlüsselwörter'],
    'Keywords (comma separated)' => ['it' => 'Parole chiave (separate da virgole)', 'de' => 'Schlüsselwörter (durch Kommas getrennt)'],
    'Landscape' => ['it' => 'Orizzontale', 'de' => 'Querformat'],
    'Language codes, joined by + (eng, ita, tha, deu, fra…)' => ['it' => 'Codici lingua uniti da + (eng, ita, tha, deu, fra…)', 'de' => 'Sprachcodes mit + verbinden (eng, ita, tha, deu, fra…)'],
    'Last' => ['it' => 'Ultima', 'de' => 'Letzte'],
    'Last successful check: {0}.' => ['it' => 'Ultimo controllo riuscito: {0}.', 'de' => 'Letzte erfolgreiche Prüfung: {0}.'],
    'Layers' => ['it' => 'Livelli', 'de' => 'Ebenen'],
    'Layout' => ['it' => 'Layout', 'de' => 'Layout'],
    'Left' => ['it' => 'Sinistra', 'de' => 'Links'],
    'Left content' => ['it' => 'Contenuto a sinistra', 'de' => 'Inhalt links'],
    'Left position' => ['it' => 'Posizione a sinistra', 'de' => 'Linke Position'],
    'Left position (points from page left)' => ['it' => 'Posizione a sinistra (punti dal bordo sinistro)', 'de' => 'Linke Position (Punkte vom linken Seitenrand)'],
    'Left to right' => ['it' => 'Da sinistra a destra', 'de' => 'Links nach rechts'],
    'Legal (8.5 × 14 in)' => ['it' => 'Legal (8,5 × 14 pollici)', 'de' => 'Legal (8,5 × 14 Zoll)'],
    'Letter (8.5 × 11 in)' => ['it' => 'Letter (8,5 × 11 pollici)', 'de' => 'Letter (8,5 × 11 Zoll)'],
    'Letter spacing (1/1000 em)' => ['it' => 'Spaziatura caratteri (1/1000 em)', 'de' => 'Zeichenabstand (1/1000 em)'],
    'LibreOffice is installed; PHP ZIP and DOM extensions are also required for safe Office validation.' => ['it' => 'LibreOffice è installato; per convalidare i file Office in sicurezza servono anche le estensioni PHP ZIP e DOM.', 'de' => 'LibreOffice ist installiert; für die sichere Prüfung von Office-Dateien sind außerdem die PHP-Erweiterungen ZIP und DOM erforderlich.'],
    'Light / 300 DPI' => ['it' => 'Leggera / 300 DPI', 'de' => 'Leicht / 300 DPI'],
    'Line' => ['it' => 'Linea', 'de' => 'Linie'],
    'Line spacing (multiplier)' => ['it' => 'Interlinea (moltiplicatore)', 'de' => 'Zeilenabstand (Multiplikator)'],
    'Linearizing PDF' => ['it' => 'Linearizzazione PDF', 'de' => 'PDF wird linearisiert'],
    'Link' => ['it' => 'Collegamento', 'de' => 'Link'],
    'Link region' => ['it' => 'Area collegamento', 'de' => 'Linkbereich'],
    'Links must use http or https.' => ['it' => 'I collegamenti devono usare http o https.', 'de' => 'Links müssen http oder https verwenden.'],
    'Live preview of current page' => ['it' => 'Anteprima della pagina corrente', 'de' => 'Live-Vorschau der aktuellen Seite'],
    'Loading PDF tools…' => ['it' => 'Caricamento strumenti PDF…', 'de' => 'PDF-Werkzeuge werden geladen…'],
    'Local autosave cleared.' => ['it' => 'Salvataggio automatico locale cancellato.', 'de' => 'Lokale automatische Sicherung gelöscht.'],
    'Local browser / PDF.js' => ['it' => 'Browser locale / PDF.js', 'de' => 'Lokaler Browser / PDF.js'],
    'Local browser / Tesseract.js' => ['it' => 'Browser locale / Tesseract.js', 'de' => 'Lokaler Browser / Tesseract.js'],
    'Local image' => ['it' => 'Immagine locale', 'de' => 'Lokales Bild'],
    'Local processing by default' => ['it' => 'Elaborazione locale predefinita', 'de' => 'Standardmäßig lokale Verarbeitung'],
    'Locally in your browser' => ['it' => 'Localmente nel browser', 'de' => 'Lokal im Browser'],
    'Locally in your browser unless you explicitly select a server engine. Interactive forms are flattened before browser merging.' => ['it' => 'Localmente nel browser, salvo scelta esplicita di un motore server. Prima dell\'unione nel browser i moduli interattivi diventano permanenti.', 'de' => 'Lokal im Browser, sofern Sie nicht ausdrücklich eine Server-Engine wählen. Interaktive Formulare werden vor der Zusammenführung im Browser abgeflacht.'],
    'Lock' => ['it' => 'Blocca', 'de' => 'Sperren'],
    'Lossless / qpdf structural optimization' => ['it' => 'Senza perdita / ottimizzazione struttura qpdf', 'de' => 'Verlustfrei / qpdf-Strukturoptimierung'],
    'Lossless optimization compresses streams and restructures objects; it does not recompress images. Ghostscript image presets can reduce resolution and affect fonts, forms, transparency and annotations. Compression may increase the size of an already optimized file.' => ['it' => 'L\'ottimizzazione senza perdita comprime i flussi e riorganizza gli oggetti; non ricomprime le immagini. I profili Ghostscript possono ridurre la risoluzione e influire su caratteri, moduli, trasparenze e annotazioni. Un file già ottimizzato può diventare più grande.', 'de' => 'Verlustfreie Optimierung komprimiert Datenströme und ordnet Objekte neu; Bilder werden nicht neu komprimiert. Ghostscript-Profile können die Auflösung reduzieren und Schriften, Formulare, Transparenz und Anmerkungen beeinflussen. Eine bereits optimierte Datei kann dadurch größer werden.'],
    'Lossy image compression' => ['it' => 'Compressione immagini con perdita', 'de' => 'Verlustbehaftete Bildkomprimierung'],
    'Low-resolution printing' => ['it' => 'Stampa a bassa risoluzione', 'de' => 'Drucken mit niedriger Auflösung'],
    'Make every PDF work for you.' => ['it' => 'Tutto ciò che serve per i tuoi PDF.', 'de' => 'Alles für Ihre PDFs.'],
    'Margin' => ['it' => 'Margine', 'de' => 'Rand'],
    'margin (mm)' => ['it' => 'margine (mm)', 'de' => 'Rand (mm)'],
    'Margin (PDF points)' => ['it' => 'Margine (punti PDF)', 'de' => 'Rand (PDF-Punkte)'],
    'Margin (points)' => ['it' => 'Margine (punti)', 'de' => 'Rand (Punkte)'],
    'Margins / gap (mm)' => ['it' => 'Margini / spazio (mm)', 'de' => 'Ränder / Abstand (mm)'],
    'Margins and spacing leave no usable cell area.' => ['it' => 'Margini e spaziatura non lasciano un\'area utile nelle celle.', 'de' => 'Ränder und Abstände lassen keine nutzbare Zellenfläche übrig.'],
    'Margins leave no space for the images.' => ['it' => 'I margini non lasciano spazio per le immagini.', 'de' => 'Die Ränder lassen keinen Platz für die Bilder.'],
    'Margins must leave at least 20 mm of usable page width and height.' => ['it' => 'I margini devono lasciare almeno 20 mm di larghezza e altezza utili.', 'de' => 'Die Ränder müssen mindestens 20 mm nutzbare Breite und Höhe übrig lassen.'],
    'Match found' => ['it' => 'Corrispondenza trovata', 'de' => 'Treffer gefunden'],
    'Maximum / 72 DPI' => ['it' => 'Massima / 72 DPI', 'de' => 'Maximal / 72 DPI'],
    'MB limit.' => ['it' => 'limite MB.', 'de' => 'MB-Grenze.'],
    'Measuring page groups' => ['it' => 'Misurazione gruppi di pagine', 'de' => 'Seitengruppen werden gemessen'],
    'Medium / 150 DPI' => ['it' => 'Media / 150 DPI', 'de' => 'Mittel / 150 DPI'],
    'Merge' => ['it' => 'Unisci', 'de' => 'Zusammenführen'],
    'Merge documents' => ['it' => 'Unisci documenti', 'de' => 'Dokumente zusammenführen'],
    'Merge files' => ['it' => 'Unisci file', 'de' => 'Dateien zusammenführen'],
    'Merge order' => ['it' => 'Ordine di unione', 'de' => 'Reihenfolge beim Zusammenführen'],
    'Merged PDF exceeds the configured page limit.' => ['it' => 'Il PDF unito supera il limite di pagine configurato.', 'de' => 'Das zusammengeführte PDF überschreitet die eingerichtete Seitengrenze.'],
    'Merging documents' => ['it' => 'Unione documenti', 'de' => 'Dokumente werden zusammengeführt'],
    'Metadata' => ['it' => 'Metadati', 'de' => 'Metadaten'],
    'Metadata cleaning removes document properties and XMP. It does not remove visible page text, attachments, annotations or hidden cropped content.' => ['it' => 'La pulizia dei metadati elimina le proprietà del documento e gli XMP. Non elimina testo visibile, allegati, annotazioni o contenuto nascosto dal ritaglio.', 'de' => 'Die Metadatenbereinigung entfernt Dokumenteigenschaften und XMP. Sie entfernt weder sichtbaren Seitentext noch Anhänge, Anmerkungen oder durch Zuschnitt verdeckten Inhalt.'],
    'Modification date (UTC)' => ['it' => 'Data di modifica (UTC)', 'de' => 'Änderungsdatum (UTC)'],
    'mPDF · strong paged documents' => ['it' => 'mPDF · documenti impaginati', 'de' => 'mPDF · gute Seitenformatierung'],
    'Multiline text' => ['it' => 'Testo su più righe', 'de' => 'Mehrzeiliger Text'],
    'must be between' => ['it' => 'deve essere compreso tra', 'de' => 'muss liegen zwischen'],
    'N pages / maximum MiB (depending on method)' => ['it' => 'N pagine / massimo MiB (in base al metodo)', 'de' => 'N Seiten / maximale MiB (je nach Methode)'],
    'N-up / contact sheet' => ['it' => 'N-up / foglio di contatto', 'de' => 'N-up / Kontaktbogen'],
    'Named anchor' => ['it' => 'Ancora con nome', 'de' => 'Benannte Textmarke'],
    'New' => ['it' => 'Nuovo', 'de' => 'Neu'],
    'New document' => ['it' => 'Nuovo documento', 'de' => 'Neues Dokument'],
    'Next' => ['it' => 'Successiva', 'de' => 'Weiter'],
    'Next comparison page' => ['it' => 'Pagina successiva di confronto', 'de' => 'Nächste Vergleichsseite'],
    'No AcroForm fields were found.' => ['it' => 'Nessun campo AcroForm trovato.', 'de' => 'Keine AcroForm-Felder gefunden.'],
    'No AcroForm fields were found. Use Create form field to add one.' => ['it' => 'Nessun campo AcroForm trovato. Usa Crea campo modulo per aggiungerne uno.', 'de' => 'Keine AcroForm-Felder gefunden. Verwenden Sie Formularfeld erstellen, um eines hinzuzufügen.'],
    'No corresponding page in PDF A.' => ['it' => 'Nessuna pagina corrispondente nel PDF A.', 'de' => 'Keine entsprechende Seite in PDF A.'],
    'No corresponding page in PDF B.' => ['it' => 'Nessuna pagina corrispondente nel PDF B.', 'de' => 'Keine entsprechende Seite in PDF B.'],
    'No diagnostic output.' => ['it' => 'Nessun risultato diagnostico.', 'de' => 'Keine Diagnoseausgabe.'],
    'No document open' => ['it' => 'Nessun documento aperto', 'de' => 'Kein Dokument geöffnet'],
    'No embedded images were found in this PDF.' => ['it' => 'Nessuna immagine incorporata trovata in questo PDF.', 'de' => 'In diesem PDF wurden keine eingebetteten Bilder gefunden.'],
    'No embedded text' => ['it' => 'Nessun testo incorporato', 'de' => 'Kein eingebetteter Text'],
    'No embedded text on these pages. Run OCR before comparing text.' => ['it' => 'Nessun testo incorporato in queste pagine. Esegui l\'OCR prima di confrontare il testo.', 'de' => 'Auf diesen Seiten gibt es keinen eingebetteten Text. Führen Sie vor dem Textvergleich OCR aus.'],
    'No encryption detected in loaded input' => ['it' => 'Nessuna cifratura rilevata nel file caricato', 'de' => 'Keine Verschlüsselung in der geladenen Datei erkannt'],
    'No installation guide is available for this dependency.' => ['it' => 'Nessuna guida di installazione disponibile per questa dipendenza.', 'de' => 'Für diese Abhängigkeit ist keine Installationsanleitung verfügbar.'],
    'No local autosave is available.' => ['it' => 'Nessun salvataggio automatico locale disponibile.', 'de' => 'Keine lokale automatische Sicherung verfügbar.'],
    'No matches' => ['it' => 'Nessuna corrispondenza', 'de' => 'Keine Treffer'],
    'No output pages were selected.' => ['it' => 'Nessuna pagina selezionata per il risultato.', 'de' => 'Keine Ausgabeseiten ausgewählt.'],
    'No PDF open' => ['it' => 'Nessun PDF aperto', 'de' => 'Kein PDF geöffnet'],
    'No PDF or supported image files were selected.' => ['it' => 'Nessun PDF o file immagine supportato selezionato.', 'de' => 'Keine PDFs oder unterstützten Bilddateien ausgewählt.'],
    'No top-level bookmarks were found.' => ['it' => 'Nessun segnalibro di primo livello trovato.', 'de' => 'Keine Lesezeichen der obersten Ebene gefunden.'],
    'No usable sandbox' => ['it' => 'Nessuna sandbox utilizzabile', 'de' => 'Keine nutzbare Sandbox'],
    'None detected' => ['it' => 'Nessuno rilevato', 'de' => 'Keine erkannt'],
    'Note text' => ['it' => 'Testo nota', 'de' => 'Notiztext'],
    'Number format' => ['it' => 'Formato numeri', 'de' => 'Zahlenformat'],
    'Number of sides' => ['it' => 'Numero di lati', 'de' => 'Anzahl der Seiten'],
    'Number width' => ['it' => 'Larghezza numero', 'de' => 'Zahlenbreite'],
    'Number width (zero padding)' => ['it' => 'Larghezza numero (zeri iniziali)', 'de' => 'Zahlenbreite (führende Nullen)'],
    'Numbering pages' => ['it' => 'Numerazione pagine', 'de' => 'Seiten werden nummeriert'],
    'Object properties' => ['it' => 'Proprietà oggetto', 'de' => 'Objekteigenschaften'],
    'OCR completed' => ['it' => 'OCR completato', 'de' => 'OCR abgeschlossen'],
    'Odd pages / even pages' => ['it' => 'Pagine dispari / pagine pari', 'de' => 'Ungerade Seiten / gerade Seiten'],
    'of {3}' => ['it' => 'di {3}', 'de' => 'von {3}'],
    'Office archive security inspection' => ['it' => 'Controllo sicurezza archivio Office', 'de' => 'Office-Archiv wird auf Sicherheit geprüft'],
    'Office conversion accepts DOCX, ODT, XLSX and PPTX files.' => ['it' => 'La conversione Office accetta file DOCX, ODT, XLSX e PPTX.', 'de' => 'Die Office-Konvertierung akzeptiert DOCX-, ODT-, XLSX- und PPTX-Dateien.'],
    'Office conversion also needs the PHP ZIP and DOM extensions.' => ['it' => 'La conversione Office richiede anche le estensioni PHP ZIP e DOM.', 'de' => 'Die Office-Konvertierung benötigt außerdem die PHP-Erweiterungen ZIP und DOM.'],
    'Office to PDF' => ['it' => 'Da Office a PDF', 'de' => 'Office in PDF'],
    'On current Ubuntu releases this installs Chromium through Snap. The PHP service must be able to launch it and access its private temporary job directory; Snap confinement can require additional server configuration.' => ['it' => 'Nelle versioni Ubuntu attuali viene installato Chromium tramite Snap. Il servizio PHP deve poterlo avviare e accedere alla cartella temporanea privata del lavoro; l\'isolamento Snap può richiedere configurazioni aggiuntive del server.', 'de' => 'Aktuelle Ubuntu-Versionen installieren Chromium über Snap. Der PHP-Dienst muss es starten und auf das private temporäre Arbeitsverzeichnis zugreifen können; die Snap-Isolation kann zusätzliche Serverkonfiguration erfordern.'],
    'On the server' => ['it' => 'Sul server', 'de' => 'Auf dem Server'],
    'On the server · document uploaded only when exporting' => ['it' => 'Sul server · il documento viene caricato solo all\'esportazione', 'de' => 'Auf dem Server · Dokument wird erst beim Export hochgeladen'],
    'Opacity' => ['it' => 'Opacità', 'de' => 'Deckkraft'],
    'Opacity (0–100)' => ['it' => 'Opacità (0–100)', 'de' => 'Deckkraft (0–100)'],
    'Open' => ['it' => 'Apri', 'de' => 'Öffnen'],
    'Open a PDF or create pages first.' => ['it' => 'Prima apri un PDF oppure crea delle pagine.', 'de' => 'Öffnen Sie zuerst ein PDF oder erstellen Sie Seiten.'],
    'Open a PDF to use' => ['it' => 'Apri un PDF per usare', 'de' => 'Öffnen Sie ein PDF für'],
    'Open PDFs or images' => ['it' => 'Apri PDF o immagini', 'de' => 'PDFs oder Bilder öffnen'],
    'Open project' => ['it' => 'Apri progetto', 'de' => 'Projekt öffnen'],
    'Open this tool' => ['it' => 'Apri questo strumento', 'de' => 'Dieses Werkzeug öffnen'],
    'Open What\'s new at any time to review the release history.' => ['it' => 'Apri Novità in qualsiasi momento per rivedere la cronologia delle versioni.', 'de' => 'Öffnen Sie jederzeit Neuigkeiten, um die Versionsgeschichte zu lesen.'],
    'Opening comparison PDFs' => ['it' => 'Apertura PDF per il confronto', 'de' => 'Vergleichs-PDFs werden geöffnet'],
    'Opening documents' => ['it' => 'Apertura documenti', 'de' => 'Dokumente werden geöffnet'],
    'Opening project' => ['it' => 'Apertura progetto', 'de' => 'Projekt wird geöffnet'],
    'Operation cancelled.' => ['it' => 'Operazione annullata.', 'de' => 'Vorgang abgebrochen.'],
    'operation exceeded the server time limit. Try fewer pages or a smaller file.' => ['it' => 'ha superato il tempo limite del server. Prova meno pagine o un file più piccolo.', 'de' => 'hat das Server-Zeitlimit überschritten. Versuchen Sie weniger Seiten oder eine kleinere Datei.'],
    'operation failed. The document may be malformed, unsupported, or password protected.' => ['it' => 'non riuscita. Il documento potrebbe essere danneggiato, non supportato o protetto da password.', 'de' => 'fehlgeschlagen. Das Dokument ist möglicherweise beschädigt, wird nicht unterstützt oder ist passwortgeschützt.'],
    'Operation progress' => ['it' => 'Avanzamento operazione', 'de' => 'Vorgangsfortschritt'],
    'Optimize' => ['it' => 'Ottimizza', 'de' => 'Optimieren'],
    'Optional damaged PDF (leave empty to use the open document)' => ['it' => 'PDF danneggiato facoltativo (vuoto per usare il documento aperto)', 'de' => 'Optionales beschädigtes PDF (leer lassen für das geöffnete Dokument)'],
    'Optional PDF (leave empty to validate the open document)' => ['it' => 'PDF facoltativo (vuoto per convalidare il documento aperto)', 'de' => 'Optionales PDF (leer lassen für die Prüfung des geöffneten Dokuments)'],
    'Options (one per line; radio/dropdown only)' => ['it' => 'Opzioni (una per riga; solo radio/menu a discesa)', 'de' => 'Optionen (eine pro Zeile; nur Optionsfelder/Auswahlliste)'],
    'Options may be separated with commas. Radio options are stacked with 28-point spacing. Signature images are available under Fill & Sign; cryptographic signature fields require a signing engine.' => ['it' => 'Le opzioni possono essere separate da virgole. I pulsanti radio sono disposti con 28 punti di distanza. Le immagini di firma sono disponibili in Compila e firma; i campi di firma crittografica richiedono un motore di firma.', 'de' => 'Optionen können durch Kommas getrennt werden. Optionsfelder werden mit 28 Punkten Abstand angeordnet. Unterschriftsbilder finden Sie unter Ausfüllen und unterschreiben; kryptografische Signaturfelder benötigen eine Signatur-Engine.'],
    'or click to browse · Files stay local until you choose a server operation' => ['it' => 'oppure fai clic per scegliere · I file restano locali finché non scegli un\'operazione server', 'de' => 'oder zum Auswählen klicken · Dateien bleiben lokal, bis Sie eine Serveroperation wählen'],
    'Or type a signature' => ['it' => 'Oppure digita una firma', 'de' => 'Oder Unterschrift tippen'],
    'Or upload an image' => ['it' => 'Oppure carica un\'immagine', 'de' => 'Oder Bild hochladen'],
    'Organize' => ['it' => 'Organizza', 'de' => 'Organisieren'],
    'Organize pages' => ['it' => 'Organizza pagine', 'de' => 'Seiten organisieren'],
    'Orientation' => ['it' => 'Orientamento', 'de' => 'Ausrichtung'],
    'Original encryption' => ['it' => 'Cifratura originale', 'de' => 'Ursprüngliche Verschlüsselung'],
    'Original PDF files are required to reopen this project.' => ['it' => 'Per riaprire questo progetto servono i PDF originali.', 'de' => 'Zum erneuten Öffnen dieses Projekts werden die Original-PDFs benötigt.'],
    'Outer margin (points)' => ['it' => 'Margine esterno (punti)', 'de' => 'Außenrand (Punkte)'],
    'Outlines' => ['it' => 'Segnalibri', 'de' => 'Lesezeichen'],
    'Output' => ['it' => 'Risultato', 'de' => 'Ausgabe'],
    'Owner password (permissions / editing)' => ['it' => 'Password proprietario (permessi / modifica)', 'de' => 'Eigentümerpasswort (Berechtigungen / Bearbeitung)'],
    'Padding' => ['it' => 'Spaziatura interna', 'de' => 'Innenabstand'],
    'Page' => ['it' => 'Pagina', 'de' => 'Seite'],
    'Page {0} of {1} · {2}%' => ['it' => 'Pagina {0} di {1} · {2}%', 'de' => 'Seite {0} von {1} · {2}%'],
    'Page {1}' => ['it' => 'Pagina {1}', 'de' => 'Seite {1}'],
    'Page {4}' => ['it' => 'Pagina {4}', 'de' => 'Seite {4}'],
    'Page {n} of {total}' => ['it' => 'Pagina {n} di {total}', 'de' => 'Seite {n} von {total}'],
    'Page background' => ['it' => 'Sfondo pagina', 'de' => 'Seitenhintergrund'],
    'Page border color' => ['it' => 'Colore bordo pagina', 'de' => 'Seitenrahmenfarbe'],
    'Page border width (pt, 0 = none)' => ['it' => 'Spessore bordo pagina (pt, 0 = nessuno)', 'de' => 'Seitenrahmenbreite (pt, 0 = keiner)'],
    'Page content remains vector-based. Form fields are flattened. PDF comment, highlight and link annotations are omitted on resized pages; editable overlays are already included in page content.' => ['it' => 'Il contenuto resta vettoriale. I campi modulo diventano permanenti. Commenti PDF, evidenziazioni e collegamenti vengono omessi nelle pagine ridimensionate; le sovrapposizioni modificabili sono già incluse nel contenuto.', 'de' => 'Der Seiteninhalt bleibt vektorbasiert. Formularfelder werden abgeflacht. PDF-Kommentare, Hervorhebungen und Verknüpfungen werden auf Seiten mit geänderter Größe weggelassen; bearbeitbare Überlagerungen sind bereits Teil des Seiteninhalts.'],
    'Page count exceeds the configured limit.' => ['it' => 'Il numero di pagine supera il limite configurato.', 'de' => 'Die Seitenzahl überschreitet die eingerichtete Grenze.'],
    'Page dimensions must be between 12.7 and 1764 mm.' => ['it' => 'Le dimensioni pagina devono essere comprese tra 12,7 e 1764 mm.', 'de' => 'Seitengrößen müssen zwischen 12,7 und 1764 mm liegen.'],
    'Page height' => ['it' => 'Altezza pagina', 'de' => 'Seitenhöhe'],
    'Page height (mm)' => ['it' => 'Altezza pagina (mm)', 'de' => 'Seitenhöhe (mm)'],
    'page limit.' => ['it' => 'limite di pagine.', 'de' => 'Seitengrenze.'],
    'Page limits' => ['it' => 'Limiti di pagine', 'de' => 'Seitengrenzen'],
    'Page margins must be between 0 and 100 mm.' => ['it' => 'I margini pagina devono essere compresi tra 0 e 100 mm.', 'de' => 'Seitenränder müssen zwischen 0 und 100 mm liegen.'],
    'Page number' => ['it' => 'Numero pagina', 'de' => 'Seitenzahl'],
    'Page order' => ['it' => 'Ordine pagine', 'de' => 'Seitenreihenfolge'],
    'Page ordering' => ['it' => 'Ordine delle pagine', 'de' => 'Seitenanordnung'],
    'Page range for {3}' => ['it' => 'Intervallo pagine per {3}', 'de' => 'Seitenbereich für {3}'],
    'Page range is outside this document (1–' => ['it' => 'L\'intervallo è fuori dal documento (1–', 'de' => 'Der Seitenbereich liegt außerhalb des Dokuments (1–'],
    'Page ranges' => ['it' => 'Intervalli di pagine', 'de' => 'Seitenbereiche'],
    'Page rasterization for OCR' => ['it' => 'Conversione pagine in immagini per OCR', 'de' => 'Seitenrasterung für OCR'],
    'Page size' => ['it' => 'Dimensioni pagina', 'de' => 'Seitengröße'],
    'Page size, margins, columns' => ['it' => 'Dimensioni pagina, margini, colonne', 'de' => 'Seitengröße, Ränder, Spalten'],
    'Page width' => ['it' => 'Larghezza pagina', 'de' => 'Seitenbreite'],
    'Page width (mm)' => ['it' => 'Larghezza pagina (mm)', 'de' => 'Seitenbreite (mm)'],
    'Pages' => ['it' => 'Pagine', 'de' => 'Seiten'],
    'Pages / PDF version' => ['it' => 'Pagine / versione PDF', 'de' => 'Seiten / PDF-Version'],
    'pages; the server limit is' => ['it' => 'pagine; il limite del server è', 'de' => 'Seiten; die Servergrenze ist'],
    'Pages: all, odd, even, or ranges such as 1-4,7' => ['it' => 'Pagine: tutte, dispari, pari oppure intervalli come 1-4,7', 'de' => 'Seiten: alle, ungerade, gerade oder Bereiche wie 1-4,7'],
    'Paper preview: {6} × {7} mm. Use Print for final pagination; the browser\'s print dialog can save PDF. Enable background graphics, use 100% scale and turn off browser headers/footers.' => ['it' => 'Anteprima foglio: {6} × {7} mm. Usa Stampa per la paginazione finale; nella finestra del browser puoi salvare in PDF. Abilita la grafica di sfondo, usa scala 100% e disattiva intestazioni e piè di pagina del browser.', 'de' => 'Papiervorschau: {6} × {7} mm. Verwenden Sie Drucken für die endgültige Seiteneinteilung; im Browserdialog können Sie als PDF speichern. Aktivieren Sie Hintergrundgrafiken, verwenden Sie 100 % Skalierung und deaktivieren Sie Browser-Kopf- und Fußzeilen.'],
    'Paper size' => ['it' => 'Formato carta', 'de' => 'Papierformat'],
    'Paragraph' => ['it' => 'Paragrafo', 'de' => 'Absatz'],
    'Paragraph format' => ['it' => 'Formato paragrafo', 'de' => 'Absatzformat'],
    'Paragraph spacing and direction' => ['it' => 'Spaziatura e direzione del paragrafo', 'de' => 'Absatzabstand und Schreibrichtung'],
    'Password to open (user password)' => ['it' => 'Password di apertura (utente)', 'de' => 'Öffnungspasswort (Benutzerpasswort)'],
    'Passwords are sent only for this operation. If the owner password is empty, the server generates one. PDF permission flags depend on the reader and do not provide DRM-grade protection.' => ['it' => 'Le password vengono inviate solo per questa operazione. Se la password proprietario è vuota, il server ne genera una. I permessi PDF dipendono dal lettore e non offrono protezione di livello DRM.', 'de' => 'Passwörter werden nur für diesen Vorgang übertragen. Ist das Eigentümerpasswort leer, erzeugt der Server eines. PDF-Berechtigungen hängen vom Leseprogramm ab und bieten keinen DRM-Schutz.'],
    'Passwords must contain at most 256 bytes and no line breaks.' => ['it' => 'Le password possono contenere al massimo 256 byte e nessuna interruzione di riga.', 'de' => 'Passwörter dürfen höchstens 256 Bytes und keine Zeilenumbrüche enthalten.'],
    'PDF A embedded text' => ['it' => 'Testo incorporato PDF A', 'de' => 'Eingebetteter Text in PDF A'],
    'PDF B embedded text' => ['it' => 'Testo incorporato PDF B', 'de' => 'Eingebetteter Text in PDF B'],
    'PDF diagnostics' => ['it' => 'Diagnostica PDF', 'de' => 'PDF-Diagnose'],
    'PDF editing' => ['it' => 'Modifica PDF', 'de' => 'PDF-Bearbeitung'],
    'PDF engine' => ['it' => 'Motore PDF', 'de' => 'PDF-Engine'],
    'PDF export accepts embedded PNG, JPEG, GIF and WebP images only. Remote and SVG resources are not fetched.' => ['it' => 'L\'esportazione PDF accetta solo immagini PNG, JPEG, GIF e WebP incorporate. Le risorse remote e SVG non vengono scaricate.', 'de' => 'Der PDF-Export akzeptiert nur eingebettete PNG-, JPEG-, GIF- und WebP-Bilder. Externe und SVG-Ressourcen werden nicht geladen.'],
    'PDF export permits inline document styles and embedded images only. Remove external fonts, URLs or executable CSS.' => ['it' => 'L\'esportazione PDF consente solo stili documento in linea e immagini incorporate. Rimuovi caratteri esterni, URL o CSS eseguibile.', 'de' => 'Der PDF-Export erlaubt nur eingebettete Dokumentstile und Bilder. Entfernen Sie externe Schriften, URLs oder ausführbares CSS.'],
    'PDF generation library detected' => ['it' => 'Libreria di generazione PDF rilevata', 'de' => 'PDF-Erstellungsbibliothek erkannt'],
    'PDF metadata' => ['it' => 'Metadati PDF', 'de' => 'PDF-Metadaten'],
    'PDF overlays and visual signatures' => ['it' => 'Sovrapposizioni PDF e firme visive', 'de' => 'PDF-Überlagerungen und sichtbare Unterschriften'],
    'PDF page' => ['it' => 'Pagina PDF', 'de' => 'PDF-Seite'],
    'PDF rendering could not load:' => ['it' => 'Impossibile caricare il rendering PDF:', 'de' => 'PDF-Rendering konnte nicht geladen werden:'],
    'PDF Studio home' => ['it' => 'Home PDF Studio', 'de' => 'PDF Studio Start'],
    'PDF Studio JSON · document source, settings and metadata' => ['it' => 'PDF Studio JSON · sorgente documento, impostazioni e metadati', 'de' => 'PDF Studio JSON · Dokumentquelle, Einstellungen und Metadaten'],
    'PDF Studio requires PHP 7.2 or newer.' => ['it' => 'PDF Studio richiede PHP 7.2 o successivo.', 'de' => 'PDF Studio benötigt PHP 7.2 oder neuer.'],
    'PDF Studio updates' => ['it' => 'Aggiornamenti PDF Studio', 'de' => 'PDF Studio Updates'],
    'PDF to images' => ['it' => 'Da PDF a immagini', 'de' => 'PDF in Bilder'],
    'PDF validation report' => ['it' => 'Rapporto di convalida PDF', 'de' => 'PDF-Prüfbericht'],
    'PDF viewer' => ['it' => 'Visualizzatore PDF', 'de' => 'PDF-Anzeige'],
    'PDF.js and pdf-lib are required. Check the library loading errors.' => ['it' => 'Sono necessari PDF.js e pdf-lib. Controlla gli errori di caricamento delle librerie.', 'de' => 'PDF.js und pdf-lib werden benötigt. Prüfen Sie die Fehler beim Laden der Bibliotheken.'],
    'PDF.js successfully parsed all pages. Run server Validate PDF for structural diagnostics when qpdf is available.' => ['it' => 'PDF.js ha letto tutte le pagine. Se qpdf è disponibile, esegui Convalida PDF sul server per la diagnostica strutturale.', 'de' => 'PDF.js hat alle Seiten erfolgreich eingelesen. Führen Sie bei verfügbarem qpdf PDF prüfen auf dem Server für eine Strukturdiagnose aus.'],
    'PHP Imagick (optional)' => ['it' => 'PHP Imagick (facoltativo)', 'de' => 'PHP Imagick (optional)'],
    'PHP proc_open is disabled. Ask the server administrator to enable it for this application; installing a package alone will not enable external tools.' => ['it' => 'PHP proc_open è disabilitato. Chiedi all\'amministratore di abilitarlo per questa applicazione; installare un pacchetto non abilita da solo gli strumenti esterni.', 'de' => 'PHP proc_open ist deaktiviert. Bitten Sie den Serveradministrator, es für diese Anwendung zu aktivieren; die Paketinstallation allein aktiviert keine externen Werkzeuge.'],
    'Place the text cursor in a table cell first. Insert tables with the table button in the editor toolbar.' => ['it' => 'Prima posiziona il cursore in una cella. Inserisci le tabelle con il pulsante tabella nella barra dell\'editor.', 'de' => 'Setzen Sie den Textcursor zuerst in eine Tabellenzelle. Fügen Sie Tabellen über die Tabellenschaltfläche in der Editor-Symbolleiste ein.'],
    'Placement' => ['it' => 'Posizionamento', 'de' => 'Platzierung'],
    'Plain text' => ['it' => 'Testo semplice', 'de' => 'Nur Text'],
    'Polygon' => ['it' => 'Poligono', 'de' => 'Polygon'],
    'Poppler extracts original embedded image streams without rendering entire pages. Images are downloaded as a ZIP; formats can include JPEG, PNG, TIFF, JBIG2 or JPEG 2000 depending on the source. Masks may be separate files.' => ['it' => 'Poppler estrae i flussi originali delle immagini incorporate senza renderizzare tutte le pagine. Le immagini vengono scaricate in ZIP; i formati possono includere JPEG, PNG, TIFF, JBIG2 o JPEG 2000 in base all\'originale. Le maschere possono essere file separati.', 'de' => 'Poppler extrahiert die ursprünglichen eingebetteten Bilddaten, ohne ganze Seiten zu rendern. Bilder werden als ZIP heruntergeladen; je nach Quelle sind JPEG, PNG, TIFF, JBIG2 oder JPEG 2000 enthalten. Masken können separate Dateien sein.'],
    'Poppler: embedded images' => ['it' => 'Poppler: immagini incorporate', 'de' => 'Poppler: eingebettete Bilder'],
    'Poppler: page rendering' => ['it' => 'Poppler: rendering pagine', 'de' => 'Poppler: Seitenrendering'],
    'Poppler: PDF information' => ['it' => 'Poppler: informazioni PDF', 'de' => 'Poppler: PDF-Informationen'],
    'Poppler: text extraction' => ['it' => 'Poppler: estrazione testo', 'de' => 'Poppler: Textextraktion'],
    'Portrait' => ['it' => 'Verticale', 'de' => 'Hochformat'],
    'Position' => ['it' => 'Posizione', 'de' => 'Position'],
    'Prefix' => ['it' => 'Prefisso', 'de' => 'Präfix'],
    'Preserve approximate line layout' => ['it' => 'Mantieni la disposizione approssimativa delle righe', 'de' => 'Ungefähre Zeilenanordnung erhalten'],
    'Prev' => ['it' => 'Precedente', 'de' => 'Zurück'],
    'Preview is still loading.' => ['it' => 'L\'anteprima è ancora in caricamento.', 'de' => 'Die Vorschau wird noch geladen.'],
    'Previous comparison page' => ['it' => 'Pagina precedente di confronto', 'de' => 'Vorherige Vergleichsseite'],
    'Print / Save PDF' => ['it' => 'Stampa / salva PDF', 'de' => 'Drucken / PDF speichern'],
    'Print preview' => ['it' => 'Anteprima stampa', 'de' => 'Druckvorschau'],
    'Print preview did not finish loading.' => ['it' => 'L\'anteprima di stampa non ha completato il caricamento.', 'de' => 'Die Druckvorschau wurde nicht vollständig geladen.'],
    'Printing' => ['it' => 'Stampa', 'de' => 'Drucken'],
    'Processing' => ['it' => 'Elaborazione', 'de' => 'Verarbeitung'],
    'Processing engine' => ['it' => 'Motore di elaborazione', 'de' => 'Verarbeitungs-Engine'],
    'Producer' => ['it' => 'Produttore', 'de' => 'Produzent'],
    'Project' => ['it' => 'Progetto', 'de' => 'Projekt'],
    'Project contains an unsupported overlay object.' => ['it' => 'Il progetto contiene un oggetto sovrapposto non supportato.', 'de' => 'Das Projekt enthält ein nicht unterstütztes Überlagerungsobjekt.'],
    'Project contains invalid object geometry.' => ['it' => 'Il progetto contiene una geometria oggetto non valida.', 'de' => 'Das Projekt enthält ungültige Objektgeometrie.'],
    'Project contains too many overlay objects.' => ['it' => 'Il progetto contiene troppi oggetti sovrapposti.', 'de' => 'Das Projekt enthält zu viele Überlagerungsobjekte.'],
    'Project drawing is too complex.' => ['it' => 'Il disegno del progetto è troppo complesso.', 'de' => 'Die Projektzeichnung ist zu komplex.'],
    'Project exceeds the configured limits.' => ['it' => 'Il progetto supera i limiti configurati.', 'de' => 'Das Projekt überschreitet die eingerichteten Grenzen.'],
    'Project image is too large.' => ['it' => 'L\'immagine del progetto è troppo grande.', 'de' => 'Das Projektbild ist zu groß.'],
    'Project image sources must be embedded PNG/JPEG/WebP/GIF data.' => ['it' => 'Le immagini del progetto devono essere dati PNG/JPEG/WebP/GIF incorporati.', 'de' => 'Projektbilder müssen als PNG/JPEG/WebP/GIF-Daten eingebettet sein.'],
    'Project references an invalid PDF page.' => ['it' => 'Il progetto fa riferimento a una pagina PDF non valida.', 'de' => 'Das Projekt verweist auf eine ungültige PDF-Seite.'],
    'Protect PDF' => ['it' => 'Proteggi PDF', 'de' => 'PDF schützen'],
    'Protecting PDF' => ['it' => 'Protezione PDF', 'de' => 'PDF wird geschützt'],
    'qpdf attempts to recover cross-reference information and rewrites the PDF structure. Repair depends on the damage; missing page content cannot be recreated. The result is downloaded.' => ['it' => 'qpdf tenta di recuperare le informazioni dei riferimenti incrociati e riscrive la struttura PDF. La riparazione dipende dal danno; il contenuto mancante non può essere ricreato. Il risultato viene scaricato.', 'de' => 'qpdf versucht Querverweisinformationen wiederherzustellen und schreibt die PDF-Struktur neu. Die Reparatur hängt vom Schaden ab; fehlender Seiteninhalt kann nicht neu erstellt werden. Das Ergebnis wird heruntergeladen.'],
    'qpdf reorganizes the document so a compatible reader can display its first page before downloading the entire file. Page content is preserved. The optimized PDF is downloaded.' => ['it' => 'qpdf riorganizza il documento per consentire a un lettore compatibile di mostrare la prima pagina prima del download completo. Il contenuto viene mantenuto. Il PDF ottimizzato viene scaricato.', 'de' => 'qpdf ordnet das Dokument so neu, dass ein kompatibles Leseprogramm die erste Seite vor dem vollständigen Download anzeigen kann. Der Seiteninhalt bleibt erhalten. Das optimierte PDF wird heruntergeladen.'],
    'qpdf reported no structural errors.' => ['it' => 'qpdf non ha segnalato errori strutturali.', 'de' => 'qpdf hat keine Strukturfehler gemeldet.'],
    'qpdf reported structural errors.' => ['it' => 'qpdf ha segnalato errori strutturali.', 'de' => 'qpdf hat Strukturfehler gemeldet.'],
    'qpdf reported structural warnings.' => ['it' => 'qpdf ha segnalato avvisi strutturali.', 'de' => 'qpdf hat Strukturwarnungen gemeldet.'],
    'QR code' => ['it' => 'Codice QR', 'de' => 'QR-Code'],
    'QR code canvas could not be generated.' => ['it' => 'Impossibile generare il codice QR.', 'de' => 'Der QR-Code konnte nicht erstellt werden.'],
    'QR text or URL' => ['it' => 'Testo o URL del codice QR', 'de' => 'QR-Text oder URL'],
    'Quality' => ['it' => 'Qualità', 'de' => 'Qualität'],
    'Quotation' => ['it' => 'Citazione', 'de' => 'Zitat'],
    'Quote' => ['it' => 'Citazione', 'de' => 'Zitat'],
    'Radio group' => ['it' => 'Gruppo radio', 'de' => 'Optionsfeldgruppe'],
    'Range' => ['it' => 'Intervallo', 'de' => 'Bereich'],
    'Raster comparison at 120 DPI. Magenta marks pixel differences. Text comparison uses embedded words; layout and reading-order differences can affect it.' => ['it' => 'Confronto immagini a 120 DPI. Il magenta evidenzia le differenze di pixel. Il confronto del testo usa le parole incorporate; layout e ordine di lettura possono influire sul risultato.', 'de' => 'Rastervergleich mit 120 DPI. Magenta markiert Pixelunterschiede. Der Textvergleich verwendet eingebettete Wörter; Layout und Lesereihenfolge können das Ergebnis beeinflussen.'],
    'Raster difference' => ['it' => 'Differenza immagini', 'de' => 'Rasterdifferenz'],
    'Raster DPI' => ['it' => 'DPI raster', 'de' => 'Raster-DPI'],
    'Read what changed before downloading an update, and see the release notes when you open an upgraded installation.' => ['it' => 'Leggi le novità prima di scaricare un aggiornamento e consulta le note quando apri l\'installazione aggiornata.', 'de' => 'Lesen Sie die Änderungen vor dem Herunterladen eines Updates und sehen Sie die Versionshinweise beim Öffnen der aktualisierten Installation.'],
    'Read-only {1}' => ['it' => 'Sola lettura {1}', 'de' => 'Schreibgeschützt {1}'],
    'Reading' => ['it' => 'Lettura', 'de' => 'Wird gelesen'],
    'Reading form fields' => ['it' => 'Lettura campi modulo', 'de' => 'Formularfelder werden gelesen'],
    'Reading metadata' => ['it' => 'Lettura metadati', 'de' => 'Metadaten werden gelesen'],
    'Ready' => ['it' => 'Pronto', 'de' => 'Bereit'],
    'Recently opened' => ['it' => 'Aperti di recente', 'de' => 'Zuletzt geöffnet'],
    'Recognize scanned pages' => ['it' => 'Riconosci pagine scansionate', 'de' => 'Gescannte Seiten erkennen'],
    'Recognize text (OCR)' => ['it' => 'Riconosci testo (OCR)', 'de' => 'Text erkennen (OCR)'],
    'Recognized text' => ['it' => 'Testo riconosciuto', 'de' => 'Erkannter Text'],
    'Recognizing page {0}' => ['it' => 'Riconoscimento pagina {0}', 'de' => 'Seite {0} wird erkannt'],
    'Recognizing page text' => ['it' => 'Riconoscimento testo pagina', 'de' => 'Seitentext wird erkannt'],
    'recognizing text' => ['it' => 'riconoscimento testo', 'de' => 'Texterkennung'],
    'Rectangle' => ['it' => 'Rettangolo', 'de' => 'Rechteck'],
    'Redo' => ['it' => 'Ripeti', 'de' => 'Wiederholen'],
    'Redo (Ctrl Y)' => ['it' => 'Ripeti (Ctrl Y)', 'de' => 'Wiederholen (Strg Y)'],
    'Refresh this page after installation to check availability again.' => ['it' => 'Aggiorna questa pagina dopo l\'installazione per ricontrollare la disponibilità.', 'de' => 'Aktualisieren Sie diese Seite nach der Installation, um die Verfügbarkeit erneut zu prüfen.'],
    'Reload PDF Studio. The changes since your previous version will be shown automatically.' => ['it' => 'Ricarica PDF Studio. Le modifiche rispetto alla versione precedente verranno mostrate automaticamente.', 'de' => 'Laden Sie PDF Studio neu. Die Änderungen seit Ihrer vorherigen Version werden automatisch angezeigt.'],
    'Remove all standard Info and XMP metadata' => ['it' => 'Elimina tutti i metadati standard Info e XMP', 'de' => 'Alle Standard-Info- und XMP-Metadaten entfernen'],
    'Remove document metadata' => ['it' => 'Elimina metadati documento', 'de' => 'Dokumentmetadaten entfernen'],
    'Remove password from PDF' => ['it' => 'Elimina password dal PDF', 'de' => 'Passwort aus PDF entfernen'],
    'Remove PDF password' => ['it' => 'Rimuovi password PDF', 'de' => 'PDF-Passwort entfernen'],
    'Removing PDF encryption' => ['it' => 'Rimozione cifratura PDF', 'de' => 'PDF-Verschlüsselung wird entfernt'],
    'Rendering comparison page…' => ['it' => 'Rendering pagina di confronto…', 'de' => 'Vergleichsseite wird gerendert…'],
    'Rendering document on the server' => ['it' => 'Rendering documento sul server', 'de' => 'Dokument wird auf dem Server gerendert'],
    'Rendering page' => ['it' => 'Rendering pagina', 'de' => 'Seite wird gerendert'],
    'Rendering page images' => ['it' => 'Rendering immagini delle pagine', 'de' => 'Seitenbilder werden gerendert'],
    'Reorder, rotate, extract and duplicate' => ['it' => 'Riordina, ruota, estrai e duplica', 'de' => 'Neu anordnen, drehen, extrahieren und duplizieren'],
    'Repair' => ['it' => 'Ripara', 'de' => 'Reparieren'],
    'Repair PDF structure' => ['it' => 'Ripara struttura PDF', 'de' => 'PDF-Struktur reparieren'],
    'Repairing PDF structure' => ['it' => 'Riparazione struttura PDF', 'de' => 'PDF-Struktur wird repariert'],
    'Replace the current editable document with a blank document? Save its source first if you need it.' => ['it' => 'Sostituire il documento modificabile corrente con un documento vuoto? Salva prima la sorgente se vuoi conservarlo.', 'de' => 'Das aktuell bearbeitbare Dokument durch ein leeres Dokument ersetzen? Speichern Sie zuerst die Quelldatei, wenn Sie es behalten möchten.'],
    'Requires' => ['it' => 'Richiede', 'de' => 'Benötigt'],
    'Requires {0}' => ['it' => 'Richiede {0}', 'de' => 'Benötigt {0}'],
    'Requires Fabric.js, which could not load.' => ['it' => 'Richiede Fabric.js, che non è stato caricato.', 'de' => 'Benötigt Fabric.js, das nicht geladen werden konnte.'],
    'Reset fields' => ['it' => 'Reimposta campi', 'de' => 'Felder zurücksetzen'],
    'Resize' => ['it' => 'Ridimensiona', 'de' => 'Größe ändern'],
    'Resize pages' => ['it' => 'Ridimensiona pagine', 'de' => 'Seitengröße ändern'],
    'Resizing pages' => ['it' => 'Ridimensionamento pagine', 'de' => 'Seitengröße wird geändert'],
    'Resolution (DPI)' => ['it' => 'Risoluzione (DPI)', 'de' => 'Auflösung (DPI)'],
    'Restore local autosave' => ['it' => 'Ripristina salvataggio automatico locale', 'de' => 'Lokale automatische Sicherung wiederherstellen'],
    'Retry a failed update download without leaving your open document.' => ['it' => 'Riprova un download di aggiornamento senza lasciare il documento aperto.', 'de' => 'Wiederholen Sie einen fehlgeschlagenen Update-Download, ohne Ihr geöffnetes Dokument zu verlassen.'],
    'Reusable plain text. Tokens:' => ['it' => 'Testo semplice riutilizzabile. Segnaposto:', 'de' => 'Wiederverwendbarer Klartext. Platzhalter:'],
    'Reverse' => ['it' => 'Inverti ordine', 'de' => 'Reihenfolge umkehren'],
    'Right' => ['it' => 'Destra', 'de' => 'Rechts'],
    'Right content' => ['it' => 'Contenuto a destra', 'de' => 'Inhalt rechts'],
    'Right to left' => ['it' => 'Da destra a sinistra', 'de' => 'Rechts nach links'],
    'Rotate' => ['it' => 'Ruota', 'de' => 'Drehen'],
    'Rotate selected pages' => ['it' => 'Ruota pagine selezionate', 'de' => 'Ausgewählte Seiten drehen'],
    'Rotation' => ['it' => 'Rotazione', 'de' => 'Drehung'],
    'Rotation (degrees)' => ['it' => 'Rotazione (gradi)', 'de' => 'Drehung (Grad)'],
    'Rounded' => ['it' => 'Arrotondato', 'de' => 'Abgerundet'],
    'Run PDF Studio on PHP 7.2 and newer servers.' => ['it' => 'Esegui PDF Studio su PHP 7.2 e versioni successive.', 'de' => 'Verwenden Sie PDF Studio auf Servern mit PHP 7.2 oder neuer.'],
    'Run PHP as an unprivileged user with a working Chromium sandbox. Installing Chromium alone does not fix a blocked sandbox.' => ['it' => 'Esegui PHP come utente non privilegiato con sandbox Chromium funzionante. Installare Chromium non risolve una sandbox bloccata.', 'de' => 'Führen Sie PHP als unprivilegierten Benutzer mit funktionierender Chromium-Sandbox aus. Die Chromium-Installation allein behebt keine blockierte Sandbox.'],
    'Run the Composer command in the directory containing index.php, as the application owner. Composer must run with the same PHP version as this page (' => ['it' => 'Esegui Composer nella cartella di index.php, come proprietario dell\'applicazione. Composer deve usare la stessa versione PHP di questa pagina (', 'de' => 'Führen Sie Composer im Verzeichnis von index.php als Eigentümer der Anwendung aus. Composer muss dieselbe PHP-Version wie diese Seite verwenden ('],
    'Run these commands in an SSH terminal on your Ubuntu server, using an account with installation permissions.' => ['it' => 'Esegui questi comandi in un terminale SSH del server Ubuntu con un account autorizzato a installare pacchetti.', 'de' => 'Führen Sie diese Befehle in einem SSH-Terminal Ihres Ubuntu-Servers mit einem zur Installation berechtigten Konto aus.'],
    'Runs in' => ['it' => 'Esecuzione', 'de' => 'Ausführung'],
    'Runs locally in your browser' => ['it' => 'Elaborazione locale nel browser', 'de' => 'Wird lokal im Browser ausgeführt'],
    'Runs qpdf structural checks and reports warnings. Validation checks PDF structure; it does not certify document authenticity.' => ['it' => 'Esegue controlli strutturali qpdf e segnala gli avvisi. La convalida controlla la struttura PDF; non certifica l\'autenticità del documento.', 'de' => 'Führt qpdf-Strukturprüfungen aus und meldet Warnungen. Die Prüfung kontrolliert die PDF-Struktur; sie bestätigt nicht die Echtheit des Dokuments.'],
    's remaining' => ['it' => 's rimanenti', 'de' => 's verbleibend'],
    'Safe HTML sanitization' => ['it' => 'Pulizia sicura HTML', 'de' => 'Sichere HTML-Bereinigung'],
    'Save editable project' => ['it' => 'Salva progetto modificabile', 'de' => 'Bearbeitbares Projekt speichern'],
    'Save editable source' => ['it' => 'Salva sorgente modificabile', 'de' => 'Bearbeitbare Quelldatei speichern'],
    'Save project' => ['it' => 'Salva progetto', 'de' => 'Projekt speichern'],
    'Save this signature in this browser' => ['it' => 'Salva questa firma nel browser', 'de' => 'Diese Unterschrift im Browser speichern'],
    'Save visualization' => ['it' => 'Salva visualizzazione', 'de' => 'Darstellung speichern'],
    'Saving form values' => ['it' => 'Salvataggio valori modulo', 'de' => 'Formularwerte werden gespeichert'],
    'Saving metadata' => ['it' => 'Salvataggio metadati', 'de' => 'Metadaten werden gespeichert'],
    'Search tools' => ['it' => 'Cerca strumenti', 'de' => 'Werkzeuge suchen'],
    'Search tools (Ctrl K)' => ['it' => 'Cerca strumenti (Ctrl K)', 'de' => 'Werkzeuge suchen (Strg K)'],
    'Search tools, e.g. watermark, crop, OCR' => ['it' => 'Cerca strumenti, es. filigrana, ritaglio, OCR', 'de' => 'Werkzeuge suchen, z. B. Wasserzeichen, Zuschnitt, OCR'],
    'Searchable OCR PDF requires server Tesseract and Poppler.' => ['it' => 'Il PDF ricercabile con OCR richiede Tesseract e Poppler sul server.', 'de' => 'Ein durchsuchbares OCR-PDF benötigt Tesseract und Poppler auf dem Server.'],
    'Searchable PDF' => ['it' => 'PDF ricercabile', 'de' => 'Durchsuchbares PDF'],
    'Searchable PDF (server engine)' => ['it' => 'PDF ricercabile (motore server)', 'de' => 'Durchsuchbares PDF (Server-Engine)'],
    'Section text' => ['it' => 'Testo sezione', 'de' => 'Abschnittstext'],
    'Secure' => ['it' => 'Sicurezza', 'de' => 'Sicherheit'],
    'Secure redaction' => ['it' => 'Oscuramento sicuro', 'de' => 'Sichere Schwärzung'],
    'Secure redaction at' => ['it' => 'Oscuramento sicuro a', 'de' => 'Sichere Schwärzung mit'],
    'Securely rasterizing page' => ['it' => 'Rasterizzazione sicura pagina', 'de' => 'Seite wird sicher gerastert'],
    'See which capabilities run in your browser and which need your server.' => ['it' => 'Vedi quali capacità funzionano nel browser e quali richiedono il server.', 'de' => 'Sehen Sie, welche Funktionen im Browser laufen und welche den Server benötigen.'],
    'Select' => ['it' => 'Seleziona', 'de' => 'Auswählen'],
    'Select A3, A4, A5, Letter, Legal or custom page size.' => ['it' => 'Scegli A3, A4, A5, Letter, Legal o un formato personalizzato.', 'de' => 'Wählen Sie A3, A4, A5, Letter, Legal oder eine benutzerdefinierte Seitengröße.'],
    'Select all pages' => ['it' => 'Seleziona tutte le pagine', 'de' => 'Alle Seiten auswählen'],
    'Select an image object first.' => ['it' => 'Prima seleziona un oggetto immagine.', 'de' => 'Wählen Sie zuerst ein Bildobjekt aus.'],
    'Select an object to change its appearance. Original PDF content remains a rendered background.' => ['it' => 'Seleziona un oggetto per cambiarne l\'aspetto. Il PDF originale resta uno sfondo renderizzato.', 'de' => 'Wählen Sie ein Objekt aus, um sein Aussehen zu ändern. Der ursprüngliche PDF-Inhalt bleibt ein gerenderter Hintergrund.'],
    'Select an overlay object first.' => ['it' => 'Prima seleziona un oggetto sovrapposto.', 'de' => 'Wählen Sie zuerst ein Überlagerungsobjekt aus.'],
    'Select at least one page.' => ['it' => 'Seleziona almeno una pagina.', 'de' => 'Wählen Sie mindestens eine Seite aus.'],
    'Select at least three objects to distribute.' => ['it' => 'Seleziona almeno tre oggetti da distribuire.', 'de' => 'Wählen Sie mindestens drei Objekte zum Verteilen aus.'],
    'Select cells with Jodit\'s table popup to merge/split or insert/delete rows and columns. Drag its column separators to resize.' => ['it' => 'Seleziona le celle nel menu tabella di Jodit per unirle/dividerle o inserire/eliminare righe e colonne. Trascina i separatori per ridimensionare le colonne.', 'de' => 'Wählen Sie Zellen im Jodit-Tabellenmenü aus, um sie zu verbinden/teilen oder Zeilen und Spalten einzufügen/zu löschen. Ziehen Sie die Spaltentrenner zum Ändern der Breite.'],
    'Select layer' => ['it' => 'Seleziona livello', 'de' => 'Ebene auswählen'],
    'Select multiple objects with Shift + click or drag a selection. Single-object alignment uses the page.' => ['it' => 'Seleziona più oggetti con Maiusc + clic oppure trascinando un\'area. L\'allineamento di un solo oggetto usa la pagina.', 'de' => 'Wählen Sie mehrere Objekte mit Umschalt + Klick oder durch Aufziehen einer Auswahl aus. Einzelobjekte werden an der Seite ausgerichtet.'],
    'Select one or more overlay objects.' => ['it' => 'Seleziona uno o più oggetti sovrapposti.', 'de' => 'Wählen Sie ein oder mehrere Überlagerungsobjekte aus.'],
    'Select page {2}' => ['it' => 'Seleziona pagina {2}', 'de' => 'Seite {2} auswählen'],
    'Select page range' => ['it' => 'Seleziona intervallo di pagine', 'de' => 'Seitenbereich auswählen'],
    'Select pages' => ['it' => 'Seleziona pagine', 'de' => 'Seiten auswählen'],
    'Select the original PDFs to relink' => ['it' => 'Seleziona i PDF originali da ricollegare', 'de' => 'Original-PDFs zum erneuten Verknüpfen auswählen'],
    'Select two PDFs, or open a document first and select one PDF to compare with it.' => ['it' => 'Seleziona due PDF oppure apri un documento e scegli un PDF con cui confrontarlo.', 'de' => 'Wählen Sie zwei PDFs aus oder öffnen Sie ein Dokument und wählen Sie ein PDF zum Vergleich.'],
    'Send backward' => ['it' => 'Porta indietro', 'de' => 'Eine Ebene nach hinten'],
    'Send to back' => ['it' => 'Porta in secondo piano', 'de' => 'In den Hintergrund'],
    'Server' => ['it' => 'Server', 'de' => 'Server'],
    'Server / FPDI (PDFs only)' => ['it' => 'Server / FPDI (solo PDF)', 'de' => 'Server / FPDI (nur PDFs)'],
    'Server / Poppler' => ['it' => 'Server / Poppler', 'de' => 'Server / Poppler'],
    'Server / qpdf (PDFs only)' => ['it' => 'Server / qpdf (solo PDF)', 'de' => 'Server / qpdf (nur PDFs)'],
    'Server / Tesseract + Poppler' => ['it' => 'Server / Tesseract + Poppler', 'de' => 'Server / Tesseract + Poppler'],
    'Server capabilities could not be checked. Reload this page to try again; browser capabilities are listed above.' => ['it' => 'Impossibile verificare le capacità server. Ricarica la pagina per riprovare; le capacità browser sono elencate sopra.', 'de' => 'Serverfunktionen konnten nicht geprüft werden. Laden Sie die Seite erneut; die Browserfunktionen sind oben aufgeführt.'],
    'Server capability check failed:' => ['it' => 'Controllo capacità server non riuscito:', 'de' => 'Prüfung der Serverfunktionen fehlgeschlagen:'],
    'Server merging accepts PDFs only.' => ['it' => 'L\'unione sul server accetta solo PDF.', 'de' => 'Die Zusammenführung auf dem Server akzeptiert nur PDFs.'],
    'Server OCR' => ['it' => 'OCR server', 'de' => 'Server-OCR'],
    'Server operation failed (HTTP' => ['it' => 'Operazione server non riuscita (HTTP', 'de' => 'Serveroperation fehlgeschlagen (HTTP'],
    'Server operation failed.' => ['it' => 'Operazione server non riuscita.', 'de' => 'Serveroperation fehlgeschlagen.'],
    'Server PDF processing requires qpdf, Poppler pdfinfo, or FPDI to enforce the page limit.' => ['it' => 'L\'elaborazione PDF sul server richiede qpdf, Poppler pdfinfo o FPDI per controllare il limite di pagine.', 'de' => 'PDF-Verarbeitung auf dem Server benötigt qpdf, Poppler pdfinfo oder FPDI zur Durchsetzung der Seitengrenze.'],
    'Server processing with' => ['it' => 'Elaborazione server con', 'de' => 'Serververarbeitung mit'],
    'Server uploads are explicit.' => ['it' => 'I caricamenti sul server richiedono una scelta esplicita.', 'de' => 'Server-Uploads erfolgen nur nach ausdrücklicher Auswahl.'],
    'Sheet orientation' => ['it' => 'Orientamento foglio', 'de' => 'Bogenausrichtung'],
    'Sheet paper size' => ['it' => 'Formato carta foglio', 'de' => 'Bogenpapierformat'],
    'Side by side' => ['it' => 'Affiancati', 'de' => 'Nebeneinander'],
    'Size' => ['it' => 'Dimensioni', 'de' => 'Größe'],
    'Size (pt)' => ['it' => 'Dimensioni (pt)', 'de' => 'Größe (pt)'],
    'Size (px)' => ['it' => 'Dimensioni (px)', 'de' => 'Größe (px)'],
    'Some advanced options require a newer qpdf than older Ubuntu releases provide.' => ['it' => 'Alcune opzioni avanzate richiedono un qpdf più recente di quello fornito dalle vecchie versioni Ubuntu.', 'de' => 'Einige erweiterte Optionen benötigen eine neuere qpdf-Version als ältere Ubuntu-Versionen bereitstellen.'],
    'Source' => ['it' => 'Sorgente', 'de' => 'Quelle'],
    'Space after (pt)' => ['it' => 'Spazio dopo (pt)', 'de' => 'Abstand danach (pt)'],
    'Space before (pt)' => ['it' => 'Spazio prima (pt)', 'de' => 'Abstand davor (pt)'],
    'Spacing' => ['it' => 'Spaziatura', 'de' => 'Abstand'],
    'Spacing / gutter (points)' => ['it' => 'Spaziatura / canale (punti)', 'de' => 'Abstand / Zwischenraum (Punkte)'],
    'Split' => ['it' => 'Dividi', 'de' => 'Aufteilen'],
    'Split method' => ['it' => 'Metodo di divisione', 'de' => 'Aufteilungsmethode'],
    'Split PDF' => ['it' => 'Dividi PDF', 'de' => 'PDF aufteilen'],
    'Splitting PDF' => ['it' => 'Divisione PDF', 'de' => 'PDF wird aufgeteilt'],
    'Stamp' => ['it' => 'Timbro', 'de' => 'Stempel'],
    'Start number' => ['it' => 'Numero iniziale', 'de' => 'Startnummer'],
    'Status' => ['it' => 'Stato', 'de' => 'Status'],
    'Sticky note' => ['it' => 'Nota adesiva', 'de' => 'Haftnotiz'],
    'Stretch' => ['it' => 'Estendi', 'de' => 'Strecken'],
    'Strike' => ['it' => 'Barra', 'de' => 'Durchstreichen'],
    'Strike through' => ['it' => 'Barrato', 'de' => 'Durchgestrichen'],
    'Stroke (pt)' => ['it' => 'Tratto (pt)', 'de' => 'Linienbreite (pt)'],
    'Strong / 96 DPI' => ['it' => 'Forte / 96 DPI', 'de' => 'Stark / 96 DPI'],
    'Structural compression' => ['it' => 'Compressione della struttura', 'de' => 'Strukturkomprimierung'],
    'Subject' => ['it' => 'Oggetto', 'de' => 'Betreff'],
    'Suffix' => ['it' => 'Suffisso', 'de' => 'Suffix'],
    'System capabilities' => ['it' => 'Capacità del sistema', 'de' => 'Systemfunktionen'],
    'Table alignment' => ['it' => 'Allineamento tabella', 'de' => 'Tabellenausrichtung'],
    'Table and cell properties' => ['it' => 'Proprietà tabella e celle', 'de' => 'Tabellen- und Zelleneigenschaften'],
    'Table of contents' => ['it' => 'Indice', 'de' => 'Inhaltsverzeichnis'],
    'Table width (%)' => ['it' => 'Larghezza tabella (%)', 'de' => 'Tabellenbreite (%)'],
    'TCPDF · basic HTML' => ['it' => 'TCPDF · HTML di base', 'de' => 'TCPDF · grundlegendes HTML'],
    'TCPDF library (optional)' => ['it' => 'Libreria TCPDF (facoltativa)', 'de' => 'TCPDF-Bibliothek (optional)'],
    'Text' => ['it' => 'Testo', 'de' => 'Text'],
    'Text alignment' => ['it' => 'Allineamento testo', 'de' => 'Textausrichtung'],
    'Text classification' => ['it' => 'Classificazione testo', 'de' => 'Textklassifizierung'],
    'Text copied.' => ['it' => 'Testo copiato.', 'de' => 'Text kopiert.'],
    'Text editor / TXT' => ['it' => 'Editor testo / TXT', 'de' => 'Texteditor / TXT'],
    'Text extraction with layout' => ['it' => 'Estrazione testo con layout', 'de' => 'Textextraktion mit Layout'],
    'Text present' => ['it' => 'Testo presente', 'de' => 'Text vorhanden'],
    'Text selected. Press Ctrl+C or Command+C to copy.' => ['it' => 'Testo selezionato. Premi Ctrl+C o Command+C per copiarlo.', 'de' => 'Text ausgewählt. Drücken Sie zum Kopieren Strg+C oder Command+C.'],
    'Text, images, shapes and drawings' => ['it' => 'Testo, immagini, forme e disegni', 'de' => 'Text, Bilder, Formen und Zeichnungen'],
    'The browser could not encode this image.' => ['it' => 'Il browser non ha potuto codificare questa immagine.', 'de' => 'Der Browser konnte dieses Bild nicht kodieren.'],
    'The combined uploads exceed the processing limit.' => ['it' => 'I caricamenti combinati superano il limite di elaborazione.', 'de' => 'Die kombinierten Uploads überschreiten die Verarbeitungsgrenze.'],
    'The crop extends beyond this image.' => ['it' => 'Il ritaglio supera i bordi di questa immagine.', 'de' => 'Der Zuschnitt liegt außerhalb dieses Bildes.'],
    'The crop rectangle must fit inside the image.' => ['it' => 'Il rettangolo di ritaglio deve stare all\'interno dell\'immagine.', 'de' => 'Das Zuschnittrechteck muss innerhalb des Bildes liegen.'],
    'The document editor loads when this mode is opened.' => ['it' => 'L\'editor documenti si carica quando apri questa modalità.', 'de' => 'Der Dokumenteditor wird beim Öffnen dieses Modus geladen.'],
    'The document HTML could not be parsed.' => ['it' => 'Impossibile leggere l\'HTML del documento.', 'de' => 'Das Dokument-HTML konnte nicht eingelesen werden.'],
    'The document HTML exceeds the export limit.' => ['it' => 'L\'HTML del documento supera il limite di esportazione.', 'de' => 'Das Dokument-HTML überschreitet die Exportgrenze.'],
    'The document stylesheet contains an invalid CSS escape.' => ['it' => 'Il foglio di stile contiene una sequenza CSS non valida.', 'de' => 'Das Dokument-Stylesheet enthält eine ungültige CSS-Escape-Sequenz.'],
    'The document stylesheet is too large.' => ['it' => 'Il foglio di stile è troppo grande.', 'de' => 'Das Dokument-Stylesheet ist zu groß.'],
    'The download failed.' => ['it' => 'Download non riuscito.', 'de' => 'Download fehlgeschlagen.'],
    'The download timed out. Check your connection and try again.' => ['it' => 'Il download ha superato il tempo limite. Controlla la connessione e riprova.', 'de' => 'Der Download hat das Zeitlimit überschritten. Prüfen Sie Ihre Verbindung und versuchen Sie es erneut.'],
    'The extracted images exceed the output limit.' => ['it' => 'Le immagini estratte superano il limite del risultato.', 'de' => 'Die extrahierten Bilder überschreiten die Ausgabegrenze.'],
    'The field rectangle extends beyond the page.' => ['it' => 'Il rettangolo del campo supera la pagina.', 'de' => 'Das Feldrechteck liegt außerhalb der Seite.'],
    'The file exceeds the server upload limit.' => ['it' => 'Il file supera il limite di caricamento del server.', 'de' => 'Die Datei überschreitet das Server-Uploadlimit.'],
    'The file is empty or exceeds the' => ['it' => 'Il file è vuoto o supera il', 'de' => 'Die Datei ist leer oder überschreitet die'],
    'The generated document exceeds the page limit.' => ['it' => 'Il documento generato supera il limite di pagine.', 'de' => 'Das erzeugte Dokument überschreitet die Seitengrenze.'],
    'The generated files exceed the server workspace limit.' => ['it' => 'I file generati superano il limite dell\'area di lavoro server.', 'de' => 'Die erzeugten Dateien überschreiten die Grenze des Serverarbeitsbereichs.'],
    'The HTML sanitizer could not load. HTML import is disabled.' => ['it' => 'Impossibile caricare il sistema di pulizia HTML. L\'importazione HTML è disabilitata.', 'de' => 'Die HTML-Bereinigung konnte nicht geladen werden. HTML-Import ist deaktiviert.'],
    'The index.php download has started. Back up your installation before replacing it.' => ['it' => 'Il download di index.php è iniziato. Salva una copia dell\'installazione prima di sostituirla.', 'de' => 'Der Download von index.php wurde gestartet. Sichern Sie Ihre Installation, bevor Sie sie ersetzen.'],
    'The margins leave no room for document content.' => ['it' => 'I margini non lasciano spazio per il contenuto.', 'de' => 'Die Ränder lassen keinen Platz für Dokumentinhalt.'],
    'The merged page count exceeds the configured limit.' => ['it' => 'Il numero di pagine unite supera il limite configurato.', 'de' => 'Die zusammengeführte Seitenzahl überschreitet die eingerichtete Grenze.'],
    'The Office file does not have a valid ZIP document signature.' => ['it' => 'Il file Office non ha una firma ZIP valida.', 'de' => 'Die Office-Datei hat keine gültige ZIP-Dateikennung.'],
    'The operation was cancelled.' => ['it' => 'L\'operazione è stata annullata.', 'de' => 'Der Vorgang wurde abgebrochen.'],
    'The original PDF for this page must be relinked.' => ['it' => 'Il PDF originale di questa pagina deve essere ricollegato.', 'de' => 'Das Original-PDF dieser Seite muss erneut verknüpft werden.'],
    'The page range is invalid.' => ['it' => 'L\'intervallo di pagine non è valido.', 'de' => 'Der Seitenbereich ist ungültig.'],
    'The PDF page tree could not be read.' => ['it' => 'Impossibile leggere l\'albero delle pagine PDF.', 'de' => 'Der PDF-Seitenbaum konnte nicht gelesen werden.'],
    'The PDF password is missing or incorrect.' => ['it' => 'La password PDF manca o non è corretta.', 'de' => 'Das PDF-Passwort fehlt oder ist falsch.'],
    'The private processing directory could not be initialized safely.' => ['it' => 'Impossibile inizializzare in sicurezza la cartella privata di elaborazione.', 'de' => 'Das private Verarbeitungsverzeichnis konnte nicht sicher eingerichtet werden.'],
    'The private session directory is invalid.' => ['it' => 'La cartella privata di sessione non è valida.', 'de' => 'Das private Sitzungsverzeichnis ist ungültig.'],
    'The processor did not produce a downloadable file.' => ['it' => 'Il motore non ha prodotto un file scaricabile.', 'de' => 'Die Verarbeitung hat keine herunterladbare Datei erzeugt.'],
    'The processor produced an excessive diagnostic report.' => ['it' => 'Il motore ha prodotto un rapporto diagnostico troppo grande.', 'de' => 'Die Verarbeitung hat einen zu umfangreichen Diagnosebericht erzeugt.'],
    'The processor returned an invalid PDF.' => ['it' => 'Il motore ha restituito un PDF non valido.', 'de' => 'Die Verarbeitung hat ein ungültiges PDF geliefert.'],
    'The published files are still updating. Check for updates again, then retry the download.' => ['it' => 'I file pubblicati si stanno ancora aggiornando. Controlla di nuovo gli aggiornamenti e riprova il download.', 'de' => 'Die veröffentlichten Dateien werden noch aktualisiert. Prüfen Sie erneut auf Updates und wiederholen Sie den Download.'],
    'The radio options extend beyond the page.' => ['it' => 'Le opzioni radio superano la pagina.', 'de' => 'Die Optionsfelder liegen außerhalb der Seite.'],
    'The release notes are invalid.' => ['it' => 'Le note di versione non sono valide.', 'de' => 'Die Versionshinweise sind ungültig.'],
    'The release version does not match its notes.' => ['it' => 'La versione pubblicata non corrisponde alle note.', 'de' => 'Die Versionsnummer stimmt nicht mit den Versionshinweisen überein.'],
    'The requested page exceeds 40 million pixels. Reduce the DPI.' => ['it' => 'La pagina richiesta supera 40 milioni di pixel. Riduci i DPI.', 'de' => 'Die angeforderte Seite überschreitet 40 Millionen Pixel. Reduzieren Sie den DPI-Wert.'],
    'The result is empty or exceeds the output limit.' => ['it' => 'Il risultato è vuoto o supera il limite di dimensione.', 'de' => 'Das Ergebnis ist leer oder überschreitet die Ausgabegrenze.'],
    'The selected OCR language data is not installed on the server.' => ['it' => 'I dati della lingua OCR selezionata non sono installati sul server.', 'de' => 'Die gewählten OCR-Sprachdaten sind nicht auf dem Server installiert.'],
    'The server could not create a private workspace.' => ['it' => 'Il server non ha potuto creare un\'area di lavoro privata.', 'de' => 'Der Server konnte keinen privaten Arbeitsbereich erstellen.'],
    'The server could not create a processing workspace.' => ['it' => 'Il server non ha potuto creare un\'area di elaborazione.', 'de' => 'Der Server konnte keinen Verarbeitungsbereich erstellen.'],
    'The server could not start' => ['it' => 'Il server non ha potuto avviare', 'de' => 'Der Server konnte Folgendes nicht starten:'],
    'The server could not store the upload in its private workspace.' => ['it' => 'Il server non ha potuto salvare il caricamento nell\'area privata.', 'de' => 'Der Server konnte den Upload nicht im privaten Arbeitsbereich speichern.'],
    'The server did not return a rendered PDF.' => ['it' => 'Il server non ha restituito un PDF renderizzato.', 'de' => 'Der Server hat kein gerendertes PDF geliefert.'],
    'The server has no writable private temporary directory.' => ['it' => 'Il server non dispone di una cartella temporanea privata scrivibile.', 'de' => 'Der Server hat kein beschreibbares privates temporäres Verzeichnis.'],
    'The server temporary directory must be outside the public web root.' => ['it' => 'La cartella temporanea del server deve essere fuori dalla radice web pubblica.', 'de' => 'Das temporäre Serververzeichnis muss außerhalb des öffentlichen Webverzeichnisses liegen.'],
    'The update check failed.' => ['it' => 'Controllo aggiornamenti non riuscito.', 'de' => 'Updateprüfung fehlgeschlagen.'],
    'The update check timed out. Check your connection and try again.' => ['it' => 'Il controllo aggiornamenti ha superato il tempo limite. Controlla la connessione e riprova.', 'de' => 'Die Updateprüfung hat das Zeitlimit überschritten. Prüfen Sie Ihre Verbindung und versuchen Sie es erneut.'],
    'The update information is invalid.' => ['it' => 'Le informazioni di aggiornamento non sono valide.', 'de' => 'Die Updateinformationen sind ungültig.'],
    'The update information is too large.' => ['it' => 'Le informazioni di aggiornamento sono troppo grandi.', 'de' => 'Die Updateinformationen sind zu umfangreich.'],
    'The upload could not be verified.' => ['it' => 'Impossibile verificare il caricamento.', 'de' => 'Der Upload konnte nicht verifiziert werden.'],
    'The upload did not complete; please select the file again.' => ['it' => 'Il caricamento non è stato completato; seleziona nuovamente il file.', 'de' => 'Der Upload wurde nicht abgeschlossen; wählen Sie die Datei erneut aus.'],
    'The upload does not appear to be a PDF document.' => ['it' => 'Il file caricato non sembra un documento PDF.', 'de' => 'Die hochgeladene Datei scheint kein PDF-Dokument zu sein.'],
    'The white sheet shows the selected paper width. Choose Print to see paginated output and save a PDF. Chrome/Chromium provide the best support for page X of Y and margin headers.' => ['it' => 'Il foglio bianco mostra la larghezza carta selezionata. Scegli Stampa per vedere il risultato paginato e salvare un PDF. Chrome/Chromium offrono il miglior supporto per pagina X di Y e intestazioni nei margini.', 'de' => 'Das weiße Blatt zeigt die gewählte Papierbreite. Wählen Sie Drucken, um die Seiteneinteilung zu sehen und ein PDF zu speichern. Chrome/Chromium bieten die beste Unterstützung für Seite X von Y und Randkopfzeilen.'],
    'The ZIP could not be created.' => ['it' => 'Impossibile creare lo ZIP.', 'de' => 'Das ZIP konnte nicht erstellt werden.'],
    'This adds a visible signature. It does not cryptographically sign the PDF.' => ['it' => 'Aggiunge una firma visibile. Non firma il PDF in modo crittografico.', 'de' => 'Fügt eine sichtbare Unterschrift hinzu. Das PDF wird nicht kryptografisch signiert.'],
    'This BMP image is too large to convert safely in the browser.' => ['it' => 'Questa immagine BMP è troppo grande per una conversione sicura nel browser.', 'de' => 'Dieses BMP-Bild ist zu groß für eine sichere Konvertierung im Browser.'],
    'This browser cannot decode that BMP image. Convert it to PNG first.' => ['it' => 'Il browser non può decodificare questa immagine BMP. Prima convertila in PNG.', 'de' => 'Der Browser kann dieses BMP-Bild nicht dekodieren. Wandeln Sie es zuerst in PNG um.'],
    'This document exceeds the' => ['it' => 'Questo documento supera il', 'de' => 'Dieses Dokument überschreitet die'],
    'This feature requires' => ['it' => 'Questa funzione richiede', 'de' => 'Diese Funktion benötigt'],
    'This file exceeds the configured' => ['it' => 'Questo file supera il limite configurato di', 'de' => 'Diese Datei überschreitet die eingerichtete Grenze von'],
    'This file has no editable document source.' => ['it' => 'Questo file non contiene una sorgente documento modificabile.', 'de' => 'Diese Datei enthält keine bearbeitbare Dokumentquelle.'],
    'This font does not contain one or more characters. Use Latin text, or add text in the PDF overlay editor where browser fonts can be rasterized.' => ['it' => 'Questo carattere non contiene tutti i simboli richiesti. Usa testo latino oppure aggiungi testo nell\'editor di sovrapposizioni PDF, che può convertire in immagini i caratteri del browser.', 'de' => 'Diese Schrift enthält nicht alle benötigten Zeichen. Verwenden Sie lateinischen Text oder fügen Sie Text im PDF-Überlagerungseditor hinzu, der Browserschriften als Bilder ausgeben kann.'],
    'This form cannot be flattened for page organization:' => ['it' => 'Questo modulo non può diventare permanente per organizzare le pagine:', 'de' => 'Dieses Formular kann für die Seitenverwaltung nicht abgeflacht werden:'],
    'This image format cannot be decoded by your browser.' => ['it' => 'Il browser non può decodificare questo formato immagine.', 'de' => 'Ihr Browser kann dieses Bildformat nicht dekodieren.'],
    'This image is too large to crop safely in the browser.' => ['it' => 'Questa immagine è troppo grande per un ritaglio sicuro nel browser.', 'de' => 'Dieses Bild ist zu groß für einen sicheren Zuschnitt im Browser.'],
    'This installation has been upgraded from {0} to {1}. Here\'s what changed.' => ['it' => 'Questa installazione è stata aggiornata dalla versione {0} alla {1}. Ecco le novità.', 'de' => 'Diese Installation wurde von Version {0} auf {1} aktualisiert. Das hat sich geändert.'],
    'This installation is newer than the published version.' => ['it' => 'Questa installazione è più recente della versione pubblicata.', 'de' => 'Diese Installation ist neuer als die veröffentlichte Version.'],
    'This is not a supported PDF Studio project.' => ['it' => 'Questo non è un progetto PDF Studio supportato.', 'de' => 'Dies ist kein unterstütztes PDF-Studio-Projekt.'],
    'This operation accepts PDF files only.' => ['it' => 'Questa operazione accetta solo file PDF.', 'de' => 'Dieser Vorgang akzeptiert nur PDF-Dateien.'],
    'This optional extension is detected only; browser image tools remain available without it.' => ['it' => 'Questa estensione facoltativa viene solo rilevata; gli strumenti immagini del browser restano disponibili senza di essa.', 'de' => 'Diese optionale Erweiterung wird nur erkannt; Browser-Bildwerkzeuge bleiben ohne sie verfügbar.'],
    'This PDF contains an XFA form. XFA editing is unsupported. Use an AcroForm PDF or the overlay text editor.' => ['it' => 'Questo PDF contiene un modulo XFA. La modifica XFA non è supportata. Usa un PDF AcroForm oppure l\'editor di testo sovrapposto.', 'de' => 'Dieses PDF enthält ein XFA-Formular. XFA-Bearbeitung wird nicht unterstützt. Verwenden Sie ein AcroForm-PDF oder den Überlagerungs-Texteditor.'],
    'This PDF has' => ['it' => 'Questo PDF contiene', 'de' => 'Dieses PDF hat'],
    'This PDF is encrypted. Use Secure → Decrypt with its valid password before editing. qpdf must be installed for decryption.' => ['it' => 'Questo PDF è cifrato. Usa Sicurezza → Rimuovi password PDF con la password corretta prima di modificarlo. Per decifrarlo occorre qpdf.', 'de' => 'Dieses PDF ist verschlüsselt. Verwenden Sie vor der Bearbeitung Sicherheit → PDF-Passwort entfernen mit dem gültigen Passwort. Zur Entschlüsselung muss qpdf installiert sein.'],
    'This render exceeds the 42 megapixel memory limit. Reduce the DPI or zoom.' => ['it' => 'Il rendering supera il limite di memoria di 42 megapixel. Riduci i DPI o lo zoom.', 'de' => 'Dieses Rendering überschreitet die Speichergrenze von 42 Megapixeln. Reduzieren Sie DPI oder Zoom.'],
    'This server accepts files up to' => ['it' => 'Questo server accetta file fino a', 'de' => 'Dieser Server akzeptiert Dateien bis'],
    'This tool is missing from the server PATH or could not be started by PHP.' => ['it' => 'Questo strumento manca dal PATH del server oppure PHP non può avviarlo.', 'de' => 'Dieses Werkzeug fehlt im Server-PATH oder konnte von PHP nicht gestartet werden.'],
    'This tool requires' => ['it' => 'Questo strumento richiede', 'de' => 'Dieses Werkzeug benötigt'],
    'This update needs PHP {0} or newer. Your server reports PHP {1}. Upgrade PHP before replacing index.php.' => ['it' => 'Questo aggiornamento richiede PHP {0} o successivo. Il server usa PHP {1}. Aggiorna PHP prima di sostituire index.php.', 'de' => 'Dieses Update benötigt PHP {0} oder neuer. Ihr Server verwendet PHP {1}. Aktualisieren Sie PHP, bevor Sie index.php ersetzen.'],
    'Threshold' => ['it' => 'Soglia', 'de' => 'Schwellenwert'],
    'Tile / repeat watermark' => ['it' => 'Ripeti filigrana', 'de' => 'Wasserzeichen wiederholen'],
    'Title' => ['it' => 'Titolo', 'de' => 'Titel'],
    'To end' => ['it' => 'Alla fine', 'de' => 'Ans Ende'],
    'To start' => ['it' => 'All\'inizio', 'de' => 'An den Anfang'],
    'Toggle light and dark mode' => ['it' => 'Alterna modalità chiara e scura', 'de' => 'Hellen und dunklen Modus umschalten'],
    'Toggle tool navigation' => ['it' => 'Mostra o nascondi navigazione strumenti', 'de' => 'Werkzeugnavigation umschalten'],
    'Tokens: {n}, {total}, {filename}, {title}, {date}, {time}. Numbering is relative to selected pages.' => ['it' => 'Segnaposto: {n}, {total}, {filename}, {title}, {date}, {time}. La numerazione è relativa alle pagine selezionate.', 'de' => 'Platzhalter: {n}, {total}, {filename}, {title}, {date}, {time}. Die Nummerierung bezieht sich auf die ausgewählten Seiten.'],
    'Too many embedded images.' => ['it' => 'Troppe immagini incorporate.', 'de' => 'Zu viele eingebettete Bilder.'],
    'Too many selected pages.' => ['it' => 'Troppe pagine selezionate.', 'de' => 'Zu viele ausgewählte Seiten.'],
    'Too many uploaded files; the limit is' => ['it' => 'Troppi file caricati; il limite è', 'de' => 'Zu viele hochgeladene Dateien; die Grenze ist'],
    'Tools' => ['it' => 'Strumenti', 'de' => 'Werkzeuge'],
    'Top' => ['it' => 'Alto', 'de' => 'Oben'],
    'Top position' => ['it' => 'Posizione in alto', 'de' => 'Obere Position'],
    'Top position (points from page top)' => ['it' => 'Posizione in alto (punti dal bordo superiore)', 'de' => 'Obere Position (Punkte vom oberen Seitenrand)'],
    'Top-level bookmarks' => ['it' => 'Segnalibri di primo livello', 'de' => 'Lesezeichen der obersten Ebene'],
    'Transparent background (PNG / WebP)' => ['it' => 'Sfondo trasparente (PNG / WebP)', 'de' => 'Transparenter Hintergrund (PNG / WebP)'],
    'Trim from bottom' => ['it' => 'Ritaglio dal basso', 'de' => 'Unten abschneiden'],
    'Trim from left (PDF points)' => ['it' => 'Ritaglio da sinistra (punti PDF)', 'de' => 'Links abschneiden (PDF-Punkte)'],
    'Trim from right' => ['it' => 'Ritaglio da destra', 'de' => 'Rechts abschneiden'],
    'Trim from top' => ['it' => 'Ritaglio dall\'alto', 'de' => 'Oben abschneiden'],
    'Two dedicated editors' => ['it' => 'Due editor dedicati', 'de' => 'Zwei eigene Editoren'],
    'Ubuntu installation commands' => ['it' => 'Comandi di installazione Ubuntu', 'de' => 'Ubuntu-Installationsbefehle'],
    'Ubuntu installation help' => ['it' => 'Guida installazione Ubuntu', 'de' => 'Ubuntu-Installationshilfe'],
    'Ubuntu installation help for {1}' => ['it' => 'Guida installazione Ubuntu per {1}', 'de' => 'Ubuntu-Installationshilfe für {1}'],
    'Ubuntu setup:' => ['it' => 'Configurazione Ubuntu:', 'de' => 'Ubuntu-Einrichtung:'],
    'Unavailable' => ['it' => 'Non disponibile', 'de' => 'Nicht verfügbar'],
    'Underline' => ['it' => 'Sottolinea', 'de' => 'Unterstreichen'],
    'Undo' => ['it' => 'Annulla modifica', 'de' => 'Rückgängig'],
    'Undo (Ctrl Z)' => ['it' => 'Annulla modifica (Ctrl Z)', 'de' => 'Rückgängig (Strg Z)'],
    'Unique field name' => ['it' => 'Nome campo univoco', 'de' => 'Eindeutiger Feldname'],
    'Unknown' => ['it' => 'Sconosciuto', 'de' => 'Unbekannt'],
    'Unknown document command:' => ['it' => 'Comando documento sconosciuto:', 'de' => 'Unbekannter Dokumentbefehl:'],
    'Unknown font' => ['it' => 'Carattere sconosciuto', 'de' => 'Unbekannte Schrift'],
    'Unknown PDF utility:' => ['it' => 'Utilità PDF sconosciuta:', 'de' => 'Unbekanntes PDF-Werkzeug:'],
    'Unknown source' => ['it' => 'Sorgente sconosciuta', 'de' => 'Unbekannte Quelle'],
    'Unknown tool:' => ['it' => 'Strumento sconosciuto:', 'de' => 'Unbekanntes Werkzeug:'],
    'Unlock' => ['it' => 'Sblocca', 'de' => 'Entsperren'],
    'Unsaved changes' => ['it' => 'Modifiche non salvate', 'de' => 'Ungespeicherte Änderungen'],
    'Unsupported file:' => ['it' => 'File non supportato:', 'de' => 'Nicht unterstützte Datei:'],
    'Untitled' => ['it' => 'Senza titolo', 'de' => 'Unbenannt'],
    'Untitled document' => ['it' => 'Documento senza titolo', 'de' => 'Unbenanntes Dokument'],
    'Update downloads now save index.php directly instead of opening its source in a new tab.' => ['it' => 'I download degli aggiornamenti salvano direttamente index.php invece di aprire il codice in una nuova scheda.', 'de' => 'Update-Downloads speichern index.php jetzt direkt, anstatt den Quelltext in einem neuen Tab zu öffnen.'],
    'Uploading to this server and processing…' => ['it' => 'Caricamento su questo server ed elaborazione…', 'de' => 'Datei wird auf diesen Server hochgeladen und verarbeitet…'],
    'Use 1-4,7,10-12, odd, even, or all.' => ['it' => 'Usa 1-4,7,10-12, dispari, pari o tutte.', 'de' => 'Verwenden Sie 1-4,7,10-12, ungerade, gerade oder alle.'],
    'Use page ranges such as 1-4,7,10-12, or all, odd, even.' => ['it' => 'Usa intervalli come 1-4,7,10-12 oppure tutte, dispari, pari.', 'de' => 'Verwenden Sie Bereiche wie 1-4,7,10-12 oder alle, ungerade, gerade.'],
    'Use page ranges such as 1-4,7,10-12.' => ['it' => 'Usa intervalli di pagine come 1-4,7,10-12.', 'de' => 'Verwenden Sie Seitenbereiche wie 1-4,7,10-12.'],
    'Use PNG, JPEG, GIF, WebP or BMP images.' => ['it' => 'Usa immagini PNG, JPEG, GIF, WebP o BMP.', 'de' => 'Verwenden Sie PNG-, JPEG-, GIF-, WebP- oder BMP-Bilder.'],
    'Use saved signature' => ['it' => 'Usa firma salvata', 'de' => 'Gespeicherte Unterschrift verwenden'],
    'Valid opening or owner password' => ['it' => 'Password di apertura o proprietario valida', 'de' => 'Gültiges Öffnungs- oder Eigentümerpasswort'],
    'Validate PDF' => ['it' => 'Convalida PDF', 'de' => 'PDF prüfen'],
    'Validate PDF structure' => ['it' => 'Convalida struttura PDF', 'de' => 'PDF-Struktur prüfen'],
    'Validation' => ['it' => 'Convalida', 'de' => 'Prüfung'],
    'Version' => ['it' => 'Versione', 'de' => 'Version'],
    'Version {0}' => ['it' => 'Versione {0}', 'de' => 'Version {0}'],
    'Version {0} is available.' => ['it' => 'La versione {0} è disponibile.', 'de' => 'Version {0} ist verfügbar.'],
    'Vertical alignment' => ['it' => 'Allineamento verticale', 'de' => 'Vertikale Ausrichtung'],
    'Vertical center' => ['it' => 'Centro verticale', 'de' => 'Vertikal zentrieren'],
    'View changelog on GitHub' => ['it' => 'Vedi cronologia modifiche su GitHub', 'de' => 'Änderungsverlauf auf GitHub ansehen'],
    'Visible dimensions' => ['it' => 'Dimensioni visibili', 'de' => 'Sichtbare Abmessungen'],
    'Visible text' => ['it' => 'Testo visibile', 'de' => 'Sichtbarer Text'],
    'Visual crop preview' => ['it' => 'Anteprima ritaglio visivo', 'de' => 'Visuelle Zuschnittvorschau'],
    'Visual redaction' => ['it' => 'Oscuramento visivo', 'de' => 'Visuelle Schwärzung'],
    'Visual signature' => ['it' => 'Firma visiva', 'de' => 'Sichtbare Unterschrift'],
    'Visual signatures are images or text. They are not certificate signatures.' => ['it' => 'Le firme visive sono immagini o testo. Non sono firme con certificato.', 'de' => 'Sichtbare Unterschriften sind Bilder oder Text. Sie sind keine Zertifikatssignaturen.'],
    'was not detected, or PHP proc_open is disabled.' => ['it' => 'non è stato rilevato oppure PHP proc_open è disabilitato.', 'de' => 'wurde nicht erkannt oder PHP proc_open ist deaktiviert.'],
    'Watermark' => ['it' => 'Filigrana', 'de' => 'Wasserzeichen'],
    'Watermark text' => ['it' => 'Testo filigrana', 'de' => 'Wasserzeichentext'],
    'Watermark type' => ['it' => 'Tipo filigrana', 'de' => 'Wasserzeichentyp'],
    'What changes in this update' => ['it' => 'Modifiche in questo aggiornamento', 'de' => 'Änderungen in diesem Update'],
    'What\'s new' => ['it' => 'Novità', 'de' => 'Neuigkeiten'],
    'What\'s new in PDF Studio' => ['it' => 'Novità in PDF Studio', 'de' => 'Neuigkeiten in PDF Studio'],
    'What\'s new in PDF Studio {0}. You can reopen this history from System capabilities.' => ['it' => 'Novità in PDF Studio {0}. Puoi riaprire questa cronologia dalle capacità del sistema.', 'de' => 'Neuigkeiten in PDF Studio {0}. Sie können diesen Verlauf über die Systemfunktionen erneut öffnen.'],
    'What\'s new in this installation' => ['it' => 'Novità di questa installazione', 'de' => 'Neuigkeiten in dieser Installation'],
    'White-out' => ['it' => 'Copri in bianco', 'de' => 'Weiß abdecken'],
    'Width' => ['it' => 'Larghezza', 'de' => 'Breite'],
    'Word diff is limited to the first 1,400 words per page. The side-by-side extracted text below is complete.' => ['it' => 'Il confronto delle parole è limitato alle prime 1.400 parole per pagina. Il testo estratto affiancato qui sotto è completo.', 'de' => 'Der Wortvergleich ist auf die ersten 1.400 Wörter je Seite begrenzt. Der unten nebeneinander angezeigte extrahierte Text ist vollständig.'],
    'Word-like document authoring' => ['it' => 'Creazione documenti simile a Word', 'de' => 'Word-ähnliche Dokumenterstellung'],
    'words ·' => ['it' => 'parole ·', 'de' => 'Wörter ·'],
    'Working PDF size' => ['it' => 'Dimensione PDF di lavoro', 'de' => 'Größe des Arbeits-PDFs'],
    'Working…' => ['it' => 'Elaborazione…', 'de' => 'In Bearbeitung…'],
    'WORKSPACE' => ['it' => 'AREA DI LAVORO', 'de' => 'ARBEITSBEREICH'],
    'Write your document here.' => ['it' => 'Scrivi qui il tuo documento.', 'de' => 'Schreiben Sie hier Ihr Dokument.'],
    'Writing imposed sheets' => ['it' => 'Scrittura fogli impaginati', 'de' => 'Montierte Bögen werden geschrieben'],
    'Writing PDF groups' => ['it' => 'Scrittura gruppi PDF', 'de' => 'PDF-Gruppen werden geschrieben'],
    'Writing ZIP archive' => ['it' => 'Scrittura archivio ZIP', 'de' => 'ZIP-Archiv wird geschrieben'],
    'X mark' => ['it' => 'Segno X', 'de' => 'X-Zeichen'],
    'XFA form editing is unsupported.' => ['it' => 'La modifica dei moduli XFA non è supportata.', 'de' => 'XFA-Formularbearbeitung wird nicht unterstützt.'],
    'XFA forms cannot be flattened by this engine.' => ['it' => 'Questo motore non può rendere permanenti i moduli XFA.', 'de' => 'Diese Engine kann XFA-Formulare nicht abflachen.'],
    'XFA forms cannot be merged safely.' => ['it' => 'I moduli XFA non possono essere uniti in sicurezza.', 'de' => 'XFA-Formulare können nicht sicher zusammengeführt werden.'],
    'XFA forms cannot be reorganized safely. Convert this PDF to static pages first.' => ['it' => 'I moduli XFA non possono essere riorganizzati in sicurezza. Prima converti il PDF in pagine statiche.', 'de' => 'XFA-Formulare können nicht sicher neu angeordnet werden. Wandeln Sie das PDF zuerst in statische Seiten um.'],
    'Yes,No' => ['it' => 'Sì,No', 'de' => 'Ja,Nein'],
    'You are using the latest published version.' => ['it' => 'Stai usando l\'ultima versione pubblicata.', 'de' => 'Sie verwenden die neueste veröffentlichte Version.'],
    'YOUR DOCUMENTS, YOUR WORKSPACE' => ['it' => 'I TUOI DOCUMENTI IL TUO SPAZIO', 'de' => 'IHRE DOKUMENTE IHR ARBEITSBEREICH'],
    'Your name' => ['it' => 'Il tuo nome', 'de' => 'Ihr Name'],
    'Your open document stays in this page while you download. Save it or its editable project before reloading.' => ['it' => 'Il documento aperto resta in questa pagina durante il download. Salvalo o salva il progetto modificabile prima di ricaricare.', 'de' => 'Ihr geöffnetes Dokument bleibt während des Downloads auf dieser Seite. Speichern Sie es oder sein bearbeitbares Projekt vor dem Neuladen.'],
    'ZIP exports' => ['it' => 'Esportazioni ZIP', 'de' => 'ZIP-Exporte'],
    'Zoom' => ['it' => 'Zoom', 'de' => 'Zoom'],
    'Zoom in' => ['it' => 'Aumenta zoom', 'de' => 'Vergrößern'],
    'Zoom out' => ['it' => 'Riduci zoom', 'de' => 'Verkleinern'],
    'Language' => ['it' => 'Lingua', 'de' => 'Sprache'],
    'Italian' => ['it' => 'Italiano', 'de' => 'Italienisch'],
    'English' => ['it' => 'Inglese', 'de' => 'Englisch'],
    'German' => ['it' => 'Tedesco', 'de' => 'Deutsch'],
    'Automatic saving is unavailable or browser storage is full. Download an editable project to keep your work.' => ['it' => 'Il salvataggio automatico non è disponibile o lo spazio del browser è pieno. Scarica un progetto modificabile per conservare il lavoro.', 'de' => 'Automatisches Speichern ist nicht verfügbar oder der Browserspeicher ist voll. Laden Sie ein bearbeitbares Projekt herunter, um Ihre Arbeit zu behalten.'],
    'Choose Italian, English or German without closing your document. Italian is the default language.' => ['it' => 'Scegli italiano, inglese o tedesco senza chiudere il documento. La lingua predefinita è l’italiano.', 'de' => 'Wählen Sie Italienisch, Englisch oder Deutsch, ohne Ihr Dokument zu schließen. Die Standardsprache ist Italienisch.'],
    'Work without a database. The language choice and automatic recovery use browser storage; downloadable projects keep an independent copy.' => ['it' => 'Lavora senza database. La lingua scelta e il recupero automatico usano lo spazio del browser; i progetti scaricabili conservano una copia indipendente.', 'de' => 'Arbeiten Sie ohne Datenbank. Sprachwahl und automatische Wiederherstellung verwenden Browserspeicher; herunterladbare Projekte bewahren eine unabhängige Kopie.'],
    'PDF rewrite' => ['it' => 'Riscrittura PDF', 'de' => 'PDF neu schreiben'],
    'characters' => ['it' => 'caratteri', 'de' => 'Zeichen'],
    'left' => ['it' => 'sinistra', 'de' => 'links'],
    'center' => ['it' => 'centro', 'de' => 'zentriert'],
    'right' => ['it' => 'destra', 'de' => 'rechts'],
    'top' => ['it' => 'alto', 'de' => 'oben'],
    'middle' => ['it' => 'centro', 'de' => 'mittig'],
    'bottom' => ['it' => 'basso', 'de' => 'unten'],
    'portrait' => ['it' => 'verticale', 'de' => 'Hochformat'],
    'landscape' => ['it' => 'orizzontale', 'de' => 'Querformat'],
    'or' => ['it' => 'oppure', 'de' => 'oder'],
    'completed.' => ['it' => 'completato.', 'de' => 'abgeschlossen.'],
    'Page break' => ['it' => 'Interruzione di pagina', 'de' => 'Seitenumbruch'],
    'Safe Office conversion also requires the PHP ZIP and DOM extensions.' => ['it' => 'La conversione Office sicura richiede anche le estensioni PHP ZIP e DOM.', 'de' => 'Die sichere Office-Konvertierung benötigt auch die PHP-Erweiterungen ZIP und DOM.'],
    'The Office document archive is invalid.' => ['it' => 'L\'archivio del documento Office non è valido.', 'de' => 'Das Office-Dokumentarchiv ist ungültig.'],
    'The Office document contains too many archive entries.' => ['it' => 'Il documento Office contiene troppe voci di archivio.', 'de' => 'Das Office-Dokument enthält zu viele Archiveinträge.'],
    'The document structure does not match its extension.' => ['it' => 'La struttura del documento non corrisponde all\'estensione.', 'de' => 'Die Dokumentstruktur passt nicht zur Dateiendung.'],
    'The Office archive contains a symbolic link.' => ['it' => 'L\'archivio Office contiene un collegamento simbolico.', 'de' => 'Das Office-Archiv enthält eine symbolische Verknüpfung.'],
    'Encrypted Office documents are not supported.' => ['it' => 'I documenti Office cifrati non sono supportati.', 'de' => 'Verschlüsselte Office-Dokumente werden nicht unterstützt.'],
    'The Office archive exceeds safe expansion limits.' => ['it' => 'L\'archivio Office supera i limiti di estrazione sicura.', 'de' => 'Das Office-Archiv überschreitet die sicheren Entpackungsgrenzen.'],
    'The Office archive contains an unsafe path.' => ['it' => 'L\'archivio Office contiene un percorso non sicuro.', 'de' => 'Das Office-Archiv enthält einen unsicheren Pfad.'],
    'Office conversion does not accept embedded programs, macros, HTML, SVG or PostScript content.' => ['it' => 'La conversione Office non accetta programmi incorporati, macro, HTML, SVG o PostScript.', 'de' => 'Die Office-Konvertierung akzeptiert keine eingebetteten Programme, Makros, HTML-, SVG- oder PostScript-Inhalte.'],
    'The Office XML contains forbidden entities.' => ['it' => 'L\'XML Office contiene entità vietate.', 'de' => 'Das Office-XML enthält unzulässige Entitäten.'],
    'Office conversion blocks dynamic external-data fields and formulas.' => ['it' => 'La conversione Office blocca campi e formule con dati esterni dinamici.', 'de' => 'Die Office-Konvertierung blockiert dynamische externe Datenfelder und Formeln.'],
    'The Office document contains malformed XML.' => ['it' => 'Il documento Office contiene XML non valido.', 'de' => 'Das Office-Dokument enthält fehlerhaftes XML.'],
    'Office conversion blocks macro event handlers.' => ['it' => 'La conversione Office blocca i gestori di eventi macro.', 'de' => 'Die Office-Konvertierung blockiert Makro-Ereignishandler.'],
    'Office conversion blocks external linked resources. Remove external links from the document first.' => ['it' => 'La conversione Office blocca le risorse collegate esterne. Prima rimuovi i collegamenti esterni dal documento.', 'de' => 'Die Office-Konvertierung blockiert extern verknüpfte Ressourcen. Entfernen Sie zuerst externe Verknüpfungen aus dem Dokument.'],
    'Office conversion blocks remote and filesystem-linked resources.' => ['it' => 'La conversione Office blocca le risorse remote e i collegamenti al filesystem.', 'de' => 'Die Office-Konvertierung blockiert externe und mit dem Dateisystem verknüpfte Ressourcen.'],
    'An Office reference escapes the document package.' => ['it' => 'Un riferimento Office esce dal pacchetto del documento.', 'de' => 'Ein Office-Verweis liegt außerhalb des Dokumentpakets.'],
    'An Office linked resource is missing from the document package.' => ['it' => 'Una risorsa Office collegata manca nel pacchetto.', 'de' => 'Eine verknüpfte Office-Ressource fehlt im Dokumentpaket.'],
    'LibreOffice could not convert this document. It may be encrypted or unsupported.' => ['it' => 'LibreOffice non ha potuto convertire il documento. Potrebbe essere cifrato o non supportato.', 'de' => 'LibreOffice konnte dieses Dokument nicht konvertieren. Es ist möglicherweise verschlüsselt oder wird nicht unterstützt.'],
    'Removing document metadata requires a newer qpdf version with --remove-info and --remove-metadata support.' => ['it' => 'La rimozione dei metadati richiede un qpdf più recente con supporto per --remove-info e --remove-metadata.', 'de' => 'Zum Entfernen der Metadaten ist eine neuere qpdf-Version mit Unterstützung für --remove-info und --remove-metadata erforderlich.'],
    'Select lossless, light, medium, strong or maximum compression.' => ['it' => 'Scegli compressione senza perdita, leggera, media, forte o massima.', 'de' => 'Wählen Sie verlustfreie, leichte, mittlere, starke oder maximale Komprimierung.'],
    'Metadata removal requires a newer qpdf version.' => ['it' => 'La rimozione dei metadati richiede una versione qpdf più recente.', 'de' => 'Das Entfernen der Metadaten benötigt eine neuere qpdf-Version.'],
    'Enter a password to open or an owner password.' => ['it' => 'Inserisci una password di apertura oppure proprietario.', 'de' => 'Geben Sie ein Öffnungs- oder Eigentümerpasswort ein.'],
    'Use different opening and owner passwords.' => ['it' => 'Usa password diverse per apertura e proprietario.', 'de' => 'Verwenden Sie unterschiedliche Öffnungs- und Eigentümerpasswörter.'],
    'Encryption must use AES-128 or AES-256.' => ['it' => 'La cifratura deve usare AES-128 o AES-256.', 'de' => 'Die Verschlüsselung muss AES-128 oder AES-256 verwenden.'],
    'This older qpdf version requires passwords that do not begin with a hyphen. Upgrade qpdf to support arbitrary passwords.' => ['it' => 'Questa vecchia versione qpdf richiede password che non iniziano con un trattino. Aggiorna qpdf per supportare qualsiasi password.', 'de' => 'Diese ältere qpdf-Version benötigt Passwörter, die nicht mit einem Bindestrich beginnen. Aktualisieren Sie qpdf für beliebige Passwörter.'],
    'Invalid printing permission.' => ['it' => 'Permesso di stampa non valido.', 'de' => 'Ungültige Druckberechtigung.'],
    'Server merge requires qpdf or FPDI. Browser merge remains available.' => ['it' => 'L\'unione server richiede qpdf o FPDI. L\'unione browser resta disponibile.', 'de' => 'Die Server-Zusammenführung benötigt qpdf oder FPDI. Die Zusammenführung im Browser bleibt verfügbar.'],
    'The merged document exceeds the page limit.' => ['it' => 'Il documento unito supera il limite di pagine.', 'de' => 'Das zusammengeführte Dokument überschreitet die Seitengrenze.'],
    'Invalid OCR language selection.' => ['it' => 'Selezione lingua OCR non valida.', 'de' => 'Ungültige OCR-Sprachauswahl.'],
    'The server does not have OCR language' => ['it' => 'Il server non dispone della lingua OCR', 'de' => 'Der Server hat folgende OCR-Sprache nicht installiert:'],
    '. Install its Tesseract traineddata package.' => ['it' => '. Installa il corrispondente pacchetto Tesseract traineddata.', 'de' => '. Installieren Sie das passende Tesseract-traineddata-Paket.'],
    'Select PDF or text OCR output.' => ['it' => 'Scegli un risultato OCR PDF oppure testo.', 'de' => 'Wählen Sie PDF oder Text als OCR-Ausgabe.'],
    'OCR exceeded the server job time limit. Process fewer pages.' => ['it' => 'L\'OCR ha superato il tempo limite del server. Elabora meno pagine.', 'de' => 'OCR hat das Server-Zeitlimit überschritten. Verarbeiten Sie weniger Seiten.'],
    'A PDF page could not be rendered for OCR.' => ['it' => 'Impossibile renderizzare una pagina PDF per l\'OCR.', 'de' => 'Eine PDF-Seite konnte für OCR nicht gerendert werden.'],
    'The processor reached a server resource limit. Try a smaller document or fewer pages.' => ['it' => 'Il motore ha raggiunto un limite di risorse del server. Prova un documento più piccolo o meno pagine.', 'de' => 'Die Verarbeitung hat eine Server-Ressourcengrenze erreicht. Versuchen Sie ein kleineres Dokument oder weniger Seiten.'],
    'Use GET for capabilities.' => ['it' => 'Usa GET per le capacità.', 'de' => 'Verwenden Sie GET für die Funktionen.'],
    'Unknown PDF Studio action.' => ['it' => 'Azione PDF Studio sconosciuta.', 'de' => 'Unbekannte PDF-Studio-Aktion.'],
    'This operation requires a POST request.' => ['it' => 'Questa operazione richiede una richiesta POST.', 'de' => 'Dieser Vorgang benötigt eine POST-Anfrage.'],
    'This request exceeds the PHP POST/upload size limit. Upload fewer or smaller files, or ask the administrator to increase post_max_size.' => ['it' => 'Questa richiesta supera il limite PHP POST/caricamento. Carica meno file o file più piccoli oppure chiedi all\'amministratore di aumentare post_max_size.', 'de' => 'Diese Anfrage überschreitet das PHP-POST-/Uploadlimit. Laden Sie weniger oder kleinere Dateien hoch oder bitten Sie den Administrator, post_max_size zu erhöhen.'],
    'The session token is missing or expired. Reload PDF Studio and try again.' => ['it' => 'Il token di sessione manca o è scaduto. Ricarica PDF Studio e riprova.', 'de' => 'Das Sitzungstoken fehlt oder ist abgelaufen. Laden Sie PDF Studio neu und versuchen Sie es erneut.'],
    'The operation options exceed the size limit.' => ['it' => 'Le opzioni dell\'operazione superano il limite di dimensione.', 'de' => 'Die Vorgangsoptionen überschreiten die Größengrenze.'],
    'The operation settings are not valid JSON.' => ['it' => 'Le impostazioni dell\'operazione non sono JSON valido.', 'de' => 'Die Vorgangseinstellungen sind kein gültiges JSON.'],
    'The operation settings must be an object.' => ['it' => 'Le impostazioni dell\'operazione devono essere un oggetto.', 'de' => 'Die Vorgangseinstellungen müssen ein Objekt sein.'],
    'Enter the valid PDF password to decrypt this document.' => ['it' => 'Inserisci la password PDF corretta per decifrare il documento.', 'de' => 'Geben Sie das gültige PDF-Passwort zum Entschlüsseln dieses Dokuments ein.'],
    'No embedded text was found. Run OCR for a scanned document.' => ['it' => 'Nessun testo incorporato trovato. Esegui OCR per un documento scansionato.', 'de' => 'Kein eingebetteter Text gefunden. Führen Sie für ein gescanntes Dokument OCR aus.'],
    'OCR did not recognize any text.' => ['it' => 'L\'OCR non ha riconosciuto testo.', 'de' => 'OCR hat keinen Text erkannt.'],
    'The document processor could not complete this operation. Check the server log using error reference' => ['it' => 'Il motore documenti non ha completato l\'operazione. Controlla il registro server con il riferimento errore', 'de' => 'Die Dokumentverarbeitung konnte diesen Vorgang nicht abschließen. Prüfen Sie das Serverprotokoll mit der Fehlerreferenz'],
    'text' => ['it' => 'testo', 'de' => 'Text'],
    'checkbox' => ['it' => 'casella di controllo', 'de' => 'Kontrollkästchen'],
    'radio' => ['it' => 'pulsante radio', 'de' => 'Optionsfeld'],
    'dropdown' => ['it' => 'menu a discesa', 'de' => 'Auswahlliste'],
    'Save an editable project before upgrading; older automatic recovery copies are not transferred.' => ['it' => 'Salva un progetto modificabile prima di aggiornare; le vecchie copie di recupero automatico non vengono trasferite.', 'de' => 'Speichern Sie vor dem Update ein bearbeitbares Projekt; ältere automatische Wiederherstellungskopien werden nicht übertragen.'],
  ];
}

function studio_language(): string {
  $requested = $_GET['lang'] ?? ($_COOKIE['PDFSTUDIOLANG'] ?? 'it');
  return is_string($requested) && in_array($requested, ['it', 'en', 'de'], true) ? $requested : 'it';
}

ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');
header('X-Frame-Options: SAMEORIGIN');
if (PHP_VERSION_ID < 70200) {
  http_response_code(500);
  exit('PDF Studio requires PHP 7.2 or newer.');
}
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_name('PDFSTUDIO');
  $studioSessionOptions = [
    'use_strict_mode' => true,
    'use_only_cookies' => true,
    'cookie_httponly' => true,
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  ];
  if (PHP_VERSION_ID >= 70300) $studioSessionOptions['cookie_samesite'] = 'Strict';
  session_start($studioSessionOptions);
  // PHP 7.2 has no native SameSite setting. Append it to the session cookie
  // while preserving any other Set-Cookie headers and existing cookie options.
  if (PHP_VERSION_ID < 70300) {
    $studioCookieHeaders = [];
    foreach (headers_list() as $studioHeader) {
      if (stripos($studioHeader, 'Set-Cookie:') !== 0) continue;
      if (strpos($studioHeader, 'Set-Cookie: ' . session_name() . '=') === 0) $studioHeader .= '; SameSite=Strict';
      $studioCookieHeaders[] = $studioHeader;
    }
    if ($studioCookieHeaders) {
      header_remove('Set-Cookie');
      foreach ($studioCookieHeaders as $studioHeader) header($studioHeader, false);
    }
  }
}
if (!isset($_SESSION['studio_csrf'])) $_SESSION['studio_csrf'] = bin2hex(random_bytes(32));
if (!isset($_SESSION['studio_workspace'])) $_SESSION['studio_workspace'] = bin2hex(random_bytes(24));
$studioBoot = ['csrf' => $_SESSION['studio_csrf'], 'version' => PDFSTUDIO_VERSION, 'release' => studio_release_info(), 'language' => studio_language(), 'translations' => studio_translations()];
$studioWorkspace = $_SESSION['studio_workspace'];
session_write_close();
if (is_file(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
}

final class StudioError extends RuntimeException {
  /** @var int */
  public $status;

  public function __construct(string $message, int $status = 400) {
    parent::__construct($message);
    $this->status = $status;
  }
}

function studio_json(array $data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=UTF-8');
  $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
  if ($json === false) throw new RuntimeException('JSON encoding failed: ' . json_last_error_msg());
  echo $json;
  exit;
}

function studio_contains(string $haystack, string $needle): bool {
  return $needle === '' || strpos($haystack, $needle) !== false;
}

function studio_starts_with(string $haystack, string $needle): bool {
  return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
}

function studio_ends_with(string $haystack, string $needle): bool {
  return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
}

function studio_enabled(string $fn): bool {
  return function_exists($fn) && !in_array($fn, array_map('trim', explode(',', (string)ini_get('disable_functions'))), true);
}

function studio_ini_bytes(string $value): int {
  $value = trim($value);
  if ($value === '' || $value === '0' || $value === '-1') return PHP_INT_MAX;
  $units = ['g' => 1073741824, 'm' => 1048576, 'k' => 1024];
  $factor = $units[strtolower(substr($value, -1))] ?? 1;
  return (int)((float)$value * $factor);
}

function studio_binary(string $name): ?string {
  static $cache = [];
  if (array_key_exists($name, $cache)) return $cache[$name];
  $names = [
    'qpdf' => ['qpdf'], 'gs' => ['gs', 'gswin64c'],
    'pdfinfo' => ['pdfinfo'], 'pdftotext' => ['pdftotext'],
    'pdfimages' => ['pdfimages'], 'pdftoppm' => ['pdftoppm'],
    'tesseract' => ['tesseract'], 'libreoffice' => ['libreoffice', 'soffice'],
    'chromium' => ['chromium', 'chromium-browser', 'google-chrome', 'google-chrome-stable'],
    'prlimit' => ['prlimit'],
    'setsid' => ['setsid'],
  ];
  if (!isset($names[$name]) || !studio_enabled('proc_open')) return $cache[$name] = null;
  $dirs = array_unique(array_merge(['/usr/bin', '/usr/local/bin', '/bin', '/snap/bin'], explode(PATH_SEPARATOR, (string)getenv('PATH'))));
  foreach ($dirs as $dir) {
    if ($dir === '' || !studio_starts_with($dir, '/')) continue;
    foreach ($names[$name] as $candidate) {
      $path = realpath($dir . '/' . $candidate);
      if ($path !== false && is_file($path) && is_executable($path)) return $cache[$name] = $path;
    }
  }
  return $cache[$name] = null;
}

/** PHP 7.4+ executes argument arrays directly; PHP 7.2/7.3 uses a fully quoted
 * POSIX exec command. Only binaries in the fixed tool map can be executed. */
function studio_run(string $tool, array $args, string $cwd, int $seconds = PDFSTUDIO_PROCESS_SECONDS, bool $check = true, array $accepted = [0]): array {
  $binary = studio_binary($tool);
  if (!$binary) throw new StudioError(ucfirst($tool) . ' was not detected, or PHP proc_open is disabled.', 503);
  foreach ($args as $arg) {
    if (!is_scalar($arg) || studio_contains((string)$arg, "\0")) throw new StudioError('Invalid processing parameter.');
  }
  $command = array_merge([$binary], array_map('strval', $args));
  $limiter = studio_binary('prlimit');
  if ($limiter && $tool !== 'prlimit' && $tool !== 'chromium') {
    $command = array_merge([$limiter, '--as=2147483648', '--cpu=' . ($seconds + 5), '--fsize=' . PDFSTUDIO_MAX_JOB_BYTES, '--'], $command);
  }
  $sessionBinary = studio_binary('setsid');
  if ($sessionBinary && $tool !== 'setsid') $command = array_merge([$sessionBinary, '--wait'], $command);
  $env = ['PATH' => '/usr/local/bin:/usr/bin:/bin', 'HOME' => $cwd, 'TMPDIR' => $cwd, 'LC_ALL' => 'C.UTF-8'];
  if (getenv('TESSDATA_PREFIX')) $env['TESSDATA_PREFIX'] = (string)getenv('TESSDATA_PREFIX');
  $pipes = [];
  $launchCommand = $command;
  if (PHP_VERSION_ID < 70400) {
    // Quote every argument separately. exec replaces the shell, preserving the
    // process ID used for timeout handling and process-group termination.
    $launchCommand = 'exec ' . implode(' ', array_map('escapeshellarg', $command));
  }
  $proc = proc_open($launchCommand, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd, $env, ['bypass_shell' => true]);
  if (!is_resource($proc)) throw new StudioError('The server could not start ' . $tool . '.', 503);
  fclose($pipes[0]);
  stream_set_blocking($pipes[1], false);
  stream_set_blocking($pipes[2], false);
  $out = ''; $err = ''; $start = microtime(true); $exit = -1; $lastBudgetCheck = 0.0;
  try {
    while (true) {
      $out .= stream_get_contents($pipes[1]);
      $err .= stream_get_contents($pipes[2]);
      if (strlen($out) + strlen($err) > 2097152) throw new StudioError('The processor produced an excessive diagnostic report.', 422);
      $status = proc_get_status($proc);
      if (!$status['running']) { $exit = $status['exitcode']; break; }
      if (microtime(true) - $start > $seconds) throw new StudioError('The ' . $tool . ' operation exceeded the server time limit. Try fewer pages or a smaller file.', 408);
      if (connection_aborted()) throw new StudioError('The operation was cancelled.', 499);
      if (studio_contains(basename($cwd), 'job-') && microtime(true) - $lastBudgetCheck > 0.5) {
        studio_job_budget($cwd);
        $lastBudgetCheck = microtime(true);
      }
      usleep(20000);
    }
    $out .= stream_get_contents($pipes[1]);
    $err .= stream_get_contents($pipes[2]);
  } catch (Throwable $e) {
    if ($sessionBinary && function_exists('posix_kill') && isset($status['pid'])) @posix_kill(-$status['pid'], 15);
    proc_terminate($proc, 15);
    usleep(50000);
    $status = proc_get_status($proc);
    if ($status['running']) {
      if ($sessionBinary && function_exists('posix_kill')) @posix_kill(-$status['pid'], 9);
      proc_terminate($proc, 9);
    }
    throw $e;
  } finally {
    foreach ([1, 2] as $i) if (is_resource($pipes[$i])) fclose($pipes[$i]);
    $closed = proc_close($proc);
    if ($exit < 0) $exit = $closed;
  }
  if ($err !== '') error_log('PDF Studio ' . $tool . ': ' . substr($err, 0, 60000));
  if ($check && !in_array($exit, $accepted, true)) {
    $message = 'The ' . $tool . ' operation failed. The document may be malformed, unsupported, or password protected.';
    if (preg_match('/invalid password|incorrect password|password required/i', $err . $out)) $message = 'The PDF password is missing or incorrect.';
    if (preg_match('/Error opening data file|Failed loading language/i', $err . $out)) $message = 'The selected OCR language data is not installed on the server.';
    if (studio_contains($err, 'No usable sandbox') || studio_contains($err, '--no-sandbox')) $message = 'Chromium requires a working server sandbox. Run PHP as an unprivileged user and enable Chromium sandboxing.';
    throw new StudioError($message, 422);
  }
  return ['code' => $exit, 'stdout' => $out, 'stderr' => $err];
}

function studio_package_version(string $package, string $class): ?string {
  try { if (!class_exists($class)) return null; } catch (Throwable $ignored) { return null; }
  if (class_exists('Composer\\InstalledVersions')) {
    try { return Composer\InstalledVersions::getPrettyVersion($package) ?? 'installed'; } catch (Throwable $ignored) {}
  }
  return 'installed';
}

function studio_release_info(): array {
  return ['version' => PDFSTUDIO_VERSION, 'minimumPhp' => '7.2', 'releases' => PDFSTUDIO_RELEASE_HISTORY];
}

function studio_install_help(string $tool): array {
  $php = 'php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
  $extensionNote = 'Install extensions for the PHP version serving this page (' . PHP_VERSION . '). These commands use matching versioned package names; that PHP version must be available in your configured Ubuntu repositories.';
  $restartNote = 'After installing PHP extensions, restart your PHP handler: sudo systemctl restart ' . $php . '-fpm for PHP-FPM, or sudo systemctl restart apache2 for Apache. Then refresh System capabilities.';
  $composerNotes = ['Run the Composer command in the directory containing index.php, as the application owner. Composer must run with the same PHP version as this page (' . PHP_VERSION . '); do not ignore its PHP requirements.'];
  $composerBase = ['sudo apt update', 'sudo apt install -y composer ' . $php . '-cli ' . $php . '-xml ' . $php . '-mbstring ' . $php . '-gd'];
  $guides = [
    'qpdf' => ['label' => 'qpdf', 'commands' => ['sudo apt update', 'sudo apt install -y qpdf'], 'notes' => ['Some advanced options require a newer qpdf than older Ubuntu releases provide.']],
    'gs' => ['label' => 'Ghostscript', 'commands' => ['sudo apt update', 'sudo apt install -y ghostscript'], 'notes' => []],
    'pdfinfo' => ['label' => 'Poppler: PDF information', 'commands' => ['sudo apt update', 'sudo apt install -y poppler-utils'], 'notes' => []],
    'pdftotext' => ['label' => 'Poppler: text extraction', 'commands' => ['sudo apt update', 'sudo apt install -y poppler-utils'], 'notes' => []],
    'pdfimages' => ['label' => 'Poppler: embedded images', 'commands' => ['sudo apt update', 'sudo apt install -y poppler-utils'], 'notes' => []],
    'pdftoppm' => ['label' => 'Poppler: page rendering', 'commands' => ['sudo apt update', 'sudo apt install -y poppler-utils'], 'notes' => []],
    'tesseract' => ['label' => 'Tesseract OCR', 'commands' => ['sudo apt update', 'sudo apt install -y tesseract-ocr tesseract-ocr-eng'], 'notes' => ['For Thai recognition, also install: sudo apt install -y tesseract-ocr-tha. Other languages have their own tesseract-ocr language packages.']],
    'libreoffice' => ['label' => 'LibreOffice', 'commands' => ['sudo apt update', 'sudo apt install -y libreoffice ' . $php . '-zip ' . $php . '-xml'], 'notes' => [$extensionNote, $restartNote, 'Office conversion also needs the PHP ZIP and DOM extensions.']],
    'chromium' => ['label' => 'Chromium', 'commands' => ['sudo apt update', 'sudo apt install -y chromium-browser'], 'notes' => ['On current Ubuntu releases this installs Chromium through Snap. The PHP service must be able to launch it and access its private temporary job directory; Snap confinement can require additional server configuration.', 'Run PHP as an unprivileged user with a working Chromium sandbox. Installing Chromium alone does not fix a blocked sandbox.']],
    'mpdf' => ['label' => 'mPDF', 'commands' => array_merge($composerBase, ['composer require mpdf/mpdf']), 'notes' => array_merge($composerNotes, [$extensionNote, $restartNote])],
    'dompdf' => ['label' => 'Dompdf', 'commands' => array_merge($composerBase, ['composer require dompdf/dompdf']), 'notes' => array_merge($composerNotes, [$extensionNote, $restartNote])],
    'fpdi' => ['label' => 'FPDI / FPDF', 'commands' => array_merge($composerBase, ['composer require setasign/fpdi setasign/fpdf']), 'notes' => $composerNotes],
    'tc_pdf' => ['label' => 'TCPDF library (optional)', 'commands' => array_merge($composerBase, ['composer require tecnickcom/tc-lib-pdf']), 'notes' => array_merge($composerNotes, ['Current tc-lib-pdf releases require PHP 8.2 or newer. On PHP 7.2, use mPDF or Dompdf for document export. This optional library is detected only; no additional PDF Studio tool depends on it.'])],
    'imagick' => ['label' => 'PHP Imagick (optional)', 'commands' => ['sudo apt update', 'sudo apt install -y ' . $php . '-imagick'], 'notes' => [$extensionNote, $restartNote, 'This optional extension is detected only; browser image tools remain available without it.']],
    'zip' => ['label' => 'PHP ZIP', 'commands' => ['sudo apt update', 'sudo apt install -y ' . $php . '-zip'], 'notes' => [$extensionNote, $restartNote]],
    'dom' => ['label' => 'PHP DOM / XML', 'commands' => ['sudo apt update', 'sudo apt install -y ' . $php . '-xml'], 'notes' => [$extensionNote, $restartNote]],
  ];
  return $guides[$tool] ?? ['label' => $tool, 'commands' => [], 'notes' => []];
}

function studio_capabilities(): array {
  static $caps = null;
  if ($caps !== null) return $caps;
  $spec = [
    'qpdf' => [['--version'], ['Structural compression', 'Merge', 'AES password protection', 'Decryption', 'Validation', 'Repair', 'Fast Web View']],
    'gs' => [['--version'], ['Lossy image compression', 'PDF rewrite']],
    'pdfinfo' => [['-v'], ['Page limits', 'PDF diagnostics']],
    'pdftotext' => [['-v'], ['Text extraction with layout']],
    'pdfimages' => [['-v'], ['Extract original embedded images']],
    'pdftoppm' => [['-v'], ['Page rasterization for OCR']],
    'tesseract' => [['--version'], ['Server OCR', 'Searchable PDF']],
    'libreoffice' => [['--version'], ['DOCX, ODT, XLSX and PPTX to PDF']],
    'chromium' => [['--version'], ['HTML/CSS print rendering']],
  ];
  $tools = [];
  foreach ($spec as $tool => [$args, $features]) {
    $available = (bool)studio_binary($tool);
    $version = null;
    if ($available) {
      try {
        $result = studio_run($tool, $args, sys_get_temp_dir(), 6, false);
        $version = substr(trim(strtok(trim($result['stdout'] . "\n" . $result['stderr']), "\n") ?: 'installed'), 0, 150);
      } catch (Throwable $ignored) { $available = false; }
    }
    $tools[$tool] = ['available' => $available, 'version' => $version, 'features' => $features];
  }
  $packageFeatures = ['mpdf' => ['HTML to PDF', 'Headers, footers, page numbering'], 'dompdf' => ['HTML to PDF'], 'fpdi' => ['Alternative PDF merge']];
  foreach (['mpdf' => ['mpdf/mpdf', 'Mpdf\\Mpdf'], 'dompdf' => ['dompdf/dompdf', 'Dompdf\\Dompdf'], 'fpdi' => ['setasign/fpdi', 'setasign\\Fpdi\\Fpdi'], 'tc_pdf' => ['tecnickcom/tc-lib-pdf', 'Com\\Tecnick\\Pdf\\Tcpdf']] as $key => [$package, $class]) {
    $version = studio_package_version($package, $class);
    $tools[$key] = ['available' => $version !== null, 'version' => $version, 'features' => $packageFeatures[$key] ?? ['PDF generation library detected']];
  }
  $tools['imagick'] = ['available' => extension_loaded('imagick'), 'version' => extension_loaded('imagick') ? phpversion('imagick') : null, 'features' => ['Image processing extension detected']];
  $tools['zip'] = ['available' => class_exists('ZipArchive'), 'version' => phpversion('zip') ?: null, 'features' => ['Office archive security inspection']];
  $tools['dom'] = ['available' => class_exists('DOMDocument'), 'version' => phpversion('dom') ?: null, 'features' => ['Safe HTML sanitization']];
  if ($tools['libreoffice']['available'] && (!$tools['zip']['available'] || !$tools['dom']['available'])) {
    $tools['libreoffice']['available'] = false;
    $tools['libreoffice']['reason'] = 'LibreOffice is installed; PHP ZIP and DOM extensions are also required for safe Office validation.';
  }
  if ($tools['chromium']['available'] && function_exists('posix_geteuid') && posix_geteuid() === 0) {
    $tools['chromium']['available'] = false;
    $tools['chromium']['reason'] = 'Chromium rendering requires PHP to run as an unprivileged user with browser sandbox support.';
  }
  if ($tools['tesseract']['available']) {
    try {
      $result = studio_run('tesseract', ['--list-langs'], sys_get_temp_dir(), 6);
      $tools['tesseract']['languages'] = array_values(array_filter(preg_split('/\R/', trim($result['stdout'])), function($line) {
        return preg_match('/^[a-zA-Z0-9_\/.-]+$/', $line) === 1 && $line !== 'osd';
      }));
    } catch (Throwable $ignored) { $tools['tesseract']['languages'] = []; }
  }
  if ($tools['qpdf']['available']) {
    try {
      $result = studio_run('qpdf', ['--help=all'], sys_get_temp_dir(), 6);
      $tools['qpdf']['metadataRemoval'] = studio_contains($result['stdout'], '--remove-info') && studio_contains($result['stdout'], '--remove-metadata');
      $tools['qpdf']['namedPasswords'] = studio_contains($result['stdout'], '--user-password');
    } catch (Throwable $ignored) { $tools['qpdf']['metadataRemoval'] = false; $tools['qpdf']['namedPasswords'] = false; }
  }
  $html = [];
  if ($tools['dom']['available']) foreach (['mpdf', 'dompdf', 'chromium'] as $name) if ($tools[$name]['available']) $html[] = $name;
  $merge = [];
  foreach (['qpdf', 'fpdi'] as $name) if ($tools[$name]['available']) $merge[] = $name;
  foreach ($tools as $name => &$details) {
    $guide = studio_install_help($name);
    $details['source'] = 'server';
    $details['label'] = $guide['label'];
    $details['install'] = $guide;
    if (isset($spec[$name]) && !studio_enabled('proc_open')) {
      $details['reason'] = 'PHP proc_open is disabled. Ask the server administrator to enable it for this application; installing a package alone will not enable external tools.';
    } elseif (isset($spec[$name]) && !$details['available'] && !isset($details['reason'])) {
      $details['reason'] = 'This tool is missing from the server PATH or could not be started by PHP.';
    }
  }
  unset($details);
  $maxFile = min(PDFSTUDIO_MAX_FILE, studio_ini_bytes((string)ini_get('upload_max_filesize')), max(0, studio_ini_bytes((string)ini_get('post_max_size')) - 65536));
  return $caps = ['version' => PDFSTUDIO_VERSION, 'php' => PHP_VERSION, 'limits' => ['maxFile' => $maxFile, 'maxLocalFile' => PDFSTUDIO_MAX_FILE, 'maxPages' => PDFSTUDIO_MAX_PAGES, 'maxFiles' => min(PDFSTUDIO_MAX_FILES, (int)ini_get('max_file_uploads')), 'maxOutput' => PDFSTUDIO_MAX_JOB_BYTES, 'processSeconds' => PDFSTUDIO_PROCESS_SECONDS], 'tools' => $tools, 'engines' => ['html' => $html, 'merge' => $merge], 'privacy' => 'Files are processed in private temporary directories and removed after the response.'];
}

function studio_need(string $tool): void {
  $caps = studio_capabilities();
  if (empty($caps['tools'][$tool]['available'])) throw new StudioError($caps['tools'][$tool]['reason'] ?? ('This feature requires ' . $tool . ', which was not detected on this server.'), 503);
}

function studio_delete_tree(string $path): void {
  if (is_link($path) || is_file($path)) { @unlink($path); return; }
  if (!is_dir($path)) return;
  foreach (new DirectoryIterator($path) as $file) {
    if ($file->isDot()) continue;
    studio_delete_tree($file->getPathname());
  }
  @rmdir($path);
}

function studio_job_budget(string $job): void {
  $total = 0; $count = 0;
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($job, FilesystemIterator::SKIP_DOTS));
  foreach ($iterator as $file) {
    if ($file->isLink()) continue;
    if ($file->isFile()) $total += $file->getSize();
    if (++$count > 15000 || $total > PDFSTUDIO_MAX_JOB_BYTES) throw new StudioError('The generated files exceed the server workspace limit.', 413);
  }
}

function studio_temp_root(): string {
  $root = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/pdfstudio-' . substr(hash('sha256', __DIR__), 0, 20);
  if (is_link($root)) throw new StudioError('The private processing directory could not be initialized safely.', 500);
  if (!is_dir($root) && !@mkdir($root, 0700)) throw new StudioError('The server has no writable private temporary directory.', 503);
  @chmod($root, 0700);
  $actual = realpath($root);
  $public = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
  if ($actual === false || ($public && ($actual === $public || studio_starts_with($actual, $public . DIRECTORY_SEPARATOR)))) throw new StudioError('The server temporary directory must be outside the public web root.', 503);
  if (random_int(1, 20) === 1) {
    foreach (new DirectoryIterator($root) as $file) {
      if ($file->isDot() || $file->isLink() || !$file->isDir()) continue;
      if (preg_match('/^session-[a-f0-9]{48}$/', $file->getFilename()) && $file->getMTime() < time() - PDFSTUDIO_STALE_SECONDS) studio_delete_tree($file->getPathname());
    }
  }
  return $actual;
}

function studio_job(): string {
  global $studioWorkspace;
  $session = studio_temp_root() . '/session-' . $studioWorkspace;
  if (is_link($session)) throw new StudioError('The private session directory is invalid.', 500);
  if (!is_dir($session) && !mkdir($session, 0700)) throw new StudioError('The server could not create a private workspace.', 503);
  touch($session);
  $job = $session . '/job-' . bin2hex(random_bytes(20));
  if (!mkdir($job, 0700)) throw new StudioError('The server could not create a processing workspace.', 503);
  register_shutdown_function(function() use ($job, $session) {
    studio_delete_tree($job);
    @rmdir($session);
  });
  return $job;
}

function studio_inputs(string $job, bool $office = false): array {
  $raw = $_FILES['files'] ?? $_FILES['file'] ?? null;
  if (!is_array($raw)) throw new StudioError('Choose a file to process. The PHP upload limit may also be smaller than this file.');
  $inputs = [];
  $multiple = is_array($raw['name']);
  $count = $multiple ? count($raw['name']) : 1;
  if ($count > PDFSTUDIO_MAX_FILES) throw new StudioError('Too many uploaded files; the limit is ' . PDFSTUDIO_MAX_FILES . '.');
  $total = 0;
  for ($i = 0; $i < $count; $i++) {
    $name = (string)($multiple ? $raw['name'][$i] : $raw['name']);
    $tmp = (string)($multiple ? $raw['tmp_name'][$i] : $raw['tmp_name']);
    $error = (int)($multiple ? $raw['error'][$i] : $raw['error']);
    if ($error !== UPLOAD_ERR_OK) throw new StudioError(in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'The file exceeds the server upload limit.' : 'The upload did not complete; please select the file again.', 413);
    if (!is_uploaded_file($tmp)) throw new StudioError('The upload could not be verified.');
    $bytes = filesize($tmp);
    if ($bytes === false || $bytes < 8 || $bytes > PDFSTUDIO_MAX_FILE) throw new StudioError('The file is empty or exceeds the ' . round(PDFSTUDIO_MAX_FILE / 1048576) . ' MB limit.', 413);
    $total += $bytes;
    if ($total > PDFSTUDIO_MAX_JOB_BYTES) throw new StudioError('The combined uploads exceed the processing limit.', 413);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if ($office) {
      if (!in_array($ext, ['docx', 'odt', 'xlsx', 'pptx'], true)) throw new StudioError('Office conversion accepts DOCX, ODT, XLSX and PPTX files.');
      if (file_get_contents($tmp, false, null, 0, 4) !== "PK\x03\x04") throw new StudioError('The Office file does not have a valid ZIP document signature.');
    } else {
      if ($ext !== 'pdf') throw new StudioError('This operation accepts PDF files only.');
      $head = file_get_contents($tmp, false, null, 0, 1024);
      $mime = class_exists('finfo') ? (new finfo(FILEINFO_MIME_TYPE))->file($tmp) : null;
      if (!preg_match('/%PDF-[12]\.\d/', (string)$head) || ($mime !== null && !in_array($mime, ['application/pdf', 'application/octet-stream'], true))) throw new StudioError('The upload does not appear to be a PDF document.');
    }
    $path = $job . '/input-' . ($i + 1) . '.' . $ext;
    if (!move_uploaded_file($tmp, $path)) throw new StudioError('The server could not store the upload in its private workspace.', 503);
    chmod($path, 0600);
    $inputs[] = ['path' => $path, 'name' => $name, 'size' => $bytes, 'extension' => $ext];
  }
  return $inputs;
}

function studio_one(array $inputs): array {
  if (count($inputs) !== 1) throw new StudioError('Choose one document for this operation.');
  return $inputs[0];
}

function studio_pdf_count(string $path, string $job, string $password = ''): int {
  if (studio_binary('qpdf')) {
    $secretFile = $password !== '' ? $job . '/page-count-password-' . bin2hex(random_bytes(10)) . '.txt' : null;
    $args = [];
    if ($secretFile !== null) {
      file_put_contents($secretFile, $password); chmod($secretFile, 0600);
      $args[] = '--password-file=' . $secretFile;
    }
    try { $result = studio_run('qpdf', array_merge($args, ['--show-npages', $path]), $job, 20, true, [0, 3]); }
    finally { if ($secretFile !== null) @unlink($secretFile); }
    $count = (int)trim($result['stdout']);
  } elseif (studio_binary('pdfinfo')) {
    if ($password !== '') throw new StudioError('Encrypted server processing requires qpdf so passwords can be supplied privately. Decrypt the document first.', 503);
    $result = studio_run('pdfinfo', [$path], $job, 20);
    preg_match('/^Pages:\s+(\d+)/m', $result['stdout'], $match);
    $count = (int)($match[1] ?? 0);
  } elseif (studio_package_version('setasign/fpdi', 'setasign\\Fpdi\\Fpdi') !== null) {
    $pdf = new setasign\Fpdi\Fpdi();
    $count = $pdf->setSourceFile($path);
  } else {
    throw new StudioError('Server PDF processing requires qpdf, Poppler pdfinfo, or FPDI to enforce the page limit.', 503);
  }
  if ($count < 1) throw new StudioError('The PDF page tree could not be read.', 422);
  if ($count > PDFSTUDIO_MAX_PAGES) throw new StudioError('This PDF has ' . $count . ' pages; the server limit is ' . PDFSTUDIO_MAX_PAGES . '.', 413);
  return $count;
}

function studio_option_int(array $options, string $name, int $default, int $min, int $max): int {
  if (!isset($options[$name])) return $default;
  $value = filter_var($options[$name], FILTER_VALIDATE_INT);
  if ($value === false || $value < $min || $value > $max) throw new StudioError($name . ' must be between ' . $min . ' and ' . $max . '.');
  return $value;
}

function studio_password(array $options, string $name): string {
  $value = (string)($options[$name] ?? '');
  if (strlen($value) > 256 || studio_contains($value, "\0") || studio_contains($value, "\r") || studio_contains($value, "\n")) throw new StudioError('Passwords must contain at most 256 bytes and no line breaks.');
  return $value;
}

function studio_ranges(string $ranges, int $count): string {
  if ($ranges === '' || $ranges === 'all' || $ranges === '1-z') return '1-' . $count;
  if (strlen($ranges) > 4000 || !preg_match('/^[0-9,\-\s]+$/', $ranges)) throw new StudioError('Use page ranges such as 1-4,7,10-12.');
  $pages = [];
  foreach (explode(',', preg_replace('/\s+/', '', $ranges)) as $range) {
    if (!preg_match('/^(\d+)(?:-(\d+))?$/', $range, $m)) throw new StudioError('The page range is invalid.');
    $first = (int)$m[1]; $last = isset($m[2]) ? (int)$m[2] : $first;
    if ($first < 1 || $last < $first || $last > $count) throw new StudioError('A selected page is outside this document.');
    foreach (range($first, $last) as $page) $pages[] = $page;
    if (count($pages) > PDFSTUDIO_MAX_PAGES) throw new StudioError('Too many selected pages.');
  }
  return implode(',', $pages);
}

/** A small streaming, uncompressed ZIP writer avoids an extra mandatory ZIP
 * library for generated images. Only known files in the job can be archived. */
function studio_zip(array $files, string $output): void {
  $stream = fopen($output, 'wb');
  if (!$stream) throw new StudioError('The ZIP could not be created.', 503);
  $central = ''; $count = 0;
  try {
    foreach ($files as $path) {
      if (!is_file($path) || is_link($path)) continue;
      $name = basename($path);
      $size = filesize($path);
      if ($size > PDFSTUDIO_MAX_JOB_BYTES || ftell($stream) + $size > PDFSTUDIO_MAX_JOB_BYTES) throw new StudioError('The extracted images exceed the output limit.', 413);
      $crc = (int)hexdec(hash_file('crc32b', $path));
      $offset = ftell($stream);
      fwrite($stream, pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, strlen($name), 0) . $name);
      $input = fopen($path, 'rb');
      stream_copy_to_stream($input, $stream);
      fclose($input);
      $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, strlen($name), 0, 0, 0, 0, 0, $offset) . $name;
      $count++;
      if ($count > 10000) throw new StudioError('Too many embedded images.', 413);
    }
    if (!$count) throw new StudioError('No embedded images were found in this PDF.', 422);
    $offset = ftell($stream);
    fwrite($stream, $central . pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, strlen($central), $offset, 0));
  } finally { fclose($stream); }
}

function studio_download(string $path, string $name, string $mime, ?int $before = null): void {
  if (!is_file($path) || is_link($path)) throw new StudioError('The processor did not produce a downloadable file.', 422);
  $size = filesize($path);
  if (!$size || $size > PDFSTUDIO_MAX_JOB_BYTES) throw new StudioError('The result is empty or exceeds the output limit.', 413);
  if ($mime === 'application/pdf' && !studio_starts_with((string)file_get_contents($path, false, null, 0, 5), '%PDF-')) throw new StudioError('The processor returned an invalid PDF.', 422);
  $name = preg_replace('/[^A-Za-z0-9_.-]/', '-', $name);
  header('Content-Type: ' . $mime);
  header('Content-Disposition: attachment; filename="' . $name . '"');
  header('Content-Length: ' . $size);
  if ($before !== null) header('X-PDFStudio-Original-Size: ' . $before);
  while (ob_get_level() > 0) ob_end_clean();
  $stream = fopen($path, 'rb');
  while (!feof($stream) && !connection_aborted()) { echo fread($stream, 65536); flush(); }
  fclose($stream);
  exit;
}

function studio_css(string $css): string {
  if (strlen($css) > 262144) throw new StudioError('The document stylesheet is too large.', 413);
  $css = preg_replace('~/\*.*?\*/~s', '', $css);
  // Inspect CSS after decoding escapes as well. This admits ordinary escaped
  // quotes/newlines in margin text, while detecting u\72l and escaped @import.
  $probe = preg_replace('/\\\\(?:\r\n|[\r\n\f])/', '', $css);
  $probe = preg_replace_callback('/\\\\([0-9a-f]{1,6})(?:\r\n|[ \t\r\n\f])?/i', function($match) {
    $point = hexdec($match[1]);
    if ($point === 0 || $point > 0x10ffff || ($point >= 0xd800 && $point <= 0xdfff)) throw new StudioError('The document stylesheet contains an invalid CSS escape.');
    return html_entity_decode('&#' . $point . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }, $probe);
  $probe = preg_replace('/\\\\(.)/s', '$1', $probe);
  $probe = preg_replace('~/\*.*?\*/~s', '', $probe);
  if (preg_match('~[\x00<>]~', $css) || preg_match('~[\x00]|url\s*\(|@import|@font-face|@namespace|expression\s*\(|behavior\s*:|-moz-binding|(?:https?|file|ftp|data)\s*:|//|image-set\s*\(|(?:^|[;{])\s*src\s*:~i', $probe)) {
    throw new StudioError('PDF export permits inline document styles and embedded images only. Remove external fonts, URLs or executable CSS.');
  }
  return $css;
}

function studio_image_uri(string $uri): string {
  if (!preg_match('~^data:image/(png|jpeg|gif|webp);base64,([A-Za-z0-9+/=\r\n]+)$~i', $uri, $m)) throw new StudioError('PDF export accepts embedded PNG, JPEG, GIF and WebP images only. Remote and SVG resources are not fetched.');
  $bytes = base64_decode($m[2], true);
  if ($bytes === false || strlen($bytes) > 8388608) throw new StudioError('An embedded image exceeds the 8 MB export limit.', 413);
  $size = @getimagesizefromstring($bytes);
  if (!$size || $size[0] * $size[1] > PDFSTUDIO_MAX_IMAGE_PIXELS || !in_array($size['mime'], ['image/png', 'image/jpeg', 'image/gif', 'image/webp'], true)) throw new StudioError('An embedded image is invalid or has too many pixels.');
  return 'data:' . $size['mime'] . ';base64,' . base64_encode($bytes);
}

/** Resource-free HTML: DOM decodes obfuscated attributes before inspection.
 * Scripts, SVG, plugins, forms, active content and filesystem/remote resources
 * never reach a renderer. Links may point outward but are never fetched. */
function studio_html(string $html): string {
  if (!class_exists('DOMDocument')) throw new StudioError('HTML export requires the PHP DOM extension.', 503);
  if (strlen($html) > PDFSTUDIO_MAX_HTML) throw new StudioError('The document HTML exceeds the export limit.', 413);
  if (preg_match('/<!DOCTYPE[^>]*(?:SYSTEM|PUBLIC)|<!ENTITY/i', $html)) throw new StudioError('External XML entities are not permitted.');
  $dom = new DOMDocument('1.0', 'UTF-8');
  $old = libxml_use_internal_errors(true);
  try { $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING); }
  finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
  if (!$loaded) throw new StudioError('The document HTML could not be parsed.');
  $allowed = array_fill_keys(explode(' ', 'html head body title style p div span br h1 h2 h3 h4 h5 h6 b strong i em u s strike del ins sub sup small big font a blockquote pre code hr ul ol li table thead tbody tfoot tr td th caption col colgroup img figure figcaption section article header footer main aside nav dl dt dd address mark'), true);
  $attributes = array_fill_keys(explode(' ', 'class id style title dir lang align valign width height border cellpadding cellspacing colspan rowspan start type face color size alt name'), true);
  $walk = function(DOMNode $node) use (&$walk, $allowed, $attributes) {
    foreach (iterator_to_array($node->childNodes) as $child) {
      if ($child instanceof DOMElement) {
        $tag = strtolower($child->tagName);
        if (!isset($allowed[$tag])) { $node->removeChild($child); continue; }
        if ($tag === 'style') $child->nodeValue = studio_css($child->textContent);
        foreach (iterator_to_array($child->attributes) as $attr) {
          $key = strtolower($attr->name); $value = trim($attr->value);
          if ($tag === 'img' && $key === 'src') {
            $child->setAttribute('src', studio_image_uri($value));
          } elseif ($tag === 'a' && $key === 'href') {
            if (!preg_match('~^(?:https?://|mailto:|tel:|#[A-Za-z0-9_.:-]+$)~i', $value) || preg_match('/[\x00-\x20]/', $value)) $child->removeAttribute($attr->name);
            else $child->setAttribute('rel', 'noopener noreferrer');
          } elseif ($key === 'style') {
            $child->setAttribute('style', studio_css($value));
          } elseif (!isset($attributes[$key]) || studio_starts_with($key, 'on')) {
            $child->removeAttribute($attr->name);
          } elseif (strlen($value) > 2000) {
            $child->removeAttribute($attr->name);
          }
        }
        $walk($child);
      } elseif ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
        $node->removeChild($child);
      }
    }
  };
  $walk($dom);
  return $dom->saveHTML();
}

/** @param array|string|null $value */
function studio_header_html($value): string {
  if (is_string($value)) $value = ['center' => $value];
  if (!is_array($value)) return '';
  $html = '<table width="100%" style="font-size:9pt;border-collapse:collapse"><tr>';
  foreach (['left', 'center', 'right'] as $alignment) {
    $text = htmlspecialchars(substr((string)($value[$alignment] ?? ''), 0, 2000), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $text = str_replace(['{{page}}', '{{pages}}'], ['{PAGENO}', '{nbpg}'], $text);
    $html .= '<td width="33.33%" style="text-align:' . $alignment . '">' . $text . '</td>';
  }
  return $html . '</tr></table>';
}

function studio_page_options(array $options): array {
  $formats = ['A3' => [297, 420], 'A4' => [210, 297], 'A5' => [148, 210], 'Letter' => [215.9, 279.4], 'Legal' => [215.9, 355.6]];
  $format = (string)($options['pageSize'] ?? 'A4');
  if ($format === 'Custom' || $format === 'custom') {
    $width = (float)($options['widthMm'] ?? 210); $height = (float)($options['heightMm'] ?? 297);
    if (!is_finite($width) || !is_finite($height) || $width < 30 || $width > 1000 || $height < 30 || $height > 1000) throw new StudioError('Custom page dimensions must be between 30 and 1000 mm.');
  } else {
    if (!isset($formats[$format])) throw new StudioError('Select A3, A4, A5, Letter, Legal or custom page size.');
    [$width, $height] = $formats[$format];
  }
  if (!empty($options['landscape']) && $width < $height) [$width, $height] = [$height, $width];
  $margins = [];
  foreach (['top', 'right', 'bottom', 'left'] as $side) {
    $value = (float)($options['margins'][$side] ?? 20);
    if (!is_finite($value) || $value < 0 || $value > 100) throw new StudioError('Page margins must be between 0 and 100 mm.');
    $margins[$side] = $value;
  }
  if ($margins['left'] + $margins['right'] >= $width - 5 || $margins['top'] + $margins['bottom'] >= $height - 5) throw new StudioError('The margins leave no room for document content.');
  return ['width' => $width, 'height' => $height, 'margins' => $margins];
}

function studio_html_pdf(array $options, string $job): string {
  $caps = studio_capabilities();
  $engine = (string)($options['engine'] ?? 'auto');
  if ($engine === 'auto') $engine = $caps['engines']['html'][0] ?? '';
  if (!in_array($engine, $caps['engines']['html'], true)) throw new StudioError('HTML-to-PDF requires mPDF, Dompdf or sandboxed Chromium, plus the PHP DOM extension. Browser print remains available.', 503);
  $html = studio_html((string)($options['html'] ?? ''));
  $page = studio_page_options($options); $m = $page['margins'];
  $output = $job . '/document.pdf';
  $meta = is_array($options['metadata'] ?? null) ? $options['metadata'] : [];
  foreach (['title', 'author', 'subject', 'keywords'] as $key) $meta[$key] = substr((string)($meta[$key] ?? ''), 0, 5000);
  if ($engine === 'mpdf') {
    $pdf = new Mpdf\Mpdf(['tempDir' => $job . '/mpdf', 'mode' => 'utf-8', 'format' => [$page['width'], $page['height']], 'margin_top' => $m['top'], 'margin_right' => $m['right'], 'margin_bottom' => $m['bottom'], 'margin_left' => $m['left']]);
    $pdf->SetTitle($meta['title']); $pdf->SetAuthor($meta['author']); $pdf->SetSubject($meta['subject']); $pdf->SetKeywords($meta['keywords']);
    $normalHeader = studio_header_html($options['header'] ?? null);
    $normalFooter = studio_header_html($options['footer'] ?? null);
    if (!empty($options['differentFirst'])) {
      $pdf->DefHTMLHeaderByName('StudioNormalHeader', $normalHeader);
      $pdf->DefHTMLFooterByName('StudioNormalFooter', $normalFooter);
      $pdf->DefHTMLHeaderByName('StudioFirstHeader', studio_header_html($options['firstHeader'] ?? null));
      $pdf->DefHTMLFooterByName('StudioFirstFooter', studio_header_html($options['firstFooter'] ?? null));
      $html = '<style>@page {header: html_StudioNormalHeader;footer: html_StudioNormalFooter;} @page :first {header: html_StudioFirstHeader;footer: html_StudioFirstFooter;}</style>' . $html;
    } else {
      $pdf->SetHTMLHeader($normalHeader); $pdf->SetHTMLFooter($normalFooter);
    }
    $pdf->WriteHTML($html);
    if ($pdf->page > PDFSTUDIO_MAX_PAGES) throw new StudioError('The generated document exceeds the page limit.', 413);
    $pdf->Output($output, 'F');
  } elseif ($engine === 'dompdf') {
    $config = new Dompdf\Options();
    $config->set('isRemoteEnabled', false); $config->set('isPhpEnabled', false); $config->set('isJavascriptEnabled', false);
    $config->set('chroot', $job); $config->set('tempDir', $job); $config->set('fontCache', $job);
    $config->set('allowedProtocols', ['data://' => ['rules' => []]]);
    $pdf = new Dompdf\Dompdf($config);
    $css = '<style>@page {size:' . $page['width'] . 'mm ' . $page['height'] . 'mm;margin:' . $m['top'] . 'mm ' . $m['right'] . 'mm ' . $m['bottom'] . 'mm ' . $m['left'] . 'mm;}</style>';
    $pdf->loadHtml($css . $html, 'UTF-8');
    $pdf->setPaper([0, 0, $page['width'] * 72 / 25.4, $page['height'] * 72 / 25.4]);
    $pdf->render();
    $canvas = $pdf->getCanvas();
    if ($canvas->get_page_count() > PDFSTUDIO_MAX_PAGES) throw new StudioError('The generated document exceeds the page limit.', 413);
    foreach (['Title' => 'title', 'Author' => 'author', 'Subject' => 'subject', 'Keywords' => 'keywords'] as $key => $source) $pdf->addInfo($key, $meta[$source]);
    if (!empty($options['header']) || !empty($options['footer']) || !empty($options['differentFirst'])) {
      $fonts = $pdf->getFontMetrics();
      $font = $fonts->getFont('Helvetica', 'normal');
      $canvas->page_script(function($number, $total, $canvas, $fontMetrics) use ($options, $font, $page, $m) {
        $first = $number === 1 && !empty($options['differentFirst']);
        foreach (['header', 'footer'] as $position) {
          $values = $options[$first ? ('first' . ucfirst($position)) : $position] ?? [];
          if (is_string($values)) $values = ['center' => $values];
          foreach (['left', 'center', 'right'] as $align) {
            $text = substr((string)($values[$align] ?? ''), 0, 1000);
            $text = str_replace(['{{page}}', '{{pages}}'], [(string)$number, (string)$total], $text);
            $width = $fontMetrics->getTextWidth($text, $font, 9);
            if ($align === 'left') $x = $m['left'] * 72 / 25.4;
            elseif ($align === 'right') $x = ($page['width'] - $m['right']) * 72 / 25.4 - $width;
            else $x = ($page['width'] * 72 / 25.4 - $width) / 2;
            $y = $position === 'header' ? max(4, $m['top'] * 72 / 25.4 / 2 - 5) : ($page['height'] - $m['bottom'] / 2) * 72 / 25.4 - 5;
            $canvas->text($x, $y, $text, $font, 9);
          }
        }
      });
    }
    file_put_contents($output, $pdf->output());
  } else {
    $csp = '<meta http-equiv="Content-Security-Policy" content="default-src &apos;none&apos;; img-src data:; style-src &apos;unsafe-inline&apos;; font-src &apos;none&apos;; script-src &apos;none&apos;; connect-src &apos;none&apos;; frame-src &apos;none&apos;">';
    $css = '<style>@page {size:' . $page['width'] . 'mm ' . $page['height'] . 'mm;margin:' . $m['top'] . 'mm ' . $m['right'] . 'mm ' . $m['bottom'] . 'mm ' . $m['left'] . 'mm;} body{print-color-adjust:exact;-webkit-print-color-adjust:exact;}</style>';
    $file = $job . '/document.html';
    // CSP must precede all untrusted markup, including the original head.
    file_put_contents($file, '<!doctype html><html><head><meta charset="utf-8">' . $csp . $css . '</head><body>' . $html . '</body></html>');
    studio_run('chromium', ['--headless', '--disable-gpu', '--disable-dev-shm-usage', '--disable-background-networking', '--disable-component-update', '--disable-default-apps', '--disable-extensions', '--disable-sync', '--no-first-run', '--no-default-browser-check', '--host-resolver-rules=MAP * ~NOTFOUND', '--proxy-server=http://127.0.0.1:9', '--proxy-bypass-list=<-loopback>', '--user-data-dir=' . $job . '/chrome-profile', '--no-pdf-header-footer', '--print-to-pdf=' . $output, 'file://' . $file], $job);
    studio_pdf_count($output, $job);
  }
  return $output;
}

function studio_office_validate(string $path, string $extension): void {
  if (!class_exists('ZipArchive') || !class_exists('DOMDocument')) throw new StudioError('Safe Office conversion also requires the PHP ZIP and DOM extensions.', 503);
  $zip = new ZipArchive();
  $readOnly = defined('ZipArchive::RDONLY') ? constant('ZipArchive::RDONLY') : 0;
  if ($zip->open($path, $readOnly) !== true) throw new StudioError('The Office document archive is invalid.', 422);
  try {
    if ($zip->numFiles > 5000) throw new StudioError('The Office document contains too many archive entries.', 413);
    $requiredParts = ['docx' => 'word/document.xml', 'xlsx' => 'xl/workbook.xml', 'pptx' => 'ppt/presentation.xml'];
    $required = $requiredParts[$extension] ?? 'content.xml';
    if ($zip->locateName($required) === false) throw new StudioError('The document structure does not match its extension.', 422);
    $total = 0;
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $stat = $zip->statIndex($i); $name = $stat['name'];
      $opsys = 0; $externalAttributes = 0;
      if ($zip->getExternalAttributesIndex($i, $opsys, $externalAttributes) && (($externalAttributes >> 16) & 0170000) === 0120000) throw new StudioError('The Office archive contains a symbolic link.', 422);
      if (!empty($stat['encryption_method'])) throw new StudioError('Encrypted Office documents are not supported.', 422);
      $total += $stat['size'];
      if ($total > PDFSTUDIO_MAX_JOB_BYTES || $stat['size'] > 67108864 || ($stat['comp_size'] > 0 && $stat['size'] / $stat['comp_size'] > 1000)) throw new StudioError('The Office archive exceeds safe expansion limits.', 413);
      if (studio_contains($name, '..') || studio_starts_with($name, '/') || studio_contains($name, '\\') || studio_contains($name, ':')) throw new StudioError('The Office archive contains an unsafe path.', 422);
      if (preg_match('~(?:vbaproject|(?:^|/)scripts/|/embeddings/|\.(?:svg|eps|ps)$|\.html?$|\.exe$|\.dll$|\.js$|\.bas$)~i', $name)) throw new StudioError('Office conversion does not accept embedded programs, macros, HTML, SVG or PostScript content.', 422);
      if (!preg_match('/\.(?:xml|rels)$/i', $name)) continue;
      $xml = $zip->getFromIndex($i);
      if ($xml === false || preg_match('/<!DOCTYPE|<!ENTITY/i', $xml)) throw new StudioError('The Office XML contains forbidden entities.', 422);
      if (preg_match('/(?:\bDDEAUTO\b|\bDDE\s*\(|\bWEBSERVICE\s*\(|\bINCLUDE(?:TEXT|PICTURE)\b)/i', html_entity_decode($xml, ENT_QUOTES | ENT_XML1, 'UTF-8'))) throw new StudioError('Office conversion blocks dynamic external-data fields and formulas.', 422);
      $dom = new DOMDocument();
      $old = libxml_use_internal_errors(true);
      try { $ok = $dom->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING); }
      finally { libxml_clear_errors(); libxml_use_internal_errors($old); }
      if (!$ok) throw new StudioError('The Office document contains malformed XML.', 422);
      foreach ($dom->getElementsByTagName('*') as $node) {
        if (preg_match('/^script:|(?:^|:)(?:scripts?|encryption-data|dde-source)$/i', $node->nodeName)) throw new StudioError('Macro scripts, DDE links and encrypted Office content are not supported.', 422);
        foreach ($node->attributes as $attr) {
          $key = strtolower($attr->localName); $value = trim($attr->value);
          if ($key === 'macro-name') throw new StudioError('Office conversion blocks macro event handlers.', 422);
          if ($key === 'targetmode' && strcasecmp($value, 'external') === 0) throw new StudioError('Office conversion blocks external linked resources. Remove external links from the document first.', 422);
          if (in_array($key, ['href', 'target'], true)) studio_office_reference($name, $value, $zip);
          if ($key === 'encryption-data') throw new StudioError('Encrypted Office documents are not supported.', 422);
        }
      }
    }
  } finally { $zip->close(); }
}

function studio_office_reference(string $entry, string $reference, ZipArchive $zip): void {
  $reference = rawurldecode($reference);
  if ($reference === '' || studio_starts_with($reference, '#')) return;
  if (preg_match('~^[a-z][a-z0-9+.-]*:|^//|[\\\\\x00]~i', $reference)) throw new StudioError('Office conversion blocks remote and filesystem-linked resources.', 422);
  $reference = explode('#', $reference, 2)[0];
  // OOXML uses ../ to reach sibling package directories; normalize these
  // against the containing part and require the target to exist in the ZIP.
  $base = dirname($entry);
  if (studio_ends_with($entry, '.rels')) $base = dirname(preg_replace('~(?:^|/)_rels/~', '/', $entry));
  $base = trim($base, '/');
  $segments = studio_starts_with($reference, '/') ? [] : ($base === '.' ? [] : explode('/', $base));
  foreach (explode('/', ltrim($reference, '/')) as $segment) {
    if ($segment === '' || $segment === '.') continue;
    if ($segment === '..') {
      if (!$segments) throw new StudioError('An Office reference escapes the document package.', 422);
      array_pop($segments);
    } else { $segments[] = $segment; }
  }
  $resolved = implode('/', $segments);
  if ($resolved !== '' && $zip->locateName($resolved) === false && $zip->locateName(rtrim($resolved, '/') . '/') === false) throw new StudioError('An Office linked resource is missing from the document package.', 422);
}

function studio_office_pdf(array $input, string $job): string {
  studio_need('libreoffice');
  studio_office_validate($input['path'], $input['extension']);
  $profile = $job . '/lo-profile';
  mkdir($profile . '/user', 0700, true);
  file_put_contents($profile . '/user/registrymodifications.xcu', '<?xml version="1.0" encoding="UTF-8"?><oor:items xmlns:oor="http://openoffice.org/2001/registry"><item oor:path="/org.openoffice.Office.Common/Security/Scripting"><prop oor:name="MacroSecurityLevel" oor:op="fuse"><value>3</value></prop></item><item oor:path="/org.openoffice.Office.Common/Load"><prop oor:name="UpdateDocMode" oor:op="fuse"><value>0</value></prop></item><item oor:path="/org.openoffice.Office.Common/Misc"><prop oor:name="AllowLinkUpdate" oor:op="fuse"><value>false</value></prop></item></oor:items>');
  studio_run('libreoffice', ['-env:UserInstallation=file://' . $profile, '--headless', '--nologo', '--nodefault', '--norestore', '--nolockcheck', '--convert-to', 'pdf', '--outdir', $job, $input['path']], $job);
  $output = $job . '/' . pathinfo($input['path'], PATHINFO_FILENAME) . '.pdf';
  if (!is_file($output)) throw new StudioError('LibreOffice could not convert this document. It may be encrypted or unsupported.', 422);
  studio_pdf_count($output, $job);
  return $output;
}

function studio_compress(array $input, array $options, string $job): string {
  $preset = (string)($options['preset'] ?? 'lossless');
  $output = $job . '/compressed.pdf';
  $caps = studio_capabilities();
  if ($preset === 'lossless') {
    studio_need('qpdf');
    $args = ['--object-streams=generate', '--stream-data=compress', '--recompress-flate', '--compression-level=9'];
    if (!empty($options['removeMetadata'])) {
      if (empty($caps['tools']['qpdf']['metadataRemoval'])) throw new StudioError('Removing document metadata requires a newer qpdf version with --remove-info and --remove-metadata support.', 503);
      $args[] = '--remove-info'; $args[] = '--remove-metadata';
    }
    if (!empty($options['linearize'])) $args[] = '--linearize';
    studio_run('qpdf', array_merge($args, [$input['path'], $output]), $job, PDFSTUDIO_PROCESS_SECONDS, true, [0, 3]);
  } else {
    studio_need('gs');
    $presets = ['light' => ['prepress', 300, 90], 'medium' => ['ebook', 150, 80], 'strong' => ['screen', 96, 65], 'maximum' => ['screen', 72, 45]];
    if (!isset($presets[$preset])) throw new StudioError('Select lossless, light, medium, strong or maximum compression.');
    [$settings, $defaultDpi, $defaultQuality] = $presets[$preset];
    $dpi = studio_option_int($options, 'dpi', $defaultDpi, 36, 600);
    $quality = studio_option_int($options, 'quality', $defaultQuality, 10, 100);
    $args = ['-dSAFER', '-dBATCH', '-dNOPAUSE', '-q', '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.7', '-dPDFSETTINGS=/' . $settings, '-dDetectDuplicateImages=true', '-dCompressFonts=true', '-dSubsetFonts=true', '-dAutoRotatePages=/None', '-dDownsampleColorImages=true', '-dDownsampleGrayImages=true', '-dDownsampleMonoImages=true', '-dColorImageDownsampleType=/Bicubic', '-dGrayImageDownsampleType=/Bicubic', '-dColorImageResolution=' . $dpi, '-dGrayImageResolution=' . $dpi, '-dMonoImageResolution=' . max(150, $dpi), '-dAutoFilterColorImages=false', '-dAutoFilterGrayImages=false', '-dColorImageFilter=/DCTEncode', '-dGrayImageFilter=/DCTEncode', '-dPassThroughJPEGImages=false', '-sOutputFile=' . $output];
    if (!empty($options['grayscale'])) { $args[] = '-sColorConversionStrategy=Gray'; $args[] = '-dProcessColorModel=/DeviceGray'; }
    $factor = number_format(max(0.05, 1 - $quality / 100), 3, '.', '');
    $args = array_merge($args, ['-c', '<< /ColorImageDict << /QFactor ' . $factor . ' >> /GrayImageDict << /QFactor ' . $factor . ' >> >> setdistillerparams', '-f', $input['path']]);
    studio_run('gs', $args, $job);
    if (!empty($options['linearize']) || !empty($options['removeMetadata'])) {
      studio_need('qpdf');
      $final = $job . '/optimized.pdf'; $qargs = [];
      if (!empty($options['linearize'])) $qargs[] = '--linearize';
      if (!empty($options['removeMetadata'])) {
        if (empty($caps['tools']['qpdf']['metadataRemoval'])) throw new StudioError('Metadata removal requires a newer qpdf version.', 503);
        $qargs[] = '--remove-info'; $qargs[] = '--remove-metadata';
      }
      studio_run('qpdf', array_merge($qargs, [$output, $final]), $job, PDFSTUDIO_PROCESS_SECONDS, true, [0, 3]);
      $output = $final;
    }
  }
  studio_pdf_count($output, $job);
  return $output;
}

function studio_protect(array $input, array $options, string $job): string {
  studio_need('qpdf');
  $user = studio_password($options, 'userPassword');
  $owner = studio_password($options, 'ownerPassword');
  if ($user === '' && $owner === '') throw new StudioError('Enter a password to open or an owner password.');
  if ($owner === '') $owner = bin2hex(random_bytes(24));
  if ($user !== '' && hash_equals($owner, $user)) throw new StudioError('Use different opening and owner passwords.');
  $bits = studio_option_int($options, 'bits', 256, 128, 256);
  if (!in_array($bits, [128, 256], true)) throw new StudioError('Encryption must use AES-128 or AES-256.');
  $caps = studio_capabilities();
  if (!empty($caps['tools']['qpdf']['namedPasswords'])) {
    $args = ['--encrypt', '--user-password=' . $user, '--owner-password=' . $owner, '--bits=' . $bits];
  } else {
    if (studio_starts_with($user, '-') || studio_starts_with($owner, '-')) throw new StudioError('This older qpdf version requires passwords that do not begin with a hyphen. Upgrade qpdf to support arbitrary passwords.');
    $args = ['--encrypt', $user, $owner, (string)$bits];
  }
  $print = (string)($options['print'] ?? 'full');
  if (!in_array($print, ['none', 'low', 'full'], true)) throw new StudioError('Invalid printing permission.');
  $args[] = '--print=' . $print;
  $args[] = '--modify=' . (!array_key_exists('modify', $options) || !empty($options['modify']) ? 'all' : 'none');
  foreach (['copy' => 'extract', 'annotate' => 'annotate', 'forms' => 'form'] as $key => $flag) $args[] = '--' . $flag . '=' . (!array_key_exists($key, $options) || !empty($options[$key]) ? 'y' : 'n');
  if ($bits === 128) $args[] = '--use-aes=y';
  $output = $job . '/protected.pdf';
  $args = array_merge($args, ['--', $input['path'], $output]);
  // Response file keeps passwords out of a process-list command line. Every
  // value is a separate line; passwords were checked to exclude line breaks.
  $response = $job . '/qpdf-private-arguments.txt';
  file_put_contents($response, implode("\n", $args) . "\n"); chmod($response, 0600);
  studio_run('qpdf', ['@' . $response], $job, PDFSTUDIO_PROCESS_SECONDS, true, [0, 3]);
  unlink($response);
  studio_pdf_count($output, $job, $user !== '' ? $user : $owner);
  return $output;
}

function studio_merge(array $inputs, array $options, string $job): string {
  $caps = studio_capabilities();
  $engine = (string)($options['engine'] ?? 'auto');
  if ($engine === 'auto') $engine = $caps['engines']['merge'][0] ?? '';
  if (!in_array($engine, $caps['engines']['merge'], true)) throw new StudioError('Server merge requires qpdf or FPDI. Browser merge remains available.', 503);
  $ranges = []; $total = 0;
  foreach ($inputs as $index => $input) {
    $count = studio_pdf_count($input['path'], $job);
    $ranges[$index] = studio_ranges((string)($options['ranges'][$index] ?? '1-z'), $count);
    foreach (explode(',', $ranges[$index]) as $range) {
      $pieces = explode('-', $range); $total += isset($pieces[1]) ? (int)$pieces[1] - (int)$pieces[0] + 1 : 1;
    }
  }
  if ($total > PDFSTUDIO_MAX_PAGES) throw new StudioError('The merged document exceeds the page limit.', 413);
  $output = $job . '/merged.pdf';
  if ($engine === 'qpdf') {
    $args = ['--empty', '--pages'];
    foreach ($inputs as $index => $input) { $args[] = $input['path']; $args[] = $ranges[$index]; }
    studio_run('qpdf', array_merge($args, ['--', $output]), $job, PDFSTUDIO_PROCESS_SECONDS, true, [0, 3]);
  } else {
    $pdf = new setasign\Fpdi\Fpdi();
    foreach ($inputs as $index => $input) {
      $count = $pdf->setSourceFile($input['path']);
      $selected = studio_ranges($ranges[$index], $count);
      foreach (explode(',', $selected) as $page) {
        $id = $pdf->importPage((int)$page);
        $size = $pdf->getTemplateSize($id);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($id);
      }
    }
    $pdf->Output('F', $output);
  }
  return $output;
}

function studio_ocr(array $input, array $options, string $job): string {
  studio_need('pdftoppm'); studio_need('tesseract');
  $count = studio_pdf_count($input['path'], $job);
  $language = (string)($options['language'] ?? 'eng');
  if (!preg_match('/^[a-zA-Z0-9_.\/\-]+(?:\+[a-zA-Z0-9_.\/\-]+)*$/', $language) || strlen($language) > 100) throw new StudioError('Invalid OCR language selection.');
  $caps = studio_capabilities();
  foreach (explode('+', $language) as $name) if (!in_array($name, $caps['tools']['tesseract']['languages'] ?? [], true)) throw new StudioError('The server does not have OCR language ' . $name . '. Install its Tesseract traineddata package.', 503);
  $dpi = studio_option_int($options, 'dpi', 200, 72, 400);
  $type = (string)($options['output'] ?? 'pdf');
  if (!in_array($type, ['pdf', 'text'], true)) throw new StudioError('Select PDF or text OCR output.');
  // Tesseract's input list creates a combined searchable PDF directly, so
  // searchable OCR does not require a separate PDF merging engine.
  $list = $job . '/ocr-inputs.txt'; $names = []; $start = microtime(true);
  for ($page = 1; $page <= $count; $page++) {
    if (microtime(true) - $start > PDFSTUDIO_OCR_SECONDS) throw new StudioError('OCR exceeded the server job time limit. Process fewer pages.', 408);
    $base = $job . '/ocr-page-' . str_pad((string)$page, 5, '0', STR_PAD_LEFT);
    $args = ['-f', (string)$page, '-l', (string)$page, '-singlefile', '-png', '-r', (string)$dpi];
    if (studio_binary('pdfinfo')) {
      $info = studio_run('pdfinfo', ['-f', (string)$page, '-l', (string)$page, $input['path']], $job, 20);
      if (preg_match('/(?:Page\s+\d+\s+size|Page size):\s*([0-9.]+)\s*x\s*([0-9.]+)/i', $info['stdout'], $dimensions) && max((float)$dimensions[1], (float)$dimensions[2]) * $dpi / 72 > 4000) $args = array_merge($args, ['-scale-to', '4000']);
    }
    studio_run('pdftoppm', array_merge($args, [$input['path'], $base]), $job, 60);
    if (!is_file($base . '.png')) throw new StudioError('A PDF page could not be rendered for OCR.', 422);
    $names[] = $base . '.png';
    studio_job_budget($job);
  }
  file_put_contents($list, implode("\n", $names) . "\n");
  $base = $job . '/ocr-result';
  $remaining = max(10, (int)(PDFSTUDIO_OCR_SECONDS - (microtime(true) - $start)));
  studio_run('tesseract', [$list, $base, '-l', $language, '--dpi', (string)$dpi, $type === 'pdf' ? 'pdf' : 'txt'], $job, $remaining);
  $output = $base . ($type === 'pdf' ? '.pdf' : '.txt');
  if ($type === 'pdf') studio_pdf_count($output, $job);
  return $output;
}

$studioAction = (string)($_GET['action'] ?? '');
if ($studioAction !== '') {
  $studioMemoryReserve = str_repeat('x', 262144);
  register_shutdown_function(function() {
    global $studioMemoryReserve;
    $studioMemoryReserve = null;
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true) && !headers_sent()) {
      http_response_code(500);
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode(['error' => 'The processor reached a server resource limit. Try a smaller document or fewer pages.']);
      // No exit here: subsequent shutdown handlers must remove private jobs.
    }
  });
  try {
    if ($studioAction === 'capabilities') {
      if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') throw new StudioError('Use GET for capabilities.', 405);
      studio_json(studio_capabilities());
    }
    $allowedActions = ['compress', 'protect', 'decrypt', 'linearize', 'repair', 'validate', 'merge', 'extract_images', 'extract_text', 'ocr', 'convert', 'html_pdf'];
    if (!in_array($studioAction, $allowedActions, true)) throw new StudioError('Unknown PDF Studio action.', 404);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') throw new StudioError('This operation requires a POST request.', 405);
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > min(PDFSTUDIO_MAX_JOB_BYTES + 1048576, studio_ini_bytes((string)ini_get('post_max_size')))) throw new StudioError('This request exceeds the PHP POST/upload size limit. Upload fewer or smaller files, or ask the administrator to increase post_max_size.', 413);
    $csrf = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf'] ?? '');
    if ($csrf === '' || !hash_equals($studioBoot['csrf'], $csrf)) throw new StudioError('The session token is missing or expired. Reload PDF Studio and try again.', 403);
    $json = (string)($_POST['options'] ?? '{}');
    if (strlen($json) > PDFSTUDIO_MAX_HTML + 262144) throw new StudioError('The operation options exceed the size limit.', 413);
    $options = json_decode($json, true, 64);
    if (json_last_error() !== JSON_ERROR_NONE) throw new StudioError('The operation settings are not valid JSON.');
    if (!is_array($options)) throw new StudioError('The operation settings must be an object.');
    @set_time_limit(($studioAction === 'ocr' ? PDFSTUDIO_OCR_SECONDS : PDFSTUDIO_PROCESS_SECONDS) + 60);
    $job = studio_job();
    if ($studioAction === 'html_pdf') {
      $output = studio_html_pdf($options, $job);
      studio_download($output, 'document.pdf', 'application/pdf');
    }
    $inputs = studio_inputs($job, $studioAction === 'convert');
    if ($studioAction === 'merge') {
      $output = studio_merge($inputs, $options, $job);
      studio_download($output, 'merged.pdf', 'application/pdf', array_sum(array_column($inputs, 'size')));
    }
    $input = studio_one($inputs);
    if ($studioAction === 'convert') {
      $output = studio_office_pdf($input, $job);
      studio_download($output, 'converted.pdf', 'application/pdf');
    }
    $password = studio_password($options, 'password');
    if ($studioAction !== 'validate' && $studioAction !== 'repair') studio_pdf_count($input['path'], $job, $password);
    switch ($studioAction) {
      case 'compress':
        $output = studio_compress($input, $options, $job);
        studio_download($output, 'compressed.pdf', 'application/pdf', $input['size']);
      case 'protect':
        $output = studio_protect($input, $options, $job);
        studio_download($output, 'protected.pdf', 'application/pdf');
      case 'decrypt':
        studio_need('qpdf');
        if ($password === '') throw new StudioError('Enter the valid PDF password to decrypt this document.');
        $passwordFile = $job . '/password.txt'; file_put_contents($passwordFile, $password); chmod($passwordFile, 0600);
        $output = $job . '/decrypted.pdf';
        studio_run('qpdf', ['--password-file=' . $passwordFile, '--decrypt', $input['path'], $output], $job, PDFSTUDIO_PROCESS_SECONDS, true, [0, 3]);
        unlink($passwordFile);
        studio_pdf_count($output, $job);
        studio_download($output, 'decrypted.pdf', 'application/pdf');
      case 'linearize':
        studio_need('qpdf'); $output = $job . '/linearized.pdf';
        studio_run('qpdf', ['--linearize', $input['path'], $output], $job, PDFSTUDIO_PROCESS_SECONDS, true, [0, 3]);
        studio_download($output, 'linearized.pdf', 'application/pdf');
      case 'repair':
        studio_need('qpdf'); $output = $job . '/repaired.pdf';
        studio_run('qpdf', ['--object-streams=generate', $input['path'], $output], $job, PDFSTUDIO_PROCESS_SECONDS, true, [0, 3]);
        studio_pdf_count($output, $job);
        studio_download($output, 'repaired.pdf', 'application/pdf');
      case 'validate':
        studio_need('qpdf');
        $result = studio_run('qpdf', ['--check', $input['path']], $job, 30, false);
        $report = $result['stdout'] . "\n" . $result['stderr'];
        $report = str_replace([$job, $input['path']], ['[private workspace]', '[document]'], $report);
        // qpdf diagnostics can reference absolute filesystem paths. Redact
        // unexpected paths as well, preserving actual PDF validation messages.
        $report = preg_replace('~(?<![A-Za-z0-9])/(?:[A-Za-z0-9_.-]+/)+[A-Za-z0-9_.-]*~', '[private path]', $report);
        $pages = null;
        try { $pages = studio_pdf_count($input['path'], $job, $password); } catch (StudioError $ignored) {}
        studio_json(['valid' => $result['code'] === 0, 'warnings' => $result['code'] === 3, 'report' => trim($report), 'pages' => $pages]);
      case 'extract_images':
        studio_need('pdfimages');
        studio_run('pdfimages', ['-all', $input['path'], $job . '/image'], $job);
        $files = glob($job . '/image-*') ?: [];
        $output = $job . '/embedded-images.zip'; studio_zip($files, $output);
        studio_download($output, 'embedded-images.zip', 'application/zip');
      case 'extract_text':
        studio_need('pdftotext'); $output = $job . '/extracted.txt';
        $args = ['-enc', 'UTF-8'];
        if (!empty($options['layout'])) $args[] = '-layout';
        studio_run('pdftotext', array_merge($args, [$input['path'], $output]), $job);
        // A scanned document may contain no text; a zero-byte text file is a
        // valid result and is delivered with an explanatory one-line message.
        if (is_file($output) && filesize($output) === 0) file_put_contents($output, "No embedded text was found. Run OCR for a scanned document.\n");
        studio_download($output, 'extracted.txt', 'text/plain; charset=UTF-8');
      case 'ocr':
        $output = studio_ocr($input, $options, $job);
        $text = ($options['output'] ?? 'pdf') === 'text';
        if ($text && is_file($output) && filesize($output) === 0) file_put_contents($output, "OCR did not recognize any text.\n");
        studio_download($output, $text ? 'ocr-text.txt' : 'searchable.pdf', $text ? 'text/plain; charset=UTF-8' : 'application/pdf');
    }
  } catch (Throwable $error) {
    $reference = bin2hex(random_bytes(5));
    $trace = array_map(function($frame) {
      return ($frame['file'] ?? '[internal]') . ':' . ($frame['line'] ?? 0) . ' ' . ($frame['function'] ?? '');
    }, $error->getTrace());
    error_log('PDF Studio error ' . $reference . ' (' . $studioAction . '): ' . $error->getMessage() . "\n" . implode("\n", $trace));
    $message = $error instanceof StudioError ? $error->getMessage() : 'The document processor could not complete this operation. Check the server log using error reference ' . $reference . '.';
    studio_json(['error' => $message, 'reference' => $reference], $error instanceof StudioError ? $error->status : 500);
  }
}
?>
<!doctype html>
<html lang="<?= studio_language() ?>" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Private browser-first PDF tools and document editor">
  <title>PDF Studio</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@7.3.1/css/all.min.css">
  <style>
:root{--studio-bg:#f2f4f8;--studio-panel:#fff;--studio-text:#1d2b42;--studio-muted:#6b788d;--studio-border:#dfe5ee;--studio-accent:#3157da;--studio-surface:#e6eaf1;--studio-radius:14px;--studio-shadow:0 12px 35px #14213a12}
[data-bs-theme=dark]{--studio-bg:#111823;--studio-panel:#1a2432;--studio-text:#e6edf7;--studio-muted:#9aabc0;--studio-border:#334154;--studio-surface:#0a111c;--studio-accent:#809bff}
*{box-sizing:border-box}body{margin:0;font:14px system-ui,-apple-system,sans-serif;color:var(--studio-text);background:var(--studio-bg)}button:focus-visible,a:focus-visible,input:focus-visible,[tabindex]:focus-visible{outline:3px solid #6e90ff;outline-offset:2px}button{touch-action:manipulation}.app-shell{display:grid;grid-template-columns:196px minmax(0,1fr);grid-template-rows:72px minmax(0,1fr) 32px;height:100dvh}.app-header{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;padding:0 22px;background:var(--studio-panel);border-bottom:1px solid var(--studio-border);z-index:50;gap:20px}.brand{display:flex;gap:10px;align-items:center;text-align:left;border:0;background:transparent;color:var(--studio-text);font-size:22px;white-space:nowrap;padding:0}.brand small{display:block;font-size:8px;font-weight:600;letter-spacing:2px;color:var(--studio-muted)}.brand-icon{display:grid;place-items:center;background:#3157da;color:white;width:40px;height:44px;border-radius:10px;font-size:23px}.header-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.header-actions kbd{background:#ffffff30;margin-left:6px;font-size:9px}.icon-button{background:transparent;color:var(--studio-muted);border:0;border-radius:7px;width:31px;height:31px;display:inline-grid;place-items:center;flex-shrink:0}.icon-button:hover{color:var(--studio-accent);background:#6481e31a}.app-sidebar{grid-row:2;background:var(--studio-panel);border-right:1px solid var(--studio-border);padding:17px 12px 8px;overflow:auto;display:flex;flex-direction:column;z-index:20}.section-label{font-size:10px;color:var(--studio-muted);font-weight:700;letter-spacing:1.5px;padding:0 10px}.sidebar-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:13px}.nav-tool{border:0;background:transparent;display:flex;gap:11px;align-items:center;text-align:left;color:var(--studio-muted);width:100%;border-radius:8px;padding:10px 12px;margin:3px 0;font-weight:600;font-size:13px}.nav-tool.active,.nav-tool:hover{color:var(--studio-accent);background:#3157da10}.nav-tool i{width:17px;text-align:center}.sidebar-note{display:flex;gap:9px;padding:15px 7px;color:var(--studio-muted);font-size:11px;margin-top:auto}.sidebar-note small{display:block;font-size:10px;line-height:1.8}.app-workspace{min-width:0;min-height:0;overflow:hidden}.home-panel{height:100%;overflow:auto;padding:38px max(24px,5vw)}.home-hero{max-width:930px;padding-bottom:28px}.eyebrow{font-size:10px;letter-spacing:2px;color:var(--studio-accent);font-weight:700;margin-bottom:12px}.home-hero h1{font-size:clamp(25px,3vw,40px);letter-spacing:-1.2px;font-weight:700}.home-hero p{color:var(--studio-muted);font-size:15px;margin:13px 0 21px}.hero-chips{display:flex;gap:22px;flex-wrap:wrap;color:var(--studio-muted);font-size:11px}.hero-chips i{color:var(--studio-accent);margin-right:5px}.home-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}.tool-card{background:var(--studio-panel);border:1px solid var(--studio-border);border-radius:var(--studio-radius);padding:19px;text-align:left;color:var(--studio-text);transition:transform .16s,border .16s;position:relative}.tool-card:hover:enabled{border-color:var(--studio-accent);transform:translateY(-2px)}.tool-card:disabled{opacity:.6}.card-icon{width:37px;height:37px;display:grid;place-items:center;border-radius:10px;background:#3157da0d;color:var(--studio-accent);font-size:17px;margin-bottom:13px}.tool-card strong{display:block;font-size:13px;margin-bottom:6px}.tool-card small{display:block;font-size:10px;color:var(--studio-muted);line-height:1.5}.dropzone{margin-top:25px;border:1.5px dashed var(--studio-border);border-radius:var(--studio-radius);display:flex;align-items:center;flex-direction:column;padding:28px;gap:8px;color:var(--studio-muted);cursor:pointer;background:var(--studio-panel)}.dropzone i{color:var(--studio-accent);font-size:25px}.dropzone strong{font-size:13px;color:var(--studio-text)}.dropzone span{font-size:10px}.dropzone:hover,.drag-over{border-color:var(--studio-accent)!important;background:#3157da0a!important}.recent-heading{display:flex;align-items:center;justify-content:space-between;margin-top:28px}.recent-heading h2{font-size:13px;font-weight:700;margin:0}.recent-file{padding:9px 2px;border-bottom:1px solid var(--studio-border);display:flex;gap:10px;font-size:11px;color:var(--studio-muted)}.recent-file strong{color:var(--studio-text)}.recent-files:empty:after{content:'Only filenames and dates are saved here. Open a file to begin.';font-size:11px;color:var(--studio-muted)}.pdf-panel{height:100%;display:flex;flex-direction:column}.pdf-panel[hidden],section[hidden]{display:none!important}.document-toolbar{padding:9px 14px;display:flex;align-items:center;gap:7px;background:var(--studio-panel);border-bottom:1px solid var(--studio-border);min-height:51px;flex-wrap:wrap}.document-title{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:270px;font-size:12px;font-weight:600;margin-right:auto}.toolbar-divider{height:25px;width:1px;background:var(--studio-border);margin:0 4px}.zoom-controls{display:flex;gap:3px;align-items:center}.zoom-controls select{width:104px}.context-toolbar{display:flex;gap:6px;padding:8px 13px;background:var(--studio-panel);border-bottom:1px solid var(--studio-border);min-height:49px;flex-wrap:wrap;z-index:15}.context-toolbar .btn{font-size:11px}.pdf-body{display:flex;min-height:0;flex:1}.thumbnail-panel{width:144px;flex-shrink:0;background:var(--studio-panel);border-right:1px solid var(--studio-border);overflow:auto}.thumbnail-actions{display:flex;gap:4px;padding:9px 5px;position:sticky;top:0;background:var(--studio-panel);z-index:2}.thumbnail-actions .btn{font-size:10px;padding:4px 6px}.thumbnail{position:relative;padding:11px 14px;border-bottom:1px solid var(--studio-border);cursor:pointer}.thumbnail.active{background:#3157da0c}.thumbnail.selected{box-shadow:inset 3px 0 var(--studio-accent)}.thumbnail .thumb-image{display:flex;align-items:center;justify-content:center;height:115px}.thumbnail canvas{max-width:100%;max-height:115px;box-shadow:0 2px 8px #0003;background:#fff}.thumbnail .thumb-loading{color:var(--studio-muted);font-size:11px}.thumbnail input{position:absolute;top:7px;left:7px;z-index:1}.thumb-caption{font-size:10px;margin-top:7px;text-align:center;line-height:1.3}.thumb-caption small{font-size:8px;color:var(--studio-muted);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.page-scroll{background:var(--studio-surface);flex:1;min-width:0;overflow:auto;position:relative;display:flex;align-items:flex-start;justify-content:flex-start;padding:25px}.page-stage{position:relative;box-shadow:0 4px 25px #0002;background:white;margin:auto;flex-shrink:0;line-height:0}.page-stage>canvas:first-child{position:absolute;inset:0}.page-stage .canvas-container{position:absolute!important;inset:0}.crop-rubber{position:absolute;border:2px dashed #3157da;background:#3157da15;z-index:5;pointer-events:none}.object-inspector{width:215px;flex-shrink:0;background:var(--studio-panel);border-left:1px solid var(--studio-border);padding:17px 13px;overflow:auto}.inspector-heading{font-size:11px;font-weight:700;margin-bottom:12px}.muted-help{font-size:10px;color:var(--studio-muted);line-height:1.6;display:block}.object-inspector .form-label{font-size:10px;color:var(--studio-muted);margin-bottom:4px}.object-inspector .form-control,.object-inspector .form-select{font-size:11px}.object-inspector .form-control-color{height:27px;width:100%}.object-inspector .btn{font-size:10px}.layer-item{display:flex;align-items:center;gap:5px;padding:6px 3px;font-size:10px;border-bottom:1px solid var(--studio-border)}.layer-item.active{background:#3157da12}.layer-item span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1}.layer-item button{width:23px;height:23px}.status-bar{grid-column:1/-1;display:flex;gap:18px;align-items:center;background:var(--studio-panel);border-top:1px solid var(--studio-border);padding:0 18px;font-size:10px;color:var(--studio-muted);min-width:0;z-index:21}.status-bar span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}#privacyIndicator{color:var(--studio-accent)}.toast-region{position:fixed;bottom:45px;right:20px;z-index:3000;max-width:440px}.studio-toast{background:var(--studio-panel);box-shadow:var(--studio-shadow);border:1px solid var(--studio-border);border-left:4px solid var(--studio-accent);border-radius:9px;padding:13px 17px;margin-top:10px;font-size:12px;line-height:1.5}.studio-toast.error{border-left-color:#dc3545}.studio-toast.success{border-left-color:#198754}.job-backdrop{position:fixed;inset:0;background:#111e3c88;backdrop-filter:blur(4px);z-index:2500;display:grid;place-items:center}.job-backdrop[hidden]{display:none}.job-card{background:var(--studio-panel);border-radius:20px;padding:30px;max-width:430px;width:92vw;display:flex;align-items:center;flex-direction:column;text-align:center;gap:15px;box-shadow:var(--studio-shadow)}.job-card .progress{width:100%;height:7px}.job-card p{font-size:11px;margin:0;color:var(--studio-muted)}.job-card small{font-size:10px;color:var(--studio-muted)}.loading-banner{position:fixed;bottom:40px;left:50%;transform:translateX(-50%);background:var(--studio-panel);box-shadow:var(--studio-shadow);border:1px solid var(--studio-border);border-radius:30px;padding:11px 20px;font-size:12px;z-index:40}.modal-backdrop{z-index:1040}.dropdown-menu{z-index:1100}.tool-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.tool-grid .btn{font-size:12px;white-space:normal;text-align:left;padding:13px}.tool-grid .btn small{display:block;font-size:10px;margin-top:4px}.signature-canvas{width:100%;height:190px;border:1px solid var(--studio-border);background:#fff;touch-action:none}.modal-body label{font-size:12px}.modal-body .form-text{font-size:11px}.modal-body .alert{font-size:12px}.render-message{position:absolute;inset:0;align-content:center;text-align:center;padding:20px;color:var(--studio-muted);background:var(--studio-surface)}.palette-result{display:block;border:0;background:transparent;text-align:left;padding:10px 13px;border-bottom:1px solid var(--studio-border);width:100%;color:var(--studio-text)}.palette-result:hover{background:#3157da12}.capability-table{font-size:11px}.capability-table td{vertical-align:middle}.group-banner{font-size:11px;color:var(--studio-muted);padding:8px 4px}.form-range{cursor:pointer}
@media(min-width:1600px){.home-cards{grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}.home-panel{padding:45px 7vw}.tool-card{padding:24px}}
@media(max-width:1199px){.app-shell{grid-template-columns:160px minmax(0,1fr)}.app-header{padding:0 13px}.header-actions{gap:5px}.header-actions kbd{display:none}.nav-tool{padding:9px 7px;font-size:12px}.home-panel{padding:30px 25px}.home-cards{grid-template-columns:repeat(3,minmax(0,1fr))}.object-inspector{width:190px}.document-title{max-width:150px}.thumbnail-panel{width:125px}.thumbnail{padding:10px}.brand{font-size:20px}}
@media(max-width:900px){.app-shell{grid-template-columns:1fr;grid-template-rows:auto minmax(0,1fr) 33px}.app-sidebar{position:fixed;left:0;top:72px;bottom:33px;width:190px;box-shadow:var(--studio-shadow);transform:translateX(-100%);transition:transform .2s}.sidebar-visible .app-sidebar{transform:none}.app-header{min-height:72px;padding:10px;gap:10px;flex-wrap:wrap}.header-actions .btn{font-size:10px;padding:6px}.header-actions{gap:3px}.brand small{display:none}.object-inspector{width:180px}.status-bar{gap:9px;padding:0 8px}.home-cards{grid-template-columns:repeat(3,minmax(0,1fr))}.document-toolbar{gap:3px}.context-toolbar{padding:7px}.page-scroll{padding:15px}.document-title{max-width:110px}}
@media(max-width:650px){.app-header{justify-content:center}.brand{font-size:18px}.brand-icon{height:31px;width:30px;font-size:19px}.header-actions{justify-content:center}.object-inspector{display:none}.home-cards{grid-template-columns:repeat(2,minmax(0,1fr))}.home-panel{padding:25px 18px}.home-hero h1{font-size:28px}.hero-chips{gap:8px}.home-hero p{font-size:13px}.tool-card{padding:15px}.thumbnail-panel{width:98px}.thumbnail .thumb-image{height:85px}.thumbnail canvas{max-height:85px}.thumbnail-actions{flex-wrap:wrap}.status-bar #statusDocument{max-width:90px}#privacyIndicator{max-width:165px}.tool-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.zoom-controls select{width:83px}.document-toolbar .btn{font-size:10px}.dropzone span{text-align:center}.app-sidebar{top:100px}.document-title{display:none}}
@media print{body>*{display:none!important}}

.doc-panel{height:100%;min-height:0;display:flex;flex-direction:column;background:var(--bs-body-bg)}
.doc-panel.d-none,.doc-panel[hidden]{display:none!important}
.doc-commandbar{background:var(--bs-body-bg);position:relative;z-index:20}
.doc-engine{width:auto;max-width:240px}
.doc-menu{position:relative}
.doc-menu>summary{list-style:none;cursor:pointer}
.doc-menu>summary::-webkit-details-marker{display:none}
.doc-menu-content{position:absolute;top:calc(100% + 5px);left:0;min-width:255px;padding:5px;background:var(--bs-body-bg);border:1px solid var(--bs-border-color);border-radius:8px;box-shadow:0 8px 30px #0003;z-index:1100}
.doc-menu-content button{display:block;width:100%;background:none;border:0;color:var(--bs-body-color);text-align:left;padding:8px 12px;border-radius:5px;font-size:.85rem}
.doc-menu-content button:hover,.doc-menu-content button:focus-visible{background:var(--bs-tertiary-bg)}
.doc-edit-workspace{flex:1;overflow:auto;background:var(--bs-tertiary-bg)}
.doc-edit-workspace>.jodit-container{max-width:1300px;margin:0 auto}
.doc-edit-workspace .jodit-workplace{background:#dfe3e8;padding:20px;overflow:auto;min-height:60vh}
.doc-edit-workspace .jodit-wysiwyg{color:#1e293b;background:white;box-shadow:0 3px 18px #0002;box-sizing:border-box;margin:0 auto;min-height:1050px;overflow-wrap:break-word;position:relative}
.doc-edit-workspace .jodit-wysiwyg img{max-width:100%}
.doc-edit-workspace .jodit-wysiwyg table{border-collapse:collapse;max-width:100%}
.doc-edit-workspace .jodit-wysiwyg td,.doc-edit-workspace .jodit-wysiwyg th{border:1px solid #adb5bd;padding:6px}
.doc-edit-workspace .jodit-wysiwyg .doc-pagebreak{display:block;border-top:2px dashed #8291a3;height:20px;margin:30px 0;color:#64748b;font-size:11px;text-align:center;break-after:page;page-break-after:always}
.doc-edit-workspace .jodit-wysiwyg .doc-pagebreak::after{content:'Page break'}
html[lang=it] .doc-edit-workspace .jodit-wysiwyg .doc-pagebreak::after{content:'Interruzione di pagina'}
html[lang=de] .doc-edit-workspace .jodit-wysiwyg .doc-pagebreak::after{content:'Seitenumbruch'}
.doc-edit-workspace .jodit-wysiwyg .doc-check{cursor:pointer;display:inline-block;margin-right:.4em}
.doc-edit-workspace .jodit-wysiwyg .doc-toc{column-span:all;border:1px solid #cbd5e1;border-radius:6px;padding:15px}
.doc-edit-workspace .jodit-wysiwyg figure{margin:1em 0}
.doc-edit-workspace .jodit-wysiwyg figcaption{font-size:.85em;color:#64748b;text-align:center}
.doc-help{color:var(--bs-secondary-color)}
.doc-preview-frame{width:100%;height:65vh;border:1px solid var(--bs-border-color);background:#fff}
@media(max-width:700px){.doc-edit-workspace{padding:5px!important}.doc-edit-workspace .jodit-workplace{padding:5px}.doc-edit-workspace .jodit-wysiwyg{width:100%!important;padding:15px!important;min-height:70vh!important}.doc-commandbar{gap:5px!important}.doc-engine{max-width:180px}}

.tools-preview-canvas { display: block; width: 100%; max-height: 560px; object-fit: contain; background: #e5e7eb; border: 1px solid var(--studio-border, #d1d5db); }
.tools-crop-canvas { touch-action: none; cursor: crosshair; max-height: none; }
.tools-compare-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.tools-compare-single { grid-template-columns: 1fr; }
.tools-compare-pane { overflow: auto; height: 55vh; background: #e5e7eb; border: 1px solid var(--studio-border, #d1d5db); }
.tools-compare-pane canvas { display: block; max-width: 100%; height: auto; margin: 0 auto; }
.tools-compare-pane p { padding: 1rem; }
.tools-word-diff { line-height: 1.9; white-space: pre-wrap; }
.tools-word-diff del { background: #fee2e2; color: #991b1b; }
.tools-word-diff ins { background: #dcfce7; color: #166534; text-decoration: none; border-bottom: 2px solid #16a34a; }
.tools-validation-report { white-space: pre-wrap; overflow-wrap: anywhere; max-height: 60vh; overflow: auto; }
@media (max-width: 650px) { .tools-compare-grid { grid-template-columns: 1fr; } .tools-compare-pane { height: 40vh; } }

  </style>
</head>
<body>
  <div class="app-shell">
    <header class="app-header">
      <button class="brand" data-action="home" aria-label="PDF Studio home"><span class="brand-icon"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i></span><span>PDF <strong>Studio</strong><small>DOCUMENT WORKSPACE</small></span></button>
      <div class="header-actions">
        <label class="small d-flex align-items-center gap-1" for="studioLanguage"><i class="fa-solid fa-language" aria-hidden="true"></i><span class="visually-hidden">Language</span><select id="studioLanguage" class="form-select form-select-sm w-auto" aria-label="Language"><option value="it" translate="no">Italiano</option><option value="en" translate="no">English</option><option value="de" translate="no">Deutsch</option></select></label>
        <button class="btn btn-primary btn-sm" data-action="open"><i class="fa-solid fa-folder-open"></i> Open <kbd>Ctrl O</kbd></button>
        <button class="btn btn-outline-secondary btn-sm" data-action="create"><i class="fa-solid fa-file-circle-plus"></i> Create</button>
        <button class="btn btn-success btn-sm" data-action="export"><i class="fa-solid fa-download"></i> Export PDF</button>
        <div class="dropdown">
          <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Project</button>
          <div class="dropdown-menu dropdown-menu-end">
            <button class="dropdown-item" data-action="save-project">Save editable project</button>
            <button class="dropdown-item" data-action="load-project">Open project</button>
            <button class="dropdown-item" data-action="restore-autosave">Restore local autosave</button>
            <button class="dropdown-item" data-action="clear-autosave">Clear local autosave</button>
          </div>
        </div>
        <button class="btn btn-outline-secondary btn-sm" data-action="palette" title="Search tools (Ctrl K)"><i class="fa-solid fa-magnifying-glass"></i><span class="d-none d-xl-inline"> Search tools</span></button>
        <button class="icon-button" data-action="theme" aria-label="Toggle light and dark mode"><i class="fa-solid fa-circle-half-stroke"></i></button>
        <button class="icon-button" data-action="fullscreen" aria-label="Full screen"><i class="fa-solid fa-expand"></i></button>
        <button class="icon-button" data-action="capabilities" aria-label="System capabilities"><i class="fa-solid fa-sliders"></i></button>
        <button class="icon-button position-relative" id="studioUpdatesButton" data-action="updates" aria-label="Check for updates" title="Check for updates"><i class="fa-solid fa-cloud-arrow-down"></i><span id="studioUpdateBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger" hidden aria-hidden="true">!</span></button>
      </div>
    </header>
    <aside class="app-sidebar" aria-label="Tools"><div class="sidebar-top"><span class="section-label">WORKSPACE</span><button class="icon-button d-xl-none" data-action="sidebar" aria-label="Collapse tools"><i class="fa-solid fa-bars"></i></button></div><nav id="toolNavigation"></nav><div class="sidebar-note"><i class="fa-solid fa-shield-halved"></i><span>Browser first.<small>Server uploads are explicit.</small></span></div></aside>
    <main id="workspace" class="app-workspace">
      <section id="homePanel" class="home-panel">
        <div class="home-hero"><div class="eyebrow">YOUR DOCUMENTS, YOUR WORKSPACE</div><h1>Make every PDF work for you.</h1><p>Edit, organize, sign, convert, or create a document from scratch.</p><div class="hero-chips"><span><i class="fa-solid fa-lock"></i> Local processing by default</span><span><i class="fa-solid fa-layer-group"></i> Editable project files</span><span><i class="fa-solid fa-file-pen"></i> Two dedicated editors</span></div></div>
        <div id="homeCards" class="home-cards"></div>
        <div class="dropzone" id="homeDrop" tabindex="0" role="button" aria-label="Open PDFs or images"><i class="fa-solid fa-cloud-arrow-up"></i><strong>Drop PDFs or images here</strong><span>or click to browse · Files stay local until you choose a server operation</span></div>
        <div class="recent-heading"><h2>Recently opened</h2><button class="btn btn-link btn-sm" data-action="clear-recents">Clear list</button></div><div id="recentFiles" class="recent-files"></div>
      </section>
      <section id="pdfPanel" class="pdf-panel" hidden>
        <div class="document-toolbar"><button class="btn btn-outline-secondary btn-sm" data-action="thumbnails"><i class="fa-solid fa-table-cells"></i> Pages</button><div class="document-title" id="documentTitle">Untitled</div><div class="toolbar-divider"></div><button class="icon-button" data-action="undo" title="Undo (Ctrl Z)" aria-label="Undo"><i class="fa-solid fa-rotate-left"></i></button><button class="icon-button" data-action="redo" title="Redo (Ctrl Y)" aria-label="Redo"><i class="fa-solid fa-rotate-right"></i></button><div class="zoom-controls"><button class="icon-button" data-action="zoom-out" aria-label="Zoom out"><i class="fa-solid fa-minus"></i></button><select id="zoomSelect" class="form-select form-select-sm" aria-label="Zoom"><option value="fit-page">Fit page</option><option value="fit-width">Fit width</option><option value=".25">25%</option><option value=".5">50%</option><option value=".75">75%</option><option value="1">100%</option><option value="1.25">125%</option><option value="1.5">150%</option><option value="2">200%</option><option value="3">300%</option><option value="4">400%</option></select><button class="icon-button" data-action="zoom-in" aria-label="Zoom in"><i class="fa-solid fa-plus"></i></button></div><button class="btn btn-outline-secondary btn-sm" data-action="inspect"><i class="fa-solid fa-circle-info"></i> Info</button></div>
        <div id="contextToolbar" class="context-toolbar"></div>
        <div class="pdf-body">
          <aside id="thumbnailPanel" class="thumbnail-panel" aria-label="Pages"><div class="thumbnail-actions"><button class="btn btn-sm btn-outline-secondary" data-action="select-all" title="Select all pages">All</button><button class="btn btn-sm btn-outline-secondary" data-action="invert-selection" title="Invert selection">Invert</button><button class="btn btn-sm btn-outline-secondary" data-action="select-range" title="Select page range">Range</button></div><div id="thumbnailList"></div></aside>
          <div id="pageScroll" class="page-scroll"><div id="pageStage" class="page-stage"><canvas id="pdfCanvas" aria-label="PDF page"></canvas><canvas id="overlayCanvas"></canvas><div id="cropRubber" class="crop-rubber" hidden></div></div><div id="renderMessage" class="render-message" hidden></div></div>
          <aside id="objectInspector" class="object-inspector"><div class="inspector-heading">Object properties</div><div id="inspectorEmpty" class="muted-help">Select an object to change its appearance. Original PDF content remains a rendered background.</div><div id="inspectorFields" hidden>
            <label class="form-label" for="objectText">Text</label><textarea id="objectText" class="form-control form-control-sm" rows="3" data-prop="text"></textarea>
            <div class="row g-2"><div class="col-7"><label for="objectFont" class="form-label">Font</label><select id="objectFont" class="form-select form-select-sm" data-prop="fontFamily"><option>Arial</option><option>Times New Roman</option><option>Georgia</option><option>Courier New</option><option>Verdana</option><option>cursive</option></select></div><div class="col-5"><label for="objectSize" class="form-label">Size (pt)</label><input id="objectSize" class="form-control form-control-sm" type="number" min="1" max="500" data-prop="fontSize"></div></div>
            <div class="row g-2 mt-1"><div class="col-6"><label for="objectFill" class="form-label">Color</label><input id="objectFill" type="color" class="form-control form-control-color" data-prop="fill"></div><div class="col-6"><label for="objectBackground" class="form-label">Background</label><input id="objectBackground" type="color" class="form-control form-control-color" data-prop="backgroundColor"><button class="btn btn-link btn-sm p-0" data-action="clear-background">Clear</button></div></div>
            <div class="btn-group w-100 my-2"><button class="btn btn-outline-secondary btn-sm" data-action="bold" aria-label="Bold"><b>B</b></button><button class="btn btn-outline-secondary btn-sm" data-action="italic" aria-label="Italic"><i>I</i></button><button class="btn btn-outline-secondary btn-sm" data-action="underline" aria-label="Underline"><u>U</u></button><button class="btn btn-outline-secondary btn-sm" data-action="strike" aria-label="Strike through"><s>S</s></button></div>
            <label for="objectAlign" class="form-label">Text alignment</label><select id="objectAlign" class="form-select form-select-sm" data-prop="textAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option><option value="justify">Justify</option></select>
            <label for="objectSpacing" class="form-label mt-2">Letter spacing (1/1000 em)</label><input id="objectSpacing" class="form-control form-control-sm" type="number" min="-300" max="1500" data-prop="charSpacing">
            <label for="objectOpacity" class="form-label mt-2">Opacity</label><input id="objectOpacity" class="form-range" type="range" min="0" max="1" step=".01" data-prop="opacity">
            <div class="row g-2"><div class="col-6"><label for="objectAngle" class="form-label">Rotation</label><input id="objectAngle" class="form-control form-control-sm" type="number" data-prop="angle"></div><div class="col-6"><label for="objectStroke" class="form-label">Stroke (pt)</label><input id="objectStroke" class="form-control form-control-sm" type="number" min="0" max="100" data-prop="strokeWidth"></div></div>
            <div class="row g-2 mt-1"><div class="col-6"><label for="objectX" class="form-label">X (pt)</label><input id="objectX" class="form-control form-control-sm" type="number" data-prop="left"></div><div class="col-6"><label for="objectY" class="form-label">Y (pt)</label><input id="objectY" class="form-control form-control-sm" type="number" data-prop="top"></div></div>
            <div class="row g-2 mt-1"><div class="col-6"><label for="objectW" class="form-label">Width</label><input id="objectW" class="form-control form-control-sm" type="number" min="1" data-size="width"></div><div class="col-6"><label for="objectH" class="form-label">Height</label><input id="objectH" class="form-control form-control-sm" type="number" min="1" data-size="height"></div></div>
            <div class="btn-group w-100 mt-3"><button class="btn btn-outline-secondary btn-sm" data-action="flip-x">Flip X</button><button class="btn btn-outline-secondary btn-sm" data-action="flip-y">Flip Y</button><button class="btn btn-outline-secondary btn-sm" data-action="lock">Lock</button></div><button class="btn btn-outline-secondary btn-sm w-100 mt-2" data-action="crop-image">Crop image</button><button class="btn btn-outline-danger btn-sm w-100 mt-2" data-action="delete-object">Delete object</button>
          </div><div class="inspector-heading mt-4">Layers</div><div id="layersList" class="layers-list"></div><small class="muted-help">Visual signatures are images or text. They are not certificate signatures.</small></aside>
        </div>
      </section>
      <section id="documentPanel" class="doc-panel" hidden aria-label="Create document">
  <div class="doc-commandbar d-flex flex-wrap align-items-center gap-2 p-2 border-bottom">
    <div class="btn-group btn-group-sm" aria-label="Document files">
      <button type="button" class="btn btn-outline-secondary" data-doc-action="new"><i class="fa-solid fa-file me-1"></i>New</button>
      <button type="button" class="btn btn-outline-secondary" data-doc-action="open"><i class="fa-solid fa-folder-open me-1"></i>Import</button>
      <button type="button" class="btn btn-outline-secondary" data-doc-action="save"><i class="fa-solid fa-floppy-disk me-1"></i>Source</button>
    </div>
    <details class="doc-menu">
      <summary class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-file-lines me-1"></i>Layout</summary>
      <div class="doc-menu-content">
        <button type="button" data-doc-action="layout">Page size, margins, columns</button>
        <button type="button" data-doc-action="headers">Headers and footers</button>
        <button type="button" data-doc-action="metadata">Document metadata</button>
        <button type="button" data-doc-action="paragraph">Paragraph spacing and direction</button>
        <button type="button" data-doc-action="pagebreak">Insert page break</button>
        <button type="button" data-doc-action="section">Insert column section</button>
      </div>
    </details>
    <details class="doc-menu">
      <summary class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-square-plus me-1"></i>Insert</summary>
      <div class="doc-menu-content">
        <button type="button" data-doc-action="image">Local image</button>
        <button type="button" data-doc-action="caption">Image caption / alt / wrapping</button>
        <button type="button" data-doc-action="crop">Crop selected image</button>
        <button type="button" data-doc-action="table">Table and cell properties</button>
        <button type="button" data-doc-action="checklist">Checklist</button>
        <button type="button" data-doc-action="quote">Block quote</button>
        <button type="button" data-doc-action="anchor">Named anchor</button>
        <button type="button" data-doc-action="toc">Table of contents</button>
        <button type="button" data-doc-action="date">Current date</button>
        <button type="button" data-doc-action="time">Current time</button>
        <button type="button" data-doc-action="qr">QR code</button>
        <button type="button" data-doc-action="barcode">Barcode</button>
        <button type="button" data-doc-action="signature">Visual signature</button>
      </div>
    </details>
    <label class="small ms-auto" for="docEngine">PDF engine</label>
    <select id="docEngine" class="form-select form-select-sm doc-engine" aria-label="Document PDF rendering engine"><option value="browser">Browser print · local</option></select>
    <button type="button" class="btn btn-sm btn-outline-secondary" data-doc-action="preview"><i class="fa-solid fa-eye me-1"></i>Print preview</button>
    <button type="button" class="btn btn-sm btn-primary" data-doc-action="export"><i class="fa-solid fa-file-pdf me-1"></i>Export PDF</button>
  </div>
  <div class="doc-info px-3 py-2 small d-flex flex-wrap gap-3 border-bottom">
    <span id="docPrivacy"><i class="fa-solid fa-shield-halved me-1"></i>Locally in your browser</span>
    <span id="docPageInfo">A4 · portrait · 20 mm margins</span>
    <span class="ms-auto" id="docCounts" aria-live="polite">0 words · 0 characters</span>
  </div>
  <div class="doc-edit-workspace p-3">
    <div id="docLoadMessage" class="alert alert-info">The document editor loads when this mode is opened.</div>
    <textarea id="docEditor" aria-label="Document source"><h1>Untitled document</h1><p>Write your document here.</p></textarea>
  </div>
  <p class="doc-help small px-3 pb-2 mb-0">Editing is continuous. Print preview uses the selected paper size and shows final pagination. Table cell selection, merging, splitting and column resizing are available in Jodit's table popup. Browser print preserves modern HTML/CSS most faithfully; page counters and margin headers depend on your browser.</p>
</section>

    </main>
    <footer class="status-bar"><button class="icon-button d-xl-none" data-action="sidebar" aria-label="Toggle tool navigation"><i class="fa-solid fa-bars"></i></button><span id="statusDocument">No document open</span><span id="statusPage"></span><span id="privacyIndicator"><i class="fa-solid fa-shield-halved"></i> Locally in your browser</span><span id="statusInfo" class="ms-auto">Ready</span></footer>
  </div>
  <div class="modal" id="toolModal" tabindex="-1" aria-labelledby="toolModalTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><form class="modal-content" id="toolForm"><div class="modal-header"><h2 class="modal-title fs-5" id="toolModalTitle"></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="toolModalBody"></div><div id="modalError" class="alert alert-danger mx-3" role="alert" hidden></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary" id="toolSubmit">Apply</button></div></form></div></div>
  <div class="job-backdrop" id="jobPanel" hidden><div class="job-card" role="dialog" aria-modal="true" aria-labelledby="jobTitle"><div class="spinner-border text-primary" aria-hidden="true"></div><h2 class="fs-5" id="jobTitle">Processing</h2><div class="progress"><div id="jobProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-label="Operation progress"></div></div><p id="jobDetail" aria-live="polite"></p><button class="btn btn-outline-danger" id="cancelJob">Cancel</button><small class="text-secondary">Downloads contain your selected changes. The opened source remains local.</small></div></div>
  <div id="toastRegion" class="toast-region" aria-live="polite"></div>
  <div id="loadingBanner" class="loading-banner"><span class="spinner-border spinner-border-sm"></span> Loading PDF tools…</div>
  <script>window.PDF_STUDIO_BOOT = <?= json_encode($studioBoot, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>;</script>
  <script>
'use strict';
const STUDIO_VERSIONS = Object.freeze({pdfjs:'6.4.299',pdfLib:'1.17.1',fabric:'7.4.0',purify:'3.4.16',sortable:'1.15.7',zip:'3.10.2',bootstrap:'5.3.8'});
const CDN = 'https://cdn.jsdelivr.net/npm/';
class StudioI18n {
  constructor(catalog,language='it'){
    this.catalog=catalog;this.language=['it','en','de'].includes(language)?language:'it';
    this.key='pdfstudio.language.'+location.origin+location.pathname;
    const requested=new URLSearchParams(location.search).get('lang');
    if(['it','en','de'].includes(requested))this.language=requested;
    else try{const saved=localStorage.getItem(this.key);if(['it','en','de'].includes(saved))this.language=saved;}catch{}
    this.records=new WeakMap();this.attributes=new WeakMap();this.cache=new Map();this.compile();
    this.ignore='script,style,textarea,code,pre,canvas,[contenteditable="true"],[contenteditable=""],.jodit-wysiwyg,[translate="no"],[data-i18n-ignore],.tools-word-diff';
  }
  normalize(value){return String(value).replace(/\s+/g,' ').trim();}
  regex(value){return value.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');}
  compile(){
    this.cache.clear();this.reverse=new Map();this.patterns=[];
    const fragments=[];
    for(const [source,translations] of Object.entries(this.catalog)){
      for(const language of ['it','de'])if(translations[language])this.reverse.set(this.normalize(translations[language]),source);
      const tokens=[...source.matchAll(/\{(\d+)\}|%[sd]/g)];
      if(tokens.length){
        let index=0,pattern='',positions=[];
        for(const token of tokens){pattern+=this.regex(source.slice(index,token.index))+'(.+?)';positions.push(token[0]);index=token.index+token[0].length;}
        pattern+=this.regex(source.slice(index));
        this.patterns.push({source,match:new RegExp('^'+pattern+'$'),positions,weight:source.replace(/\{\d+\}|%[sd]/g,'').length});
      }else if(source.length>1)fragments.push(source);
    }
    this.patterns.sort((a,b)=>b.weight-a.weight);
    fragments.sort((a,b)=>b.length-a.length);
    // Complete phrases take precedence. Fragments also cover joined labels and
    // server error details; values, filenames and document content stay intact.
    this.fragments=new RegExp('(?<![\\p{L}\\p{N}_])(?:'+fragments.map(s=>this.regex(s)).join('|')+')(?![\\p{L}\\p{N}_])','gu');
  }
  registerEditor(languages){
    for(const source of new Set([...Object.keys(languages?.it||{}),...Object.keys(languages?.de||{})])){
      if(this.catalog[source])continue;
      const it=languages.it?.[source],de=languages.de?.[source];
      if(typeof it==='string'&&typeof de==='string')this.catalog[source]={it,de};
    }
    this.compile();
    // The editor's native wording can differ from our preferred translation.
    // Remember both forms so its open toolbar also switches back to English.
    for(const language of ['it','de'])for(const [source,translated] of Object.entries(languages?.[language]||{})){
      if(typeof translated==='string')this.reverse.set(this.normalize(translated),source);
    }
  }
  source(value){const trimmed=this.normalize(value);return Object.prototype.hasOwnProperty.call(this.catalog,trimmed)?trimmed:(this.reverse.get(trimmed)||trimmed);}
  text(value){
    const raw=String(value??'');if(this.language==='en'||!raw.trim())return raw;
    if(this.cache.has(raw))return this.cache.get(raw);
    const normalized=this.normalize(raw),direct=this.catalog[normalized]?.[this.language];
    let translated=direct;
    if(!translated){
      for(const rule of this.patterns){
        const match=normalized.match(rule.match);if(!match)continue;
        const template=this.catalog[rule.source]?.[this.language];if(!template)continue;
        let sequential=0;
        translated=template.replace(/\{\d+\}|%[sd]/g,token=>{
          const position=token[0]==='%'?sequential++:rule.positions.indexOf(token);
          return position<0?token:match[position+1];
        });break;
      }
    }
    if(!translated){
      // Protect file names and URLs inside error messages and status details.
      translated=normalized.split(/((?:https?:\/\/|mailto:)[^\s]+|[^\s]+\.(?:pdf|php|docx|odt|xlsx|pptx|zip|json|html|txt|png|jpe?g|webp|gif|bmp)\b)/gi).map((part,i)=>i%2?part:part.replace(this.fragments,source=>this.catalog[source]?.[this.language]||source)).join('');
    }
    const result=raw.match(/^\s*/)[0]+translated+raw.match(/\s*$/)[0];
    if(this.cache.size>4000)this.cache.clear();this.cache.set(raw,result);return result;
  }
  blocked(node){const element=node.nodeType===Node.ELEMENT_NODE?node:node.parentElement;return !element||!!element.closest(this.ignore);}
  translateText(node){
    if(this.blocked(node))return;
    let record=this.records.get(node);
    if(!record||node.data!==record.rendered)record={source:this.source(node.data),space:[node.data.match(/^\s*/)[0],node.data.match(/\s*$/)[0]]};
    const translated=record.space[0]+this.text(record.source)+record.space[1];
    record.rendered=translated;this.records.set(node,record);if(node.data!==translated)node.data=translated;
  }
  translateElement(element){
    if(this.blocked(element))return;
    if(element.tagName==='OPTION'&&!element.hasAttribute('value'))element.value=element.textContent;
    let records=this.attributes.get(element);if(!records){records={};this.attributes.set(element,records);}
    for(const name of ['title','placeholder','aria-label','alt']){
      if(!element.hasAttribute(name))continue;
      const current=element.getAttribute(name);let record=records[name];
      if(!record||current!==record.rendered)record={source:this.source(current)};
      record.rendered=this.text(record.source);records[name]=record;
      if(current!==record.rendered)element.setAttribute(name,record.rendered);
    }
  }
  apply(root=document.body){
    if(!root||this.blocked(root))return;
    if(root.nodeType===Node.TEXT_NODE){this.translateText(root);return;}
    this.translateElement(root);
    const walker=document.createTreeWalker(root,NodeFilter.SHOW_ELEMENT|NodeFilter.SHOW_TEXT,{acceptNode:node=>this.blocked(node)?NodeFilter.FILTER_REJECT:NodeFilter.FILTER_ACCEPT});
    let node;while(node=walker.nextNode())if(node.nodeType===Node.TEXT_NODE)this.translateText(node);else this.translateElement(node);
  }
  observe(){this.observer.observe(document.body,{subtree:true,childList:true,characterData:true,attributes:true,attributeFilter:['title','placeholder','aria-label','alt','translate']});}
  start(){
    document.documentElement.lang=this.language;
    this.observer=new MutationObserver(records=>{
      this.observer.disconnect();
      for(const record of records){if(record.type==='childList'){for(const node of record.addedNodes)this.apply(node);}else this.apply(record.target);}
      this.observe();
    });
    this.apply();this.observe();
    const selector=document.getElementById('studioLanguage');
    if(selector){selector.value=this.language;selector.addEventListener('change',()=>this.setLanguage(selector.value));}
  }
  setLanguage(language){
    if(!['it','en','de'].includes(language))return;
    this.language=language;this.cache.clear();document.documentElement.lang=language;
    try{localStorage.setItem(this.key,language);}catch{}
    document.cookie='PDFSTUDIOLANG='+language+'; Path='+location.pathname+'; Max-Age=31536000; SameSite=Strict'+(location.protocol==='https:'?'; Secure':'');
    const selector=document.getElementById('studioLanguage');if(selector)selector.value=language;
    if(window.Studio?.documentEditor?.editor)window.Studio.documentEditor.editor.o.language=language;
    this.observer?.disconnect();this.apply();if(this.observer)this.observe();
  }
}

class PDFStudio {
  constructor() {
    this.pages=[]; this.sources=new Map(); this.selected=new Set(); this.current=0; this.mode='home'; this.group='Edit PDF'; this.zoom='fit-page'; this.scale=1;
    this.metadata={}; this.name='document.pdf'; this.dirty=false; this.history=[]; this.historyIndex=-1; this.renderEpoch=0; this.canvas=null; this.clipboard=null; this.suppress=false; this.toolMode='select'; this.drawStart=null; this.drawObject=null; this.activeJob=null;
    this.server={tools:{},engines:{html:[],merge:[]},limits:{maxFile:104857600,maxPages:500}}; this.toolDefs=[]; this.autosaveTimer=null; this.scripts=new Map(); this.currentPageId=null; this.recent=[];
    this.overlayProps=['studioKind','studioLink','studioLabel','studioLocked'];
    this.groups=[['Home','fa-house'],['Organize','fa-layer-group'],['Edit PDF','fa-file-pen'],['Annotate','fa-comment-dots'],['Fill & Sign','fa-signature'],['Create','fa-file-circle-plus'],['Convert','fa-arrows-rotate'],['OCR','fa-spell-check'],['Forms','fa-list-check'],['Secure','fa-shield-halved'],['Optimize','fa-gauge-high'],['Advanced','fa-toolbox']];
  }
  $(selector){return document.querySelector(selector);}
  escape(value){return String(value??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
  sanitize(html){
    if(!window.DOMPurify)throw new Error('The HTML sanitizer could not load. HTML import is disabled.');
    const clean=DOMPurify.sanitize(String(html),{USE_PROFILES:{html:true},FORBID_TAGS:['iframe','object','embed','script','style','form','input','button','video','audio'],FORBID_ATTR:['srcset','action','formaction','background']});
    const t=document.createElement('template');t.innerHTML=clean;
    for(const e of t.content.querySelectorAll('*')){
      for(const a of [...e.attributes]){
        if(a.name==='src'&&!/^data:image\/(png|jpeg|webp|gif);base64,/i.test(a.value))e.removeAttribute(a.name);
        if(a.name==='href'&&!/^(https?:|mailto:|tel:|#)/i.test(a.value))e.removeAttribute(a.name);
        if(a.name==='style'&&/[\\\x00-\x1f]|url\s*\(|image-set|expression|@|behavior|javascript:/i.test(a.value))e.removeAttribute(a.name);
      }
    }
    return t.innerHTML;
  }
  notify(message,type='info'){
    const e=document.createElement('div');e.className='studio-toast '+type;e.textContent=message;this.$('#toastRegion').append(e);setTimeout(()=>e.remove(),type==='error'?12000:6000);
  }
  error(e){if(e?.name==='AbortError'){this.notify('Operation cancelled.');return;}console.error('PDF Studio:',e);this.notify(e?.message||String(e),'error');}
  async loadScript(url,globalName){
    if(globalName&&window[globalName])return window[globalName];
    if(!this.scripts.has(url))this.scripts.set(url,new Promise((resolve,reject)=>{const s=document.createElement('script');s.src=url;s.crossOrigin='anonymous';s.onload=()=>resolve(globalName?window[globalName]:true);s.onerror=()=>reject(new Error('Could not load '+(globalName||'a required library')+'. Check your network or CDN policy.'));document.head.append(s);}));
    return this.scripts.get(url);
  }
  async init(){
    let theme='light';try{theme=localStorage.getItem('pdfstudio.theme')||theme;this.recent=JSON.parse(localStorage.getItem('pdfstudio.recent')||'[]');}catch{}document.documentElement.dataset.bsTheme=theme;
    this.bindUI();this.renderNavigation();this.renderRecents();
    await this.loadScript(CDN+'bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js','bootstrap');
    const capabilities=fetch('?action=capabilities',{credentials:'same-origin',cache:'no-store'}).then(r=>r.json()).then(c=>{if(c.error)throw new Error(c.error.message||c.error);this.server=c;}).catch(e=>this.notify('Server capability check failed: '+e.message+'. Local tools remain available.'));
    const packages=[['pdf-lib@1.17.1/dist/pdf-lib.min.js','PDFLib'],['fabric@7.4.0/dist/index.min.js','fabric'],['dompurify@3.4.16/dist/purify.min.js','DOMPurify'],['sortablejs@1.15.7/Sortable.min.js','Sortable'],['jszip@3.10.2/dist/jszip.min.js','JSZip']];
    const results=await Promise.allSettled(packages.map(([p,g])=>this.loadScript(CDN+p,g)));
    results.forEach(r=>{if(r.status==='rejected')this.error(r.reason);});
    try{window.pdfjsLib=await import(CDN+'pdfjs-dist@6.4.299/legacy/build/pdf.mjs');pdfjsLib.GlobalWorkerOptions.workerSrc=CDN+'pdfjs-dist@6.4.299/legacy/build/pdf.worker.mjs';}catch(e){this.error(new Error('PDF rendering could not load: '+e.message));}
    await capabilities;
    this.documentEditor=new DocumentStudio(this);this.tools=new PDFTools(this);
    this.toolDefs=[{id:'edit',title:'Edit PDF',group:'Edit PDF',icon:'fa-file-pen',requirements:'editing',description:'Text, images, shapes and drawings'},{id:'create',title:'Create Document',group:'Create',icon:'fa-file-circle-plus',requirements:'document',description:'Word-like document authoring'},{id:'merge',title:'Merge files',group:'Organize',icon:'fa-object-group',requirements:'browser',description:'Combine PDFs and images'},{id:'organize',title:'Organize pages',group:'Organize',icon:'fa-layer-group',requirements:'browser',description:'Reorder, rotate, extract and duplicate'},{id:'image-pdf',title:'Images to PDF',group:'Convert',icon:'fa-images',requirements:'browser',description:'Arrange photos on PDF pages'},{id:'sign',title:'Visual signature',group:'Fill & Sign',icon:'fa-signature',requirements:'editing',description:'Draw, type or upload a signature'},{id:'scan',title:'Camera / scan',group:'Convert',icon:'fa-camera',requirements:'browser',description:'Capture and preprocess an image'},...(this.tools.definitions||this.tools.toolDefs||[])];
    // The utility module can alternatively publish its registry directly.
    if(!this.tools.definitions&&!this.tools.toolDefs&&PDFTools.definitions)this.toolDefs.push(...PDFTools.definitions);
    this.renderHome();this.$('#loadingBanner').hidden=true;this.status();
    this.updates=new StudioUpdates(this,window.PDF_STUDIO_BOOT.release);
    this.updates.announceUpgrade();
    this.updates.check(false);
  }
  available(def){
    const req=def.requirements||'browser';if(req==='document')return !!window.DOMPurify;if(req==='editing')return !!window.fabric&&!!window.PDFLib&&!!window.pdfjsLib;if(req==='browser')return !!window.PDFLib&&!!window.pdfjsLib;
    return req.split('|').some(k=>this.server.tools[k]?.available);
  }
  renderNavigation(){this.$('#toolNavigation').innerHTML=this.groups.map(([g,icon])=>`<button class="nav-tool ${g==='Home'?'active':''}" data-group="${this.escape(g)}"><i class="fa-solid ${icon}" aria-hidden="true"></i>${this.escape(g)}</button>`).join('');}
  renderHome(){
    const ids=['edit','create','merge','split','compress','organize','pdf-images','image-pdf','ocr','sign','protect','office-convert'];
    this.$('#homeCards').innerHTML=ids.map(id=>{const t=this.toolDefs.find(t=>t.id===id);if(!t)return '';const available=this.available(t);const reason=available?'':`Requires ${t.requirements}`;return `<button class="tool-card" data-action="${id}" ${available?'':`disabled title="${this.escape(reason)}"`}><span class="card-icon"><i class="fa-solid ${t.icon||'fa-file-pdf'}"></i></span><strong>${this.escape(t.title)}</strong><small>${this.escape(t.description||reason||'Open this tool')}</small></button>`;}).join('');
  }
  renderRecents(){this.$('#recentFiles').innerHTML=this.recent.slice(0,6).map(f=>`<div class="recent-file"><i class="fa-regular fa-file-pdf"></i><strong translate="no">${this.escape(f.name)}</strong><span>${this.formatBytes(f.size)}</span><span class="ms-auto">${this.escape(new Date(f.date).toLocaleString())}</span></div>`).join('');}
  formatBytes(n){if(n<1024)return n+' B';const k=Math.floor(Math.log(n)/Math.log(1024));return (n/1024**k).toFixed(1)+' '+['B','KB','MB','GB'][k];}
  bindUI(){
    document.addEventListener('click',e=>{const b=e.target.closest('[data-action],[data-group]');if(!b||b.disabled)return;if(b.dataset.group){this.switchGroup(b.dataset.group).catch(x=>this.error(x));return;}Promise.resolve(this.action(b.dataset.action,b)).catch(x=>this.error(x));});
    this.$('#homeDrop').addEventListener('click',()=>this.action('open').catch(e=>this.error(e)));this.$('#homeDrop').addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();this.action('open').catch(x=>this.error(x));}});
    document.addEventListener('dragover',e=>{if(e.dataTransfer?.types.includes('Files')){e.preventDefault();this.$('#homeDrop').classList.add('drag-over');}});
    document.addEventListener('dragleave',e=>{if(!e.relatedTarget)this.$('#homeDrop').classList.remove('drag-over');});
    document.addEventListener('drop',e=>{if(!e.dataTransfer?.files.length)return;if(e.target.closest('#documentPanel'))return;e.preventDefault();this.$('#homeDrop').classList.remove('drag-over');const f=[...e.dataTransfer.files];this.busy('Opening documents',j=>this.openFiles(f,j)).catch(x=>this.error(x));});
    this.$('#zoomSelect').addEventListener('change',e=>{this.zoom=e.target.value;this.renderCurrent().catch(x=>this.error(x));});
    this.$('#pageScroll').addEventListener('wheel',e=>{if(e.ctrlKey){e.preventDefault();this.changeZoom(e.deltaY<0?1.15:1/1.15);}}, {passive:false});
    this.$('#cancelJob').addEventListener('click',()=>{this.activeJob?.controller.abort();this.$('#jobDetail').textContent='Cancelling…';});
    this.$('#toolForm').addEventListener('submit',async e=>{e.preventDefault();if(!this.modalHandler)return;const form=e.currentTarget;if(!form.reportValidity())return;const values={};for(const el of form.elements){if(!el.name||el.disabled)continue;if(el.type==='checkbox')values[el.name]=el.checked;else if(el.type==='radio'){if(el.checked)values[el.name]=el.value;}else if(el.type==='file')values[el.name]=el.files;else if(el.multiple)values[el.name]=[...el.selectedOptions].map(x=>x.value);else values[el.name]=el.value;}this.$('#modalError').hidden=true;this.$('#toolSubmit').disabled=true;try{const result=await this.modalHandler(values,form);if(result!==false)bootstrap.Modal.getInstance(this.$('#toolModal'))?.hide();}catch(x){this.$('#modalError').textContent=x.message||String(x);this.$('#modalError').hidden=false;this.error(x);}finally{this.$('#toolSubmit').disabled=false;}});
    for(const el of document.querySelectorAll('[data-prop],[data-size]'))el.addEventListener('change',()=>this.inspectorChange(el));
    this.$('#thumbnailList').addEventListener('click',e=>{const row=e.target.closest('.thumbnail');if(!row)return;const i=this.pages.findIndex(p=>p.id===row.dataset.page);if(i<0)return;if(e.target.matches('input')||e.ctrlKey||e.metaKey){if(this.selected.has(row.dataset.page))this.selected.delete(row.dataset.page);else this.selected.add(row.dataset.page);}else if(e.shiftKey){const a=Math.min(this.current,i),b=Math.max(this.current,i);for(let n=a;n<=b;n++)this.selected.add(this.pages[n].id);}this.captureOverlay();this.current=i;this.renderCurrent().catch(x=>this.error(x));this.updateThumbnailSelection();});
    this.$('#layersList').addEventListener('click',e=>{const b=e.target.closest('[data-layer]');if(!b)return;const obj=this.canvas?.getObjects()[Number(b.dataset.layer)];if(!obj)return;if(b.dataset.unlock){obj.studioLocked=false;obj.set({selectable:true,evented:true,lockMovementX:false,lockMovementY:false,lockScalingX:false,lockScalingY:false,lockRotation:false});this.canvas.setActiveObject(obj);this.commit();}else this.canvas.setActiveObject(obj);this.canvas.requestRenderAll();this.inspector();});
    document.addEventListener('keydown',e=>this.keydown(e));
    document.addEventListener('paste',e=>{if(this.mode!=='pdf'||this.isTyping(e.target))return;const item=[...(e.clipboardData?.items||[])].find(i=>i.type.startsWith('image/'));if(item){e.preventDefault();this.addImage(item.getAsFile()).catch(x=>this.error(x));}});
    window.addEventListener('beforeunload',e=>{if(this.dirty){e.preventDefault();e.returnValue='';}});
    new ResizeObserver(()=>{if(this.mode==='pdf'&&typeof this.zoom==='string'&&this.zoom.startsWith('fit')){clearTimeout(this.resizeTimer);this.resizeTimer=setTimeout(()=>this.renderCurrent().catch(x=>this.error(x)),120);}}).observe(this.$('#pageScroll'));
  }
  isTyping(target){return !!target?.closest('input,textarea,select,[contenteditable=true],.jodit-container');}
  keydown(e){
    if(this.activeJob){if(e.key==='Escape')this.activeJob.controller.abort();return;}
    const mod=e.ctrlKey||e.metaKey;
    if(mod&&e.key.toLowerCase()==='o'){e.preventDefault();this.action('open').catch(x=>this.error(x));return;}
    if(mod&&e.key.toLowerCase()==='s'){e.preventDefault();this.action('export').catch(x=>this.error(x));return;}
    if(mod&&e.key.toLowerCase()==='k'){e.preventDefault();this.palette();return;}
    if(this.isTyping(e.target)||this.$('#toolModal').classList.contains('show'))return;
    const map={z:e.shiftKey?'redo':'undo',y:'redo',c:'copy',x:'cut',v:'paste',d:'duplicate-object'};
    if(mod&&map[e.key.toLowerCase()]){e.preventDefault();this.action(map[e.key.toLowerCase()]).catch(x=>this.error(x));return;}
    if(mod&&['+','=','-'].includes(e.key)){e.preventDefault();this.changeZoom(e.key==='-'?1/1.2:1.2);return;}
    if(e.key==='Delete'||e.key==='Backspace'){e.preventDefault();this.deleteObjects();}
    if(e.key==='Escape'){this.toolMode='select';if(this.canvas){this.canvas.isDrawingMode=false;this.canvas.discardActiveObject();this.canvas.requestRenderAll();this.inspector();}}
    if(['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(e.key)&&this.canvas?.getActiveObject()){
      e.preventDefault();const amount=e.shiftKey?10:1;const o=this.canvas.getActiveObject();o.set({left:o.left+(e.key==='ArrowRight'?amount:e.key==='ArrowLeft'?-amount:0),top:o.top+(e.key==='ArrowDown'?amount:e.key==='ArrowUp'?-amount:0)});o.setCoords();this.canvas.requestRenderAll();this.commit();this.inspector();
    }
  }
  dialog(title,html,onSubmit,opts={}){
    const el=this.$('#toolModal');const instance=bootstrap.Modal.getOrCreateInstance(el);this.$('#toolModalTitle').textContent=title;this.$('#toolModalBody').innerHTML=html;this.$('#toolSubmit').textContent=opts.submit||'Apply';this.$('#toolSubmit').hidden=opts.submit===false;this.$('#toolSubmit').disabled=false;this.$('#modalError').hidden=true;el.querySelector('.modal-dialog').classList.toggle('modal-xl',!!opts.wide);this.modalHandler=onSubmit;queueMicrotask(()=>{instance.show();opts.onOpen?.(el);});return el;
  }
  async busy(label,fn){
    if(this.activeJob)throw new Error('A processing operation is already running. Wait or cancel it first.');
    const controller=new AbortController();const start=Date.now();const job={controller,signal:controller.signal,check(){if(controller.signal.aborted)throw new DOMException('Cancelled','AbortError');},progress:(done,total,detail)=>{job.check();const bar=this.$('#jobProgress');bar.style.width=total?Math.max(0,Math.min(100,done/total*100))+'%':'100%';bar.classList.toggle('progress-bar-striped',!total);if(total){bar.setAttribute('aria-valuenow',String(done));bar.setAttribute('aria-valuemax',String(total));}else{bar.removeAttribute('aria-valuenow');bar.removeAttribute('aria-valuemax');}const eta=done>0&&total>done&&Date.now()-start>1500?' · about '+Math.ceil((Date.now()-start)/done*(total-done)/1000)+' s remaining':'';this.$('#jobDetail').textContent=(detail|| (total?done+' / '+total:'Working…'))+eta;}};
    this.activeJob=job;this.$('#jobTitle').textContent=label;this.$('#jobPanel').hidden=false;job.progress(0,0);
    try{return await fn(job);}finally{this.activeJob=null;this.$('#jobPanel').hidden=true;this.$('#privacyIndicator').innerHTML='<i class="fa-solid fa-shield-halved"></i> Locally in your browser';}
  }
  async api(action,files=[],options={},job){
    job?.check();if(files.some(f=>(f.size||f.length||0)>this.server.limits.maxFile))throw new Error('This server accepts files up to '+this.formatBytes(this.server.limits.maxFile)+'. Increase PHP upload_max_filesize/post_max_size to process larger files on the server. Local PDF editing is still available.');this.$('#privacyIndicator').innerHTML='<i class="fa-solid fa-server"></i> On the server';if(this.activeJob)this.$('#jobDetail').textContent='Uploading to this server and processing…';
    const body=new FormData();body.append('csrf',PDF_STUDIO_BOOT.csrf);body.append('options',JSON.stringify(options));for(let i=0;i<files.length;i++){const f=files[i];body.append('files[]',f instanceof Blob?f:new Blob([f],{type:'application/pdf'}),f.name||'document-'+(i+1)+'.pdf');}
    const r=await fetch('?action='+encodeURIComponent(action),{method:'POST',body,credentials:'same-origin',headers:{'X-CSRF-Token':PDF_STUDIO_BOOT.csrf},signal:job?.signal});
    if(r.headers.get('Content-Type')?.includes('application/json')){const data=await r.json();if(!r.ok||data.error)throw new Error(typeof data.error==='string'?data.error:data.error?.message||data.message||'Server operation failed.');return data;}
    if(!r.ok)throw new Error('Server operation failed (HTTP '+r.status+').');const blob=await r.blob();job?.check();const d=r.headers.get('Content-Disposition')||'';const m=d.match(/filename="([^"]+)"/);return{blob,name:m?m[1]:action+'.pdf'};
  }
  download(data,name,mime='application/octet-stream'){
    const b=data instanceof Blob?data:new Blob([data],{type:mime});const url=URL.createObjectURL(b);const a=document.createElement('a');a.href=url;a.download=String(name).replace(/[\/\\\x00-\x1f]/g,'_');document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),60000);
  }
  pickFiles(accept,multiple=false){return new Promise(resolve=>{const i=document.createElement('input');i.type='file';i.accept=accept;i.multiple=multiple;i.style.display='none';document.body.append(i);let done=false;const finish=()=>{if(done)return;done=true;resolve([...i.files]);i.remove();};i.addEventListener('change',finish,{once:true});i.addEventListener('cancel',finish,{once:true});i.click();});}
  requireDocument(){if(!this.pages.length)throw new Error('Open a PDF or create pages first.');if(!window.PDFLib||!window.pdfjsLib)throw new Error('PDF.js and pdf-lib are required. Check the library loading errors.');}
  range(text,total){
    let value=String(text||'all').trim().toLowerCase();value=({tutte:'all',tutti:'all',alle:'all',dispari:'odd',ungerade:'odd',pari:'even',gerade:'even'})[value]||value;if(value==='all'||value==='')return Array.from({length:total},(_,i)=>i);if(value==='odd')return Array.from({length:total},(_,i)=>i).filter(i=>i%2===0);if(value==='even')return Array.from({length:total},(_,i)=>i).filter(i=>i%2===1);
    const out=[];for(const chunk of value.split(',')){const m=chunk.trim().match(/^(\d+)(?:\s*-\s*(\d+))?$/);if(!m)throw new Error('Use page ranges such as 1-4,7,10-12, or all, odd, even.');const a=Number(m[1]),b=Number(m[2]||a);if(a<1||b<a||b>total)throw new Error('Page range is outside this document (1–'+total+').');for(let n=a;n<=b;n++)out.push(n-1);}return [...new Set(out)];
  }
  getSelected(){const out=this.pages.map((p,i)=>this.selected.has(p.id)?i:-1).filter(i=>i>=0);return out.length?out:[this.current];}
  id(){if(crypto.randomUUID)return crypto.randomUUID();const bytes=crypto.getRandomValues(new Uint8Array(16));bytes[6]=(bytes[6]&15)|64;bytes[8]=(bytes[8]&63)|128;const h=[...bytes].map(n=>n.toString(16).padStart(2,'0')).join('');return h.slice(0,8)+'-'+h.slice(8,12)+'-'+h.slice(12,16)+'-'+h.slice(16,20)+'-'+h.slice(20);}
  async hash(bytes){
    if(crypto.subtle)return [...new Uint8Array(await crypto.subtle.digest('SHA-256',bytes))].map(n=>n.toString(16).padStart(2,'0')).join('');
    // SHA-256 identity checks also work on private HTTP installations without Web Crypto.
    const K=new Uint32Array([0x428a2f98,0x71374491,0xb5c0fbcf,0xe9b5dba5,0x3956c25b,0x59f111f1,0x923f82a4,0xab1c5ed5,0xd807aa98,0x12835b01,0x243185be,0x550c7dc3,0x72be5d74,0x80deb1fe,0x9bdc06a7,0xc19bf174,0xe49b69c1,0xefbe4786,0x0fc19dc6,0x240ca1cc,0x2de92c6f,0x4a7484aa,0x5cb0a9dc,0x76f988da,0x983e5152,0xa831c66d,0xb00327c8,0xbf597fc7,0xc6e00bf3,0xd5a79147,0x06ca6351,0x14292967,0x27b70a85,0x2e1b2138,0x4d2c6dfc,0x53380d13,0x650a7354,0x766a0abb,0x81c2c92e,0x92722c85,0xa2bfe8a1,0xa81a664b,0xc24b8b70,0xc76c51a3,0xd192e819,0xd6990624,0xf40e3585,0x106aa070,0x19a4c116,0x1e376c08,0x2748774c,0x34b0bcb5,0x391c0cb3,0x4ed8aa4a,0x5b9cca4f,0x682e6ff3,0x748f82ee,0x78a5636f,0x84c87814,0x8cc70208,0x90befffa,0xa4506ceb,0xbef9a3f7,0xc67178f2]);
    const H=new Uint32Array([0x6a09e667,0xbb67ae85,0x3c6ef372,0xa54ff53a,0x510e527f,0x9b05688c,0x1f83d9ab,0x5be0cd19]);const W=new Uint32Array(64),full=bytes.length-bytes.length%64,tail=new Uint8Array(Math.ceil((bytes.length-full+9)/64)*64);tail.set(bytes.subarray(full));tail[bytes.length-full]=128;const bitLength=bytes.length*8;new DataView(tail.buffer).setUint32(tail.length-8,Math.floor(bitLength/4294967296));new DataView(tail.buffer).setUint32(tail.length-4,bitLength>>>0);const rot=(x,n)=>(x>>>n)|(x<<(32-n));
    for(let offset=0;offset<full+tail.length;offset+=64){const block=offset<full?bytes.subarray(offset,offset+64):tail.subarray(offset-full,offset-full+64);for(let i=0;i<16;i++)W[i]=((block[i*4]<<24)|(block[i*4+1]<<16)|(block[i*4+2]<<8)|block[i*4+3])>>>0;for(let i=16;i<64;i++){const x=W[i-15],y=W[i-2];W[i]=(W[i-16]+(rot(x,7)^rot(x,18)^(x>>>3))+W[i-7]+(rot(y,17)^rot(y,19)^(y>>>10)))>>>0;}let[a,b,c,d,e,f,g,h]=H;for(let i=0;i<64;i++){const t1=(h+(rot(e,6)^rot(e,11)^rot(e,25))+((e&f)^(~e&g))+K[i]+W[i])>>>0,t2=((rot(a,2)^rot(a,13)^rot(a,22))+((a&b)^(a&c)^(b&c)))>>>0;h=g;g=f;f=e;e=(d+t1)>>>0;d=c;c=b;b=a;a=(t1+t2)>>>0;}[a,b,c,d,e,f,g,h].forEach((v,i)=>H[i]=(H[i]+v)>>>0);if(offset&&offset%1048576===0)await new Promise(r=>setTimeout(r,0));}
    return [...H].map(n=>n.toString(16).padStart(8,'0')).join('');
  }
  pdfOptions(bytes){return {data:bytes.slice(),cMapUrl:CDN+'pdfjs-dist@6.4.299/cmaps/',cMapPacked:true,standardFontDataUrl:CDN+'pdfjs-dist@6.4.299/standard_fonts/',wasmUrl:CDN+'pdfjs-dist@6.4.299/wasm/',isEvalSupported:false,stopAtErrors:false};}
  async source(bytes,name,job){
    bytes=bytes instanceof Uint8Array?bytes:new Uint8Array(bytes);if(bytes.length>(this.server.limits.maxLocalFile||this.server.limits.maxFile))throw new Error('This file exceeds the configured '+this.formatBytes(this.server.limits.maxLocalFile||this.server.limits.maxFile)+' limit.');
    if(!new TextDecoder().decode(bytes.subarray(0,1024)).includes('%PDF-'))throw new Error(name+' does not have a valid PDF signature.');job?.check();
    let lib;try{lib=await PDFLib.PDFDocument.load(bytes,{updateMetadata:false});}catch(e){if(/encrypt/i.test(e.message))throw new Error('This PDF is encrypted. Use Secure → Decrypt with its valid password before editing. qpdf must be installed for decryption.');throw new Error('Cannot read '+name+': '+e.message);}
    if(lib.getPageCount()>this.server.limits.maxPages)throw new Error('This document exceeds the '+this.server.limits.maxPages+' page limit.');
    const loadingTask=pdfjsLib.getDocument(this.pdfOptions(bytes));const pdfjs=await loadingTask.promise;const id=this.id();const hash=await this.hash(bytes);const src={id,name,bytes,lib,pdfjs,loadingTask,hash,size:bytes.length};this.sources.set(id,src);
    const pages=[];for(let i=0;i<lib.getPageCount();i++){job?.check();const page=await pdfjs.getPage(i+1);const [x,y,x2,y2]=page.view;const lp=lib.getPage(i);pages.push({id:this.id(),sourceId:id,index:i,rotation:((lp.getRotation().angle%360)+360)%360,width:x2-x,height:y2-y,view:{x,y,width:x2-x,height:y2-y},overlay:null});if(i%20===0)await new Promise(r=>setTimeout(r,0));}
    return {src,pages};
  }
  async openPDF(bytes,name='document.pdf',job){
    this.captureOverlay();const created=await this.source(bytes,name,job);const old=[...this.sources.keys()].filter(id=>id!==created.src.id);this.pages=created.pages;this.current=0;this.selected.clear();this.currentPageId=null;this.name=name;const doc=created.src.lib;
    this.metadata={title:doc.getTitle()||'',author:doc.getAuthor()||'',subject:doc.getSubject()||'',keywords:(doc.getKeywords()||''),creator:doc.getCreator()||'',producer:doc.getProducer()||''};
    // Release previous documents only after the new source has passed validation.
    for(const id of old){const s=this.sources.get(id);await s?.loadingTask.destroy();this.sources.delete(id);}
    this.history=[];this.historyIndex=-1;this.suppress=true;this.canvas?.clear();this.suppress=false;this.commit();this.dirty=false;this.setMode('pdf');await this.refresh();
    this.recent=[{name,size:created.src.bytes.length,date:Date.now()},...this.recent.filter(r=>r.name!==name)].slice(0,10);try{localStorage.setItem('pdfstudio.recent',JSON.stringify(this.recent));}catch{}this.renderRecents();return created;
  }
  async replaceBytes(bytes,name=this.name){
    this.captureOverlay();const created=await this.source(bytes,name);this.pages=created.pages;this.name=name;this.current=0;this.currentPageId=null;this.selected.clear();const doc=created.src.lib;this.metadata={title:doc.getTitle()||'',author:doc.getAuthor()||'',subject:doc.getSubject()||'',keywords:doc.getKeywords()||'',creator:doc.getCreator()||'',producer:doc.getProducer()||''};this.commit();this.setMode('pdf');await this.refresh();await this.pruneSources();return created;
  }
  async pruneSources(){
    const referenced=()=>new Set([...this.pages.map(p=>p.sourceId),...this.history.flatMap(h=>h.pages.map(p=>p.sourceId))]);let used=referenced();const bytes=()=>[...used].reduce((n,id)=>n+(this.sources.get(id)?.bytes.length||0),0);
    while(bytes()>300*1024*1024&&this.history.length>1&&this.historyIndex>0){this.history.shift();this.historyIndex--;used=referenced();}
    for(const [id,s] of this.sources)if(!used.has(id)){await s.loadingTask.destroy();this.sources.delete(id);}
  }
  async openFiles(files,job){
    if(!files.length)return;const projects=files.filter(f=>/\.pdfstudio\.json$|\.json$/i.test(f.name));if(projects.length){await this.restoreProject(JSON.parse(await projects[0].text()));return;}
    const pdfs=files.filter(f=>f.type==='application/pdf'||/\.pdf$/i.test(f.name));const images=files.filter(f=>f.type.startsWith('image/'));
    if(pdfs.length===1&&!images.length){await this.openPDF(new Uint8Array(await pdfs[0].arrayBuffer()),pdfs[0].name,job);return;}
    const doc=await PDFLib.PDFDocument.create();let done=0;for(const f of files){job?.check();if(pdfs.includes(f)){const src=await PDFLib.PDFDocument.load(await f.arrayBuffer(),{updateMetadata:false});if(src.getForm().hasXFA())throw new Error('XFA forms cannot be merged safely.');if(src.getForm().getFields().length)src.getForm().flatten();for(const p of await doc.copyPages(src,src.getPageIndices()))doc.addPage(p);}else if(images.includes(f)){const canvas=await this.imageCanvas(f);const png=await doc.embedPng(canvas.toDataURL('image/png'));const p=doc.addPage([canvas.width*.75,canvas.height*.75]);p.drawImage(png,{x:0,y:0,width:p.getWidth(),height:p.getHeight()});}else throw new Error('Unsupported file: '+f.name);if(doc.getPageCount()>this.server.limits.maxPages)throw new Error('The merged page count exceeds the configured limit.');job?.progress(++done,files.length,f.name);}
    if(!doc.getPageCount())throw new Error('No PDF or supported image files were selected.');await this.openPDF(await doc.save(),'merged.pdf',job);
  }
  captureOverlay(){if(!this.canvas||!this.currentPageId||this.suppress)return;const p=this.pages.find(p=>p.id===this.currentPageId);if(p)p.overlay=this.canvas.toObject(this.overlayProps);}
  snapshot(){this.captureOverlay();return {pages:JSON.parse(JSON.stringify(this.pages)),metadata:{...this.metadata},current:this.current,name:this.name};}
  commit(){if(this.suppress)return;const snapshot=this.snapshot();const serial=JSON.stringify(snapshot);if(this.historyIndex>=0&&JSON.stringify(this.history[this.historyIndex])===serial)return;this.history=this.history.slice(0,this.historyIndex+1);this.history.push(snapshot);if(this.history.length>25)this.history.shift();this.historyIndex=this.history.length-1;this.markDirty();this.updateLayers();this.status();}
  markDirty(){this.dirty=true;clearTimeout(this.autosaveTimer);this.autosaveTimer=setTimeout(()=>this.saveAutosave().catch(()=>{}),1500);}
  async undo(direction=-1){const n=this.historyIndex+direction;if(n<0||n>=this.history.length)return;this.historyIndex=n;const snap=this.history[n];this.suppress=true;this.currentPageId=null;this.pages=JSON.parse(JSON.stringify(snap.pages));this.metadata={...snap.metadata};this.current=snap.current;this.name=snap.name;this.selected.clear();this.suppress=false;this.markDirty();await this.refresh();}
  dimensions(p){return p.rotation%180?{width:p.height,height:p.width}:{width:p.width,height:p.height};}
  async renderPage(p,scale=1,opts={}){
    const source=this.sources.get(p.sourceId);if(!source)throw new Error('The original PDF for this page must be relinked.');const page=await source.pdfjs.getPage(p.index+1);const view=page.getViewport({scale,rotation:p.rotation});
    const maxPixels=42000000;if(view.width*view.height>maxPixels)throw new Error('This render exceeds the 42 megapixel memory limit. Reduce the DPI or zoom.');const c=document.createElement('canvas');c.width=Math.ceil(view.width);c.height=Math.ceil(view.height);await page.render({canvas:c,viewport:view,background:opts.background===null?'rgba(0,0,0,0)':opts.background||'#fff',annotationMode:pdfjsLib.AnnotationMode.ENABLE}).promise;return c;
  }
  async renderCurrent(){
    if(!this.pages.length||this.mode!=='pdf')return;const epoch=++this.renderEpoch;this.captureOverlay();const p=this.pages[this.current];if(!p)return;const dims=this.dimensions(p);const container=this.$('#pageScroll');
    this.scale=this.zoom==='fit-width'?Math.max(.25,Math.min(4,(container.clientWidth-50)/dims.width)):this.zoom==='fit-page'?Math.max(.25,Math.min(4,Math.min((container.clientWidth-50)/dims.width,(container.clientHeight-50)/dims.height))):Math.max(.25,Math.min(4,Number(this.zoom)||1));
    this.$('#renderMessage').hidden=false;this.$('#renderMessage').textContent='Rendering page '+(this.current+1)+'…';let c;
    try{c=await this.renderPage(p,this.scale);}catch(e){if(epoch===this.renderEpoch){this.$('#renderMessage').textContent=e.message;}throw e;}if(epoch!==this.renderEpoch)return;
    const renderScale=this.scale;
    this.overlayRender=(this.overlayRender||Promise.resolve()).catch(()=>{}).then(async()=>{
      if(epoch!==this.renderEpoch){c.width=c.height=1;return;}
      const target=this.$('#pdfCanvas');target.width=c.width;target.height=c.height;target.getContext('2d').drawImage(c,0,0);c.width=c.height=1;
      const stage=this.$('#pageStage');stage.style.width=target.width+'px';stage.style.height=target.height+'px';
      if(!window.fabric){this.$('#overlayCanvas').style.display='none';this.currentPageId=p.id;this.$('#renderMessage').hidden=true;this.status();return;}if(!this.canvas)this.initCanvas();this.suppress=true;
      try{this.canvas.clear();this.canvas.setDimensions({width:target.width,height:target.height});this.canvas.setZoom(renderScale);if(p.overlay)await this.canvas.loadFromJSON(p.overlay);
        if(epoch!==this.renderEpoch)return;this.canvas.setViewportTransform([renderScale,0,0,renderScale,0,0]);this.canvas.backgroundColor='';this.canvas.getObjects().forEach(o=>{if(o.studioLocked)o.set({selectable:false,evented:false});});this.currentPageId=p.id;this.canvas.requestRenderAll();
      }finally{this.suppress=false;}
      this.$('#renderMessage').hidden=true;this.inspector();this.updateThumbnailSelection();this.updateLayers();this.status();
    });
    await this.overlayRender;
  }
  initCanvas(){
    this.canvas=new fabric.Canvas('overlayCanvas',{selection:true,preserveObjectStacking:true,fireRightClick:false,stopContextMenu:true,enableRetinaScaling:false});
    this.canvas.on('object:modified',()=>{this.commit();this.inspector();});this.canvas.on('path:created',()=>this.commit());for(const event of ['selection:created','selection:updated','selection:cleared'])this.canvas.on(event,()=>this.inspector());
    this.canvas.on('mouse:down',e=>this.canvasDown(e));this.canvas.on('mouse:move',e=>this.canvasMove(e));this.canvas.on('mouse:up',()=>this.canvasUp());
    this.canvas.on('object:moving',e=>{if(e.e?.shiftKey&&e.transform){const t=e.transform;const dx=Math.abs(e.target.left-t.original.left),dy=Math.abs(e.target.top-t.original.top);if(dx>dy)e.target.top=t.original.top;else e.target.left=t.original.left;}});
  }
  async refresh(){this.buildThumbnails();await this.renderCurrent();this.status();}
  buildThumbnails(){
    this.thumbObserver?.disconnect();this.thumbSortable?.destroy();const list=this.$('#thumbnailList');list.innerHTML=this.pages.map((p,i)=>`<div class="thumbnail" data-page="${p.id}" tabindex="0" role="button" aria-label="Page ${i+1}"><input type="checkbox" class="form-check-input" aria-label="Select page ${i+1}"><div class="thumb-image"><span class="thumb-loading">${i+1}</span></div><div class="thumb-caption">Page ${i+1}<small translate="no">${this.escape(this.sources.get(p.sourceId)?.name||'Unknown source')}</small></div></div>`).join('');
    const epoch=this.pages.map(p=>p.id).join();this.thumbObserver=new IntersectionObserver(entries=>{for(const entry of entries){const row=entry.target;if(!entry.isIntersecting)continue;if(row.dataset.rendered)continue;row.dataset.rendered='1';const p=this.pages.find(p=>p.id===row.dataset.page);if(!p)continue;this.renderPage(p,.16).then(c=>{if(epoch!==this.pages.map(p=>p.id).join()||!row.isConnected)return;row.querySelector('.thumb-image').replaceChildren(c);}).catch(()=>{row.querySelector('.thumb-loading')?.remove();});}}, {root:this.$('#thumbnailPanel'),rootMargin:'160px'});for(const row of list.children)this.thumbObserver.observe(row);
    if(window.Sortable)this.thumbSortable=new Sortable(list,{animation:140,filter:'input',preventOnFilter:false,delay:100,delayOnTouchOnly:true,onEnd:()=>{this.captureOverlay();const current=this.pages[this.current]?.id;const map=new Map(this.pages.map(p=>[p.id,p]));this.pages=[...list.children].map(e=>map.get(e.dataset.page)).filter(Boolean);this.current=Math.max(0,this.pages.findIndex(p=>p.id===current));this.commit();this.refresh().catch(x=>this.error(x));}});this.updateThumbnailSelection();
  }
  updateThumbnailSelection(){for(const el of this.$('#thumbnailList').children){const active=this.pages[this.current]?.id===el.dataset.page;el.classList.toggle('active',active);el.classList.toggle('selected',this.selected.has(el.dataset.page));el.querySelector('input').checked=this.selected.has(el.dataset.page);el.setAttribute('aria-current',active?'page':'false');}}
  changeZoom(factor){this.zoom=String(Math.max(.25,Math.min(4,this.scale*factor)));this.$('#zoomSelect').value=[.25,.5,.75,1,1.25,1.5,2,3,4].includes(Number(this.zoom))?this.zoom:'';this.renderCurrent().catch(e=>this.error(e));}
  status(){this.$('#statusDocument').setAttribute('translate',this.pages.length?'no':'yes');this.$('#statusDocument').textContent=this.pages.length?this.name:'No PDF open';this.$('#statusPage').textContent=this.pages.length?`Page ${this.current+1} of ${this.pages.length} · ${Math.round(this.scale*100)}%`:'';this.$('#statusInfo').textContent=this.dirty?'Unsaved changes':'Ready';this.$('#documentTitle').setAttribute('translate','no');this.$('#documentTitle').textContent=this.name;}
  setMode(mode){this.captureOverlay();this.mode=mode;this.$('#homePanel').hidden=mode!=='home';this.$('#pdfPanel').hidden=mode!=='pdf';const d=this.$('#documentPanel');if(d)d.hidden=mode!=='document';if(mode==='pdf'){this.renderToolbar();this.renderCurrent().catch(e=>this.error(e));}this.status();}
  async switchGroup(group){
    document.querySelectorAll('.nav-tool').forEach(el=>el.classList.toggle('active',el.dataset.group===group));document.body.classList.remove('sidebar-visible');
    if(group==='Home'){this.setMode('home');return;}if(group==='Create'){await this.documentEditor.show();return;}
    this.group=group;if(this.pages.length){this.setMode('pdf');this.renderToolbar();}else this.groupDialog(group);
  }
  groupDialog(group){
    const defs=this.toolDefs.filter(t=>t.group===group);if(group==='Edit PDF'||group==='Annotate'||group==='Fill & Sign'){
      if(!defs.length){this.notify('Open a PDF to use '+group+'.');this.action('open').catch(e=>this.error(e));return;}
    }
    const body='<div class="tool-grid">'+defs.map(t=>`<button type="button" class="btn btn-outline-secondary" data-action="${t.id}" ${this.available(t)?'':'disabled'}><i class="fa-solid ${t.icon||'fa-file-pdf'}"></i> ${this.escape(t.title)}<small>${this.available(t)?this.escape(t.description||'Locally in your browser'):this.escape('Requires '+t.requirements)}</small></button>`).join('')+'</div>';
    this.dialog(group,body,null,{submit:false,wide:true});
  }
  toolbarButton(action,label,icon){return `<button class="btn btn-outline-secondary btn-sm" data-action="${action}"><i class="fa-solid ${icon||'fa-file-pdf'}"></i> ${label}</button>`;}
  renderToolbar(){
    const b=(id,l,i)=>this.toolbarButton(id,l,i);let html='';
    if(this.group==='Organize')html=b('insert-files','Insert files','fa-file-circle-plus')+b('blank','Blank page','fa-file')+b('rotate-pages','Rotate','fa-rotate-right')+b('duplicate-pages','Duplicate','fa-copy')+b('delete-pages','Delete','fa-trash')+b('extract-pages','Extract','fa-file-export')+b('reverse','Reverse','fa-arrow-down-up-across-line')+b('move-start','To start','fa-angles-left')+b('move-end','To end','fa-angles-right')+b('merge','Merge','fa-object-group')+b('split','Split','fa-scissors')+b('crop','Crop','fa-crop-simple')+b('resize','Resize','fa-up-right-and-down-left-from-center');
    else if(this.group==='Edit PDF')html=b('select','Select','fa-arrow-pointer')+b('add-text','Text','fa-font')+b('add-image','Image','fa-image')+b('rectangle','Rectangle','fa-square')+b('round-rect','Rounded','fa-square')+b('ellipse','Ellipse','fa-circle')+b('line','Line','fa-minus')+b('arrow','Arrow','fa-arrow-right')+b('polygon','Polygon','fa-draw-polygon')+b('draw','Draw','fa-pencil')+b('arrange','Arrange','fa-layer-group')+b('duplicate-object','Duplicate','fa-copy')+b('unlock-all','Unlock','fa-lock-open');
    else if(this.group==='Annotate')html=b('highlight','Highlight','fa-highlighter')+b('underline-line','Underline','fa-underline')+b('strike-line','Strike','fa-strikethrough')+b('note','Sticky note','fa-note-sticky')+b('link','Link region','fa-link')+b('whiteout','White-out','fa-eraser')+b('visual-redact','Visual redaction','fa-square')+b('secure-redact','Secure redaction','fa-shield-halved')+b('stamp','Stamp','fa-stamp')+b('watermark','Watermark','fa-droplet');
    else if(this.group==='Fill & Sign')html=b('sign','Visual signature','fa-signature')+b('checkmark','Check','fa-check')+b('xmark','X mark','fa-xmark')+b('initials','Initials','fa-font')+b('date-stamp','Date','fa-calendar-day')+b('forms','Fill form','fa-list-check')+b('create-field','Create field','fa-square-plus')+b('flatten-forms','Flatten form','fa-file');
    else{const defs=this.toolDefs.filter(t=>t.group===this.group);html=defs.map(t=>{const content=b(t.id,t.title,t.icon);return this.available(t)?content:content.replace('<button ','<button disabled title="'+this.escape('Requires '+t.requirements)+'" ');}).join('');}
    this.$('#contextToolbar').innerHTML=html;if(!window.fabric){const needsFabric=new Set(['select','add-text','add-image','rectangle','round-rect','ellipse','line','arrow','polygon','draw','arrange','duplicate-object','unlock-all','highlight','underline-line','strike-line','note','link','whiteout','visual-redact','secure-redact','stamp','sign','checkmark','xmark','initials','date-stamp']);for(const b of this.$('#contextToolbar').querySelectorAll('button'))if(needsFabric.has(b.dataset.action)){b.disabled=true;b.title='Requires Fabric.js, which could not load.';}}
  }
  async action(id,button){
    if(button?.closest('#toolModal')&&!['palette','capabilities','install-help','updates','check-updates','download-update','changelog'].includes(id))bootstrap.Modal.getInstance(this.$('#toolModal'))?.hide();
    if(id==='home'){this.setMode('home');return;}
    if(id==='open'){const f=await this.pickFiles('.pdf,.pdfstudio.json,image/png,image/jpeg,image/webp,image/gif,image/bmp',true);if(f.length)await this.busy('Opening documents',j=>this.openFiles(f,j));return;}
    if(id==='create'){await this.documentEditor.show();return;}
    if(id==='export'){if(this.mode==='document'){await this.documentEditor.exportPDF();return;}this.requireDocument();await this.busy('Exporting PDF',async j=>{const b=await this.exportBytes({},j);this.download(b,this.name.replace(/\.pdf$/i,'')+'-edited.pdf','application/pdf');this.dirty=false;this.status();});return;}
    if(id==='theme'){const t=document.documentElement.dataset.bsTheme==='dark'?'light':'dark';document.documentElement.dataset.bsTheme=t;try{localStorage.setItem('pdfstudio.theme',t);}catch{}return;}
    if(id==='sidebar'){document.body.classList.toggle('sidebar-visible');return;}
    if(id==='fullscreen'){if(document.fullscreenElement)await document.exitFullscreen();else if(document.documentElement.requestFullscreen)await document.documentElement.requestFullscreen();else this.notify('Full-screen mode is unavailable in this browser.');return;}
    if(id==='capabilities'){this.capabilitiesDialog();return;}if(id==='palette'){this.palette();return;}
    if(id==='install-help'){this.installHelp(button.dataset.tool);return;}
    if(id==='updates'){this.updates?.show();return;}if(id==='check-updates'){await this.updates?.check(true);return;}
    if(id==='download-update'){await this.updates?.download();return;}
    if(id==='changelog'){this.updates?.showChangelog();return;}
    if(id==='clear-recents'){this.recent=[];try{localStorage.removeItem('pdfstudio.recent');}catch{}this.renderRecents();return;}
    if(id==='save-project'){this.saveProjectDialog();return;}if(id==='load-project'){const f=await this.pickFiles('.json');if(f[0])await this.busy('Opening project',async()=>this.restoreProject(JSON.parse(await f[0].text())));return;}
    if(id==='restore-autosave'){const state=await this.readAutosave();if(!state)throw new Error('No local autosave is available.');await this.restoreProject(state);return;}
    if(id==='clear-autosave'){await this.writeAutosave(null);this.notify('Local autosave cleared.');return;}
    if(id==='image-pdf'){this.imagesToPDF();return;}if(id==='merge'){this.mergeDialog();return;}if(id==='scan'){this.scanDialog();return;}
    if(id==='edit'||id==='organize'){if(!this.pages.length){await this.action('open');if(!this.pages.length)return;}await this.switchGroup(id==='edit'?'Edit PDF':'Organize');return;}
    if(id==='zoom-in'){this.changeZoom(1.2);return;}if(id==='zoom-out'){this.changeZoom(1/1.2);return;}
    if(id==='undo'){await this.undo(-1);return;}if(id==='redo'){await this.undo(1);return;}
    if(this.tools?.definitions?.some(t=>t.id===id)||this.tools?.toolDefs?.some(t=>t.id===id)){
      const def=this.toolDefs.find(t=>t.id===id);if(def&&!this.available(def))throw new Error('This tool requires '+def.requirements.replaceAll('|',' or ')+', which was not detected.');
      if(!this.pages.length&&!['office-convert','decrypt','repair','validate','compare'].includes(id)){await this.action('open');if(!this.pages.length)return;}
      this.$('#privacyIndicator').innerHTML=def?.requirements!=='browser'?'<i class="fa-solid fa-server"></i> On the server':'<i class="fa-solid fa-shield-halved"></i> Locally in your browser';await this.tools.run(id);return;
    }
    if(id==='select-all'){this.pages.forEach(p=>this.selected.add(p.id));this.updateThumbnailSelection();return;}if(id==='invert-selection'){for(const p of this.pages){if(this.selected.has(p.id))this.selected.delete(p.id);else this.selected.add(p.id);}this.updateThumbnailSelection();return;}
    if(id==='select-range'){this.requireDocument();this.dialog('Select pages','<label class="form-label">Page ranges</label><input class="form-control" name="range" value="all" required><div class="form-text">Use 1-4,7,10-12, odd, even, or all.</div>',v=>{this.selected=new Set(this.range(v.range,this.pages.length).map(i=>this.pages[i].id));this.updateThumbnailSelection();});return;}
    if(id==='thumbnails'){this.$('#thumbnailPanel').hidden=!this.$('#thumbnailPanel').hidden;return;}
    if(['insert-files','blank','rotate-pages','duplicate-pages','delete-pages','extract-pages','reverse','move-start','move-end'].includes(id)){await this.organize(id);return;}
    if(!this.pages.length&&id==='sign'){await this.action('open');if(!this.pages.length)return;}this.requireDocument();if(this.mode!=='pdf')this.setMode('pdf');
    if(id==='select'){this.setDrawing('select');return;}if(['rectangle','round-rect','ellipse','line','arrow','highlight','underline-line','strike-line','whiteout','visual-redact','link'].includes(id)){if(id==='link'){this.dialog('Hyperlink region','<label class="form-label">URL</label><input class="form-control" name="url" type="url" value="https://" required><div class="form-text">Draw a rectangle over the area you want to make clickable.</div>',v=>{if(!/^https?:\/\//i.test(v.url))throw new Error('Links must use http or https.');this.pendingLink=v.url;this.setDrawing('link');});}else this.setDrawing(id);return;}
    if(id==='secure-redact'){this.dialog('Secure redaction','<div class="alert alert-warning">For safe removal of shared resources, this exporter rebuilds the entire output as page images. Original text, vectors, forms, attachments and metadata are discarded. Searchability is lost. Draw black redaction regions, then export.</div><label class="form-label">Raster DPI</label><select class="form-select" name="dpi"><option>150</option><option selected>300</option><option>450</option><option>600</option></select>',v=>{this.secureDPI=Number(v.dpi);this.setDrawing('secure');},{submit:'Draw secure region'});return;}
    if(id==='draw'){this.setDrawing('draw');return;}if(id==='add-text'){this.addText('Double-click to edit');return;}if(id==='add-image'){const f=await this.pickFiles('image/png,image/jpeg,image/webp,image/gif,image/bmp');if(f[0])await this.addImage(f[0]);return;}
    if(id==='note'){this.dialog('Sticky note','<label class="form-label">Note text</label><textarea class="form-control" name="text" rows="4" required></textarea>',v=>{this.addText(v.text,{fontSize:13,backgroundColor:'#fff0a3',studioKind:'note'});});return;}
    if(id==='polygon'){this.dialog('Polygon','<label class="form-label">Number of sides</label><input name="sides" class="form-control" type="number" min="3" max="20" value="5" required>',v=>{const n=Number(v.sides);const points=Array.from({length:n},(_,i)=>({x:70*Math.cos(2*Math.PI*i/n-Math.PI/2),y:70*Math.sin(2*Math.PI*i/n-Math.PI/2)}));this.addObject(new fabric.Polygon(points,{...this.objectDefaults(),fill:'#3157da33',stroke:'#3157da',strokeWidth:2}));});return;}
    if(id==='sign'){this.signatureDialog();return;}if(id==='initials'||id==='stamp'){this.dialog(id==='initials'?'Initials':'Custom stamp','<label class="form-label">Text</label><input class="form-control" name="text" required maxlength="200">',v=>this.addText(v.text,{fontSize:28,fontFamily:id==='initials'?'cursive':'Arial',fill:id==='initials'?'#152f70':'#b02525',studioKind:'stamp'}));return;}
    if(id==='date-stamp'){this.addText(new Date().toLocaleDateString(),{fontSize:16,studioKind:'stamp'});return;}if(id==='checkmark'||id==='xmark'){this.addText(id==='checkmark'?'✓':'✕',{fontSize:30,fill:id==='checkmark'?'#138447':'#bc2424',studioKind:'mark'});return;}
    if(id==='delete-object'){this.deleteObjects();return;}if(id==='duplicate-object'){await this.copyObjects();await this.pasteObjects();return;}if(id==='copy'){await this.copyObjects();return;}if(id==='cut'){await this.copyObjects();this.deleteObjects();return;}if(id==='paste'){await this.pasteObjects();return;}
    if(id==='arrange'){this.arrangeDialog();return;}if(id==='unlock-all'){this.canvas?.getObjects().forEach(o=>{o.studioLocked=false;o.set({selectable:true,evented:true,lockMovementX:false,lockMovementY:false,lockScalingX:false,lockScalingY:false,lockRotation:false});});this.commit();this.canvas.requestRenderAll();return;}
    if(id==='crop-image'){this.cropImageDialog();return;}
    const obj=this.canvas?.getActiveObject();if(['bold','italic','underline','strike','flip-x','flip-y','lock','clear-background'].includes(id)){
      if(!obj)throw new Error('Select an overlay object first.');const prop={'bold':'fontWeight','italic':'fontStyle','underline':'underline','strike':'linethrough','flip-x':'flipX','flip-y':'flipY'}[id];
      if(id==='lock'){obj.studioLocked=true;obj.set({selectable:false,evented:false,lockMovementX:true,lockMovementY:true,lockScalingX:true,lockScalingY:true,lockRotation:true});this.canvas.discardActiveObject();}
      else if(id==='clear-background')obj.set({backgroundColor:'',textBackgroundColor:''});else if(id==='bold')obj.set(prop,obj.fontWeight==='bold'?'normal':'bold');else if(id==='italic')obj.set(prop,obj.fontStyle==='italic'?'normal':'italic');else obj.set(prop,!obj[prop]);this.canvas.requestRenderAll();this.commit();this.inspector();return;
    }
    throw new Error('Unknown tool: '+id);
  }
  async organize(id){
    this.requireDocument();this.captureOverlay();const indexes=this.getSelected();const set=new Set(indexes);const currentId=this.pages[this.current]?.id;
    if(id==='insert-files'){const files=await this.pickFiles('.pdf,image/png,image/jpeg,image/webp,image/gif',true);if(!files.length)return;await this.busy('Inserting pages',async job=>{const added=[];for(let i=0;i<files.length;i++){let bytes;const f=files[i];if(f.type.startsWith('image/')){const c=await this.imageCanvas(f);const d=await PDFLib.PDFDocument.create();const img=await d.embedPng(c.toDataURL('image/png'));const p=d.addPage([c.width*.75,c.height*.75]);p.drawImage(img,{width:p.getWidth(),height:p.getHeight()});bytes=await d.save();}else bytes=new Uint8Array(await f.arrayBuffer());const src=await this.source(bytes,f.name,job);added.push(...src.pages);job.progress(i+1,files.length,f.name);}if(this.pages.length+added.length>this.server.limits.maxPages)throw new Error('Page count exceeds the configured limit.');this.pages.splice(this.current+1,0,...added);this.commit();await this.refresh();});return;}
    if(id==='blank'){this.dialog('Insert blank page','<label class="form-label">Page size</label><select class="form-select mb-3" name="size"><option value="A4">A4</option><option value="Letter">Letter</option><option value="A3">A3</option><option value="A5">A5</option><option value="Legal">Legal</option></select><label class="form-label">Position</label><select class="form-select" name="position"><option value="after">After current page</option><option value="before">Before current page</option></select>',async v=>{const d=await PDFLib.PDFDocument.create();d.addPage(PDFLib.PageSizes[v.size]);const src=await this.source(await d.save(),'Blank page');this.pages.splice(this.current+(v.position==='after'?1:0),0,...src.pages);this.commit();await this.refresh();});return;}
    if(id==='rotate-pages'){this.dialog('Rotate selected pages','<label class="form-label">Clockwise rotation</label><select class="form-select" name="angle"><option value="90">90°</option><option value="180">180°</option><option value="270">270°</option></select>',async v=>{const angle=Number(v.angle);for(const i of indexes){const p=this.pages[i];for(let a=0;a<angle;a+=90){const dims=this.dimensions(p);for(const o of p.overlay?.objects||[]){const x=o.left,y=o.top;o.left=dims.height-y;o.top=x;o.angle=(o.angle||0)+90;}p.rotation=(p.rotation+90)%360;}}this.currentPageId=null;this.commit();await this.refresh();});return;}
    if(id==='duplicate-pages'){const next=[];this.pages.forEach((p,i)=>{next.push(p);if(set.has(i))next.push({...JSON.parse(JSON.stringify(p)),id:this.id()});});if(next.length>this.server.limits.maxPages)throw new Error('Page count exceeds the configured limit.');this.pages=next;}
    if(id==='delete-pages'){if(indexes.length===this.pages.length)throw new Error('A PDF must retain at least one page.');this.pages=this.pages.filter((p,i)=>!set.has(i));this.selected.clear();}
    if(id==='extract-pages'){await this.busy('Extracting pages',async j=>{this.download(await this.exportBytes({selection:indexes},j),'extracted-pages.pdf','application/pdf');});return;}
    if(id==='reverse')this.pages.reverse();if(id==='move-start'||id==='move-end'){const chosen=this.pages.filter((p,i)=>set.has(i)),rest=this.pages.filter((p,i)=>!set.has(i));this.pages=id==='move-start'?[...chosen,...rest]:[...rest,...chosen];}
    this.current=Math.max(0,this.pages.findIndex(p=>p.id===currentId));this.currentPageId=null;this.commit();await this.refresh();
  }
  objectDefaults(){const d=this.dimensions(this.pages[this.current]);return {left:d.width/2,top:d.height/2,originX:'center',originY:'center',fill:'#24365a',strokeWidth:0,opacity:1};}
  addObject(object){if(!this.canvas)this.initCanvas();this.canvas.add(object);this.canvas.setActiveObject(object);this.canvas.requestRenderAll();this.commit();this.inspector();}
  addText(text,opts={}){this.setDrawing('select');this.addObject(new fabric.IText(text,{...this.objectDefaults(),fontFamily:'Arial',fontSize:22,...opts}));}
  async imageCanvas(file){
    if(file.size>(this.server.limits.maxLocalFile||this.server.limits.maxFile))throw new Error('Image exceeds the file size limit.');const url=URL.createObjectURL(file);try{const img=new Image();await new Promise((res,rej)=>{img.onload=res;img.onerror=()=>rej(new Error('This image format cannot be decoded by your browser.'));img.src=url;});if(img.width*img.height>42000000)throw new Error('Image exceeds the 42 megapixel safety limit.');const c=document.createElement('canvas');c.width=img.width;c.height=img.height;c.getContext('2d').drawImage(img,0,0);return c;}finally{URL.revokeObjectURL(url);}
  }
  async addImage(file){const c=await this.imageCanvas(file);await this.addImageURL(c.toDataURL('image/png'));c.width=c.height=1;}
  async addImageURL(url,opts={}){const image=await fabric.FabricImage.fromURL(url);const d=this.dimensions(this.pages[this.current]);const scale=Math.min(1,d.width*.6/image.width,d.height*.6/image.height);image.set({...this.objectDefaults(),scaleX:scale,scaleY:scale,...opts});this.addObject(image);}
  setDrawing(mode){
    this.requireDocument();this.toolMode=mode;if(this.canvas){this.canvas.isDrawingMode=mode==='draw';this.canvas.selection=mode==='select';this.canvas.defaultCursor=mode==='select'?'default':'crosshair';if(mode==='draw'){const brush=new fabric.PencilBrush(this.canvas);brush.color='#24365a';brush.width=2;this.canvas.freeDrawingBrush=brush;}this.canvas.discardActiveObject();this.canvas.requestRenderAll();}if(mode!=='select'&&mode!=='draw')this.notify('Drag on the page to add '+(mode==='secure'?'a secure redaction region':mode.replaceAll('-',' '))+'.');
  }
  canvasDown(e){
    if(this.suppress||this.toolMode==='select'||this.toolMode==='draw')return;const point=this.canvas.getScenePoint(e.e);this.drawStart=point;const mode=this.toolMode;
    const base={left:point.x,top:point.y,originX:'left',originY:'top',width:1,height:1,fill:'transparent',stroke:'#3157da',strokeWidth:2,selectable:false,evented:false,studioKind:mode};
    if(['line','underline-line','strike-line','arrow'].includes(mode)){this.drawObject=new fabric.Line([point.x,point.y,point.x+1,point.y+1],{stroke:'#24365a',strokeWidth:mode==='line'||mode==='arrow'?2:1.5,selectable:false,evented:false,studioKind:mode});}
    else if(mode==='ellipse')this.drawObject=new fabric.Ellipse({...base,rx:.5,ry:.5});else{if(mode==='highlight')Object.assign(base,{fill:'#ffe23a',opacity:.35,strokeWidth:0});if(mode==='whiteout')Object.assign(base,{fill:'#fff',strokeWidth:0});if(mode==='visual-redact'||mode==='secure')Object.assign(base,{fill:'#000',strokeWidth:0});if(mode==='round-rect')Object.assign(base,{rx:10,ry:10});if(mode==='link')Object.assign(base,{fill:'#3157da18',stroke:'#3157da',studioLink:this.pendingLink});this.drawObject=new fabric.Rect(base);}this.canvas.add(this.drawObject);
  }
  canvasMove(e){
    if(!this.drawStart||!this.drawObject)return;const end=this.canvas.getScenePoint(e.e),a=this.drawStart,o=this.drawObject;if(['line','arrow','underline-line','strike-line'].includes(this.toolMode)){const y=e.e.shiftKey?a.y:end.y;o.set({x2:end.x,y2:y});}
    else{let w=Math.abs(end.x-a.x),h=Math.abs(end.y-a.y);if(e.e.shiftKey)w=h=Math.max(w,h);o.set({left:Math.min(a.x,end.x),top:Math.min(a.y,end.y),width:Math.max(1,w),height:Math.max(1,h)});if(this.toolMode==='ellipse')o.set({rx:w/2,ry:h/2});}o.setCoords();this.canvas.requestRenderAll();
  }
  canvasUp(){
    const o=this.drawObject;if(!o)return;const mode=this.toolMode;this.drawObject=null;this.drawStart=null;
    if(mode==='arrow'){
      const angle=Math.atan2(o.y2-o.y1,o.x2-o.x1),size=12;const points=[{x:o.x2,y:o.y2},{x:o.x2-size*Math.cos(angle-.5),y:o.y2-size*Math.sin(angle-.5)},{x:o.x2-size*Math.cos(angle+.5),y:o.y2-size*Math.sin(angle+.5)}];const head=new fabric.Polygon(points,{fill:'#24365a',strokeWidth:0});this.canvas.remove(o);const g=new fabric.Group([o,head],{studioKind:'arrow'});this.canvas.add(g);this.canvas.setActiveObject(g);
    }else{const center=o.getCenterPoint();o.set({originX:'center',originY:'center',left:center.x,top:center.y,selectable:true,evented:true});o.setCoords();this.canvas.setActiveObject(o);}
    this.setDrawing('select');this.canvas.setActiveObject(mode==='arrow'?this.canvas.getObjects().at(-1):o);this.canvas.requestRenderAll();this.commit();this.inspector();
  }
  inspector(){
    const o=this.canvas?.getActiveObject();this.$('#inspectorEmpty').hidden=!!o;this.$('#inspectorFields').hidden=!o;if(!o)return;
    for(const el of document.querySelectorAll('[data-prop]')){let val=o[el.dataset.prop]??'';if(el.type==='color')val=/^#[0-9a-f]{6}$/i.test(val)?val:'#000000';el.value=val;el.disabled=['text','fontFamily','fontSize','textAlign','charSpacing','backgroundColor'].includes(el.dataset.prop)&&!('text'in o);}
    this.$('#objectW').value=Math.round(o.getScaledWidth());this.$('#objectH').value=Math.round(o.getScaledHeight());this.updateLayers();
  }
  inspectorChange(el){
    const o=this.canvas?.getActiveObject();if(!o)return;const prop=el.dataset.prop;if(el.dataset.size){const axis=el.dataset.size==='width'?'scaleX':'scaleY';const dim=el.dataset.size==='width'?o.width:o.height;const v=Number(el.value);if(dim&&v>0)o.set(axis,v/dim);}else{const numeric=['fontSize','charSpacing','opacity','angle','strokeWidth','left','top'];const value=numeric.includes(prop)?Number(el.value):el.value;if(numeric.includes(prop)&&!Number.isFinite(value))return;o.set(prop,value);}o.setCoords();this.canvas.requestRenderAll();this.commit();this.inspector();
  }
  updateLayers(){
    const objects=this.canvas?.getObjects()||[];this.$('#layersList').innerHTML=objects.map((o,i)=>({o,i})).reverse().map(({o,i})=>`<div class="layer-item ${this.canvas.getActiveObjects().includes(o)?'active':''}"><button class="icon-button" data-layer="${i}" ${o.studioLocked?'data-unlock="1"':''} title="${o.studioLocked?'Unlock':'Select'}" aria-label="${o.studioLocked?'Unlock':'Select'} layer"><i class="fa-solid ${o.studioLocked?'fa-lock':'fa-layer-group'}"></i></button><span>${this.escape(o.studioKind||o.text?.slice(0,26)||o.type)}</span><button class="icon-button" data-layer="${i}" aria-label="Select layer"><i class="fa-solid fa-arrow-pointer"></i></button></div>`).join('');
  }
  deleteObjects(){if(!this.canvas)return;const objs=this.canvas.getActiveObjects();this.canvas.discardActiveObject();objs.forEach(o=>this.canvas.remove(o));this.canvas.requestRenderAll();this.commit();this.inspector();}
  async copyObjects(){const objects=this.canvas?.getActiveObjects()||[];if(!objects.length)return;this.clipboard=objects.map(o=>o.toObject(this.overlayProps));}
  async pasteObjects(){if(!this.clipboard?.length)return;const objs=await fabric.util.enlivenObjects(this.clipboard);this.canvas.discardActiveObject();objs.forEach(o=>{o.set({left:o.left+15,top:o.top+15,selectable:true,evented:true});o.studioLocked=false;this.canvas.add(o);});if(objs.length===1)this.canvas.setActiveObject(objs[0]);else this.canvas.setActiveObject(new fabric.ActiveSelection(objs,{canvas:this.canvas}));this.canvas.requestRenderAll();this.commit();this.inspector();}
  arrangeDialog(){
    const controls=[['left','Align left'],['right','Align right'],['top','Align top'],['bottom','Align bottom'],['hcenter','Horizontal center'],['vcenter','Vertical center'],['hdistribute','Distribute horizontally'],['vdistribute','Distribute vertically'],['front','Bring to front'],['back','Send to back'],['forward','Bring forward'],['backward','Send backward']];
    const el=this.dialog('Arrange objects','<div class="tool-grid">'+controls.map(([id,label])=>`<button class="btn btn-outline-secondary" type="button" data-arrange="${id}">${label}</button>`).join('')+'</div><div class="form-text">Select multiple objects with Shift + click or drag a selection. Single-object alignment uses the page.</div>',null,{submit:false});el.querySelectorAll('[data-arrange]').forEach(b=>b.addEventListener('click',()=>this.arrange(b.dataset.arrange)));
  }
  arrange(mode){
    const objs=this.canvas.getActiveObjects();if(!objs.length)throw new Error('Select one or more overlay objects.');const active=this.canvas.getActiveObject();if(active?.type==='activeselection')this.canvas.discardActiveObject();
    if(['front','back','forward','backward'].includes(mode)){const method={front:'bringObjectToFront',back:'sendObjectToBack',forward:'bringObjectForward',backward:'sendObjectBackwards'}[mode];objs.forEach(o=>this.canvas[method](o));}
    else{const dims=this.dimensions(this.pages[this.current]);const bounds=objs.map(o=>o.getBoundingRect());const box=objs.length===1?{left:0,top:0,right:dims.width,bottom:dims.height}:{left:Math.min(...bounds.map(b=>b.left)),top:Math.min(...bounds.map(b=>b.top)),right:Math.max(...bounds.map(b=>b.left+b.width)),bottom:Math.max(...bounds.map(b=>b.top+b.height))};
      if(mode.endsWith('distribute')){if(objs.length<3){this.notify('Select at least three objects to distribute.');return;}const horizontal=mode==='hdistribute';const sorted=objs.slice().sort((a,b)=>a.getBoundingRect()[horizontal?'left':'top']-b.getBoundingRect()[horizontal?'left':'top']);const size=horizontal?'width':'height',position=horizontal?'left':'top';const first=sorted[0].getBoundingRect(),last=sorted.at(-1).getBoundingRect();const span=last[position]+last[size]-first[position];const total=sorted.reduce((sum,o)=>sum+o.getBoundingRect()[size],0),gap=(span-total)/(sorted.length-1);let pos=first[position];for(const o of sorted){const b=o.getBoundingRect();o.set(horizontal?'left':'top',o[horizontal?'left':'top']+pos-b[position]);pos+=b[size]+gap;o.setCoords();}}
      else objs.forEach(o=>{const b=o.getBoundingRect();let dx=0,dy=0;if(mode==='left')dx=box.left-b.left;if(mode==='right')dx=box.right-b.left-b.width;if(mode==='hcenter')dx=(box.left+box.right-b.width)/2-b.left;if(mode==='top')dy=box.top-b.top;if(mode==='bottom')dy=box.bottom-b.top-b.height;if(mode==='vcenter')dy=(box.top+box.bottom-b.height)/2-b.top;o.set({left:o.left+dx,top:o.top+dy});o.setCoords();});
    }
    if(objs.length>1)this.canvas.setActiveObject(new fabric.ActiveSelection(objs,{canvas:this.canvas}));else this.canvas.setActiveObject(objs[0]);this.canvas.requestRenderAll();this.commit();this.inspector();
  }
  cropImageDialog(){
    const o=this.canvas?.getActiveObject();if(!(o instanceof fabric.FabricImage))throw new Error('Select an image object first.');const original=o.getElement();this.dialog('Crop image','<div class="row g-2">'+[['x','Left',o.cropX||0],['y','Top',o.cropY||0],['width','Width',o.width],['height','Height',o.height]].map(([name,label,value])=>`<div class="col-6"><label class="form-label">${label} (source pixels)</label><input class="form-control" type="number" min="${name==='width'||name==='height'?1:0}" name="${name}" value="${Math.round(value)}" required></div>`).join('')+'</div><div class="form-text">Cropping adjusts the visible portion of this image without changing the original PDF.</div>',v=>{const x=Number(v.x),y=Number(v.y),w=Number(v.width),h=Number(v.height);if(x+w>original.naturalWidth||y+h>original.naturalHeight)throw new Error('The crop extends beyond this image.');o.set({cropX:x,cropY:y,width:w,height:h});o.setCoords();this.canvas.requestRenderAll();this.commit();this.inspector();});
  }
  async overlayPNG(p,multiplier=1,secureMask=false){
    if(!p.overlay?.objects?.length)return null;if(!window.fabric)throw new Error('Editable overlay export requires Fabric.js, which could not load.');const d=this.dimensions(p);const c=new fabric.StaticCanvas(document.createElement('canvas'),{width:d.width,height:d.height,enableRetinaScaling:false});try{let data=p.overlay;if(secureMask){data=JSON.parse(JSON.stringify(p.overlay));data.objects=data.objects.filter(o=>o.studioKind==='secure').map(o=>({...o,fill:'#000000',opacity:1,visible:true,strokeWidth:0,stroke:null,shadow:null,backgroundColor:'',globalCompositeOperation:'source-over'}));}await c.loadFromJSON(data);c.backgroundColor='';c.renderAll();return c.toDataURL({format:'png',multiplier});}finally{await c.dispose();}
  }
  async exportBytes(options={},job){
    this.requireDocument();this.captureOverlay();const indexes=options.selection||this.pages.map((p,i)=>i);if(!indexes.length)throw new Error('Select at least one page.');const selected=indexes.map(i=>this.pages[i]);const secure=selected.some(p=>p.overlay?.objects?.some(o=>o.studioKind==='secure'));
    const output=await PDFLib.PDFDocument.create();
    if(secure){
      const dpi=this.secureDPI||300;
      for(let i=0;i<selected.length;i++){job?.check();const p=selected[i];const d=this.dimensions(p),scale=dpi/72;const pixels=d.width*d.height*scale*scale;if(pixels>42000000)throw new Error('Secure redaction at '+dpi+' DPI exceeds the memory limit for this page. Select a lower DPI.');const base=await this.renderPage(p,scale);const overlay=await this.overlayPNG(p,scale);if(overlay){const img=new Image();await new Promise((r,j)=>{img.onload=r;img.onerror=j;img.src=overlay;});base.getContext('2d').drawImage(img,0,0,base.width,base.height);}const mask=await this.overlayPNG(p,scale,true);if(mask){const img=new Image();await new Promise((r,j)=>{img.onload=r;img.onerror=j;img.src=mask;});base.getContext('2d').globalCompositeOperation='source-over';base.getContext('2d').drawImage(img,0,0,base.width,base.height);}const page=output.addPage([d.width,d.height]);const image=await output.embedPng(base.toDataURL('image/png'));page.drawImage(image,{x:0,y:0,width:d.width,height:d.height});base.width=base.height=1;job?.progress(i+1,selected.length,'Securely rasterizing page '+(indexes[i]+1));await new Promise(r=>setTimeout(r,0));}
      output.setTitle('');output.setAuthor('');output.setSubject('');output.setKeywords([]);output.setCreator('');output.setProducer('');return output.save({useObjectStreams:true});
    }
    const first=this.sources.get(selected[0].sourceId);const unchanged=selected.length===first.lib.getPageCount()&&selected.every((p,i)=>p.sourceId===first.id&&p.index===i);
    let dest=unchanged?await PDFLib.PDFDocument.load(first.bytes,{updateMetadata:false}):output;
    const preparedSources=new Map();let warnedForms=false;
    for(let i=0;i<selected.length;i++){
      job?.check();const p=selected[i];let page;if(unchanged)page=dest.getPage(i);else{
        let source=preparedSources.get(p.sourceId);if(!source){const original=this.sources.get(p.sourceId);source=await PDFLib.PDFDocument.load(original.bytes,{updateMetadata:false});if(source.getForm().hasXFA())throw new Error('XFA forms cannot be reorganized safely. Convert this PDF to static pages first.');if(source.getForm().getFields().length){try{source.getForm().flatten();}catch(e){throw new Error('This form cannot be flattened for page organization: '+e.message);}if(!warnedForms){this.notify('Interactive fields are flattened when pages are reorganized or extracted. Their visible appearances are retained.');warnedForms=true;}}preparedSources.set(p.sourceId,source);}page=(await dest.copyPages(source,[p.index]))[0];dest.addPage(page);
      }
      page.setRotation(PDFLib.degrees(p.rotation));if(p.crop)page.setCropBox(p.crop.x,p.crop.y,p.crop.width,p.crop.height);
      const png=await this.overlayPNG(p,2);if(png){const img=await dest.embedPng(png);const b=page.getCropBox();const r=p.rotation%360;const coords=r===90?{x:b.x+b.width,y:b.y,width:b.height,height:b.width,rotate:PDFLib.degrees(90)}:r===180?{x:b.x+b.width,y:b.y+b.height,width:b.width,height:b.height,rotate:PDFLib.degrees(180)}:r===270?{x:b.x,y:b.y+b.height,width:b.height,height:b.width,rotate:PDFLib.degrees(270)}:{x:b.x,y:b.y,width:b.width,height:b.height};page.drawImage(img,coords);}
      // Link regions remain real PDF annotations. The visible overlay is still editable in the project.
      for(const o of p.overlay?.objects||[]){if(!o.studioLink||!/^https?:\/\//i.test(o.studioLink))continue;const w=(o.width||0)*(o.scaleX||1),h=(o.height||0)*(o.scaleY||1),cx=o.left,cy=o.top;const b=page.getCropBox();const inverse=(x,y)=>p.rotation===90?[b.x+y,b.y+x]:p.rotation===180?[b.x+b.width-x,b.y+y]:p.rotation===270?[b.x+b.width-y,b.y+b.height-x]:[b.x+x,b.y+b.height-y];const angle=(o.angle||0)*Math.PI/180;const corners=[[-w/2,-h/2],[w/2,-h/2],[-w/2,h/2],[w/2,h/2]].map(([x,y])=>inverse(cx+x*Math.cos(angle)-y*Math.sin(angle),cy+x*Math.sin(angle)+y*Math.cos(angle)));const xs=corners.map(v=>v[0]),ys=corners.map(v=>v[1]);const annot=dest.context.obj({Type:'Annot',Subtype:'Link',Rect:[Math.min(...xs),Math.min(...ys),Math.max(...xs),Math.max(...ys)],Border:[0,0,0],A:{Type:'Action',S:'URI',URI:PDFLib.PDFString.of(o.studioLink)}});page.node.addAnnot(dest.context.register(annot));}
      job?.progress(i+1,selected.length,'Exporting page '+(indexes[i]+1));await new Promise(r=>setTimeout(r,0));
    }
    const metadata=options.metadata||this.metadata;if(metadata?.removeMetadata){const m=dest.catalog.get(PDFLib.PDFName.of('Metadata'));dest.catalog.delete(PDFLib.PDFName.of('Metadata'));if(m instanceof PDFLib.PDFRef)dest.context.delete(m);if(dest.context.trailerInfo.Info){dest.context.delete(dest.context.trailerInfo.Info);delete dest.context.trailerInfo.Info;}}else if(metadata){dest.setTitle(String(metadata.title||''));dest.setAuthor(String(metadata.author||''));dest.setSubject(String(metadata.subject||''));dest.setKeywords(Array.isArray(metadata.keywords)?metadata.keywords:String(metadata.keywords||'').split(',').map(s=>s.trim()).filter(Boolean));dest.setCreator(String(metadata.creator||''));dest.setProducer(String(metadata.producer||'PDF Studio'));}if(options.flattenForms)dest.getForm().flatten();return dest.save({useObjectStreams:true});
  }
  signatureDialog(){
    const saved=(()=>{try{return localStorage.getItem('pdfstudio.signature');}catch{return null;}})();
    const el=this.dialog('Visual signature','<div class="alert alert-info">This adds a visible signature. It does not cryptographically sign the PDF.</div><label class="form-label">Draw with your mouse or finger</label><canvas id="signatureCanvas" class="signature-canvas" width="700" height="220"></canvas><button class="btn btn-outline-secondary btn-sm my-2" type="button" id="clearSignature">Clear drawing</button><label class="form-label d-block">Or type a signature</label><input class="form-control mb-2" name="typed" placeholder="Your name"><label class="form-label">Or upload an image</label><input class="form-control" name="image" type="file" accept="image/png,image/jpeg,image/webp"><div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="save" id="saveSignature"><label class="form-check-label" for="saveSignature">Save this signature in this browser</label></div>'+(saved?'<button class="btn btn-outline-primary btn-sm mt-2" type="button" id="useSavedSignature">Use saved signature</button>':''),async (v)=>{if(v.image?.[0]){const c=await this.imageCanvas(v.image[0]);const data=c.toDataURL('image/png');await this.addImageURL(data,{studioKind:'signature'});if(v.save)try{localStorage.setItem('pdfstudio.signature',data);}catch{}return;}if(v.typed.trim()){this.addText(v.typed,{fontFamily:'cursive',fontSize:35,studioKind:'signature'});return;}if(!hasInk)throw new Error('Draw, type, or upload a signature first.');const data=canvas.toDataURL('image/png');await this.addImageURL(data,{studioKind:'signature'});if(v.save)try{localStorage.setItem('pdfstudio.signature',data);}catch{}},{submit:'Insert signature'});
    const canvas=el.querySelector('#signatureCanvas'),ctx=canvas.getContext('2d');let down=false,hasInk=false;const point=e=>{const r=canvas.getBoundingClientRect();return {x:(e.clientX-r.left)*canvas.width/r.width,y:(e.clientY-r.top)*canvas.height/r.height};};canvas.addEventListener('pointerdown',e=>{e.preventDefault();down=true;hasInk=true;canvas.setPointerCapture(e.pointerId);const p=point(e);ctx.beginPath();ctx.moveTo(p.x,p.y);});canvas.addEventListener('pointermove',e=>{if(!down)return;const p=point(e);ctx.lineWidth=3;ctx.strokeStyle='#182849';ctx.lineCap='round';ctx.lineJoin='round';ctx.lineTo(p.x,p.y);ctx.stroke();});canvas.addEventListener('pointerup',()=>down=false);canvas.addEventListener('pointercancel',()=>down=false);el.querySelector('#clearSignature').onclick=()=>{ctx.clearRect(0,0,canvas.width,canvas.height);hasInk=false;};el.querySelector('#useSavedSignature')?.addEventListener('click',async()=>{try{await this.addImageURL(saved,{studioKind:'signature'});bootstrap.Modal.getInstance(el).hide();}catch(e){this.error(e);}});
  }
  pageSize(size,width,height,landscape){let p=size==='custom'?[Number(width)*72/25.4,Number(height)*72/25.4]:PDFLib.PageSizes[size]||PDFLib.PageSizes.A4;if(!p.every(n=>Number.isFinite(n)&&n>=36&&n<=5000))throw new Error('Page dimensions must be between 12.7 and 1764 mm.');p=[...p];if(landscape)p.reverse();return p;}
  color(hex){const m=String(hex).match(/^#([0-9a-f]{6})$/i);if(!m)return PDFLib.rgb(1,1,1);return PDFLib.rgb(parseInt(m[1].slice(0,2),16)/255,parseInt(m[1].slice(2,4),16)/255,parseInt(m[1].slice(4),16)/255);}
  async imagesToPDF(){
    const files=await this.pickFiles('image/png,image/jpeg,image/webp,image/gif,image/bmp',true);if(!files.length)return;let order=files.map((f,i)=>i);
    const html='<label class="form-label">Drag to arrange images</label><div id="imageOrder" class="list-group mb-3">'+files.map((f,i)=>`<div class="list-group-item" data-index="${i}"><i class="fa-solid fa-grip-vertical me-2"></i>${this.escape(f.name)}</div>`).join('')+'</div><div class="row g-3"><div class="col-6"><label class="form-label">Page size</label><select name="size" class="form-select"><option>A4</option><option>Letter</option><option>A3</option><option>A5</option><option>Legal</option><option value="custom">Custom</option></select></div><div class="col-6"><label class="form-label">Images per page</label><select name="perPage" class="form-select"><option>1</option><option>2</option><option>4</option><option>6</option><option>9</option></select></div><div class="col-6"><label class="form-label">Custom width (mm)</label><input name="width" class="form-control" type="number" min="13" max="1764" value="210"></div><div class="col-6"><label class="form-label">Custom height (mm)</label><input name="height" class="form-control" type="number" min="13" max="1764" value="297"></div><div class="col-6"><label class="form-label">Placement</label><select name="fit" class="form-select"><option value="fit">Fit inside</option><option value="fill">Fill / crop</option><option value="actual">Actual size (96 DPI)</option><option value="stretch">Stretch</option></select></div><div class="col-6"><label class="form-label">Margins / gap (mm)</label><input name="margin" class="form-control" type="number" min="0" max="80" value="10"></div><div class="col-6"><label class="form-label">Background</label><input name="background" type="color" value="#ffffff" class="form-control form-control-color"></div><div class="col-6"><label class="form-label">JPEG quality</label><input name="quality" type="number" min="10" max="100" value="92" class="form-control"></div><div class="col-12"><div class="form-check"><input name="landscape" type="checkbox" class="form-check-input" id="imagesLandscape"><label for="imagesLandscape" class="form-check-label">Landscape</label></div></div></div><div class="form-text">GIF uses its first frame. Transparent images are composited onto the selected background.</div>';
    const el=this.dialog('Images to PDF',html,async v=>{order=[...el.querySelector('#imageOrder').children].map(r=>Number(r.dataset.index));await this.busy('Creating PDF from images',async job=>{const [w,h]=this.pageSize(v.size,v.width,v.height,v.landscape),n=Number(v.perPage),cols=n===1?1:n===2?2:n===4?2:3,rows=Math.ceil(n/cols),m=Number(v.margin)*72/25.4,cellW=(w-m*(cols+1))/cols,cellH=(h-m*(rows+1))/rows;if(cellW<=0||cellH<=0)throw new Error('Margins leave no space for the images.');const d=await PDFLib.PDFDocument.create();let page;
      for(let i=0;i<order.length;i++){job.check();if(i%n===0){page=d.addPage([w,h]);page.drawRectangle({x:0,y:0,width:w,height:h,color:this.color(v.background)});}let c=await this.imageCanvas(files[order[i]]);const cw=cellW,ch=cellH;const aspect=c.width/c.height;let iw=cw,ih=ch;
        if(v.fit==='fill'){const desired=cw/ch;const sx=aspect>desired?(c.width-c.height*desired)/2:0,sy=aspect<desired?(c.height-c.width/desired)/2:0,sw=aspect>desired?c.height*desired:c.width,sh=aspect<desired?c.width/desired:c.height;const crop=document.createElement('canvas');crop.width=Math.round(sw);crop.height=Math.round(sh);crop.getContext('2d').drawImage(c,sx,sy,sw,sh,0,0,sw,sh);c.width=c.height=1;c=crop;}else if(v.fit==='fit'){const scale=Math.min(cw/c.width,ch/c.height);iw=c.width*scale;ih=c.height*scale;}else if(v.fit==='actual'){iw=c.width*.75;ih=c.height*.75;if(iw>cw||ih>ch)throw new Error('Actual-size image does not fit its cell. Use Fit or a larger page.');}
        const flat=document.createElement('canvas');flat.width=c.width;flat.height=c.height;const ctx=flat.getContext('2d');ctx.fillStyle=v.background;ctx.fillRect(0,0,flat.width,flat.height);ctx.drawImage(c,0,0);const img=await d.embedJpg(flat.toDataURL('image/jpeg',Number(v.quality)/100));const pos=i%n,col=pos%cols,row=Math.floor(pos/cols);page.drawImage(img,{x:m+col*(cw+m)+(cw-iw)/2,y:h-m-row*(ch+m)-ch+(ch-ih)/2,width:iw,height:ih});c.width=c.height=flat.width=flat.height=1;job.progress(i+1,order.length,files[order[i]].name);await new Promise(r=>setTimeout(r,0));}await this.openPDF(await d.save(),'images.pdf',job);});},{submit:'Create PDF',wide:true});if(window.Sortable)new Sortable(el.querySelector('#imageOrder'),{animation:130});
  }
  async mergeDialog(){
    const files=await this.pickFiles('.pdf,image/png,image/jpeg,image/webp,image/gif',true);if(!files.length)return;
    const el=this.dialog('Merge documents','<div class="alert alert-info">Locally in your browser unless you explicitly select a server engine. Interactive forms are flattened before browser merging.</div><label class="form-label">Drag files to arrange them. Page range applies to PDFs.</label><div id="mergeOrder" class="list-group mb-3">'+files.map((f,i)=>`<div class="list-group-item d-flex gap-2 align-items-center" data-index="${i}"><i class="fa-solid fa-grip-vertical"></i><span class="flex-grow-1">${this.escape(f.name)}</span><input name="range${i}" class="form-control form-control-sm" style="max-width:100px" value="all" aria-label="Page range for ${this.escape(f.name)}"></div>`).join('')+'</div><div class="row g-3"><div class="col-6"><label class="form-label">Engine</label><select class="form-select" name="engine"><option value="browser">Auto / Browser (pdf-lib)</option>'+(this.server.tools.qpdf?.available?'<option value="qpdf">Server / qpdf (PDFs only)</option>':'')+(this.server.tools.fpdi?.available?'<option value="fpdi">Server / FPDI (PDFs only)</option>':'')+'</select></div><div class="col-6"><label class="form-label">Merge order</label><select class="form-select" name="method"><option value="normal">Entire files in order</option><option value="interleave">Alternate pages (two PDFs)</option></select></div><div class="col-12"><div class="form-check"><input type="checkbox" name="bookmarks" id="mergeBookmarks" class="form-check-input"><label class="form-check-label" for="mergeBookmarks">Add filename bookmarks (browser engine)</label></div></div></div>',async v=>{const ordered=[...el.querySelector('#mergeOrder').children].map(r=>Number(r.dataset.index));await this.busy('Merging documents',async job=>{
      if(v.engine!=='browser'){if(v.method!=='normal'||v.bookmarks)throw new Error('Interleaving and bookmarks require the browser engine.');if(files.some(f=>f.type.startsWith('image/')))throw new Error('Server merging accepts PDFs only.');const ranges=ordered.map(i=>v['range'+i]==='all'?'1-z':v['range'+i]);const r=await this.api('merge',ordered.map(i=>files[i]),{engine:v.engine,ranges},job);await this.openPDF(new Uint8Array(await r.blob.arrayBuffer()),r.name,job);return;}
      const dest=await PDFLib.PDFDocument.create();const loaded=[];for(const i of ordered){job.check();const f=files[i];if(f.type.startsWith('image/')){const c=await this.imageCanvas(f);const doc=await PDFLib.PDFDocument.create();const image=await doc.embedPng(c.toDataURL('image/png'));const p=doc.addPage([c.width*.75,c.height*.75]);p.drawImage(image,{width:p.getWidth(),height:p.getHeight()});loaded.push({doc,indexes:[0],name:f.name});c.width=c.height=1;}else{const doc=await PDFLib.PDFDocument.load(await f.arrayBuffer(),{updateMetadata:false});if(doc.getForm().hasXFA())throw new Error('XFA forms cannot be merged safely.');if(doc.getForm().getFields().length)doc.getForm().flatten();loaded.push({doc,indexes:this.range(v['range'+i],doc.getPageCount()),name:f.name});}job.progress(loaded.length,ordered.length,'Reading '+f.name);}
      if(v.method==='interleave'&&(loaded.length!==2||files.some(f=>f.type.startsWith('image/'))))throw new Error('Interleaving requires exactly two PDF files.');const marks=[];
      if(v.method==='interleave'){const max=Math.max(...loaded.map(d=>d.indexes.length));for(let n=0;n<max;n++)for(const src of loaded)if(n<src.indexes.length)dest.addPage((await dest.copyPages(src.doc,[src.indexes[n]]))[0]);}
      else for(const src of loaded){marks.push({title:src.name,index:dest.getPageCount()});for(const p of await dest.copyPages(src.doc,src.indexes))dest.addPage(p);}
      if(dest.getPageCount()>this.server.limits.maxPages)throw new Error('Merged PDF exceeds the configured page limit.');if(v.bookmarks&&marks.length)this.addBookmarks(dest,marks);await this.openPDF(await dest.save(),'merged.pdf',job);
    });},{wide:true,submit:'Merge'});if(window.Sortable)new Sortable(el.querySelector('#mergeOrder'),{animation:130,filter:'input',preventOnFilter:false});
  }
  addBookmarks(doc,marks){
    const {PDFName,PDFString}=PDFLib;const root=doc.context.obj({Type:'Outlines',Count:marks.length});const rootRef=doc.context.register(root);const refs=marks.map(m=>doc.context.register(doc.context.obj({Title:PDFString.of(m.title),Parent:rootRef,Dest:[doc.getPage(m.index).ref,'Fit']})));refs.forEach((r,i)=>{const node=doc.context.lookup(r);if(i)node.set(PDFName.of('Prev'),refs[i-1]);if(i<refs.length-1)node.set(PDFName.of('Next'),refs[i+1]);});root.set(PDFName.of('First'),refs[0]);root.set(PDFName.of('Last'),refs.at(-1));doc.catalog.set(PDFName.of('Outlines'),rootRef);doc.catalog.set(PDFName.of('PageMode'),PDFName.of('UseOutlines'));
  }
  async scanDialog(){
    const el=this.dialog('Camera / scan','<label class="form-label">Capture or upload an image</label><input class="form-control mb-3" id="scanFile" name="image" type="file" accept="image/*" capture="environment" required><canvas id="scanPreview" style="max-width:100%;max-height:300px;border:1px solid #ddd"></canvas><div class="row g-2 mt-2"><div class="col-6"><label class="form-label">Brightness (%)</label><input class="form-range" id="scanBrightness" name="brightness" type="range" min="20" max="200" value="100"></div><div class="col-6"><label class="form-label">Contrast (%)</label><input class="form-range" id="scanContrast" name="contrast" type="range" min="20" max="250" value="100"></div><div class="col-6"><label class="form-label">Rotate</label><select class="form-select" id="scanRotation" name="rotation"><option value="0">0°</option><option value="90">90°</option><option value="180">180°</option><option value="270">270°</option></select></div><div class="col-6"><label class="form-label">Color</label><select class="form-select" id="scanColor" name="color"><option value="color">Color</option><option value="gray">Grayscale</option><option value="bw">Black and white</option></select></div><div class="col-12"><label class="form-label">Threshold</label><input class="form-range" id="scanThreshold" name="threshold" type="range" min="0" max="255" value="150"></div><div class="col-6"><label class="form-label">Crop left / top (%)</label><div class="d-flex gap-2"><input name="cropX" id="scanX" type="number" min="0" max="90" value="0" class="form-control"><input name="cropY" id="scanY" type="number" min="0" max="90" value="0" class="form-control"></div></div><div class="col-6"><label class="form-label">Crop width / height (%)</label><div class="d-flex gap-2"><input name="cropW" id="scanW" type="number" min="10" max="100" value="100" class="form-control"><input name="cropH" id="scanH" type="number" min="10" max="100" value="100" class="form-control"></div></div></div><div class="form-text mt-2">Camera capture depends on your device. After creating the PDF, choose OCR to recognize text.</div>',async()=>{if(!source)throw new Error('Capture or upload an image first.');const c=preprocess();await this.busy('Creating scanned PDF',async job=>{const d=await PDFLib.PDFDocument.create();const image=await d.embedPng(c.toDataURL('image/png'));const [w,h]=PDFLib.PageSizes.A4;const scale=Math.min((w-40)/c.width,(h-40)/c.height);const p=d.addPage([w,h]);p.drawImage(image,{x:(w-c.width*scale)/2,y:(h-c.height*scale)/2,width:c.width*scale,height:c.height*scale});await this.openPDF(await d.save(),'scan.pdf',job);});},{submit:'Create PDF'});
    let source=null;const canvas=el.querySelector('#scanPreview');const preprocess=()=>{
      const value=id=>Number(el.querySelector('#'+id).value);const x=value('scanX')/100,y=value('scanY')/100,w=value('scanW')/100,h=value('scanH')/100;if(x+w>1.001||y+h>1.001)throw new Error('Crop extends beyond the image.');const rotation=value('scanRotation'),width=source.width*w,height=source.height*h;const c=document.createElement('canvas');c.width=Math.round(rotation%180?height:width);c.height=Math.round(rotation%180?width:height);const ctx=c.getContext('2d');ctx.translate(c.width/2,c.height/2);ctx.rotate(rotation*Math.PI/180);ctx.filter=`brightness(${value('scanBrightness')}%) contrast(${value('scanContrast')}%)`;ctx.drawImage(source,source.width*x,source.height*y,width,height,-width/2,-height/2,width,height);ctx.setTransform(1,0,0,1,0,0);ctx.filter='none';const color=el.querySelector('#scanColor').value;if(color!=='color'){const data=ctx.getImageData(0,0,c.width,c.height),a=data.data;for(let i=0;i<a.length;i+=4){let gray=.2126*a[i]+.7152*a[i+1]+.0722*a[i+2];if(color==='bw')gray=gray>=value('scanThreshold')?255:0;a[i]=a[i+1]=a[i+2]=gray;}ctx.putImageData(data,0,0);}return c;
    };
    const preview=()=>{if(!source)return;try{const c=preprocess();const scale=Math.min(1,900/c.width,500/c.height);canvas.width=c.width*scale;canvas.height=c.height*scale;canvas.getContext('2d').drawImage(c,0,0,canvas.width,canvas.height);c.width=c.height=1;}catch(e){this.error(e);}};
    el.querySelector('#scanFile').addEventListener('change',async e=>{try{if(e.target.files[0]){source=await this.imageCanvas(e.target.files[0]);preview();}}catch(x){this.error(x);}});for(const control of el.querySelectorAll('input[type=range],input[type=number],select'))control.addEventListener('input',preview);el.addEventListener('hidden.bs.modal',()=>{if(source)source.width=source.height=1;},{once:true});
  }
  capabilitiesDialog(){
    const browserLabels={pdfjs:'PDF viewer',pdfLib:'PDF editing',fabric:'PDF overlays and visual signatures',purify:'Document safety',sortable:'Page ordering',zip:'ZIP exports',bootstrap:'Interface'};
    const browserGlobals={pdfjs:'pdfjsLib',pdfLib:'PDFLib',fabric:'fabric',purify:'DOMPurify',sortable:'Sortable',zip:'JSZip',bootstrap:'bootstrap'};
    const browserRows=Object.entries(STUDIO_VERSIONS).map(([name,version])=>`<tr data-source="browser"><td><strong>${this.escape(browserLabels[name]||name)}</strong></td><td><span class="badge text-bg-primary">Browser</span></td><td><span class="badge ${window[browserGlobals[name]]?'text-bg-success':'text-bg-secondary'}">${window[browserGlobals[name]]?'Available':'Unavailable'}</span></td><td>${this.escape(version)}</td><td>Runs locally on your device.</td></tr>`).join('');
    const serverRows=Object.entries(this.server.tools||{}).map(([name,tool])=>`<tr data-source="server"><td><strong>${this.escape(tool.label||name)}</strong></td><td><span class="badge text-bg-dark">Server</span></td><td>${tool.available?'<span class="badge text-bg-success">Available</span>':`<span class="badge text-bg-secondary">Unavailable</span> <button type="button" class="btn btn-link btn-sm p-1" data-action="install-help" data-tool="${this.escape(name)}" title="Ubuntu installation help" aria-label="Ubuntu installation help for ${this.escape(tool.label||name)}"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></button>`}</td><td>${this.escape(tool.version||'—')}</td><td>${this.escape((tool.features||[]).join(', '))}${tool.reason?`<div class="text-secondary mt-1">${this.escape(tool.reason)}</div>`:''}</td></tr>`).join('');
    const missingServer=serverRows?'':'<tr><td colspan="5" class="text-secondary">Server capabilities could not be checked. Reload this page to try again; browser capabilities are listed above.</td></tr>';
    const html=`<div class="d-flex flex-wrap align-items-center gap-2 mb-3"><span class="me-auto small">PDF Studio ${this.escape(window.PDF_STUDIO_BOOT.version)} · PHP ${this.escape(this.server.php||'unknown')}</span><button type="button" class="btn btn-outline-primary btn-sm" data-action="updates"><i class="fa-solid fa-cloud-arrow-down me-1"></i> Check for updates</button><button type="button" class="btn btn-outline-secondary btn-sm" data-action="changelog">What's new</button></div><p class="small text-secondary">Browser capabilities process documents on your device. Server capabilities use this installation; files are uploaded only when you choose a server operation.</p><div class="table-responsive"><table class="table capability-table"><thead><tr><th>Capability / dependency</th><th>Runs in</th><th>Status</th><th>Version</th><th>Features / details</th></tr></thead><tbody>${browserRows}${serverRows}${missingServer}</tbody></table></div><div class="alert alert-info mb-0">Click <i class="fa-solid fa-circle-info" aria-hidden="true"></i> beside an unavailable server capability for Ubuntu installation commands. Some installed tools also need permissions, extensions or server configuration before they can run.</div>`;
    this.dialog('System capabilities',html,null,{wide:true,submit:false});
  }
  installHelp(name){
    const tool=this.server.tools?.[name];if(!tool)return;
    const help=tool.install||{commands:[],notes:['No installation guide is available for this dependency.']};
    const commands=(help.commands||[]).join('\n');
    const notes=(help.notes||[]).map(note=>`<li>${this.escape(note)}</li>`).join('');
    const html=`<p class="small">Run these commands in an SSH terminal on your Ubuntu server, using an account with installation permissions.</p>${tool.reason?`<div class="alert alert-warning">${this.escape(tool.reason)}</div>`:''}<label for="studioInstallCommands" class="form-label">Ubuntu installation commands</label><textarea id="studioInstallCommands" class="form-control font-monospace mb-2" rows="${Math.max(3,help.commands?.length||0)}" readonly>${this.escape(commands)}</textarea><button type="button" id="studioCopyInstall" class="btn btn-outline-primary btn-sm mb-3" ${commands?'':'disabled'}><i class="fa-regular fa-copy me-1"></i> Copy commands</button>${notes?`<ul class="small">${notes}</ul>`:''}<p class="small text-secondary">Refresh this page after installation to check availability again.</p><button type="button" class="btn btn-outline-secondary btn-sm" data-action="capabilities">Back to System capabilities</button>`;
    this.dialog('Ubuntu setup: '+(tool.label||name),html,null,{submit:false,onOpen:modal=>{
      modal.querySelector('#studioCopyInstall').addEventListener('click',async()=>{
        try{await navigator.clipboard.writeText(commands);this.notify('Installation commands copied.');}
        catch{const area=modal.querySelector('#studioInstallCommands');area.focus();area.select();this.notify('Commands selected. Press Ctrl+C or Command+C to copy.');}
      });
    }});
  }
  palette(){const el=this.dialog('Search tools','<input class="form-control mb-3" id="paletteQuery" type="search" placeholder="Search tools, e.g. watermark, crop, OCR" aria-label="Search tools"><div id="paletteResults"></div>',null,{submit:false});const results=el.querySelector('#paletteResults');const update=()=>{const q=el.querySelector('#paletteQuery').value.toLowerCase();results.innerHTML=this.toolDefs.filter(t=>(t.title+' '+t.group+' '+(window.StudioLanguage?.text(t.title)||'')+' '+(window.StudioLanguage?.text(t.group)||'')).toLowerCase().includes(q)).map(t=>`<button type="button" class="palette-result" data-action="${t.id}" ${this.available(t)?'':'disabled'}><i class="fa-solid ${t.icon||'fa-file-pdf'} me-2"></i>${this.escape(t.title)}<small class="text-secondary float-end">${this.escape(this.available(t)?t.group:'Requires '+t.requirements)}</small></button>`).join('');};update();el.querySelector('#paletteQuery').addEventListener('input',update);el.addEventListener('shown.bs.modal',()=>el.querySelector('#paletteQuery')?.focus(),{once:true});}
  base64(bytes){let out='';for(let i=0;i<bytes.length;i+=32768)out+=String.fromCharCode(...bytes.subarray(i,i+32768));return btoa(out);}
  unbase64(text){if(typeof text!=='string'||text.length>(this.server.limits.maxLocalFile||this.server.limits.maxFile)*1.4)throw new Error('Embedded PDF exceeds the configured file limit.');return Uint8Array.from(atob(text),c=>c.charCodeAt(0));}
  project(includeBinary=false){
    this.captureOverlay();const used=new Set(this.pages.map(p=>p.sourceId));return {format:'pdfstudio-project',version:1,created:new Date().toISOString(),name:this.name,pages:JSON.parse(JSON.stringify(this.pages)),sources:[...this.sources.values()].filter(s=>used.has(s.id)).map(s=>({id:s.id,name:s.name,size:s.bytes.length,hash:s.hash,...(includeBinary?{binary:this.base64(s.bytes)}:{})})),metadata:{...this.metadata},settings:{current:this.current,group:this.group,zoom:this.zoom,secureDPI:this.secureDPI||300},document:this.documentEditor?.getState()||null};
  }
  saveProjectDialog(){this.dialog('Save editable project','<p class="small">Includes page order, editable overlays, document source, settings and metadata.</p><div class="form-check"><input class="form-check-input" type="checkbox" name="binary" id="includeProjectBinary"><label class="form-check-label" for="includeProjectBinary">Include source PDF files for a self-contained project</label></div><div class="form-text">By default source PDFs are linked by SHA-256 hash and must be selected again when reopening. Including them increases project size.</div>',v=>{const state=this.project(v.binary);this.download(JSON.stringify(state),this.name.replace(/\.pdf$/i,'')+'.pdfstudio.json','application/json');this.dirty=false;this.status();},{submit:'Save project'});}
  validateOverlay(json){
    if(!json)return null;if(!Array.isArray(json.objects)||json.objects.length>2000)throw new Error('Project contains too many overlay objects.');const types=new Set(['rect','ellipse','line','polygon','polyline','path','group','activeselection','text','i-text','itext','textbox','image']);let count=0;
    const scrub=(o,depth=0)=>{if(!o||typeof o!=='object'||depth>12||++count>5000||!types.has(String(o.type).toLowerCase()))throw new Error('Project contains an unsupported overlay object.');
      for(const name of ['left','top','width','height','scaleX','scaleY','fontSize','strokeWidth','angle','opacity','rx','ry','cropX','cropY'])if(o[name]!==undefined&&(!Number.isFinite(o[name])||Math.abs(o[name])>100000))throw new Error('Project contains invalid object geometry.');
      if(o.src&&!/^data:image\/(png|jpeg|webp|gif);base64,[a-z0-9+/=]+$/i.test(o.src))throw new Error('Project image sources must be embedded PNG/JPEG/WebP/GIF data.');if(o.src&&o.src.length>40000000)throw new Error('Project image is too large.');
      if(o.studioLink&&!/^https?:\/\//i.test(o.studioLink))delete o.studioLink;
      for(const name of ['fill','stroke','backgroundColor','textBackgroundColor'])if(o[name]&&typeof o[name]!=='string')throw new Error('Complex paint resources are not accepted in imported projects.');
      delete o.clipPath;delete o.filters;delete o.resizeFilter;delete o.shadow;if(Array.isArray(o.objects))o.objects=o.objects.map(x=>scrub(x,depth+1));
      if(Array.isArray(o.points)&&(o.points.length>10000||o.points.some(p=>!Number.isFinite(p.x)||!Number.isFinite(p.y)||Math.abs(p.x)>100000||Math.abs(p.y)>100000)))throw new Error('Invalid polygon geometry.');if(Array.isArray(o.path)){if(o.path.length>100000)throw new Error('Project drawing is too complex.');for(const part of o.path)if(!Array.isArray(part)||part.some(x=>typeof x==='number'&&(!Number.isFinite(x)||Math.abs(x)>1000000)))throw new Error('Invalid drawing path.');}
      return o;
    };return {version:json.version,objects:json.objects.map(o=>scrub(JSON.parse(JSON.stringify(o))))};
  }
  async restoreProject(project){
    if(project?.format==='pdfstudio-document'){await this.documentEditor.restore(project);await this.documentEditor.show();this.markDirty();return;}
    if(project?.format!=='pdfstudio-project'||project.version!==1||!Array.isArray(project.pages)||!Array.isArray(project.sources))throw new Error('This is not a supported PDF Studio project.');if(project.pages.length>this.server.limits.maxPages||project.sources.length>100)throw new Error('Project exceeds the configured limits.');
    const relink=project.sources.filter(s=>!s.binary&&!Array.from(this.sources.values()).some(v=>v.hash===s.hash));let linked=[];if(relink.length){this.notify('Select the original PDFs to relink '+relink.map(s=>s.name).join(', ')+'.');linked=await this.pickFiles('.pdf',true);if(!linked.length)throw new Error('Original PDF files are required to reopen this project.');}
    const fileMap=new Map();for(const f of linked){const bytes=new Uint8Array(await f.arrayBuffer());fileMap.set(await this.hash(bytes),bytes);}const idMap=new Map();
    for(const s of project.sources){if(typeof s.id!=='string'||typeof s.hash!=='string')throw new Error('Invalid project source.');const existing=[...this.sources.values()].find(v=>v.hash===s.hash);if(existing){idMap.set(s.id,existing.id);continue;}const bytes=s.binary?this.unbase64(s.binary):fileMap.get(s.hash);if(!bytes)throw new Error('Could not relink '+s.name+'. The SHA-256 hash must match the original PDF.');if(await this.hash(bytes)!==s.hash)throw new Error('Embedded source hash does not match '+s.name+'.');const c=await this.source(bytes,String(s.name||'document.pdf'));idMap.set(s.id,c.src.id);}
    const pages=project.pages.map(p=>{const sid=idMap.get(p.sourceId),src=this.sources.get(sid);if(!src||!Number.isInteger(p.index)||p.index<0||p.index>=src.lib.getPageCount())throw new Error('Project references an invalid PDF page.');if(![0,90,180,270].includes(p.rotation)||!Number.isFinite(p.width)||!Number.isFinite(p.height)||p.width<=0||p.height<=0||p.width>14400||p.height>14400)throw new Error('Invalid project page dimensions.');return {...p,id:this.id(),sourceId:sid,overlay:this.validateOverlay(p.overlay)};});
    this.currentPageId=null;this.pages=pages;this.metadata=project.metadata||{};this.name=String(project.name||'document.pdf');this.current=Math.max(0,Math.min(pages.length-1,Number(project.settings?.current)||0));this.group=project.settings?.group||'Edit PDF';this.zoom=project.settings?.zoom||'fit-page';this.secureDPI=[150,300,450,600].includes(Number(project.settings?.secureDPI))?Number(project.settings.secureDPI):300;this.selected.clear();this.history=[];this.historyIndex=-1;
    if(project.document)await this.documentEditor.restore(project.document);if(pages.length){this.commit();this.setMode('pdf');await this.refresh();}else await this.documentEditor.show();this.dirty=false;this.status();
  }
  autosaveKey(){return 'pdfstudio.autosave.'+location.origin+location.pathname;}
  async writeAutosave(value){if(value===null)localStorage.removeItem(this.autosaveKey());else localStorage.setItem(this.autosaveKey(),JSON.stringify(value));}
  async readAutosave(){try{return JSON.parse(localStorage.getItem(this.autosaveKey())||'null');}catch{return null;}}
  async saveAutosave(){
    try{const state=this.project(false),size=JSON.stringify(state).length;if(size>4000000)throw new Error('Autosave size limit');await this.writeAutosave(state);this.autosaveWarning=false;}
    catch{if(!this.autosaveWarning){this.autosaveWarning=true;this.notify('Automatic saving is unavailable or browser storage is full. Download an editable project to keep your work.');}}
  }
}

class DocumentStudio {
  constructor(S) {
    this.S = S;
    this.editor = null;
    this.loading = null;
    this.selection = null;
    this.activeImage = null;
    this.state = this.defaults();
    this.html = '<h1>'+S.escape(window.StudioLanguage?.text('Untitled document')||'Untitled document')+'</h1><p>'+S.escape(window.StudioLanguage?.text('Write your document here.')||'Write your document here.')+'</p>';
    this.panel = document.getElementById('documentPanel');
    this.panel.addEventListener('pointerdown', e => {
      if (e.target.closest('[data-doc-action]')) this.remember();
    });
    this.panel.addEventListener('click', e => {
      const button = e.target.closest('[data-doc-action]');
      if (!button) return;
      this.panel.querySelectorAll('details[open]').forEach(d => d.open = false);
      this.act(button.dataset.docAction).catch(error => S.error(error));
    });
    document.getElementById('docEngine').addEventListener('change', () => this.updatePrivacy());
  }

  defaults() {
    return {size:'A4',width:210,height:297,landscape:false,margins:{top:20,right:20,bottom:20,left:20},columns:1,columnGap:8,background:'#ffffff',borderWidth:0,borderColor:'#000000',font:'Arial',fontSize:11,direction:'ltr',header:{left:'',center:'',right:''},footer:{left:'',center:'',right:''},firstHeader:{left:'',center:'',right:''},firstFooter:{left:'',center:'',right:''},differentFirst:false,metadata:{title:window.StudioLanguage?.text('Untitled document')||'Untitled document',author:'',subject:'',keywords:'',created:new Date().toISOString()}};
  }

  async init() {
    if (this.editor) return this.editor;
    if (this.loading) return this.loading;
    this.loading = (async () => {
      const cssId = 'pdfstudioJoditCSS';
      if (!document.getElementById(cssId)) {
        const css = document.createElement('link');
        css.id = cssId; css.rel = 'stylesheet';
        css.href = 'https://cdn.jsdelivr.net/npm/jodit@4.17.2/es2021/jodit.min.css';
        document.head.append(css);
      }
      await this.S.loadScript('https://cdn.jsdelivr.net/npm/jodit@4.17.2/es2021/jodit.min.js', 'Jodit');
      window.StudioLanguage?.registerEditor(Jodit.lang);
      const localImage = {name:'docLocalImage',icon:'image',tooltip:'Insert a local image',exec:()=>this.insertImages().catch(error=>this.S.error(error))};
      this.editor = Jodit.make('#docEditor', {
        height:'auto',minHeight:550,toolbarAdaptive:true,toolbarSticky:false,language:window.StudioLanguage?.language||'it',
        spellcheck:true,askBeforePasteHTML:false,askBeforePasteFromWord:false,
        defaultActionOnPaste:'insert_as_html',processPasteFromWord:true,
        uploader:{insertImageAsBase64URI:true},imageDefaultWidth:300,
        image:{editSrc:false,useImageEditor:false,editStyle:false},
        showCharsCounter:true,showWordsCounter:true,showXPathInStatusbar:false,
        saveModeInStorage:false,enter:'P',direction:this.state.direction,
        cleanHTML:{removeEmptyElements:false,denyTags:'script,iframe,object,embed,form,video,audio'},
        events:{beforeSetNativeEditorValue:payload=>{payload.value=this.clean(payload.value);}},
        buttons:['undo','redo','|','bold','italic','underline','strikethrough','superscript','subscript','|','font','fontsize','brush','paragraph','|','ul','ol','outdent','indent','|','align','lineHeight','|',localImage,'table','link','hr','symbols','|','find','copyformat','eraser','source','fullsize'],
        buttonsMD:['undo','redo','|','bold','italic','underline','|','font','fontsize','brush','paragraph','|','ul','ol','align','|',localImage,'table','link','|','find','source','dots'],
        buttonsSM:['undo','redo','|','bold','italic','underline','|','paragraph','brush','|',localImage,'table','|','dots'],
        buttonsXS:['undo','redo','|','bold','italic','|','paragraph','|','dots'],
        controls:{paragraph:{list:{p:'Paragraph',h1:'Heading 1',h2:'Heading 2',h3:'Heading 3',h4:'Heading 4',h5:'Heading 5',h6:'Heading 6',blockquote:'Quote',pre:'Code'}},font:{list:{'Arial,Helvetica,sans-serif':'Arial','Georgia,serif':'Georgia','Times New Roman,serif':'Times New Roman','Verdana,sans-serif':'Verdana','Tahoma,sans-serif':'Tahoma','Courier New,monospace':'Courier New','system-ui,sans-serif':'System UI'}}}
      });
      this.editor.value = this.clean(this.html);
      this.editor.events.on('change', () => { this.updateCounts(); this.S.markDirty(); });
      this.editor.editor.addEventListener('keyup', () => this.remember());
      this.editor.editor.addEventListener('mouseup', () => this.remember());
      this.editor.editor.addEventListener('click', e => {
        if (e.target.tagName === 'IMG') this.activeImage = e.target;
        const cell = e.target.closest('td,th');
        if (cell) this.activeCell = cell;
        const check = e.target.closest('.doc-check');
        if (check) {
          check.textContent = check.textContent === '☑' ? '☐' : '☑';
          check.setAttribute('aria-checked',check.textContent === '☑' ? 'true' : 'false');
          this.changed();
        }
      });
      this.editor.editor.addEventListener('paste', e => {
        const pasted = e.clipboardData?.getData('text/html');
        if (pasted) { e.preventDefault(); e.stopImmediatePropagation(); this.insert(this.clean(pasted)); }
      }, true);
      this.editor.editor.addEventListener('drop', e => {
        const files = [...(e.dataTransfer?.files || [])].filter(f => f.type.startsWith('image/'));
        if (!files.length) return;
        e.preventDefault(); e.stopImmediatePropagation();
        this.addImages(files).catch(error => this.S.error(error));
      }, true);
      document.getElementById('docLoadMessage').classList.add('d-none');
      this.refreshEngines(); this.applyLayout(); this.updateCounts();
      return this.editor;
    })();
    try { return await this.loading; }
    catch (error) {
      this.loading = null;
      document.getElementById('docLoadMessage').textContent = 'Document editor could not load: ' + error.message + '. Check your Internet connection and CDN access.';
      throw error;
    }
  }

  async show() {
    this.S.setMode('document');
    await this.init();
    this.refreshEngines();
    // A project can restore the editor while its panel is hidden. Recalculate
    // Jodit's adaptive toolbar only after the visible panel has been laid out.
    await new Promise(resolve=>requestAnimationFrame(resolve));
    this.editor.e.fire('resize');
  }

  clean(html) {
    const template = document.createElement('template');
    template.innerHTML = this.S.sanitize(String(html));
    template.content.querySelectorAll('script,iframe,object,embed,form,video,audio,link,style,meta').forEach(el => el.remove());
    template.content.querySelectorAll('*').forEach(el => {
      if (el.hasAttribute('style') && /[\\\x00-\x1f]|url\s*\(|image-set|expression\s*\(|@|-moz-binding|behavior\s*:|javascript:/i.test(el.getAttribute('style'))) el.removeAttribute('style');
      else if (el.hasAttribute('style')) el.setAttribute('style',el.getAttribute('style').replace(/(?:^|;)\s*mso-[^:;]+:[^;]*/gi,''));
      if (el.hasAttribute('class')) el.setAttribute('class',el.getAttribute('class').split(/\s+/).filter(name=>!/^Mso/i.test(name)).join(' '));
      for (const a of [...el.attributes]) {
        if (a.name.startsWith('on') || ['srcset','background','formaction'].includes(a.name)) el.removeAttribute(a.name);
      }
      if (el.tagName === 'IMG' && !/^data:image\/(?:png|jpe?g|gif|webp|bmp);base64,/i.test(el.getAttribute('src') || '')) el.removeAttribute('src');
    });
    return template.innerHTML;
  }

  getState() { return {html:this.clean(this.editor ? this.editor.value : this.html),settings:JSON.parse(JSON.stringify(this.state))}; }

  async restore(project) {
    if (!project || typeof project.html !== 'string') throw new Error('This file has no editable document source.');
    if (project.html.length > 50000000) throw new Error('Document source exceeds the 50 MB browser project limit.');
    const defaults = this.defaults(), incoming = project.settings || {};
    this.state = {...defaults,...incoming,margins:{...defaults.margins,...incoming.margins},metadata:{...defaults.metadata,...incoming.metadata}};
    ['header','footer','firstHeader','firstFooter'].forEach(k => this.state[k] = {...defaults[k],...incoming[k]});
    this.state.width = this.number(this.state.width,50,1000,210);
    this.state.height = this.number(this.state.height,50,1000,297);
    this.state.columns = Math.round(this.number(this.state.columns,1,3,1));
    this.state.landscape = this.state.landscape === true;
    this.state.differentFirst = this.state.differentFirst === true;
    this.state.columnGap = this.number(this.state.columnGap,0,50,8);
    this.state.fontSize = this.number(this.state.fontSize,6,96,11);
    this.state.borderWidth = this.number(this.state.borderWidth,0,10,0);
    this.state.background = /^#[0-9a-f]{6}$/i.test(this.state.background) ? this.state.background : '#ffffff';
    this.state.borderColor = /^#[0-9a-f]{6}$/i.test(this.state.borderColor) ? this.state.borderColor : '#000000';
    this.state.font = ['Arial','Georgia','Times New Roman','Verdana','Tahoma','Courier New','system-ui'].includes(this.state.font) ? this.state.font : 'Arial';
    this.state.direction = this.state.direction === 'rtl' ? 'rtl' : 'ltr';
    ['top','right','bottom','left'].forEach(k => this.state.margins[k] = this.number(this.state.margins[k],0,100,20));
    this.html = this.clean(project.html);
    await this.init(); this.editor.value = this.html; this.applyLayout(); this.updateCounts(); this.S.markDirty();
  }

  number(value,min,max,fallback) { const n = Number(value); return Number.isFinite(n) ? Math.min(max,Math.max(min,n)) : fallback; }
  escape(value) { return this.S.escape(String(value ?? '')); }

  remember() {
    const sel = window.getSelection();
    if (this.editor && sel?.rangeCount && this.editor.editor.contains(sel.anchorNode)) this.selection = sel.getRangeAt(0).cloneRange();
  }

  focusSelection() {
    this.editor.editor.focus();
    if (this.selection?.startContainer.isConnected && this.editor.editor.contains(this.selection.startContainer)) {
      const sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(this.selection);
    }
  }

  insert(html) { this.focusSelection(); this.editor.s.insertHTML(this.clean(html)); this.remember(); this.changed(); }
  changed() { this.editor.synchronizeValues(); this.updateCounts(); this.S.markDirty(); }

  updateCounts() {
    const text = this.editor ? this.editor.editor.textContent || '' : '';
    const words = text.trim() ? text.trim().split(/\s+/u).length : 0;
    document.getElementById('docCounts').textContent = words.toLocaleString() + ' words · ' + [...text].length.toLocaleString() + ' characters';
  }

  refreshEngines() {
    const select = document.getElementById('docEngine'), previous = select.value;
    const engines = this.S.server?.engines?.html || [];
    const detected = engines.length ? engines.map(e => typeof e === 'string' ? e : e.id || e.name) : ['chromium','mpdf','dompdf','tcpdf'].filter(e => this.S.server?.tools?.[e]?.available);
    const labels = {chromium:'Chromium · best modern CSS',mpdf:'mPDF · strong paged documents',dompdf:'Dompdf · basic HTML/CSS',tcpdf:'TCPDF · basic HTML'};
    select.innerHTML = '<option value="browser">Browser print · local</option>' + (detected.length ? '<option value="auto">Auto · server renderer</option>' : '') + detected.filter(e => labels[e]).map(e => '<option value="' + e + '">' + labels[e] + '</option>').join('');
    if ([...select.options].some(o => o.value === previous)) select.value = previous;
    this.updatePrivacy();
  }

  updatePrivacy() { document.getElementById('docPrivacy').textContent = document.getElementById('docEngine').value === 'browser' ? 'Locally in your browser' : 'On the server · document uploaded only when exporting'; }

  dimensions() { return this.state.landscape ? {width:this.state.height,height:this.state.width} : {width:this.state.width,height:this.state.height}; }

  applyLayout() {
    if (!this.editor) return;
    const s = this.state, dim = this.dimensions(), body = this.editor.editor;
    Object.assign(body.style,{width:dim.width+'mm',minHeight:dim.height+'mm',padding:`${s.margins.top}mm ${s.margins.right}mm ${s.margins.bottom}mm ${s.margins.left}mm`,backgroundColor:s.background,border:s.borderWidth ? `${s.borderWidth}pt solid ${s.borderColor}` : 'none',fontFamily:s.font,fontSize:s.fontSize+'pt',direction:s.direction,columnCount:String(s.columns),columnGap:s.columnGap+'mm'});
    document.getElementById('docPageInfo').textContent = `${s.size} · ${dim.width} × ${dim.height} mm · ${s.landscape ? 'landscape' : 'portrait'} · ${s.columns} column${s.columns>1?'s':''}`;
  }

  field(label,name,type='text',value='',extra='') {
    return `<div class="mb-3"><label class="form-label" for="docField_${name}">${this.escape(label)}</label><input id="docField_${name}" class="form-control" type="${type}" name="${name}" value="${this.escape(value)}" ${extra}></div>`;
  }

  async act(action) {
    await this.init();
    const actions = {new:()=>this.newDocument(),open:()=>this.openSource(),save:()=>this.saveSource(),layout:()=>this.layoutDialog(),headers:()=>this.headerDialog(),metadata:()=>this.metadataDialog(),paragraph:()=>this.paragraphDialog(),pagebreak:()=>this.insert('<div class="doc-pagebreak" style="break-after:page;page-break-after:always" contenteditable="false"><br></div><p><br></p>'),section:()=>this.sectionDialog(),image:()=>this.insertImages(),caption:()=>this.captionDialog(),crop:()=>this.cropDialog(),table:()=>this.tableDialog(),checklist:()=>this.insert('<ul style="list-style:none;padding-left:0"><li><span class="doc-check" contenteditable="false" role="checkbox" aria-checked="false">☐</span> Checklist item</li></ul><p><br></p>'),quote:()=>this.insert('<blockquote><p>Quotation</p></blockquote><p><br></p>'),anchor:()=>this.anchorDialog(),toc:()=>this.insertTOC(),date:()=>this.insert(this.escape(new Date().toLocaleDateString())),time:()=>this.insert(this.escape(new Date().toLocaleTimeString())),qr:()=>this.codeDialog('qr'),barcode:()=>this.codeDialog('barcode'),signature:()=>this.signatureDialog(),preview:()=>this.preview(),export:()=>this.exportPDF()};
    if (!actions[action]) throw new Error('Unknown document command: ' + action);
    return actions[action]();
  }

  newDocument() {
    this.S.dialog('New document','<p>Replace the current editable document with a blank document? Save its source first if you need it.</p>',() => {
      this.state = this.defaults(); this.editor.value = '<p><br></p>'; this.applyLayout(); this.changed();
    },{submit:'Create new document'});
  }

  async openSource() {
    const files = await this.S.pickFiles('.json,.pdfstudio.json,.html,.htm,.txt,.md,.markdown',false);
    if (!files.length) return;
    const file = files[0];
    if (file.size > 50000000) throw new Error('Document source exceeds the 50 MB browser project limit.');
    const text = await file.text();
    if (/\.json$/i.test(file.name)) {
      const source = JSON.parse(text);
      await this.restore(source.document || source.wysiwyg || source);
    } else if (/\.html?$/i.test(file.name)) {
      const parsed = document.createElement('template');
      parsed.innerHTML = text;
      parsed.content.querySelectorAll('.doc-screen-note').forEach(el=>el.remove());
      const stateNode = parsed.content.querySelector('#pdfstudioDocumentState');
      let settings;
      if (stateNode) { try { settings = JSON.parse(stateNode.textContent); } catch (_) {} stateNode.remove(); }
      await this.restore({html:parsed.innerHTML,settings});
      this.S.notify('HTML imported. Remote images and executable markup were removed.');
    } else if (/\.(md|markdown)$/i.test(file.name)) {
      await this.S.loadScript('https://cdn.jsdelivr.net/npm/marked@18.1.0/lib/marked.umd.js','marked');
      const markdownHTML = marked.parse(text).replace(/<input\b[^>]*type="checkbox"[^>]*>/gi,tag=>`<span class="doc-check" contenteditable="false" role="checkbox" aria-checked="${/\bchecked\b/i.test(tag)?'true':'false'}">${/\bchecked\b/i.test(tag)?'☑':'☐'}</span>`);
      this.editor.value = this.clean(markdownHTML); this.changed();
    } else { this.editor.value = text.split(/\r?\n/).map(line => '<p>'+this.escape(line || '\u00a0')+'</p>').join(''); this.changed(); }
    this.state.metadata.title = file.name.replace(/\.[^.]+$/,''); this.applyLayout();
  }

  saveSource() {
    this.S.dialog('Save editable source','<label class="form-label" for="docSourceFormat">Format</label><select id="docSourceFormat" class="form-select" name="format"><option value="json">PDF Studio JSON · document source, settings and metadata</option><option value="html">HTML · standalone document with embedded settings</option><option value="txt">Plain text</option></select>',data => {
      const base = this.fileName();
      if (data.format === 'html') this.S.download(this.buildHTML(true),base+'.html','text/html;charset=utf-8');
      else if (data.format === 'txt') this.S.download(this.editor.editor.innerText,base+'.txt','text/plain;charset=utf-8');
      else this.S.download(JSON.stringify({format:'pdfstudio-document',version:1,...this.getState()},null,2),base+'.pdfstudio.json','application/json');
    },{submit:'Download source'});
  }

  fileName() { return String(this.state.metadata.title || 'document').replace(/[<>:"/\\|?*\x00-\x1f]/g,'_').slice(0,100) || 'document'; }

  layoutDialog() {
    const s = this.state;
    this.S.dialog('Document page layout',`<div class="row"><div class="col-md-6"><label class="form-label" for="docPageSize">Paper size</label><select id="docPageSize" class="form-select mb-3" name="size">${['A4','A3','A5','Letter','Legal','Custom'].map(v=>`<option${s.size===v?' selected':''}>${v}</option>`).join('')}</select>${this.field('Page width (mm)','width','number',s.width,'min="50" max="1000" step="0.1"')}${this.field('Page height (mm)','height','number',s.height,'min="50" max="1000" step="0.1"')}<label class="form-label" for="docOrientation">Orientation</label><select id="docOrientation" name="landscape" class="form-select mb-3"><option value="0"${!s.landscape?' selected':''}>Portrait</option><option value="1"${s.landscape?' selected':''}>Landscape</option></select><label class="form-label" for="docColumns">Columns</label><select id="docColumns" name="columns" class="form-select mb-3">${[1,2,3].map(v=>`<option value="${v}"${s.columns===v?' selected':''}>${v}</option>`).join('')}</select>${this.field('Column gap (mm)','columnGap','number',s.columnGap,'min="0" max="50"')}</div><div class="col-md-6">${['top','right','bottom','left'].map(k=>this.field(k+' margin (mm)',k,'number',s.margins[k],'min="0" max="100" step="0.1"')).join('')}${this.field('Page background','background','color',s.background)}${this.field('Page border width (pt, 0 = none)','borderWidth','number',s.borderWidth,'min="0" max="10" step="0.25"')}${this.field('Page border color','borderColor','color',s.borderColor)}</div></div>`,data => {
      const next = {...s,margins:{...s.margins}};
      const sizes = {A4:[210,297],A3:[297,420],A5:[148,210],Letter:[215.9,279.4],Legal:[215.9,355.6]};
      const size = sizes[data.size] || [this.number(data.width,50,1000,210),this.number(data.height,50,1000,297)];
      next.size = data.size; [next.width,next.height] = size; next.landscape = data.landscape === '1';
      next.columns = Number(data.columns); next.columnGap = this.number(data.columnGap,0,50,8); next.background = data.background; next.borderWidth = this.number(data.borderWidth,0,10,0); next.borderColor = data.borderColor;
      ['top','right','bottom','left'].forEach(k => next.margins[k] = this.number(data[k],0,100,20));
      const dim = next.landscape ? {width:next.height,height:next.width} : {width:next.width,height:next.height};
      if (next.margins.left+next.margins.right >= dim.width-20 || next.margins.top+next.margins.bottom >= dim.height-20) throw new Error('Margins must leave at least 20 mm of usable page width and height.');
      this.state = next;
      this.applyLayout(); this.S.markDirty();
    },{wide:true});
  }

  headerDialog() {
    const s = this.state;
    const row = (key,label) => `<fieldset class="mb-3"><legend class="fs-6">${label}</legend><div class="row">${['left','center','right'].map(pos=>'<div class="col-md-4">'+this.field(pos,key+'_'+pos,'text',s[key][pos])+'</div>').join('')}</div></fieldset>`;
    this.S.dialog('Headers, footers and page numbering',`<p class="small">Reusable plain text. Tokens: <code>{{page}}</code>, <code>{{pages}}</code>, <code>{{title}}</code>, <code>{{date}}</code>. Example: Page {{page}} of {{pages}}. Chrome and Chromium support margin counters; browser support varies.</p>${row('header','Header')}${row('footer','Footer')}<div class="form-check mb-3"><input id="docDifferentFirst" class="form-check-input" type="checkbox" name="differentFirst"${s.differentFirst?' checked':''}><label class="form-check-label" for="docDifferentFirst">Different first page</label></div>${row('firstHeader','First-page header')}${row('firstFooter','First-page footer')}`,data => {
      ['header','footer','firstHeader','firstFooter'].forEach(k=>['left','center','right'].forEach(pos=>s[k][pos]=String(data[k+'_'+pos] || '').slice(0,500)));
      s.differentFirst = Boolean(data.differentFirst); this.S.markDirty();
    },{wide:true});
  }

  metadataDialog() {
    const m = this.state.metadata;
    this.S.dialog('Document metadata',this.field('Title','title','text',m.title)+this.field('Author','author','text',m.author)+this.field('Subject','subject','text',m.subject)+this.field('Keywords','keywords','text',m.keywords)+`<p class="small text-secondary">Created ${this.escape(m.created)}. Export time is used for the PDF modification date.</p>`,data=>{['title','author','subject','keywords'].forEach(k=>m[k]=String(data[k] || '').slice(0,1000));this.S.markDirty();});
  }

  selectedBlocks() {
    const body = this.editor.editor, range = this.selection;
    let current = range?.startContainer;
    if (current?.nodeType === Node.TEXT_NODE) current = current.parentElement;
    const nearest = current?.closest?.('p,h1,h2,h3,h4,h5,h6,li,blockquote,pre,td,th');
    if (!range || range.collapsed) return [nearest || body];
    const blocks = [...body.querySelectorAll('p,h1,h2,h3,h4,h5,h6,li,blockquote,pre')].filter(el => { try { return range.intersectsNode(el); } catch (_) { return false; } });
    return blocks.length ? blocks : [nearest || body];
  }

  paragraphDialog() {
    const blocks = this.selectedBlocks(), style = getComputedStyle(blocks[0]);
    this.S.dialog('Paragraph format',`<div class="row"><div class="col-md-6">${this.field('Line spacing (multiplier)','lineHeight','number',1.5,'min="0.7" max="5" step="0.05"')}${this.field('Space before (pt)','before','number',parseFloat(style.marginTop)||0,'min="0" max="200"')}${this.field('Space after (pt)','after','number',parseFloat(style.marginBottom)||0,'min="0" max="200"')}</div><div class="col-md-6">${this.field('First-line indent (mm)','indent','number',0,'min="-50" max="100" step="0.5"')}<label class="form-label" for="docDirection">Direction</label><select id="docDirection" class="form-select mb-3" name="direction"><option value="ltr">Left to right</option><option value="rtl"${style.direction==='rtl'?' selected':''}>Right to left</option></select><label class="form-label" for="docKeep">Keep paragraph</label><select id="docKeep" name="keep" class="form-select"><option value="auto">Allow page breaks</option><option value="avoid">Keep paragraph on one page</option></select></div></div>`,data=>{
      blocks.forEach(el=>Object.assign(el.style,{lineHeight:String(this.number(data.lineHeight,.7,5,1.5)),marginTop:this.number(data.before,0,200,0)+'pt',marginBottom:this.number(data.after,0,200,0)+'pt',textIndent:this.number(data.indent,-50,100,0)+'mm',direction:data.direction==='rtl'?'rtl':'ltr',breakInside:data.keep==='avoid'?'avoid':'auto'}));
      if (blocks.includes(this.editor.editor)) this.state.direction = data.direction;
      this.changed();
    },{wide:true});
  }

  sectionDialog() {
    this.S.dialog('Insert a column section','<p class="small">A section applies its own column count to the selected text. Use a page break to begin a section on a new page.</p><label class="form-label" for="docSectionColumns">Columns</label><select id="docSectionColumns" name="columns" class="form-select"><option>1</option><option>2</option><option>3</option></select>',data=>{
      this.focusSelection(); const selected = this.editor.s.html || '<p>Section text</p>';
      this.insert(`<section style="column-count:${Number(data.columns)};column-gap:8mm">${selected}</section><p><br></p>`);
    });
  }

  async readImage(file) {
    if (!/^image\/(png|jpe?g|gif|webp|bmp)$/i.test(file.type)) throw new Error('Use PNG, JPEG, GIF, WebP or BMP images.');
    if (file.size > 20000000) throw new Error('Individual document images must be smaller than 20 MB.');
    const source = await new Promise((resolve,reject) => { const reader = new FileReader(); reader.onload = () => resolve(reader.result); reader.onerror = () => reject(new Error('Could not read image.')); reader.readAsDataURL(file); });
    if (file.type !== 'image/bmp') return source;
    const image = new Image(); image.src = source;
    try { await image.decode(); } catch (_) { throw new Error('This browser cannot decode that BMP image. Convert it to PNG first.'); }
    if (image.naturalWidth*image.naturalHeight > 40000000) throw new Error('This BMP image is too large to convert safely in the browser.');
    const canvas = document.createElement('canvas'); canvas.width = image.naturalWidth; canvas.height = image.naturalHeight;
    canvas.getContext('2d').drawImage(image,0,0); return canvas.toDataURL('image/png');
  }

  async addImages(files) { for (const file of files) this.insert(`<img src="${await this.readImage(file)}" alt="${this.escape(file.name)}" style="max-width:100%;width:300px">`); }
  async insertImages() { const files = await this.S.pickFiles('image/png,image/jpeg,image/webp,image/gif,image/bmp',true); await this.addImages(files); }

  selectedImage() {
    let node = this.selection?.startContainer;
    if (node?.nodeType === Node.TEXT_NODE) node = node.parentElement;
    const img = node?.closest?.('img') || (this.activeImage?.isConnected ? this.activeImage : null);
    if (!img || !this.editor.editor.contains(img)) throw new Error('Click an image in the document first.');
    return img;
  }

  captionDialog() {
    const img = this.selectedImage(), figure = img.closest('figure'), caption = figure?.querySelector('figcaption')?.textContent || '';
    this.S.dialog('Image properties',this.field('Alternative text','alt','text',img.alt)+this.field('Caption','caption','text',caption)+this.field('Image width (px)','width','number',Math.round(img.getBoundingClientRect().width),'min="20" max="2000"')+'<label class="form-label" for="docImageWrap">Alignment / wrapping</label><select id="docImageWrap" class="form-select" name="wrap"><option value="block">Centered block</option><option value="left">Float left · text wraps right</option><option value="right">Float right · text wraps left</option><option value="inline">Inline</option></select>',data=>{
      img.alt = data.alt || ''; img.style.width = this.number(data.width,20,2000,300)+'px'; img.style.height = 'auto'; img.style.maxWidth='100%';
      let wrap = figure;
      if (data.caption || figure) { if (!wrap) {wrap = document.createElement('figure');img.replaceWith(wrap);wrap.append(img);}let c = wrap.querySelector('figcaption');if (!c) {c=document.createElement('figcaption');wrap.append(c);}c.textContent = data.caption || '';}
      const target = wrap || img;
      Object.assign(target.style,{float:['left','right'].includes(data.wrap)?data.wrap:'none',display:data.wrap==='inline'?'inline':'block',margin:data.wrap==='block'?'1em auto':data.wrap==='left'?'0 1em 1em 0':data.wrap==='right'?'0 0 1em 1em':'0'});
      this.changed();
    });
  }

  cropDialog() {
    const img = this.selectedImage();
    this.S.dialog('Crop image',`<p class="small">Crop the original image using percentage coordinates. The original image is replaced with the cropped bitmap.</p><div class="row">${['x','y','width','height'].map(k=>'<div class="col-6">'+this.field(k+' (%)',k,'number',['width','height'].includes(k)?100:0,'min="0" max="100" step="0.1"')+'</div>').join('')}</div>`,async data=>{
      const x = this.number(data.x,0,99,0), y=this.number(data.y,0,99,0), w=this.number(data.width,1,100,100), h=this.number(data.height,1,100,100);
      if(x+w>100 || y+h>100) throw new Error('The crop rectangle must fit inside the image.');
      await img.decode(); const canvas=document.createElement('canvas');canvas.width=Math.max(1,Math.round(img.naturalWidth*w/100));canvas.height=Math.max(1,Math.round(img.naturalHeight*h/100));
      if(canvas.width*canvas.height>40000000) throw new Error('This image is too large to crop safely in the browser.');
      canvas.getContext('2d').drawImage(img,img.naturalWidth*x/100,img.naturalHeight*y/100,img.naturalWidth*w/100,img.naturalHeight*h/100,0,0,canvas.width,canvas.height);
      img.src=canvas.toDataURL('image/png');img.style.height='auto';this.changed();
    });
  }

  tableDialog() {
    let node=this.selection?.startContainer;if(node?.nodeType===Node.TEXT_NODE)node=node.parentElement;
    const cell=node?.closest?.('td,th') || (this.activeCell?.isConnected ? this.activeCell : null),table=cell?.closest('table') || node?.closest?.('table');
    if(!table) throw new Error('Place the text cursor in a table cell first. Insert tables with the table button in the editor toolbar.');
    this.S.dialog('Table and cell properties',`<div class="row"><div class="col-md-6">${this.field('Table width (%)','tableWidth','number',parseFloat(table.style.width)||100,'min="10" max="100"')}${this.field('Cell padding (px)','padding','number',6,'min="0" max="80"')}${this.field('Border width (px)','border','number',1,'min="0" max="10"')}${this.field('Border color','color','color','#adb5bd')}</div><div class="col-md-6">${this.field('Cell background','background','color','#ffffff')}${this.field('Current cell width (%) · 0 = automatic','cellWidth','number',parseFloat(cell?.style.width)||0,'min="0" max="100"')}<label class="form-label" for="docCellAlign">Cell alignment</label><select id="docCellAlign" class="form-select mb-3" name="align"><option>left</option><option>center</option><option>right</option></select><label class="form-label" for="docCellVertical">Vertical alignment</label><select id="docCellVertical" class="form-select mb-3" name="vertical"><option>top</option><option>middle</option><option>bottom</option></select><label class="form-label" for="docTableAlign">Table alignment</label><select id="docTableAlign" class="form-select mb-3" name="tableAlign"><option>left</option><option>center</option><option>right</option></select></div></div><div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="docWholeTable" name="whole" checked><label class="form-check-label" for="docWholeTable">Apply cell styling to the whole table</label></div><div class="form-check"><input class="form-check-input" type="checkbox" id="docHeaderRow" name="header"${table.tHead?' checked':''}><label class="form-check-label" for="docHeaderRow">First row is a repeating header</label></div><p class="small mt-3 mb-0">Select cells with Jodit's table popup to merge/split or insert/delete rows and columns. Drag its column separators to resize.</p>`,data=>{
      table.style.width=this.number(data.tableWidth,10,100,100)+'%';table.style.borderCollapse='collapse';table.style.marginLeft=data.tableAlign==='center'||data.tableAlign==='right'?'auto':'0';table.style.marginRight=data.tableAlign==='center'?'auto':'0';
      const cells=data.whole?[...table.querySelectorAll('td,th')]:[cell].filter(Boolean);
      cells.forEach(c=>Object.assign(c.style,{padding:this.number(data.padding,0,80,6)+'px',border:`${this.number(data.border,0,10,1)}px solid ${data.color}`,backgroundColor:data.background,textAlign:['left','center','right'].includes(data.align)?data.align:'left',verticalAlign:['top','middle','bottom'].includes(data.vertical)?data.vertical:'top'}));
      if(cell)cell.style.width=Number(data.cellWidth)>0?this.number(data.cellWidth,1,100,0)+'%':'';
      if(data.header&&!table.tHead&&table.rows.length){const head=table.createTHead();head.append(table.rows[0]);}
      else if(!data.header&&table.tHead){const body=table.tBodies[0]||table.createTBody();[...table.tHead.rows].reverse().forEach(row=>body.prepend(row));table.tHead.remove();}
      this.changed();
    },{wide:true});
  }

  anchorDialog() { this.S.dialog('Named anchor',this.field('Anchor name','name','text','','required pattern="[A-Za-z][A-Za-z0-9_-]{0,99}"')+this.field('Visible text','text','text','Anchor'),data=>this.insert(`<a id="${this.escape(data.name)}">${this.escape(data.text||data.name)}</a>`)); }

  insertTOC() {
    const headings=[...this.editor.editor.querySelectorAll('h1,h2,h3,h4,h5,h6')].filter(h=>!h.closest('.doc-toc'));
    if(!headings.length)throw new Error('Add headings before inserting a table of contents.');
    this.editor.editor.querySelectorAll('.doc-toc').forEach(el=>el.remove());
    const links=headings.map((h,index)=>{if(!h.id)h.id='heading-'+Date.now()+'-'+index;return `<li style="margin-left:${(Number(h.tagName[1])-1)*12}px"><a href="#${this.escape(h.id)}">${this.escape(h.textContent)}</a></li>`;}).join('');
    this.insert(`<nav class="doc-toc" aria-label="Table of contents"><h2>Contents</h2><ul style="list-style:none;padding-left:0">${links}</ul></nav><p><br></p>`);
  }

  codeDialog(type) {
    const qr=type==='qr';
    this.S.dialog(qr?'Insert QR code':'Insert barcode',this.field(qr?'QR text or URL':'Barcode value','value','text','','required maxlength="1000"')+(qr?'':`<label class="form-label" for="docBarcodeFormat">Format</label><select id="docBarcodeFormat" class="form-select mb-3" name="format"><option>CODE128</option><option>CODE39</option><option>EAN13</option><option>EAN8</option><option>UPC</option><option>ITF</option></select>`)+this.field(qr?'Size (px)':'Height (px)','size','number',qr?180:80,'min="40" max="800"'),async data=>{
      let url;
      if(qr){await this.S.loadScript('https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js','QRCode');const box=document.createElement('div');new QRCode(box,{text:data.value,width:Number(data.size),height:Number(data.size),correctLevel:QRCode.CorrectLevel.M});const canvas=box.querySelector('canvas');if(!canvas)throw new Error('QR code canvas could not be generated.');url=canvas.toDataURL('image/png');}
      else{await this.S.loadScript('https://cdn.jsdelivr.net/npm/jsbarcode@3.12.3/dist/JsBarcode.all.min.js','JsBarcode');const canvas=document.createElement('canvas');JsBarcode(canvas,data.value,{format:data.format,height:this.number(data.size,40,800,80),displayValue:true,margin:12});url=canvas.toDataURL('image/png');}
      this.insert(`<img src="${url}" alt="${this.escape(qr?'QR code':'Barcode')}: ${this.escape(data.value)}" style="max-width:100%">`);
    },{submit:'Insert'});
  }

  signatureDialog() {
    let pad, drawing=false, last=null;
    this.S.dialog('Insert visual signature','<p class="small">Draw below or type your signature. This is a visual mark, with no cryptographic signing.</p><canvas id="docSignatureCanvas" width="700" height="180" style="width:100%;height:180px;border:1px solid #aab3c0;background:#fff;touch-action:none" aria-label="Draw a signature"></canvas><button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="docSignatureClear">Clear drawing</button>'+this.field('Or type a signature','typed','text',''),data=>{
      if(data.typed)this.insert(`<p style="font-family:Georgia,serif;font-size:26pt;font-style:italic">${this.escape(data.typed)}</p>`);
      else if(pad?.dataset.drawn)this.insert(`<img src="${pad.toDataURL('image/png')}" alt="Visual handwritten signature" style="width:350px;max-width:100%">`);
      else throw new Error('Draw or type a signature first.');
    },{submit:'Insert signature',onOpen:modal=>{
      pad=modal.querySelector('#docSignatureCanvas');const ctx=pad.getContext('2d');ctx.strokeStyle='#111';ctx.lineWidth=2.5;ctx.lineCap='round';ctx.lineJoin='round';
      const point=e=>{const r=pad.getBoundingClientRect();return{x:(e.clientX-r.left)*pad.width/r.width,y:(e.clientY-r.top)*pad.height/r.height};};
      pad.addEventListener('pointerdown',e=>{e.preventDefault();drawing=true;last=point(e);pad.setPointerCapture(e.pointerId);});
      pad.addEventListener('pointermove',e=>{if(!drawing)return;const p=point(e);ctx.beginPath();ctx.moveTo(last.x,last.y);ctx.lineTo(p.x,p.y);ctx.stroke();last=p;pad.dataset.drawn='1';});
      const end=()=>{drawing=false;last=null;};pad.addEventListener('pointerup',end);pad.addEventListener('pointercancel',end);
      modal.querySelector('#docSignatureClear').addEventListener('click',()=>{ctx.clearRect(0,0,pad.width,pad.height);delete pad.dataset.drawn;});
    }});
  }

  marginContent(text) {
    const value=String(text||'').replace(/\{\{title\}\}/g,()=>String(this.state.metadata.title||'')).replace(/\{\{date\}\}/g,()=>new Date().toLocaleDateString());
    return value.split(/(\{\{page\}\}|\{\{pages\}\})/g).filter(Boolean).map(part=>part==='{{page}}'?'counter(page)':part==='{{pages}}'?'counter(pages)':JSON.stringify(part).replace(/</g,'\\3c ')).join(' ')||'none';
  }

  pageRules(header,footer) {
    return ['left','center','right'].map(pos=>`@top-${pos}{content:${this.marginContent(header[pos])};font:9pt Arial;color:#444;white-space:pre-wrap}@bottom-${pos}{content:${this.marginContent(footer[pos])};font:9pt Arial;color:#444;white-space:pre-wrap}`).join('');
  }

  buildHTML(includeState=false) {
    const s=this.state,dim=this.dimensions(),meta=s.metadata;
    const css=`@page{size:${dim.width}mm ${dim.height}mm;margin:${s.margins.top}mm ${s.margins.right}mm ${s.margins.bottom}mm ${s.margins.left}mm;${this.pageRules(s.header,s.footer)}}${s.differentFirst?'@page:first{'+this.pageRules(s.firstHeader,s.firstFooter)+'}':''}*{box-sizing:border-box}html{background:white}body{font-family:"${s.font}";font-size:${s.fontSize}pt;line-height:1.5;color:#111;direction:${s.direction};background:${s.background};margin:0;column-count:${s.columns};column-gap:${s.columnGap}mm;${s.borderWidth?'border:'+s.borderWidth+'pt solid '+s.borderColor+';':''}}img{max-width:100%;height:auto}table{border-collapse:collapse;max-width:100%}td,th{border:1px solid #adb5bd;padding:6px}thead{display:table-header-group}tr,img,figure{break-inside:avoid}h1,h2,h3,h4,h5,h6{break-after:avoid}p{orphans:2;widows:2}.doc-pagebreak{break-after:page;page-break-after:always;height:0}.doc-toc{column-span:all}.doc-check{margin-right:.4em}figure{margin:1em 0}figcaption{font-size:.85em;color:#555;text-align:center}a{color:#2459ab}.doc-screen-note{display:none}@media screen{html{background:#e1e5eb;padding:20px}body{box-shadow:0 3px 18px #0002;width:${dim.width}mm;min-height:${dim.height}mm;padding:${s.margins.top}mm ${s.margins.right}mm ${s.margins.bottom}mm ${s.margins.left}mm;margin:auto}.doc-screen-note{display:block;font:12px Arial;column-span:all;background:#edf2ff;border:1px solid #bdcfff;padding:8px;margin-bottom:20px}.doc-pagebreak{border-top:2px dashed #aab2c0;margin:25px 0;height:5px}}@media print{html{padding:0}body{-webkit-print-color-adjust:exact;print-color-adjust:exact}.doc-screen-note{display:none}}`;
    const state=includeState?`<script id="pdfstudioDocumentState" type="application/json">${JSON.stringify(s).replace(/</g,'\\u003c')}<\/script>`:'';
    const content=this.clean(this.editor?this.editor.value:this.html);
    return `<!doctype html><html lang="${window.StudioLanguage?.language||'it'}" dir="${s.direction}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="Content-Security-Policy" content="default-src 'none'; img-src data:; style-src 'unsafe-inline'; font-src 'none'; base-uri 'none'; form-action 'none'"><title>${this.escape(meta.title)}</title><meta name="author" content="${this.escape(meta.author)}"><meta name="description" content="${this.escape(meta.subject)}"><meta name="keywords" content="${this.escape(meta.keywords)}"><style>${css}</style></head><body><aside class="doc-screen-note">${this.escape(window.StudioLanguage?.text(`Paper preview: ${dim.width} × ${dim.height} mm. Use Print for final pagination; the browser's print dialog can save PDF. Enable background graphics, use 100% scale and turn off browser headers/footers.`)||'')}</aside>${content}${state}</body></html>`;
  }

  async waitFrame(frame) {
    await new Promise((resolve,reject)=>{
      let timer;const done=()=>{clearTimeout(timer);resolve();};frame.addEventListener('load',done,{once:true});timer=setTimeout(()=>reject(new Error('Print preview did not finish loading.')),20000);
      frame.srcdoc=this.buildHTML();
    });
    const doc=frame.contentDocument;
    if(doc.fonts)await doc.fonts.ready;
    await Promise.all([...doc.images].map(img=>img.complete?Promise.resolve():new Promise(resolve=>{img.onload=resolve;img.onerror=resolve;})));
  }

  preview() {
    let frame;
    this.S.dialog('Document print preview','<p class="small">The white sheet shows the selected paper width. Choose Print to see paginated output and save a PDF. Chrome/Chromium provide the best support for page X of Y and margin headers.</p><iframe id="docPreviewFrame" class="doc-preview-frame" sandbox="allow-same-origin allow-modals" title="Document paper preview"></iframe>',()=>{if(!frame?.contentWindow)throw new Error('Preview is still loading.');frame.contentWindow.focus();frame.contentWindow.print();},{submit:'Print / Save PDF',wide:true,onOpen:modal=>{frame=modal.querySelector('#docPreviewFrame');this.waitFrame(frame).catch(error=>this.S.error(error));}});
  }

  async browserPrint() {
    const frame=document.createElement('iframe');frame.title='Document print';frame.setAttribute('sandbox','allow-same-origin allow-modals');Object.assign(frame.style,{position:'fixed',right:'0',bottom:'0',width:'1px',height:'1px',border:'0'});document.body.append(frame);
    try {await this.waitFrame(frame);frame.contentWindow.addEventListener('afterprint',()=>frame.remove(),{once:true});frame.contentWindow.focus();frame.contentWindow.print();setTimeout(()=>frame.remove(),300000);}
    catch(error){frame.remove();throw error;}
  }

  async exportPDF() {
    const engine=document.getElementById('docEngine').value;
    if(engine==='browser')return this.browserPrint();
    await this.S.busy('Rendering document on the server',async job=>{
      const s=this.state,html=this.buildHTML(),dim=this.dimensions();
      const result=await this.S.api('html_pdf',[],{engine,html,pageSize:s.size,landscape:s.landscape,margins:s.margins,widthMm:dim.width,heightMm:dim.height,metadata:s.metadata,header:s.header,footer:s.footer,firstHeader:s.firstHeader,firstFooter:s.firstFooter,differentFirst:s.differentFirst},job);
      if(!result.blob)throw new Error('The server did not return a rendered PDF.');this.S.download(result.blob,this.fileName()+'.pdf','application/pdf');
    });
  }
}

class PDFTools {
  constructor(S) {
    this.S = S;
    this.e = value => S.escape(String(value ?? ''));
    this.dialogCounter = 0;
    this.loadingTasks = new WeakMap();
    const defs = [
      ['split', 'Split PDF', 'Organize', 'scissors', 'browser'],
      ['pdf-images', 'PDF to images', 'Convert', 'file-image', 'browser'],
      ['text', 'Extract text', 'Convert', 'align-left', 'browser'],
      ['extract-images', 'Extract embedded images', 'Convert', 'images', 'pdfimages'],
      ['office-convert', 'Office to PDF', 'Convert', 'file-word', 'libreoffice'],
      ['ocr', 'Recognize text (OCR)', 'OCR', 'eye', 'browser'],
      ['watermark', 'Watermark', 'Annotate', 'droplet', 'browser'],
      ['numbering', 'Headers, footers & page numbers', 'Annotate', 'list-ol', 'browser'],
      ['bates', 'Bates numbering', 'Advanced', 'hashtag', 'browser'],
      ['crop', 'Crop pages', 'Organize', 'crop-simple', 'browser'],
      ['resize', 'Resize pages', 'Organize', 'expand', 'browser'],
      ['forms', 'Fill PDF forms', 'Forms', 'list-check', 'browser'],
      ['create-field', 'Create form field', 'Forms', 'square-plus', 'browser'],
      ['flatten-forms', 'Flatten form fields', 'Forms', 'layer-group', 'browser'],
      ['metadata', 'Edit metadata / privacy clean', 'Advanced', 'tags', 'browser'],
      ['inspect', 'Document inspector', 'Advanced', 'circle-info', 'browser'],
      ['compare', 'Compare two PDFs', 'Advanced', 'code-compare', 'browser'],
      ['nup', 'N-up / contact sheet', 'Advanced', 'table-cells-large', 'browser'],
      ['booklet', 'Booklet imposition', 'Advanced', 'book-open', 'browser'],
      ['compress', 'Compress PDF', 'Optimize', 'compress', 'qpdf|gs'],
      ['repair', 'Repair PDF structure', 'Optimize', 'screwdriver-wrench', 'qpdf'],
      ['validate', 'Validate PDF', 'Advanced', 'check-double', 'qpdf'],
      ['linearize', 'Fast Web View', 'Optimize', 'bolt', 'qpdf'],
      ['protect', 'Protect PDF', 'Secure', 'lock', 'qpdf'],
      ['decrypt', 'Remove PDF password', 'Secure', 'unlock', 'qpdf']
    ];
    this.definitions = defs.map(([id, title, group, icon, requirements]) => ({id, title, group, icon: 'fa-' + icon, requirements, description: requirements === 'browser' ? 'Runs locally in your browser' : 'Server processing with ' + requirements.replace('|', ' or ')}));
  }
  async run(id) {
    const methods = {
      'pdf-images': 'images', 'extract-images': 'extractImages', 'office-convert': 'office',
      'create-field': 'createField', 'flatten-forms': 'flattenForms'
    };
    const method = methods[id] || id;
    if (typeof this[method] !== 'function') throw new Error('Unknown PDF utility: ' + id);
    return this[method]();
  }
  has(tool) { return Boolean(this.S.server?.tools?.[tool]?.available); }
  field(label, name, value = '', type = 'text', extra = '') {
    if (type === 'number' && !/\bstep\s*=/.test(extra)) extra += ' step="any"';
    return `<label class="form-label d-block mb-3">${this.e(label)}<input class="form-control mt-1" name="${this.e(name)}" type="${this.e(type)}" value="${this.e(value)}" ${extra}></label>`;
  }
  select(label, name, options, value = '') {
    return `<label class="form-label d-block mb-3">${this.e(label)}<select class="form-select mt-1" name="${this.e(name)}">${options.map(x => {
      const [v, t] = Array.isArray(x) ? x : [x, x];
      return `<option value="${this.e(v)}"${String(v) === String(value) ? ' selected' : ''}>${this.e(t)}</option>`;
    }).join('')}</select></label>`;
  }
  check(label, name, checked = false) {
    return `<label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="${this.e(name)}"${checked ? ' checked' : ''}><span class="form-check-label">${this.e(label)}</span></label>`;
  }
  range(value = 'all') { if(value==='all')value=({it:'tutte',de:'alle'})[window.StudioLanguage?.language]||'all';return this.field('Pages: all, odd, even, or ranges such as 1-4,7', 'pages', value); }
  selectionRange() {
    const indexes = this.S.getSelected();
    return indexes.map(i => i + 1).join(',');
  }
  indexes(value) { return this.S.range(value || 'all', this.S.pages.length); }
  name(suffix, extension = 'pdf') {
    const source = this.S.sources.get(this.S.pages[this.S.current]?.sourceId);
    return (source?.name || 'document.pdf').replace(/\.[^.]+$/, '') + '-' + suffix + '.' + extension;
  }
  async exported(job) {
    this.S.requireDocument();
    job?.check();
    return this.S.exportBytes({}, job);
  }
  async editable(job) { return PDFLib.PDFDocument.load(await this.exported(job), {updateMetadata: false}); }
  async rendered(job) {
    const bytes = await this.exported(job);
    return this.loadPDF(bytes);
  }
  async loadPDF(bytes) {
    const options = this.S.pdfOptions ? this.S.pdfOptions(bytes) : {data: bytes.slice(), cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@6.4.299/cmaps/', cMapPacked: true, standardFontDataUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@6.4.299/standard_fonts/', wasmUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@6.4.299/wasm/', isEvalSupported: false};
    const task = pdfjsLib.getDocument(options);
    try {
      const doc = await task.promise;
      this.loadingTasks.set(doc, task);
      return doc;
    } catch (error) { await task.destroy().catch(() => {}); throw error; }
  }
  async disposePDF(doc) {
    const task = this.loadingTasks.get(doc);
    if (task) { this.loadingTasks.delete(doc); await task.destroy(); }
    else await doc.cleanup();
  }
  async mutate(label, callback) {
    return this.S.busy(label, async job => {
      const removeMetadata = Boolean(this.S.metadata?.removeMetadata);
      const pdf = await this.editable(job);
      await callback(pdf, job);
      job.check();
      const bytes = await pdf.save();
      job.check();
      await this.S.replaceBytes(bytes, this.name(label.toLowerCase().replace(/[^a-z0-9]+/g, '-')));
      if (removeMetadata) { this.S.metadata.removeMetadata = true; this.S.commit(); }
      this.S.notify(label + ' completed.');
    });
  }
  color(value) {
    const match = /^#([a-f\d]{6})$/i.exec(value || '#000000');
    if (!match) throw new Error('Choose a six-digit hexadecimal color.');
    return PDFLib.rgb(parseInt(match[1].slice(0, 2), 16) / 255, parseInt(match[1].slice(2, 4), 16) / 255, parseInt(match[1].slice(4), 16) / 255);
  }
  fontOptions() { return [['Helvetica', 'Helvetica / Arial'], ['TimesRoman', 'Times Roman'], ['Courier', 'Courier']]; }
  async font(pdf, family) { return pdf.embedFont(PDFLib.StandardFonts[family] || PDFLib.StandardFonts.Helvetica); }
  safeText(font, text) {
    try { font.encodeText(text); } catch (_) { throw new Error('This font does not contain one or more characters. Use Latin text, or add text in the PDF overlay editor where browser fonts can be rasterized.'); }
    return text;
  }
  async renderCanvas(doc, index, dpi, transparent = false, job) {
    job?.check();
    const page = await doc.getPage(index + 1);
    const viewport = page.getViewport({scale: dpi / 72});
    if (viewport.width * viewport.height > 40000000) throw new Error('The requested page exceeds 40 million pixels. Reduce the DPI.');
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.ceil(viewport.width));
    canvas.height = Math.max(1, Math.ceil(viewport.height));
    const task = page.render({canvasContext: canvas.getContext('2d'), viewport, background: transparent ? 'rgba(0,0,0,0)' : '#ffffff'});
    const cancel = () => task.cancel();
    job?.signal.addEventListener('abort', cancel, {once: true});
    try { await task.promise; } finally { job?.signal.removeEventListener('abort', cancel); page.cleanup(); }
    return canvas;
  }
  blob(canvas, type = 'image/png', quality = 0.9) {
    return new Promise((resolve, reject) => canvas.toBlob(b => b ? resolve(b) : reject(new Error('The browser could not encode this image.')), type, quality));
  }
  async saved(pdf, job) {
    job?.check();
    const bytes = await pdf.save();
    job?.check();
    return bytes;
  }
  async zipBlob(zip, job) {
    const blob = await zip.generateAsync({type: 'blob', compression: 'STORE'}, metadata => { job?.check(); job?.progress(metadata.percent, 100, 'Writing ZIP archive'); });
    job?.check();
    return blob;
  }
  abortable(promise, job, onLateValue = null) {
    if (!job) return promise;
    return new Promise((resolve, reject) => {
      let settled = false;
      const abort = () => { if (settled) return; settled = true; reject(new DOMException('Cancelled', 'AbortError')); };
      job.signal.addEventListener('abort', abort, {once: true});
      if (job.signal.aborted) abort();
      Promise.resolve(promise).then(value => {
        job.signal.removeEventListener('abort', abort);
        if (settled) { if (onLateValue) Promise.resolve(onLateValue(value)).catch(() => {}); return; }
        settled = true; resolve(value);
      }, error => {
        job.signal.removeEventListener('abort', abort);
        if (!settled) { settled = true; reject(error instanceof Error ? error : new Error(String(error))); }
      });
    });
  }
  async createOCRWorker(language, options, job) {
    const NativeWorker = window.Worker;
    let nativeWorker = null, launcherURL = null, rejectInitialization;
    const failed = new Promise((resolve, reject) => { rejectInitialization = reject; });
    const stop = () => { nativeWorker?.terminate(); };
    const capture = new Proxy(NativeWorker, {construct(target, args) {
      const worker = Reflect.construct(target, args);
      nativeWorker = worker;
      if (typeof args[0] === 'string' && args[0].startsWith('blob:')) launcherURL = args[0];
      return worker;
    }});
    let pending;
    // Tesseract 7 creates its native Worker synchronously, but exposes it only
    // after initialization. Capture that constructor call so cancellation and
    // failed language downloads can terminate initialization too. No await or
    // event-loop yield occurs while the constructor is temporarily intercepted.
    window.Worker = capture;
    try {
      pending = Tesseract.createWorker(language, 1, {...options, errorHandler: error => {
        rejectInitialization(error instanceof Error ? error : new Error(String(error)));
      }});
    } finally { window.Worker = NativeWorker; }
    job.signal.addEventListener('abort', stop, {once: true});
    try {
      return await this.abortable(Promise.race([pending, failed]), job, lateWorker => lateWorker.terminate());
    } catch (error) { stop(); throw error; }
    finally {
      job.signal.removeEventListener('abort', stop);
      if (launcherURL) URL.revokeObjectURL(launcherURL);
    }
  }
  async server(action, options, job, file = null) {
    const bytes = file ? null : await this.exported(job);
    const input = file || new File([bytes], 'document.pdf', {type: 'application/pdf'});
    return this.S.api(action, [input], options, job);
  }
  async downloadServer(action, options = {}, label = action, file = null, apply = false) {
    return this.S.busy(label, async job => {
      const result = await this.server(action, options, job, file);
      job.check();
      if (apply && result.blob.type.includes('pdf')) await this.S.replaceBytes(new Uint8Array(await result.blob.arrayBuffer()), result.name || this.name(action));
      else this.S.download(result.blob, result.name || this.name(action), result.blob.type);
      this.S.notify(label + ' completed.');
    });
  }
  async split() {
    this.S.requireDocument();
    const S = this.S;
    const html = this.select('Split method', 'mode', [['every', 'Every page'], ['n', 'Every N pages'], ['ranges', 'Custom groups'], ['odd-even', 'Odd pages / even pages'], ['before', 'Before current page'], ['after', 'After current page'], ['size', 'Approximate maximum size'], ['bookmarks', 'Top-level bookmarks']]) +
      this.field('N pages / maximum MiB (depending on method)', 'amount', 2, 'number', 'min="1" step="1"') +
      this.field('Custom groups, separated by semicolons', 'groups', '1-3;4-6') +
      '<p class="small text-muted">Custom groups accept the same page ranges as the organizer. A ZIP is downloaded for multiple outputs. Maximum size is measured after saving each group; a single page may exceed your target.</p>';
    S.dialog('Split PDF', html, async values => {
      await S.busy('Splitting PDF', async job => {
        const pdf = await this.editable(job);
        const total = pdf.getPageCount();
        let groups = [];
        const n = Math.max(1, Math.floor(Number(values.amount)));
        if (!Number.isFinite(n)) throw new Error('Enter a positive number.');
        if (values.mode === 'every' || values.mode === 'n') {
          const step = values.mode === 'every' ? 1 : n;
          for (let i = 0; i < total; i += step) groups.push(Array.from({length: Math.min(step, total - i)}, (_, j) => i + j));
        } else if (values.mode === 'ranges') {
          groups = values.groups.split(';').filter(x => x.trim()).map(x => this.indexes(x));
        } else if (values.mode === 'odd-even') {
          groups = [Array.from({length: total}, (_, i) => i).filter(i => i % 2 === 0), Array.from({length: total}, (_, i) => i).filter(i => i % 2 === 1)].filter(x => x.length);
        } else if (values.mode === 'before' || values.mode === 'after') {
          const pivot = S.current + (values.mode === 'after' ? 1 : 0);
          groups = [Array.from({length: pivot}, (_, i) => i), Array.from({length: total - pivot}, (_, i) => pivot + i)].filter(x => x.length);
        } else if (values.mode === 'bookmarks') {
          if (S.sources.size !== 1 || S.pages.some((p, i) => p.index !== i)) throw new Error('Bookmark split requires one PDF in its original page order.');
          const source = S.sources.values().next().value.pdfjs;
          const outline = await source.getOutline();
          if (!outline?.length) throw new Error('No top-level bookmarks were found.');
          const starts = [0];
          for (const item of outline) {
            let dest = item.dest;
            if (typeof dest === 'string') dest = await source.getDestination(dest);
            if (!Array.isArray(dest) || dest[0] == null) continue;
            starts.push(typeof dest[0] === 'number' ? dest[0] : await source.getPageIndex(dest[0]));
          }
          const unique = [...new Set(starts)].filter(i => i >= 0 && i < total).sort((a, b) => a - b);
          groups = unique.map((i, k) => Array.from({length: (unique[k + 1] ?? total) - i}, (_, j) => i + j));
        } else if (values.mode === 'size') {
          const limit = n * 1024 * 1024;
          let current = [];
          for (let i = 0; i < total; i++) {
            job.check();
            const candidate = [...current, i];
            const test = await PDFLib.PDFDocument.create();
            (await test.copyPages(pdf, candidate)).forEach(p => test.addPage(p));
            const bytes = await test.save();
            if (bytes.length > limit && current.length) { groups.push(current); current = [i]; } else current = candidate;
            job.progress(i + 1, total, 'Measuring page groups');
          }
          if (current.length) groups.push(current);
        }
        if (!groups.length) throw new Error('No output pages were selected.');
        const zip = new JSZip();
        for (let i = 0; i < groups.length; i++) {
          job.check();
          const out = await PDFLib.PDFDocument.create();
          (await out.copyPages(pdf, groups[i])).forEach(p => out.addPage(p));
          const bytes = await this.saved(out, job);
          if (groups.length === 1) S.download(bytes, this.name('split'), 'application/pdf');
          else zip.file(`part-${String(i + 1).padStart(3, '0')}.pdf`, bytes);
          job.progress(i + 1, groups.length, 'Writing PDF groups');
        }
        if (groups.length > 1) S.download(await this.zipBlob(zip, job), this.name('split', 'zip'), 'application/zip');
      });
    });
  }
  async images() {
    this.S.requireDocument();
    const html = this.range() + this.select('Image format', 'format', [['png', 'PNG'], ['jpeg', 'JPEG'], ['webp', 'WebP']]) +
      this.field('Resolution (DPI)', 'dpi', 144, 'number', 'min="36" max="600"') +
      this.field('JPEG / WebP quality (1–100)', 'quality', 90, 'number', 'min="1" max="100"') +
      this.check('Grayscale', 'gray') + this.check('Transparent background (PNG / WebP)', 'transparent');
    this.S.dialog('Export PDF pages as images', html, async values => {
      const indexes = this.indexes(values.pages);
      const dpi = this.number(values.dpi, 36, 600, 'DPI');
      const quality = this.number(values.quality, 1, 100, 'Quality') / 100;
      await this.S.busy('Rendering page images', async job => {
        const doc = await this.rendered(job);
        try {
          const zip = new JSZip();
          for (let k = 0; k < indexes.length; k++) {
            const canvas = await this.renderCanvas(doc, indexes[k], dpi, values.transparent && values.format !== 'jpeg', job);
            if (values.gray) {
              const ctx = canvas.getContext('2d');
              const pixels = ctx.getImageData(0, 0, canvas.width, canvas.height);
              for (let i = 0; i < pixels.data.length; i += 4) pixels.data[i] = pixels.data[i + 1] = pixels.data[i + 2] = Math.round(pixels.data[i] * 0.2126 + pixels.data[i + 1] * 0.7152 + pixels.data[i + 2] * 0.0722);
              ctx.putImageData(pixels, 0, 0);
            }
            const blob = await this.blob(canvas, 'image/' + values.format, quality);
            job.check();
            const ext = blob.type.split('/')[1] === 'jpeg' ? 'jpg' : blob.type.split('/')[1];
            const name = `page-${String(indexes[k] + 1).padStart(4, '0')}.${ext}`;
            if (indexes.length === 1) this.S.download(blob, name, blob.type); else zip.file(name, blob);
            canvas.width = canvas.height = 0;
            job.progress(k + 1, indexes.length, 'Exporting images');
          }
          if (indexes.length > 1) this.S.download(await this.zipBlob(zip, job), this.name('images', 'zip'), 'application/zip');
        } finally { await this.disposePDF(doc); }
      });
    });
  }
  number(value, min, max, label) {
    const n = Number(value);
    if (!Number.isFinite(n) || n < min || n > max) throw new Error(`${label} must be between ${min} and ${max}.`);
    return n;
  }
  async extractPageText(doc, index, layout = false) {
    const page = await doc.getPage(index + 1);
    const content = await page.getTextContent();
    let result = '';
    if (!layout) {
      result = content.items.filter(x => typeof x.str === 'string').map(x => x.str + (x.hasEOL ? '\n' : ' ')).join('').trim();
    } else {
      const lines = [];
      for (const item of content.items.filter(x => typeof x.str === 'string')) {
        const y = item.transform[5];
        let line = lines.find(l => Math.abs(l.y - y) < Math.max(2, item.height * 0.25));
        if (!line) { line = {y, items: []}; lines.push(line); }
        line.items.push(item);
      }
      result = lines.sort((a, b) => b.y - a.y).map(line => {
        const items = line.items.sort((a, b) => a.transform[4] - b.transform[4]);
        let text = '', lastRight = items[0]?.transform[4] || 0;
        for (const item of items) {
          const charWidth = Math.max(2, item.width / Math.max(1, item.str.length));
          const spaces = Math.min(80, Math.max(text ? 1 : 0, Math.round((item.transform[4] - lastRight) / charWidth)));
          text += ' '.repeat(spaces) + item.str;
          lastRight = item.transform[4] + item.width;
        }
        return text;
      }).join('\n');
    }
    page.cleanup();
    return result;
  }
  textViewer(title, text) {
    const key = 'pdf-tools-text-' + (++this.dialogCounter);
    const html = `<div class="d-flex gap-2 mb-2"><input class="form-control" id="${key}-search" placeholder="Find in extracted text" aria-label="Find text"><button class="btn btn-outline-secondary" type="button" id="${key}-find">Find</button></div><textarea class="form-control font-monospace" id="${key}" rows="19" spellcheck="false" aria-label="Extracted text">${this.e(text)}</textarea><div class="d-flex flex-wrap gap-2 mt-3"><button class="btn btn-outline-primary" type="button" id="${key}-copy">Copy</button><button class="btn btn-outline-primary" type="button" id="${key}-txt">Download TXT</button><button class="btn btn-outline-primary" type="button" id="${key}-html">Download HTML</button><span class="small align-self-center" id="${key}-status"></span></div>`;
    const el = this.S.dialog(title, html, () => {}, {wide: true, submit: 'Close'});
    const area = el.querySelector('#' + key);
    el.querySelector('#' + key + '-copy').onclick = async () => {
      try { await navigator.clipboard.writeText(area.value); this.S.notify('Text copied.'); } catch (_) { area.focus(); area.select(); this.S.notify('Text selected. Press Ctrl+C or Command+C to copy.'); }
    };
    el.querySelector('#' + key + '-txt').onclick = () => this.S.download(area.value, this.name('text', 'txt'), 'text/plain;charset=utf-8');
    el.querySelector('#' + key + '-html').onclick = () => this.S.download('<!doctype html><meta charset="utf-8"><title>Extracted PDF text</title><pre>' + this.e(area.value) + '</pre>', this.name('text', 'html'), 'text/html;charset=utf-8');
    el.querySelector('#' + key + '-find').onclick = () => {
      const term = el.querySelector('#' + key + '-search').value;
      if (!term) return;
      const found = area.value.toLocaleLowerCase().indexOf(term.toLocaleLowerCase(), area.selectionEnd);
      const position = found < 0 ? area.value.toLocaleLowerCase().indexOf(term.toLocaleLowerCase()) : found;
      el.querySelector('#' + key + '-status').textContent = position < 0 ? 'No matches' : 'Match found';
      if (position >= 0) { area.focus(); area.setSelectionRange(position, position + term.length); }
    };
  }
  async text() {
    this.S.requireDocument();
    const html = this.range() + this.select('Processing engine', 'engine', [['browser', 'Local browser / PDF.js'], ...(this.has('pdftotext') ? [['server', 'Server / Poppler']] : [])]) + this.check('Preserve approximate line layout', 'layout', true) + this.check('Include page separators', 'separators', true);
    this.S.dialog('Extract embedded text', html, async values => {
      let result = '';
      await this.S.busy('Extracting text', async job => {
        if (values.engine === 'server') {
          const bytes = await this.S.exportBytes({selection: this.indexes(values.pages)}, job);
          const response = await this.server('extract_text', {layout: values.layout}, job, new File([bytes], 'selection.pdf', {type: 'application/pdf'}));
          result = await response.blob.text();
        } else {
          const doc = await this.rendered(job);
          try {
            const indexes = this.indexes(values.pages);
            const chunks = [];
            for (let i = 0; i < indexes.length; i++) {
              job.check();
              chunks.push((values.separators ? `── Page ${indexes[i] + 1} ──\n` : '') + await this.extractPageText(doc, indexes[i], values.layout));
              job.progress(i + 1, indexes.length, 'Extracting page text');
            }
            result = chunks.join('\n\n');
          } finally { await this.disposePDF(doc); }
        }
      });
      setTimeout(() => this.textViewer('Extracted PDF text', result), 50);
    });
  }
  async ocr() {
    this.S.requireDocument();
    const server = this.has('tesseract') && this.has('pdftoppm');
    const html = this.range() + this.select('Processing engine', 'engine', [['browser', 'Local browser / Tesseract.js'], ...(server ? [['server', 'Server / Tesseract + Poppler']] : [])]) +
      this.field('Language codes, joined by + (eng, ita, tha, deu, fra…)', 'language', 'eng') +
      this.field('Resolution (DPI)', 'dpi', 180, 'number', 'min="72" max="400"') +
      this.select('Output', 'output', [['text', 'Text editor / TXT'], ...(server ? [['pdf', 'Searchable PDF (server engine)']] : [])]) +
      '<p class="small text-muted">Browser OCR renders selected pages to images before recognition. Language data is downloaded when first used. Existing PDF text is unchanged. Searchable PDF output uses the server engine.</p>';
    this.S.dialog('Recognize scanned pages', html, async values => {
      if (!/^[a-z_]{3,20}(\+[a-z_]{3,20}){0,7}$/.test(values.language)) throw new Error('Enter valid Tesseract language codes, for example eng+ita.');
      const indexes = this.indexes(values.pages);
      const dpi = this.number(values.dpi, 72, 400, 'DPI');
      let result = '';
      await this.S.busy('Recognizing page text', async job => {
        if (values.engine === 'server' || values.output === 'pdf') {
          if (!server) throw new Error('Searchable OCR PDF requires server Tesseract and Poppler.');
          const bytes = await this.S.exportBytes({selection: indexes}, job);
          const response = await this.server('ocr', {language: values.language, dpi, output: values.output}, job, new File([bytes], 'selection.pdf', {type: 'application/pdf'}));
          if (values.output === 'pdf') { this.S.download(response.blob, response.name || this.name('ocr'), 'application/pdf'); return; }
          result = await response.blob.text();
        } else {
          await this.abortable(this.S.loadScript('https://cdn.jsdelivr.net/npm/tesseract.js@7.0.0/dist/tesseract.min.js', 'Tesseract'), job);
          let worker = null, doc = null, pageIndex = 0;
          const abort = () => { if (worker) worker.terminate().catch(() => {}); };
          job.signal.addEventListener('abort', abort, {once: true});
          try {
            worker = await this.createOCRWorker(values.language, {
              workerPath: 'https://cdn.jsdelivr.net/npm/tesseract.js@7.0.0/dist/worker.min.js',
              corePath: 'https://cdn.jsdelivr.net/npm/tesseract.js-core@7.0.0',
              langPath: 'https://tessdata.projectnaptha.com/4.0.0',
              logger: event => {
                if (job.signal.aborted) return;
                if (event.status === 'recognizing text' && Number.isFinite(event.progress)) job.progress(pageIndex + event.progress, indexes.length, `Recognizing page ${indexes[pageIndex] + 1}`);
                else if (event.status) job.progress(0, 0, event.status);
              }
            }, job);
            job.check();
            doc = await this.rendered(job);
            const chunks = [];
            for (pageIndex = 0; pageIndex < indexes.length; pageIndex++) {
              job.check();
              const canvas = await this.renderCanvas(doc, indexes[pageIndex], dpi, false, job);
              const response = await this.abortable(worker.recognize(canvas), job);
              canvas.width = canvas.height = 0;
              chunks.push(`── Page ${indexes[pageIndex] + 1} ──\n${response.data.text}`);
              job.progress(pageIndex + 1, indexes.length, 'OCR completed');
            }
            result = chunks.join('\n\n');
          } finally {
            job.signal.removeEventListener('abort', abort);
            if (worker) await worker.terminate().catch(() => {});
            if (doc) await this.disposePDF(doc);
          }
        }
      });
      if (values.output === 'text') setTimeout(() => this.textViewer('Recognized text', result), 50);
    });
  }
  visualSize(page) {
    const box = page.getCropBox();
    const rotation = ((page.getRotation().angle % 360) + 360) % 360;
    return {width: rotation % 180 ? box.height : box.width, height: rotation % 180 ? box.width : box.height, rotation, box};
  }
  visualMatrix(page) {
    const {rotation, box: b} = this.visualSize(page);
    if (rotation === 90) return [0, 1, -1, 0, b.x + b.width, b.y];
    if (rotation === 180) return [-1, 0, 0, -1, b.x + b.width, b.y + b.height];
    if (rotation === 270) return [0, -1, 1, 0, b.x, b.y + b.height];
    return [1, 0, 0, 1, b.x, b.y];
  }
  withVisualPage(page, draw) {
    page.pushOperators(PDFLib.pushGraphicsState(), PDFLib.concatTransformationMatrix(...this.visualMatrix(page)));
    try { draw(); } finally { page.pushOperators(PDFLib.popGraphicsState()); }
  }
  position(position, width, height, margin = 36) {
    const [vertical, horizontal] = position.split('-');
    return {
      x: horizontal === 'left' ? margin : horizontal === 'right' ? width - margin : width / 2,
      y: vertical === 'top' ? height - margin : vertical === 'bottom' ? margin : height / 2
    };
  }
  positionOptions() {
    return ['center-center', 'top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center', 'bottom-right'].map(x => [x, x.split('-').map(w => w[0].toUpperCase() + w.slice(1)).join(' ')]);
  }
  async watermark() {
    this.S.requireDocument();
    const key = 'watermark-' + (++this.dialogCounter);
    const html = `<div class="row"><div class="col-md-6">` + this.range() + this.select('Watermark type', 'type', [['text', 'Text'], ['image', 'Image']]) +
      this.field('Watermark text', 'text', 'CONFIDENTIAL') + this.field('Image file (PNG / JPEG)', 'image', '', 'file', 'accept="image/png,image/jpeg"') +
      this.select('Font', 'font', this.fontOptions()) + this.field('Font size / image width (PDF points)', 'size', 56, 'number', 'min="6" max="1200"') +
      this.field('Color', 'color', '#dc2626', 'color') + this.field('Opacity (0–100)', 'opacity', 25, 'number', 'min="0" max="100"') +
      this.field('Rotation (degrees)', 'rotation', 35, 'number', 'min="-360" max="360"') +
      this.select('Position', 'position', this.positionOptions()) + this.check('Tile / repeat watermark', 'tile') +
      `</div><div class="col-md-6"><p class="small text-muted">Live preview of current page</p><canvas id="${key}" class="tools-preview-canvas"></canvas></div></div>`;
    let image = null, imageBytes = null, previewBase = null, previewVersion = 0, closed = false;
    const el = this.S.dialog('Add watermark', html, async values => {
      const indexes = this.indexes(values.pages);
      const size = this.number(values.size, 6, 1200, 'Size');
      const opacity = this.number(values.opacity, 0, 100, 'Opacity') / 100;
      const angle = this.number(values.rotation, -360, 360, 'Rotation');
      const file = el.querySelector('[name="image"]').files[0];
      if (values.type === 'image' && !file) throw new Error('Choose a PNG or JPEG watermark image.');
      await this.mutate('Add watermark', async (pdf, job) => {
        const font = values.type === 'text' ? await this.font(pdf, values.font) : null;
        if (font) this.safeText(font, values.text);
        const bytes = file ? new Uint8Array(await file.arrayBuffer()) : null;
        const embedded = bytes ? (file.type === 'image/png' ? await pdf.embedPng(bytes) : await pdf.embedJpg(bytes)) : null;
        for (let k = 0; k < indexes.length; k++) {
          job.check();
          const page = pdf.getPage(indexes[k]);
          const {width, height} = this.visualSize(page);
          const w = font ? font.widthOfTextAtSize(values.text, size) : size;
          const h = font ? size * 0.75 : size * embedded.height / embedded.width;
          const points = [];
          if (values.tile) {
            for (let y = 60; y < height; y += Math.max(90, h + 70)) for (let x = 60; x < width; x += Math.max(120, w + 60)) points.push({x, y});
          } else points.push(this.position(values.position, width, height, Math.max(36, Math.min(w, h) / 2 + 12)));
          this.withVisualPage(page, () => {
            const radians = angle * Math.PI / 180;
            for (const point of points) {
              const x = point.x - Math.cos(radians) * w / 2 + Math.sin(radians) * h / 2;
              const y = point.y - Math.sin(radians) * w / 2 - Math.cos(radians) * h / 2;
              if (font) page.drawText(values.text, {x, y, size, font, color: this.color(values.color), opacity, rotate: PDFLib.degrees(angle)});
              else page.drawImage(embedded, {x, y, width: w, height: h, opacity, rotate: PDFLib.degrees(angle)});
            }
          });
          job.progress(k + 1, indexes.length, 'Adding watermark');
        }
      });
    }, {wide: true});
    const preview = el.querySelector('#' + key);
    const draw = () => {
      if (!previewBase || closed || !preview.isConnected) return;
      const values = Object.fromEntries(new FormData(el.querySelector('form')));
      const ctx = preview.getContext('2d');
      preview.width = previewBase.width; preview.height = previewBase.height;
      ctx.drawImage(previewBase, 0, 0);
      const p = this.S.pages[this.S.current];
      const scale = preview.width / (p.rotation % 180 ? p.height : p.width);
      const size = Math.max(6, Number(values.size) || 56) * scale;
      const isImage = values.type === 'image';
      ctx.font = `${size}px ${values.font === 'Courier' ? 'monospace' : values.font === 'TimesRoman' ? 'serif' : 'Arial'}`;
      const w = isImage ? size : ctx.measureText(values.text || '').width;
      const h = isImage && image ? size * image.height / image.width : size * 0.75;
      const points = [];
      if (el.querySelector('[name="tile"]').checked) {
        for (let y = 60 * scale; y < preview.height; y += Math.max(90 * scale, h + 70 * scale)) for (let x = 60 * scale; x < preview.width; x += Math.max(120 * scale, w + 60 * scale)) points.push({x, y});
      } else {
        const point = this.position(values.position, preview.width, preview.height, Math.max(36 * scale, Math.min(w, h) / 2 + 12 * scale));
        points.push({x: point.x, y: preview.height - point.y});
      }
      for (const point of points) {
        ctx.save(); ctx.translate(point.x, point.y); ctx.rotate(-(Number(values.rotation) || 0) * Math.PI / 180); ctx.globalAlpha = (Number(values.opacity) || 0) / 100;
        if (isImage && image) ctx.drawImage(image, -w / 2, -h / 2, w, h);
        else if (!isImage) { ctx.fillStyle = values.color; ctx.fillText(values.text || '', -w / 2, h / 2); }
        ctx.restore();
      }
    };
    el.addEventListener('input', draw);
    el.querySelector('[name="image"]').addEventListener('change', async event => {
      const file = event.target.files[0];
      const version = ++previewVersion;
      if (!file) { image = null; draw(); return; }
      try {
        imageBytes = await file.arrayBuffer();
        const bitmap = await createImageBitmap(new Blob([imageBytes], {type: file.type}));
        if (version !== previewVersion || closed || !preview.isConnected) { bitmap.close(); return; }
        image?.close?.(); image = bitmap; draw();
      } catch (error) { this.S.error(error); }
    });
    const source = this.S.pages[this.S.current];
    this.S.renderPage(source, Math.min(1, 480 / source.width)).then(canvas => { if (closed) { canvas.width = canvas.height = 0; return; } previewBase = canvas; draw(); }).catch(error => this.S.error(error));
    el.addEventListener('hidden.bs.modal', () => { closed = true; previewVersion++; el.removeEventListener('input', draw); image?.close?.(); if (previewBase) previewBase.width = previewBase.height = 0; }, {once: true});
  }
  roman(n) {
    if (n < 1 || n > 3999) return String(n);
    const values = [[1000, 'M'], [900, 'CM'], [500, 'D'], [400, 'CD'], [100, 'C'], [90, 'XC'], [50, 'L'], [40, 'XL'], [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I']];
    let result = '';
    for (const [value, symbol] of values) while (n >= value) { result += symbol; n -= value; }
    return result;
  }
  letters(n) {
    if (n < 1) return String(n);
    let result = '';
    while (n) { n--; result = String.fromCharCode(65 + n % 26) + result; n = Math.floor(n / 26); }
    return result;
  }
  async numbering() { return this.numberDialog(false); }
  async bates() { return this.numberDialog(true); }
  numberDialog(bates) {
    this.S.requireDocument();
    const html = this.range() + (bates ? this.field('Prefix', 'prefix', 'DOC-') + this.field('Suffix', 'suffix') + this.field('Number width (zero padding)', 'padding', 6, 'number', 'min="1" max="20"') :
      this.field('Left content', 'left') + this.field('Center content', 'center', 'Page {n} of {total}') + this.field('Right content', 'right') + '<p class="small text-muted">Tokens: {n}, {total}, {filename}, {title}, {date}, {time}. Numbering is relative to selected pages.</p>') +
      this.field('Start number', 'start', 1, 'number', 'min="0" max="999999999"') +
      (bates ? this.select('Position', 'position', this.positionOptions(), 'bottom-right') : this.select('Placement', 'placement', [['bottom', 'Footer'], ['top', 'Header']]) + this.select('Number format', 'format', [['decimal', '1, 2, 3'], ['roman', 'I, II, III'], ['letters', 'A, B, C']])) +
      this.select('Font', 'font', this.fontOptions()) + this.field('Font size', 'size', 11, 'number', 'min="6" max="100"') +
      this.field('Color', 'color', '#333333', 'color') + this.field('Margin (PDF points)', 'margin', 24, 'number', 'min="0" max="300"');
    this.S.dialog(bates ? 'Add Bates numbering' : 'Add header, footer or page numbers', html, async values => {
      const indexes = this.indexes(values.pages);
      const start = Math.floor(this.number(values.start, 0, 999999999, 'Start number'));
      const size = this.number(values.size, 6, 100, 'Font size');
      const margin = this.number(values.margin, 0, 300, 'Margin');
      const padding = bates ? Math.floor(this.number(values.padding, 1, 20, 'Number width')) : 0;
      await this.mutate(bates ? 'Bates numbering' : 'Header and footer', async (pdf, job) => {
        const font = await this.font(pdf, values.font);
        const color = this.color(values.color);
        const now = new Date();
        for (let k = 0; k < indexes.length; k++) {
          job.check();
          const page = pdf.getPage(indexes[k]);
          const {width, height} = this.visualSize(page);
          const n = start + k;
          const number = values.format === 'roman' ? this.roman(n) : values.format === 'letters' ? this.letters(n) : String(n);
          const tokens = {n: number, total: String(indexes.length), filename: this.S.sources.get(this.S.pages[indexes[k]].sourceId)?.name || '', title: pdf.getTitle() || '', date: now.toLocaleDateString(), time: now.toLocaleTimeString()};
          const replace = str => String(str || '').replace(/\{(n|total|filename|title|date|time)\}/g, (_, token) => tokens[token]);
          this.withVisualPage(page, () => {
            const draw = (text, x, y, align) => {
              this.safeText(font, text);
              const tw = font.widthOfTextAtSize(text, size);
              if (align === 'right') x -= tw; else if (align === 'center') x -= tw / 2;
              page.drawText(text, {x, y, size, font, color});
            };
            if (bates) {
              const point = this.position(values.position, width, height, margin);
              const align = values.position.split('-')[1];
              draw(values.prefix + String(n).padStart(padding, '0') + values.suffix, point.x, point.y - (values.position.startsWith('top') ? size : 0), align);
            } else {
              const y = values.placement === 'top' ? height - margin - size : margin;
              draw(replace(values.left), margin, y, 'left');
              draw(replace(values.center), width / 2, y, 'center');
              draw(replace(values.right), width - margin, y, 'right');
            }
          });
          job.progress(k + 1, indexes.length, 'Numbering pages');
        }
      });
    });
  }
  async crop() {
    this.S.requireDocument();
    const key = 'crop-preview-' + (++this.dialogCounter);
    const html = '<div class="row"><div class="col-md-6">' + this.range(this.selectionRange()) + this.field('Trim from left (PDF points)', 'left', 0, 'number', 'min="0" step="0.1"') +
      this.field('Trim from top', 'top', 0, 'number', 'min="0"') + this.field('Trim from right', 'right', 0, 'number', 'min="0"') +
      this.field('Trim from bottom', 'bottom', 0, 'number', 'min="0"') + this.check('Detect whitespace automatically', 'auto') +
      this.field('Automatic crop padding (points)', 'padding', 12, 'number', 'min="0" max="144"') +
      `<p class="small text-muted">Cropping changes the PDF CropBox. Hidden content remains in the file; use secure redaction to remove sensitive content. Automatic detection samples pages at 72 DPI.</p></div><div class="col-md-6"><p class="small text-muted">Drag a crop rectangle on the current page, then adjust its corner handles. The same trim measurements apply to the selected pages.</p><canvas id="${key}" class="tools-preview-canvas tools-crop-canvas" aria-label="Visual crop preview"></canvas></div></div>`;
    const el = this.S.dialog('Crop selected pages', html, async values => {
      const indexes = this.indexes(values.pages);
      const amounts = ['left', 'top', 'right', 'bottom'].map(k => this.number(values[k], 0, 10000, k));
      const padding = this.number(values.padding, 0, 144, 'Padding');
      await this.mutate('Crop pages', async (pdf, job) => {
        let doc = null;
        try {
          if (values.auto) doc = await this.rendered(job);
          for (let k = 0; k < indexes.length; k++) {
            job.check();
            const page = pdf.getPage(indexes[k]);
            const {width, height} = this.visualSize(page);
            let [left, top, right, bottom] = amounts;
            if (doc) {
              const canvas = await this.renderCanvas(doc, indexes[k], 72, false, job);
              const data = canvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height).data;
              let minX = canvas.width, minY = canvas.height, maxX = -1, maxY = -1;
              for (let y = 0; y < canvas.height; y += 2) for (let x = 0; x < canvas.width; x += 2) {
                const i = (y * canvas.width + x) * 4;
                if (data[i] < 245 || data[i + 1] < 245 || data[i + 2] < 245) { minX = Math.min(minX, x); minY = Math.min(minY, y); maxX = Math.max(maxX, x); maxY = Math.max(maxY, y); }
              }
              if (maxX >= 0) {
                left = Math.max(0, minX * width / canvas.width - padding);
                top = Math.max(0, minY * height / canvas.height - padding);
                right = Math.max(0, width - maxX * width / canvas.width - padding);
                bottom = Math.max(0, height - maxY * height / canvas.height - padding);
              }
              canvas.width = canvas.height = 0;
            }
            if (left + right >= width - 1 || top + bottom >= height - 1) throw new Error(`Crop leaves no usable area on page ${indexes[k] + 1}.`);
            const matrix = this.visualMatrix(page);
            const corners = [[left, bottom], [width - right, bottom], [left, height - top], [width - right, height - top]].map(([x, y]) => ({x: matrix[0] * x + matrix[2] * y + matrix[4], y: matrix[1] * x + matrix[3] * y + matrix[5]}));
            const minX = Math.min(...corners.map(p => p.x)), minY = Math.min(...corners.map(p => p.y));
            page.setCropBox(minX, minY, Math.max(...corners.map(p => p.x)) - minX, Math.max(...corners.map(p => p.y)) - minY);
            job.progress(k + 1, indexes.length, 'Cropping pages');
          }
        } finally { if (doc) await this.disposePDF(doc); }
      });
    }, {wide: true});
    this.cropPreview(el, key);
  }
  cropPreview(el, key) {
    const canvas = el.querySelector('#' + key);
    const p = this.S.pages[this.S.current];
    const width = p.rotation % 180 ? p.height : p.width, height = p.rotation % 180 ? p.width : p.height;
    let base = null, drag = null, closed = false;
    const rect = () => ({left: Number(el.querySelector('[name="left"]').value) || 0, top: Number(el.querySelector('[name="top"]').value) || 0, right: width - (Number(el.querySelector('[name="right"]').value) || 0), bottom: height - (Number(el.querySelector('[name="bottom"]').value) || 0)});
    const paint = () => {
      if (!base || closed) return;
      canvas.width = base.width; canvas.height = base.height;
      const ctx = canvas.getContext('2d'), scale = canvas.width / width, r = rect();
      ctx.drawImage(base, 0, 0); ctx.fillStyle = 'rgba(15,23,42,.42)';
      ctx.fillRect(0, 0, canvas.width, r.top * scale); ctx.fillRect(0, r.bottom * scale, canvas.width, canvas.height - r.bottom * scale);
      ctx.fillRect(0, r.top * scale, r.left * scale, (r.bottom - r.top) * scale); ctx.fillRect(r.right * scale, r.top * scale, canvas.width - r.right * scale, (r.bottom - r.top) * scale);
      ctx.strokeStyle = '#2563eb'; ctx.lineWidth = 2; ctx.strokeRect(r.left * scale, r.top * scale, (r.right - r.left) * scale, (r.bottom - r.top) * scale);
      ctx.fillStyle = '#fff';
      for (const [x, y] of [[r.left, r.top], [r.right, r.top], [r.left, r.bottom], [r.right, r.bottom]]) { ctx.fillRect(x * scale - 5, y * scale - 5, 10, 10); ctx.strokeRect(x * scale - 5, y * scale - 5, 10, 10); }
    };
    const point = event => {
      const box = canvas.getBoundingClientRect();
      return {x: Math.max(0, Math.min(width, (event.clientX - box.left) / box.width * width)), y: Math.max(0, Math.min(height, (event.clientY - box.top) / box.height * height))};
    };
    canvas.addEventListener('pointerdown', event => {
      if (!base) return;
      event.preventDefault(); canvas.setPointerCapture(event.pointerId);
      const pos = point(event), r = rect(), tolerance = 14 * width / canvas.getBoundingClientRect().width;
      const handle = [['lt', r.left, r.top], ['rt', r.right, r.top], ['lb', r.left, r.bottom], ['rb', r.right, r.bottom]].find(([, x, y]) => Math.abs(pos.x - x) < tolerance && Math.abs(pos.y - y) < tolerance);
      drag = {start: pos, original: r, handle: handle?.[0] || 'new'};
    });
    canvas.addEventListener('pointermove', event => {
      if (!drag) return;
      const pos = point(event); let r = {...drag.original};
      if (drag.handle === 'new') r = {left: Math.min(drag.start.x, pos.x), top: Math.min(drag.start.y, pos.y), right: Math.max(drag.start.x, pos.x), bottom: Math.max(drag.start.y, pos.y)};
      else { if (drag.handle.includes('l')) r.left = Math.min(pos.x, r.right - 4); if (drag.handle.includes('r')) r.right = Math.max(pos.x, r.left + 4); if (drag.handle.includes('t')) r.top = Math.min(pos.y, r.bottom - 4); if (drag.handle.includes('b')) r.bottom = Math.max(pos.y, r.top + 4); }
      const values = {left: r.left, top: r.top, right: width - r.right, bottom: height - r.bottom};
      for (const [name, value] of Object.entries(values)) el.querySelector('[name="' + name + '"]').value = Math.max(0, value).toFixed(1);
      el.querySelector('[name="auto"]').checked = false; paint();
    });
    canvas.addEventListener('pointerup', () => { drag = null; }); canvas.addEventListener('pointercancel', () => { drag = null; });
    for (const name of ['left', 'top', 'right', 'bottom']) el.querySelector('[name="' + name + '"]').addEventListener('input', paint);
    el.addEventListener('hidden.bs.modal', () => { closed = true; if (base) base.width = base.height = 0; }, {once: true});
    this.S.renderPage(p, Math.min(1, 480 / width)).then(result => { if (closed) { result.width = result.height = 0; return; } base = result; paint(); }).catch(error => this.S.error(error));
  }
  paperOptions() { return [['a4', 'A4 (210 × 297 mm)'], ['a3', 'A3 (297 × 420 mm)'], ['a5', 'A5 (148 × 210 mm)'], ['letter', 'Letter (8.5 × 11 in)'], ['legal', 'Legal (8.5 × 14 in)'], ['custom', 'Custom (PDF points)']]; }
  paper(values) {
    const sizes = {a4: [595.276, 841.89], a3: [841.89, 1190.551], a5: [419.528, 595.276], letter: [612, 792], legal: [612, 1008]};
    let result = sizes[values.paper] || [this.number(values.width, 36, 14400, 'Page width'), this.number(values.height, 36, 14400, 'Page height')];
    if (values.orientation === 'landscape' && result[0] < result[1]) result = [result[1], result[0]];
    if (values.orientation === 'portrait' && result[0] > result[1]) result = [result[1], result[0]];
    return result;
  }
  async embedVisual(pdf, source) {
    const {box, width, height, rotation} = this.visualSize(source);
    const embedded = await pdf.embedPage(source, {left: box.x, bottom: box.y, right: box.x + box.width, top: box.y + box.height});
    return {embedded, width, height, rotation};
  }
  drawEmbedded(page, visual, x, y, width, height) {
    const sx = width / visual.width, sy = height / visual.height;
    if (visual.rotation === 90) page.drawPage(visual.embedded, {x, y: y + height, xScale: sy, yScale: sx, rotate: PDFLib.degrees(270)});
    else if (visual.rotation === 180) page.drawPage(visual.embedded, {x: x + width, y: y + height, xScale: sx, yScale: sy, rotate: PDFLib.degrees(180)});
    else if (visual.rotation === 270) page.drawPage(visual.embedded, {x: x + width, y, xScale: sy, yScale: sx, rotate: PDFLib.degrees(90)});
    else page.drawPage(visual.embedded, {x, y, xScale: sx, yScale: sy});
  }
  async resize() {
    this.S.requireDocument();
    const html = this.range(this.selectionRange()) + this.select('Paper size', 'paper', this.paperOptions()) + this.select('Orientation', 'orientation', [['portrait', 'Portrait'], ['landscape', 'Landscape']]) +
      this.field('Custom width (PDF points)', 'width', 595.276, 'number', 'min="36" max="14400"') + this.field('Custom height', 'height', 841.89, 'number', 'min="36" max="14400"') +
      this.select('Content sizing', 'fit', [['fit', 'Fit / preserve aspect ratio'], ['stretch', 'Stretch'], ['actual', 'Actual size']]) + this.field('Margin (points)', 'margin', 0, 'number', 'min="0" max="500"') +
      '<p class="small text-muted">Page content remains vector-based. Form fields are flattened. PDF comment, highlight and link annotations are omitted on resized pages; editable overlays are already included in page content.</p>';
    this.S.dialog('Change page size', html, async values => {
      const selected = new Set(this.indexes(values.pages));
      const [width, height] = this.paper(values);
      const margin = this.number(values.margin, 0, Math.min(width, height) / 2 - 1, 'Margin');
      await this.S.busy('Resizing pages', async job => {
        const source = await this.editable(job);
        if (source.getForm().getFields().length) source.getForm().flatten();
        const out = await PDFLib.PDFDocument.create();
        for (let i = 0; i < source.getPageCount(); i++) {
          job.check();
          if (!selected.has(i)) out.addPage((await out.copyPages(source, [i]))[0]);
          else {
            const page = out.addPage([width, height]);
            const visual = await this.embedVisual(out, source.getPage(i));
            const scale = values.fit === 'actual' ? 1 : Math.min((width - margin * 2) / visual.width, (height - margin * 2) / visual.height);
            const w = values.fit === 'stretch' ? width - margin * 2 : visual.width * scale;
            const h = values.fit === 'stretch' ? height - margin * 2 : visual.height * scale;
            this.drawEmbedded(page, visual, (width - w) / 2, (height - h) / 2, w, h);
          }
          job.progress(i + 1, source.getPageCount(), 'Resizing pages');
        }
        const bytes = await this.saved(out, job);
        await this.S.replaceBytes(bytes, this.name('resized'));
      });
    });
  }
  async forms() {
    this.S.requireDocument();
    let pdf;
    await this.S.busy('Reading form fields', async job => { pdf = await this.editable(job); });
    const form = pdf.getForm();
    if (form.hasXFA()) throw new Error('This PDF contains an XFA form. XFA editing is unsupported. Use an AcroForm PDF or the overlay text editor.');
    const fields = form.getFields();
    if (!fields.length) throw new Error('No AcroForm fields were found. Use Create form field to add one.');
    let html = '<p class="small text-muted">Field values are saved in the PDF. Unsupported button and cryptographic signature fields are shown as read-only. Standard Latin characters are supported by the appearance font.</p>';
    fields.forEach((field, i) => {
      const name = 'f' + i;
      const label = field.getName();
      if (field instanceof PDFLib.PDFTextField) {
        html += `<label class="form-label d-block mb-3">${this.e(label)}<textarea class="form-control mt-1" name="${name}" rows="${field.isMultiline() ? 3 : 1}"${field.isReadOnly() ? ' disabled' : ''}>${this.e(field.getText() || '')}</textarea></label>`;
      } else if (field instanceof PDFLib.PDFCheckBox) html += this.check(label, name, field.isChecked());
      else if (field instanceof PDFLib.PDFRadioGroup) html += this.select(label, name, [['', '(No selection)'], ...field.getOptions()], field.getSelected() || '');
      else if (field instanceof PDFLib.PDFDropdown || field instanceof PDFLib.PDFOptionList) {
        const multiple = field.isMultiselect();
        const selected = new Set(field.getSelected());
        html += `<label class="form-label d-block mb-3">${this.e(label)}<select class="form-select mt-1" name="${name}"${multiple ? ' multiple' : ''}${field.isReadOnly() ? ' disabled' : ''}>${!multiple ? '<option value="">(No selection)</option>' : ''}${this.choiceOptions(field).map(option => `<option value="${option.index}"${selected.has(option.value) ? ' selected' : ''}>${this.e(option.label)}</option>`).join('')}</select></label>`;
      } else html += `<div class="mb-3"><strong>${this.e(label)}</strong><span class="badge text-bg-secondary ms-2">Read-only ${this.e(field.constructor.name)}</span></div>`;
    });
    html += this.check('Flatten fields after filling (cannot edit later)', 'flatten') + '<button type="button" class="btn btn-outline-secondary" id="tools-reset-form">Reset fields</button>';
    const el = this.S.dialog('Fill PDF form', html, async values => {
      await this.S.busy('Saving form values', async job => {
        const font = await pdf.embedFont(PDFLib.StandardFonts.Helvetica);
        for (let i = 0; i < fields.length; i++) {
          job.check();
          const field = fields[i];
          if (field.isReadOnly()) continue;
          const value = values['f' + i];
          if (field instanceof PDFLib.PDFTextField) { this.safeText(font, String(value || '')); field.setText(String(value || '')); }
          else if (field instanceof PDFLib.PDFCheckBox) value ? field.check() : field.uncheck();
          else if (field instanceof PDFLib.PDFRadioGroup) value ? field.select(value) : field.clear();
          else if (field instanceof PDFLib.PDFDropdown || field instanceof PDFLib.PDFOptionList) {
            const selected = Array.isArray(value) ? value : value ? [value] : [];
            this.setChoice(field, selected, font);
          }
        }
        form.updateFieldAppearances(font);
        if (values.flatten) form.flatten();
        const bytes = await pdf.save(); job.check();
        await this.S.replaceBytes(bytes, this.name(values.flatten ? 'form-flattened' : 'form-filled'));
      });
    }, {wide: true});
    el.querySelector('#tools-reset-form').onclick = () => {
      for (const field of el.querySelectorAll('[name^="f"]')) {
        if (field.name === 'flatten' || field.disabled) continue;
        if (field.type === 'checkbox') field.checked = false;
        else if (field.tagName === 'SELECT') { for (const option of field.options) option.selected = false; if (!field.multiple && field.options[0]?.value === '') field.selectedIndex = 0; }
        else field.value = '';
      }
    };
  }
  choiceOptions(field) {
    return field.acroField.getOptions().map((option, index) => ({index, value: option.value.decodeText(), label: (option.display || option.value).decodeText()}));
  }
  setChoice(field, selections, font) {
    const options = this.choiceOptions(field);
    const selected = selections.map(value => options[Number(value)]).filter(Boolean);
    const dict = field.acroField.dict;
    const values = selected.map(option => PDFLib.PDFHexString.fromText(option.value));
    if (!values.length) dict.delete(PDFLib.PDFName.of('V'));
    else dict.set(PDFLib.PDFName.of('V'), values.length === 1 ? values[0] : dict.context.obj(values));
    if (selected.length > 1) dict.set(PDFLib.PDFName.of('I'), dict.context.obj(selected.map(option => option.index).sort((a, b) => a - b)));
    else dict.delete(PDFLib.PDFName.of('I'));
    const original = field.getSelected;
    field.getSelected = () => selected.map(option => option.label);
    try { selected.forEach(option => this.safeText(font, option.label)); field.updateAppearances(font); }
    finally { field.getSelected = original; }
    field.markAsClean();
  }
  rawRect(page, x, y, width, height) {
    const matrix = this.visualMatrix(page);
    const corners = [[x, y], [x + width, y], [x, y + height], [x + width, y + height]].map(([px, py]) => [matrix[0] * px + matrix[2] * py + matrix[4], matrix[1] * px + matrix[3] * py + matrix[5]]);
    const left = Math.min(...corners.map(p => p[0])), bottom = Math.min(...corners.map(p => p[1]));
    return {x: left, y: bottom, width: Math.max(...corners.map(p => p[0])) - left, height: Math.max(...corners.map(p => p[1])) - bottom};
  }
  async createField() {
    this.S.requireDocument();
    const html = this.field('Page number', 'page', this.S.current + 1, 'number', `min="1" max="${this.S.pages.length}"`) +
      this.field('Unique field name', 'name', 'Field' + Date.now()) + this.select('Field type', 'type', [['text', 'Text'], ['multiline', 'Multiline text'], ['checkbox', 'Checkbox'], ['radio', 'Radio group'], ['dropdown', 'Dropdown']]) +
      this.field('Left position (points from page left)', 'x', 36, 'number', 'min="0"') + this.field('Top position (points from page top)', 'y', 36, 'number', 'min="0"') +
      this.field('Width', 'width', 180, 'number', 'min="8"') + this.field('Height', 'height', 28, 'number', 'min="8"') +
      this.field('Default text / selected option', 'value') + this.field('Options (one per line; radio/dropdown only)', 'options', 'Yes,No') +
      '<p class="small text-muted">Options may be separated with commas. Radio options are stacked with 28-point spacing. Signature images are available under Fill & Sign; cryptographic signature fields require a signing engine.</p>';
    this.S.dialog('Create AcroForm field', html, async values => {
      const index = Math.floor(this.number(values.page, 1, this.S.pages.length, 'Page')) - 1;
      if (!values.name.trim() || values.name.length > 200) throw new Error('Enter a unique field name of 1–200 characters.');
      const x = this.number(values.x, 0, 14000, 'Left position'), top = this.number(values.y, 0, 14000, 'Top position');
      const width = this.number(values.width, 8, 14000, 'Width'), height = this.number(values.height, 8, 14000, 'Height');
      const options = [...new Set(values.options.split(/[,\n]/).map(x => x.trim()).filter(Boolean))];
      await this.mutate('Create form field', async pdf => {
        const page = pdf.getPage(index), size = this.visualSize(page);
        if (x + width > size.width || top + height > size.height) throw new Error('The field rectangle extends beyond the page.');
        const form = pdf.getForm();
        if (form.hasXFA()) throw new Error('XFA form editing is unsupported.');
        if (form.getFieldMaybe(values.name)) throw new Error('A field with this name already exists.');
        const font = await this.font(pdf, 'Helvetica');
        const rect = this.rawRect(page, x, size.height - top - height, width, height);
        const style = {...rect, borderWidth: 1, borderColor: PDFLib.rgb(0.35, 0.4, 0.5), backgroundColor: PDFLib.rgb(1, 1, 1), textColor: PDFLib.rgb(0, 0, 0), font};
        let created;
        if (values.type === 'text' || values.type === 'multiline') {
          const field = form.createTextField(values.name);
          created = field;
          if (values.type === 'multiline') field.enableMultiline();
          this.safeText(font, values.value);
          field.setText(values.value); field.addToPage(page, style); field.setFontSize(12);
        } else if (values.type === 'checkbox') {
          const field = form.createCheckBox(values.name); created = field; field.addToPage(page, style);
          if (/^(yes|true|1|checked)$/i.test(values.value)) field.check();
        } else if (values.type === 'dropdown') {
          if (!options.length) throw new Error('Enter at least one dropdown option.');
          const field = form.createDropdown(values.name); field.addOptions(options); field.addToPage(page, style);
          created = field;
          if (values.value) { if (!options.includes(values.value)) throw new Error('Default value must match an option.'); field.select(values.value); }
        } else if (values.type === 'radio') {
          if (!options.length) throw new Error('Enter at least one radio option.');
          if (top + options.length * 28 > size.height) throw new Error('The radio options extend beyond the page.');
          const field = form.createRadioGroup(values.name);
          created = field;
          for (let i = 0; i < options.length; i++) {
            const radioRect = this.rawRect(page, x, size.height - top - 18 - i * 28, 18, 18);
            field.addOptionToPage(options[i], page, {...style, ...radioRect});
            this.withVisualPage(page, () => page.drawText(this.safeText(font, options[i]), {x: x + 26, y: size.height - top - 14 - i * 28, size: 12, font}));
          }
          if (values.value) { if (!options.includes(values.value)) throw new Error('Default value must match an option.'); field.select(values.value); }
        }
        for (const widget of created.acroField.getWidgets()) widget.getOrCreateAppearanceCharacteristics().setRotation(size.rotation);
        created.markAsDirty();
        form.updateFieldAppearances(font);
      });
    });
  }
  async flattenForms() {
    this.S.requireDocument();
    this.S.dialog('Flatten form fields', '<p>Form values will become permanent page content. Fields will no longer be interactive. Undo can restore the previous document.</p>', async () => {
      await this.mutate('Flatten forms', async pdf => {
        const form = pdf.getForm();
        if (form.hasXFA()) throw new Error('XFA forms cannot be flattened by this engine.');
        if (!form.getFields().length) throw new Error('No AcroForm fields were found.');
        form.flatten();
      });
    });
  }
  async metadata() {
    this.S.requireDocument();
    let pdf;
    await this.S.busy('Reading metadata', async job => { pdf = await this.editable(job); });
    const dateValue = date => date instanceof Date && !Number.isNaN(date.getTime()) ? date.toISOString().slice(0, 19) : '';
    const html = this.field('Title', 'title', pdf.getTitle() || '') + this.field('Author', 'author', pdf.getAuthor() || '') + this.field('Subject', 'subject', pdf.getSubject() || '') +
      this.field('Keywords (comma separated)', 'keywords', pdf.getKeywords() || '') + this.field('Creator', 'creator', pdf.getCreator() || '') + this.field('Producer', 'producer', pdf.getProducer() || '') +
      this.field('Creation date (UTC)', 'created', dateValue(pdf.getCreationDate()), 'datetime-local') + this.field('Modification date (UTC)', 'modified', dateValue(pdf.getModificationDate()), 'datetime-local') +
      this.check('Remove all standard Info and XMP metadata', 'remove') + '<p class="small text-muted">Metadata cleaning removes document properties and XMP. It does not remove visible page text, attachments, annotations or hidden cropped content.</p>';
    this.S.dialog('PDF metadata', html, async values => {
      await this.S.busy('Saving metadata', async job => {
        job.check();
        if (values.remove) {
          const metadata = pdf.catalog.get(PDFLib.PDFName.of('Metadata'));
          pdf.catalog.delete(PDFLib.PDFName.of('Metadata'));
          if (metadata instanceof PDFLib.PDFRef) pdf.context.delete(metadata);
          if (pdf.context.trailerInfo.Info) { pdf.context.delete(pdf.context.trailerInfo.Info); delete pdf.context.trailerInfo.Info; }
        } else {
          pdf.setTitle(values.title); pdf.setAuthor(values.author); pdf.setSubject(values.subject);
          pdf.setKeywords(values.keywords.split(',').map(x => x.trim()).filter(Boolean));
          pdf.setCreator(values.creator); pdf.setProducer(values.producer);
          for (const [key, method] of [['created', 'setCreationDate'], ['modified', 'setModificationDate']]) if (values[key]) {
            const date = new Date(values[key] + 'Z');
            if (Number.isNaN(date.getTime())) throw new Error('Enter a valid UTC date.');
            pdf[method](date);
          }
        }
        const bytes = await pdf.save({updateFieldAppearances: false}); job.check();
        await this.S.replaceBytes(bytes, this.name(values.remove ? 'metadata-removed' : 'metadata'));
        this.S.metadata.removeMetadata = Boolean(values.remove); this.S.commit();
      });
    });
  }
  async inspect() {
    this.S.requireDocument();
    let report;
    await this.S.busy('Inspecting PDF', async job => {
      const bytes = await this.exported(job);
      const pdf = await PDFLib.PDFDocument.load(bytes, {updateMetadata: false});
      const doc = await this.loadPDF(bytes);
      try {
        const fonts = new Set(), rows = [];
        let textPages = 0, imageDraws = 0, annotations = 0;
        for (let i = 0; i < doc.numPages; i++) {
          job.check();
          const page = await doc.getPage(i + 1);
          const text = await page.getTextContent();
          if (text.items.some(x => x.str?.trim())) textPages++;
          Object.values(text.styles || {}).forEach(style => fonts.add(style.fontFamily || 'Unknown font'));
          const ann = await page.getAnnotations();
          annotations += ann.length;
          const operators = await page.getOperatorList();
          const imageOps = [pdfjsLib.OPS.paintImageXObject, pdfjsLib.OPS.paintInlineImageXObject, pdfjsLib.OPS.paintImageMaskXObject, pdfjsLib.OPS.paintImageXObjectRepeat, pdfjsLib.OPS.paintImageMaskXObjectRepeat];
          const imageCount = operators.fnArray.filter(op => imageOps.includes(op)).length;
          imageDraws += imageCount;
          const viewport = page.getViewport({scale: 1});
          rows.push(`<tr><td>${i + 1}</td><td>${viewport.width.toFixed(1)} × ${viewport.height.toFixed(1)} pt</td><td>${page.rotate}°</td><td>${text.items.some(x => x.str?.trim()) ? 'Text present' : 'No embedded text'}</td><td>${imageCount}</td><td>${ann.length}</td></tr>`);
          page.cleanup();
          job.progress(i + 1, doc.numPages, 'Inspecting pages');
        }
        const version = new TextDecoder('latin1').decode(bytes.subarray(0, 1024)).match(/%PDF-(\d\.\d)/)?.[1] || 'Unknown';
        const meta = {Title: pdf.getTitle(), Author: pdf.getAuthor(), Subject: pdf.getSubject(), Keywords: pdf.getKeywords(), Creator: pdf.getCreator(), Producer: pdf.getProducer(), Created: pdf.getCreationDate()?.toISOString(), Modified: pdf.getModificationDate()?.toISOString()};
        const originalEncrypted = [...this.S.sources.values()].some(source => source.lib?.isEncrypted || source.encrypted);
        report = `<dl class="row"><dt class="col-sm-4">Working PDF size</dt><dd class="col-sm-8">${bytes.length.toLocaleString()} bytes</dd><dt class="col-sm-4">Pages / PDF version</dt><dd class="col-sm-8">${doc.numPages} / ${this.e(version)}</dd><dt class="col-sm-4">Original encryption</dt><dd class="col-sm-8">${originalEncrypted ? 'Detected on an input document' : 'No encryption detected in loaded input'}</dd><dt class="col-sm-4">Text classification</dt><dd class="col-sm-8">${textPages} of ${doc.numPages} pages have embedded text${textPages === 0 ? ' (likely scanned or outlined text)' : ''}</dd><dt class="col-sm-4">Font families</dt><dd class="col-sm-8">${this.e([...fonts].join(', ') || 'None detected')}</dd><dt class="col-sm-4">Image drawing operations</dt><dd class="col-sm-8">${imageDraws} (repeated uses may count more than once)</dd><dt class="col-sm-4">Form fields / annotations</dt><dd class="col-sm-8">${pdf.getForm().getFields().length} / ${annotations}${pdf.getForm().hasXFA() ? ' · XFA present, editing unsupported' : ''}</dd></dl>` +
          `<h6>Document properties</h6><table class="table table-sm"><tbody>${Object.entries(meta).map(([k, v]) => `<tr><th>${this.e(k)}</th><td>${this.e(v || '—')}</td></tr>`).join('')}</tbody></table><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Page</th><th>Visible dimensions</th><th>Rotation</th><th>Text</th><th>Images</th><th>Annotations</th></tr></thead><tbody>${rows.join('')}</tbody></table></div><p class="small text-muted">PDF.js successfully parsed all pages. Run server Validate PDF for structural diagnostics when qpdf is available.</p>`;
      } finally { await this.disposePDF(doc); }
    });
    this.S.dialog('Document inspector', report, () => {}, {wide: true, submit: 'Close'});
  }
  wordDiff(a, b) {
    const limit = 1400;
    const fullA = a.trim().split(/\s+/), fullB = b.trim().split(/\s+/);
    const A = fullA.slice(0, limit), B = fullB.slice(0, limit);
    const width = B.length + 1;
    const matrix = new Uint16Array((A.length + 1) * width);
    for (let i = A.length - 1; i >= 0; i--) for (let j = B.length - 1; j >= 0; j--) matrix[i * width + j] = A[i] === B[j] ? matrix[(i + 1) * width + j + 1] + 1 : Math.max(matrix[(i + 1) * width + j], matrix[i * width + j + 1]);
    let i = 0, j = 0, html = '';
    while (i < A.length || j < B.length) {
      if (i < A.length && j < B.length && A[i] === B[j]) { html += this.e(A[i]) + ' '; i++; j++; }
      else if (j < B.length && (i >= A.length || matrix[i * width + j + 1] >= matrix[(i + 1) * width + j])) html += '<ins>' + this.e(B[j++]) + '</ins> ';
      else html += '<del>' + this.e(A[i++]) + '</del> ';
    }
    if (fullA.length > limit || fullB.length > limit) html += '<p class="small text-muted">Word diff is limited to the first 1,400 words per page. The side-by-side extracted text below is complete.</p>';
    return html;
  }
  async compare() {
    const files = await this.S.pickFiles('.pdf,application/pdf', true);
    if (!files.length) return;
    if (files.length !== 2 && !(files.length === 1 && this.S.pages.length)) throw new Error('Select two PDFs, or open a document first and select one PDF to compare with it.');
    let A, B, nameA, nameB;
    await this.S.busy('Opening comparison PDFs', async job => {
      const bytesA = files.length === 2 ? new Uint8Array(await files[0].arrayBuffer()) : await this.exported(job);
      const bytesB = new Uint8Array(await files[files.length - 1].arrayBuffer());
      nameA = files.length === 2 ? files[0].name : 'Current edited document'; nameB = files[files.length - 1].name;
      A = await this.loadPDF(bytesA);
      try { B = await this.loadPDF(bytesB); } catch (error) { await this.disposePDF(A); throw error; }
    });
    const key = 'compare-' + (++this.dialogCounter);
    const html = `<div class="d-flex flex-wrap gap-2 align-items-center mb-3"><button type="button" class="btn btn-outline-secondary" id="${key}-prev" aria-label="Previous comparison page"><i class="fa-solid fa-chevron-left"></i></button><label class="d-flex align-items-center gap-2">Page<input type="number" min="1" max="${Math.max(A.numPages, B.numPages)}" value="1" class="form-control" style="width:85px" id="${key}-page"></label><span>of ${Math.max(A.numPages, B.numPages)}</span><button type="button" class="btn btn-outline-secondary" id="${key}-next" aria-label="Next comparison page"><i class="fa-solid fa-chevron-right"></i></button><select class="form-select w-auto" id="${key}-mode" aria-label="Comparison view"><option value="side">Side by side</option><option value="overlay">50% overlay</option><option value="difference">Raster difference</option></select><label class="d-flex align-items-center gap-2">Difference intensity<input type="range" min="1" max="8" step="0.25" value="3" id="${key}-intensity"></label><button type="button" class="btn btn-outline-secondary" id="${key}-save">Save visualization</button></div><div class="tools-compare-grid" id="${key}-grid"><div><h6>${this.e(nameA)} (${A.numPages} pages)</h6><div class="tools-compare-pane" id="${key}-a"><canvas></canvas><p class="small"></p></div></div><div id="${key}-b-column"><h6>${this.e(nameB)} (${B.numPages} pages)</h6><div class="tools-compare-pane" id="${key}-b"><canvas></canvas><p class="small"></p></div></div></div><p id="${key}-status" class="small text-muted mt-2"></p><details class="mt-3"><summary>Embedded text differences (deleted / inserted words)</summary><div class="tools-word-diff mt-2" id="${key}-diff"></div><div class="row mt-3"><div class="col-md-6"><textarea class="form-control font-monospace" id="${key}-text-a" rows="8" readonly aria-label="PDF A embedded text"></textarea></div><div class="col-md-6"><textarea class="form-control font-monospace" id="${key}-text-b" rows="8" readonly aria-label="PDF B embedded text"></textarea></div></div></details>`;
    const el = this.S.dialog('Compare PDFs', html, () => {}, {wide: true, submit: 'Close'});
    const q = suffix => el.querySelector('#' + key + '-' + suffix);
    const paneA = q('a'), paneB = q('b'), canvasA = paneA.querySelector('canvas'), canvasB = paneB.querySelector('canvas');
    let version = 0, currentA = null, currentB = null, closed = false, scrolling = false;
    const syncScroll = (from, to) => {
      if (scrolling) return; scrolling = true;
      to.scrollTop = from.scrollTop; to.scrollLeft = from.scrollLeft;
      requestAnimationFrame(() => { scrolling = false; });
    };
    paneA.addEventListener('scroll', () => syncScroll(paneA, paneB));
    paneB.addEventListener('scroll', () => syncScroll(paneB, paneA));
    const paint = () => {
      const mode = q('mode').value;
      q('b-column').hidden = mode !== 'side';
      q('grid').classList.toggle('tools-compare-single', mode !== 'side');
      const width = Math.max(currentA?.width || 0, currentB?.width || 0, 1), height = Math.max(currentA?.height || 0, currentB?.height || 0, 1);
      canvasA.width = mode === 'side' ? currentA?.width || width : width; canvasA.height = mode === 'side' ? currentA?.height || height : height;
      canvasB.width = currentB?.width || width; canvasB.height = currentB?.height || height;
      const ctxA = canvasA.getContext('2d'), ctxB = canvasB.getContext('2d');
      ctxA.fillStyle = ctxB.fillStyle = '#fff'; ctxA.fillRect(0, 0, canvasA.width, canvasA.height); ctxB.fillRect(0, 0, canvasB.width, canvasB.height);
      if (currentB) ctxB.drawImage(currentB, 0, 0);
      if (mode === 'side') { if (currentA) ctxA.drawImage(currentA, 0, 0); }
      else if (mode === 'overlay') {
        if (currentA) ctxA.drawImage(currentA, 0, 0); ctxA.globalAlpha = 0.5;
        if (currentB) ctxA.drawImage(currentB, 0, 0); ctxA.globalAlpha = 1;
      } else {
        const tempA = document.createElement('canvas'), tempB = document.createElement('canvas');
        tempA.width = tempB.width = width; tempA.height = tempB.height = height;
        const ca = tempA.getContext('2d'), cb = tempB.getContext('2d');
        ca.fillStyle = cb.fillStyle = '#fff'; ca.fillRect(0, 0, width, height); cb.fillRect(0, 0, width, height);
        if (currentA) ca.drawImage(currentA, 0, 0); if (currentB) cb.drawImage(currentB, 0, 0);
        const da = ca.getImageData(0, 0, width, height), db = cb.getImageData(0, 0, width, height), out = ctxA.createImageData(width, height);
        const intensity = Number(q('intensity').value);
        for (let i = 0; i < out.data.length; i += 4) {
          const delta = Math.max(Math.abs(da.data[i] - db.data[i]), Math.abs(da.data[i + 1] - db.data[i + 1]), Math.abs(da.data[i + 2] - db.data[i + 2]));
          out.data[i] = 255; out.data[i + 1] = Math.max(0, 255 - delta * intensity); out.data[i + 2] = 255; out.data[i + 3] = 255;
        }
        ctxA.putImageData(out, 0, 0); tempA.width = tempB.width = 0;
      }
    };
    const render = async () => {
      const token = ++version;
      const index = Math.min(Math.max(1, Number(q('page').value) || 1), Math.max(A.numPages, B.numPages)) - 1;
      q('page').value = index + 1;
      q('status').textContent = 'Rendering comparison page…';
      try {
        const [a, b, textA, textB] = await Promise.all([
          index < A.numPages ? this.renderCanvas(A, index, 120) : null,
          index < B.numPages ? this.renderCanvas(B, index, 120) : null,
          index < A.numPages ? this.extractPageText(A, index, true) : '',
          index < B.numPages ? this.extractPageText(B, index, true) : ''
        ]);
        if (closed || token !== version) { if (a) a.width = a.height = 0; if (b) b.width = b.height = 0; return; }
        if (currentA) currentA.width = currentA.height = 0; if (currentB) currentB.width = currentB.height = 0;
        currentA = a; currentB = b;
        paneA.querySelector('p').textContent = a ? '' : 'No corresponding page in PDF A.';
        paneB.querySelector('p').textContent = b ? '' : 'No corresponding page in PDF B.';
        q('text-a').value = textA; q('text-b').value = textB;
        q('diff').innerHTML = textA || textB ? this.wordDiff(textA, textB) : '<p>No embedded text on these pages. Run OCR before comparing text.</p>';
        q('status').textContent = 'Raster comparison at 120 DPI. Magenta marks pixel differences. Text comparison uses embedded words; layout and reading-order differences can affect it.';
        paint();
      } catch (error) { if (!closed) { q('status').textContent = error.message; this.S.error(error); } }
    };
    q('prev').onclick = () => { q('page').value = Math.max(1, Number(q('page').value) - 1); render(); };
    q('next').onclick = () => { q('page').value = Math.min(Math.max(A.numPages, B.numPages), Number(q('page').value) + 1); render(); };
    q('page').addEventListener('change', render); q('mode').addEventListener('change', paint); q('intensity').addEventListener('input', paint);
    q('save').onclick = async () => {
      if (!currentA && !currentB) return;
      if (q('mode').value !== 'side') this.S.download(await this.blob(canvasA), `comparison-page-${q('page').value}.png`, 'image/png');
      else {
        const out = document.createElement('canvas'); out.width = canvasA.width + canvasB.width + 20; out.height = Math.max(canvasA.height, canvasB.height);
        const ctx = out.getContext('2d'); ctx.fillStyle = '#eee'; ctx.fillRect(0, 0, out.width, out.height); ctx.drawImage(canvasA, 0, 0); ctx.drawImage(canvasB, canvasA.width + 20, 0);
        this.S.download(await this.blob(out), `comparison-page-${q('page').value}.png`, 'image/png'); out.width = out.height = 0;
      }
    };
    el.addEventListener('hidden.bs.modal', () => { closed = true; version++; if (currentA) currentA.width = currentA.height = 0; if (currentB) currentB.width = currentB.height = 0; this.disposePDF(A).catch(() => {}); this.disposePDF(B).catch(() => {}); }, {once: true});
    render();
  }
  async nup() { return this.imposition(false); }
  async booklet() { return this.imposition(true); }
  imposition(booklet) {
    this.S.requireDocument();
    const html = this.range() + (booklet ? '<p>Creates left-bound booklet sheets in duplex order. Print two-sided with short-edge binding at actual size. Blank pages are added to a multiple of four. Interactive fields are flattened.</p>' : this.select('Layout', 'layout', [['2', '2-up'], ['4', '4-up'], ['6', '6-up'], ['9', '9-up']]) + this.select('Page order', 'order', [['row', 'Across rows'], ['column', 'Down columns']])) + '<p class="small text-muted">Form fields are flattened. PDF comments, highlight annotations and link annotations are omitted from imposed sheets.</p>' +
      this.select('Sheet paper size', 'paper', this.paperOptions()) + this.select('Sheet orientation', 'orientation', [['landscape', 'Landscape'], ['portrait', 'Portrait']], 'landscape') +
      this.field('Custom sheet width (PDF points)', 'width', 842, 'number') + this.field('Custom sheet height', 'height', 595, 'number') +
      this.field('Outer margin (points)', 'margin', 18, 'number', 'min="0" max="200"') + this.field('Spacing / gutter (points)', 'spacing', 12, 'number', 'min="0" max="200"') + this.check('Draw cell borders', 'borders');
    this.S.dialog(booklet ? 'Create printable booklet' : 'Create N-up contact sheet', html, async values => {
      const indexes = this.indexes(values.pages);
      const [width, height] = this.paper(values);
      const margin = this.number(values.margin, 0, Math.min(width, height) / 3, 'Margin');
      const gap = this.number(values.spacing, 0, 200, 'Spacing');
      const cols = booklet ? 2 : values.layout === '2' ? 2 : values.layout === '9' ? 3 : values.layout === '6' ? 3 : 2;
      const rows = booklet || values.layout === '2' ? 1 : values.layout === '9' ? 3 : 2;
      const cellW = (width - margin * 2 - gap * (cols - 1)) / cols, cellH = (height - margin * 2 - gap * (rows - 1)) / rows;
      if (cellW < 10 || cellH < 10) throw new Error('Margins and spacing leave no usable cell area.');
      await this.S.busy(booklet ? 'Imposing booklet' : 'Creating contact sheet', async job => {
        const source = await this.editable(job);
        if (source.getForm().getFields().length) source.getForm().flatten();
        const out = await PDFLib.PDFDocument.create();
        let groups;
        if (booklet) {
          const padded = indexes.slice(); while (padded.length % 4) padded.push(null);
          groups = [];
          for (let sheet = 0; sheet < padded.length / 4; sheet++) {
            groups.push([padded[padded.length - 1 - sheet * 2], padded[sheet * 2]]);
            groups.push([padded[sheet * 2 + 1], padded[padded.length - 2 - sheet * 2]]);
          }
        } else {
          groups = [];
          for (let i = 0; i < indexes.length; i += rows * cols) groups.push(indexes.slice(i, i + rows * cols));
        }
        for (let k = 0; k < groups.length; k++) {
          job.check();
          const sheet = out.addPage([width, height]);
          for (let n = 0; n < groups[k].length; n++) {
            const col = values.order === 'column' ? Math.floor(n / rows) : n % cols;
            const row = values.order === 'column' ? n % rows : Math.floor(n / cols);
            const x = margin + col * (cellW + gap), y = height - margin - (row + 1) * cellH - row * gap;
            if (values.borders) sheet.drawRectangle({x, y, width: cellW, height: cellH, borderWidth: 0.5, borderColor: PDFLib.rgb(0.5, 0.5, 0.5)});
            if (groups[k][n] == null) continue;
            const visual = await this.embedVisual(out, source.getPage(groups[k][n]));
            const scale = Math.min(cellW / visual.width, cellH / visual.height);
            this.drawEmbedded(sheet, visual, x + (cellW - visual.width * scale) / 2, y + (cellH - visual.height * scale) / 2, visual.width * scale, visual.height * scale);
          }
          job.progress(k + 1, groups.length, 'Writing imposed sheets');
        }
        this.S.download(await this.saved(out, job), this.name(booklet ? 'booklet' : 'n-up'), 'application/pdf');
      });
    });
  }
  async compress() {
    this.S.requireDocument();
    const presets = [];
    if (this.has('qpdf')) presets.push(['lossless', 'Lossless / qpdf structural optimization']);
    if (this.has('gs')) presets.push(['light', 'Light / 300 DPI'], ['medium', 'Medium / 150 DPI'], ['strong', 'Strong / 96 DPI'], ['maximum', 'Maximum / 72 DPI']);
    if (!presets.length) throw new Error('Compression requires qpdf for structural optimization or Ghostscript for image recompression.');
    const html = this.select('Compression preset', 'preset', presets) + this.field('Image DPI (Ghostscript presets)', 'dpi', 150, 'number', 'min="36" max="600"') +
      this.field('JPEG quality (10–100; Ghostscript)', 'quality', 80, 'number', 'min="10" max="100"') + this.check('Convert to grayscale (Ghostscript)', 'grayscale') +
      (this.has('qpdf') ? this.check('Fast Web View / linearize output', 'linearize') : '') +
      (this.S.server?.tools?.qpdf?.metadataRemoval ? this.check('Remove document metadata', 'removeMetadata') : '') +
      '<div class="alert alert-info small" id="tools-compress-quality">Lossless optimization compresses streams and restructures objects; it does not recompress images. Ghostscript image presets can reduce resolution and affect fonts, forms, transparency and annotations. Compression may increase the size of an already optimized file.</div>';
    const el = this.S.dialog('Compress / optimize PDF on server', html, async values => {
      const options = {preset: values.preset, linearize: Boolean(values.linearize), removeMetadata: Boolean(values.removeMetadata)};
      if (values.preset !== 'lossless') {
        options.dpi = this.number(values.dpi, 36, 600, 'DPI');
        options.quality = this.number(values.quality, 10, 100, 'Quality');
        options.grayscale = Boolean(values.grayscale);
      }
      let before, after, percent;
      await this.S.busy('Compressing PDF on server', async job => {
        const bytes = await this.exported(job);
        before = bytes.length;
        const response = await this.server('compress', options, job, new File([bytes], 'document.pdf', {type: 'application/pdf'}));
        after = response.blob.size;
        percent = (100 - after / before * 100).toFixed(1);
        this.S.download(response.blob, response.name || this.name('compressed'), 'application/pdf');
      });
      this.S.notify(`Compression: ${before.toLocaleString()} → ${after.toLocaleString()} bytes (${Number(percent) >= 0 ? percent + '% smaller' : Math.abs(Number(percent)).toFixed(1) + '% larger'}).`);
    });
    el.querySelector('[name="preset"]').addEventListener('change', event => {
      const presets = {light: [300, 90], medium: [150, 80], strong: [96, 65], maximum: [72, 45]};
      const values = presets[event.target.value];
      if (values) { el.querySelector('[name="dpi"]').value = values[0]; el.querySelector('[name="quality"]').value = values[1]; }
      for (const name of ['dpi', 'quality', 'grayscale']) el.querySelector('[name="' + name + '"]').disabled = !values;
    });
    el.querySelector('[name="preset"]').dispatchEvent(new Event('change'));
  }
  async protect() {
    this.S.requireDocument();
    const html = this.field('Password to open (user password)', 'userPassword', '', 'password', 'autocomplete="new-password"') +
      this.field('Owner password (permissions / editing)', 'ownerPassword', '', 'password', 'autocomplete="new-password"') +
      this.select('Encryption', 'bits', [['256', 'AES-256'], ['128', 'AES-128']], '256') + this.select('Printing', 'print', [['full', 'Full-quality printing'], ['low', 'Low-resolution printing'], ['none', 'Disallow printing']]) +
      this.check('Allow copying', 'copy', true) + this.check('Allow modifying document', 'modify', true) + this.check('Allow annotations', 'annotate', true) + this.check('Allow form filling', 'forms', true) +
      '<p class="small text-muted">Passwords are sent only for this operation. If the owner password is empty, the server generates one. PDF permission flags depend on the reader and do not provide DRM-grade protection.</p>';
    this.S.dialog('Encrypt PDF on server', html, async values => {
      if (!values.userPassword && !values.ownerPassword) throw new Error('Enter an opening password or owner password.');
      await this.downloadServer('protect', {...values, bits: Number(values.bits)}, 'Protecting PDF');
    });
  }
  async decrypt() {
    const files = await this.S.pickFiles('.pdf,application/pdf');
    if (!files.length) return;
    this.S.dialog('Remove password from PDF', `<p>${this.e(files[0].name)}</p>` + this.field('Valid opening or owner password', 'password', '', 'password', 'autocomplete="off"'), async values => {
      await this.downloadServer('decrypt', {password: values.password}, 'Removing PDF encryption', files[0]);
    });
  }
  async linearize() {
    this.S.requireDocument();
    this.S.dialog('Fast Web View / linearize', '<p>qpdf reorganizes the document so a compatible reader can display its first page before downloading the entire file. Page content is preserved. The optimized PDF is downloaded.</p>', async () => {
      await this.downloadServer('linearize', {}, 'Linearizing PDF');
    });
  }
  async repair() {
    const html = this.field('Optional damaged PDF (leave empty to use the open document)', 'file', '', 'file', 'accept=".pdf,application/pdf"') +
      '<p>qpdf attempts to recover cross-reference information and rewrites the PDF structure. Repair depends on the damage; missing page content cannot be recreated. The result is downloaded.</p>';
    const el = this.S.dialog('Attempt structural repair', html, async () => {
      const file = el.querySelector('[name="file"]').files[0] || null;
      if (!file) this.S.requireDocument();
      await this.downloadServer('repair', {}, 'Repairing PDF structure', file);
    });
  }
  async validate() {
    const html = this.field('Optional PDF (leave empty to validate the open document)', 'file', '', 'file', 'accept=".pdf,application/pdf"') + '<p>Runs qpdf structural checks and reports warnings. Validation checks PDF structure; it does not certify document authenticity.</p>';
    const el = this.S.dialog('Validate PDF structure', html, async () => {
      const file = el.querySelector('[name="file"]').files[0] || null;
      if (!file) this.S.requireDocument();
      let response;
      await this.S.busy('Checking PDF structure', async job => { response = await this.server('validate', {}, job, file); });
      setTimeout(() => this.S.dialog('PDF validation report', `<div class="alert ${response.valid ? 'alert-success' : response.warnings ? 'alert-warning' : 'alert-danger'}">${response.valid ? 'qpdf reported no structural errors.' : response.warnings ? 'qpdf reported structural warnings.' : 'qpdf reported structural errors.'}</div><pre class="tools-validation-report">${this.e(response.report || 'No diagnostic output.')}</pre>`, () => {}, {wide: true, submit: 'Close'}), 50);
    });
  }
  async extractImages() {
    this.S.requireDocument();
    this.S.dialog('Extract embedded images on server', '<p>Poppler extracts original embedded image streams without rendering entire pages. Images are downloaded as a ZIP; formats can include JPEG, PNG, TIFF, JBIG2 or JPEG 2000 depending on the source. Masks may be separate files.</p>', async () => {
      await this.downloadServer('extract_images', {}, 'Extracting embedded images');
    });
  }
  async office() {
    const files = await this.S.pickFiles('.docx,.odt,.xlsx,.pptx');
    if (!files.length) return;
    await this.downloadServer('convert', {}, 'Converting Office document to PDF', files[0], true);
  }
}

class StudioUpdates {
  constructor(studio,release){
    this.S=studio;this.local=release;this.remote=null;this.pending=null;this.error='';this.downloading=false;this.downloadError='';
    this.repository='https://github.com/ziobit/zbpdfstudio';
    this.raw='https://raw.githubusercontent.com/ziobit/zbpdfstudio/main/';
    this.storageKey='pdfstudio.updates.'+location.origin+location.pathname;
    this.state={};
    try{this.state=JSON.parse(localStorage.getItem(this.storageKey)||'{}')||{};}catch{}
    if(typeof this.state!=='object'||Array.isArray(this.state))this.state={};
    try{if(this.state.manifest)this.remote=this.validateManifest(this.state.manifest);}catch{delete this.state.manifest;}
  }
  save(){try{localStorage.setItem(this.storageKey,JSON.stringify(this.state));}catch{}}
  compare(a,b){
    const left=String(a).split('.').map(Number),right=String(b).split('.').map(Number);
    for(let i=0;i<3;i++){const difference=(left[i]||0)-(right[i]||0);if(difference)return Math.sign(difference);}
    return 0;
  }
  validateManifest(value){
    const version=/^\d{1,4}\.\d{1,4}\.\d{1,4}$/;
    if(!value||typeof value!=='object'||!version.test(value.version)||typeof value.minimumPhp!=='string'||!/^\d{1,2}\.\d{1,2}(?:\.\d{1,2})?$/.test(value.minimumPhp)||!Array.isArray(value.releases)||!value.releases.length||value.releases.length>30)throw new Error('The update information is invalid.');
    const seen=new Set();
    const releases=value.releases.map(release=>{
      if(!release||!version.test(release.version)||seen.has(release.version)||typeof release.date!=='string'||!/^\d{4}-\d{2}-\d{2}$/.test(release.date)||!Array.isArray(release.changes)||!release.changes.length||release.changes.length>40||release.changes.some(change=>typeof change!=='string'||!change.trim()||change.length>2000))throw new Error('The release notes are invalid.');
      seen.add(release.version);return {version:release.version,date:release.date,changes:release.changes};
    });
    releases.sort((a,b)=>this.compare(b.version,a.version));
    if(releases[0].version!==value.version)throw new Error('The release version does not match its notes.');
    return {version:value.version,minimumPhp:value.minimumPhp,releases};
  }
  notes(releases){
    const escape=value=>this.S.escape(value);
    return releases.map(release=>`<section class="mb-4"><h3 class="h6">Version ${escape(release.version)} <small class="text-secondary ms-2">${escape(release.date)}</small></h3><ul class="small">${release.changes.map(change=>`<li class="mb-2">${escape(change)}</li>`).join('')}</ul></section>`).join('');
  }
  announceUpgrade(){
    const previous=this.state.seenVersion;
    if(!previous||(/^\d+\.\d+\.\d+$/.test(previous)&&this.compare(this.local.version,previous)>0))this.showChangelog(previous||null);
    this.state.seenVersion=this.local.version;this.save();this.updateBadge();
  }
  showChangelog(previous=null){
    const changes=previous?this.local.releases.filter(release=>this.compare(release.version,previous)>0&&this.compare(release.version,this.local.version)<=0):this.local.releases;
    const intro=previous?`<div class="alert alert-success">This installation has been upgraded from ${this.S.escape(previous)} to ${this.S.escape(this.local.version)}. Here's what changed.</div>`:`<p class="small">What's new in PDF Studio ${this.S.escape(this.local.version)}. You can reopen this history from System capabilities.</p>`;
    this.S.dialog("What's new in PDF Studio",intro+this.notes(changes),null,{wide:true,submit:false});
  }
  updateBadge(){
    const available=!!this.remote&&this.compare(this.remote.version,this.local.version)>0;
    const badge=this.S.$('#studioUpdateBadge'),button=this.S.$('#studioUpdatesButton');
    if(badge)badge.hidden=!available;
    if(button){const label=available?'PDF Studio '+this.remote.version+' is available':'Check for updates';button.title=label;button.setAttribute('aria-label',label);}
  }
  show(){
    this.S.dialog('PDF Studio updates','<div id="studioUpdatePanel" aria-live="polite"></div>',null,{wide:true,submit:false});
    this.render();this.check(true);
  }
  async check(manual=false){
    if(this.pending){try{await this.pending;}catch{}return;}
    const attempted=Number(this.state.lastAttemptAt)||0;
    if(!manual&&attempted>0&&Date.now()-attempted>=0&&Date.now()-attempted<86400000){this.updateBadge();return;}
    this.error='';this.state.lastAttemptAt=Date.now();this.save();
    // Defer the request until pending is assigned so the loading state appears.
    this.pending=Promise.resolve().then(()=>this.fetchManifest());this.render();
    try{
      this.remote=await this.pending;this.state.manifest=this.remote;this.state.lastCheckedAt=Date.now();this.save();
      this.updateBadge();
      if(!manual&&this.compare(this.remote.version,this.local.version)>0)this.S.notify('PDF Studio '+this.remote.version+' is available. Open Check for updates to read what changed.');
    }catch(error){this.error=error?.name==='AbortError'?'The update check timed out. Check your connection and try again.':(error?.message||'The update check failed.');}
    finally{this.pending=null;this.render();}
  }
  async fetchManifest(){
    const controller=new AbortController(),timer=setTimeout(()=>controller.abort(),8000);
    try{
      const response=await fetch(this.raw+'release.json',{signal:controller.signal,credentials:'omit',cache:'no-store',referrerPolicy:'no-referrer'});
      if(!response.ok)throw new Error('GitHub could not provide update information (HTTP '+response.status+'). Try again later.');
      const text=await response.text();if(text.length>65536)throw new Error('The update information is too large.');
      return this.validateManifest(JSON.parse(text));
    }finally{clearTimeout(timer);}
  }
  async download(){
    if(this.downloading||!this.remote||this.compare(this.remote.version,this.local.version)<=0)return;
    const version=this.remote.version,controller=new AbortController(),timer=setTimeout(()=>controller.abort(),20000);
    this.downloading=true;this.downloadError='';this.render();
    try{
      const response=await fetch(this.raw+'index.php',{signal:controller.signal,credentials:'omit',cache:'no-store',referrerPolicy:'no-referrer'});
      if(!response.ok)throw new Error('GitHub could not provide index.php (HTTP '+response.status+'). Try again later.');
      const source=await response.text(),published=source.match(/\bconst\s+PDFSTUDIO_VERSION\s*=\s*'(\d+\.\d+\.\d+)'\s*;/);
      if(source.length>2000000||!source.startsWith('<'+'?php')||!published)throw new Error('GitHub did not return a valid PDF Studio file. Try again later.');
      if(published[1]!==version)throw new Error('The published files are still updating. Check for updates again, then retry the download.');
      this.S.download(source,'index.php','application/x-httpd-php');
      this.S.notify('The index.php download has started. Back up your installation before replacing it.');
    }catch(error){this.downloadError=error?.name==='AbortError'?'The download timed out. Check your connection and try again.':(error?.message||'The download failed.');}
    finally{clearTimeout(timer);this.downloading=false;this.render();}
  }
  render(){
    const panel=this.S.$('#studioUpdatePanel');if(!panel)return;
    const escape=value=>this.S.escape(value),latest=this.remote;
    const available=!!latest&&this.compare(latest.version,this.local.version)>0;
    let status=this.pending?'<div class="alert alert-info"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Checking GitHub for a newer version…</div>':'';
    if(this.error)status+=`<div class="alert alert-warning">Could not check for updates: ${escape(this.error)} You can continue using PDF Studio.</div>`;
    if(latest&&!this.pending){
      status+=available?`<div class="alert alert-success">Version ${escape(latest.version)} is available.</div>`:`<div class="alert alert-success">${this.compare(this.local.version,latest.version)>0?'This installation is newer than the published version.':'You are using the latest published version.'}</div>`;
    }
    const php=this.S.server.php||'',phpMatch=php.match(/^\d+\.\d+(?:\.\d+)?/);
    const supported=!latest||!phpMatch||this.compare(phpMatch[0],latest.minimumPhp)>=0;
    const compatibility=available&&!supported?`<div class="alert alert-warning">This update needs PHP ${escape(latest.minimumPhp)} or newer. Your server reports PHP ${escape(php)}. Upgrade PHP before replacing index.php.</div>`:'';
    const changes=available?latest.releases.filter(release=>this.compare(release.version,this.local.version)>0):[];
    const instructions=available?`<h3 class="h6">How to upgrade</h3><ol class="small"><li>Back up your current index.php outside the public web directory.</li><li>Download the new index.php and replace it on your server, keeping your optional vendor directory.</li><li>Reload PDF Studio. The changes since your previous version will be shown automatically.</li></ol><p class="small">Your open document stays in this page while you download. Save it or its editable project before reloading.</p>${this.downloadError?`<div class="alert alert-warning">Could not download the update: ${escape(this.downloadError)} Your open document is unchanged.</div>`:''}<button type="button" class="btn btn-primary btn-sm me-2" data-action="download-update" ${this.downloading?'disabled':''}>${this.downloading?'Downloading…':'Download index.php'}</button><a class="btn btn-outline-secondary btn-sm" href="${this.repository}/blob/main/CHANGELOG.md" target="_blank" rel="noopener noreferrer">View changelog on GitHub</a>`:'';
    panel.innerHTML=`<p class="small">Installed version: <strong>${escape(this.local.version)}</strong>${latest?` · Latest published version: <strong>${escape(latest.version)}</strong>`:''}</p>${status}${compatibility}${changes.length?'<h3 class="h6">What changes in this update</h3>'+this.notes(changes):''}${instructions}<div class="d-flex flex-wrap gap-2 mt-3"><button type="button" class="btn btn-outline-primary btn-sm" data-action="check-updates" ${this.pending?'disabled':''}>${this.pending?'Checking…':'Check again'}</button><button type="button" class="btn btn-outline-secondary btn-sm" data-action="changelog">What's new in this installation</button></div><p class="small text-secondary mt-3 mb-0">Checks contact GitHub for version information only. No documents are sent. Automatic checks run at most once a day.${this.state.lastCheckedAt?` Last successful check: ${escape(new Date(this.state.lastCheckedAt).toLocaleString())}.`:''}</p>`;
  }
}

window.StudioLanguage = new StudioI18n(PDF_STUDIO_BOOT.translations||{},PDF_STUDIO_BOOT.language||'it');
StudioLanguage.start();
window.Studio = new PDFStudio();
window.addEventListener('unhandledrejection',event=>{event.preventDefault();Studio.error(event.reason);});
Studio.init().catch(error=>{Studio.error(error);document.querySelector('#loadingBanner').textContent='Startup failed: '+error.message;});
  </script>
</body>
</html>
