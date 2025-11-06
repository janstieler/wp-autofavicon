# WP AutoFavicon
[![en](https://img.shields.io/badge/lang-en-red.svg)](https://github.com/janstieler/wp-autofavicon/blob/main/README.md)

Ein WordPress-Plugin, das automatisch ein SVG-Favicon mit Dark-Mode-Unterstützung generiert. Portiert vom [Kirby AutoFavicon Plugin](https://github.com/medienbaecker/kirby-autofavicon).

## Features

- 🎨 Automatisch generiertes SVG-Favicon
- 🌓 Dark-Mode-Unterstützung (reagiert auf `prefers-color-scheme`)
- ⚙️ Einfache Konfiguration über WordPress-Einstellungen
- 🚀 Keine zusätzlichen Dateien notwendig
- 💾 SVG wird dynamisch generiert und mit Cache-Headers ausgeliefert

## Installation

### Manuelle Installation

1. Lade die Datei `wp-autofavicon.php` in dein WordPress `wp-content/plugins/` Verzeichnis hoch
2. Aktiviere das Plugin über das WordPress Admin-Panel unter "Plugins"
3. Gehe zu "Einstellungen" → "AutoFavicon" um das Plugin zu konfigurieren

### Installation via ZIP

1. Erstelle einen Ordner `wp-autofavicon`
2. Lege die Datei `wp-autofavicon.php` in diesen Ordner
3. Komprimiere den Ordner als ZIP-Datei
4. Lade das ZIP über das WordPress Admin-Panel hoch ("Plugins" → "Installieren" → "Plugin hochladen")

## Konfiguration

Nach der Aktivierung findest du die Einstellungen unter **Einstellungen → AutoFavicon**.

### Verfügbare Optionen

- **Text**: Ein oder zwei Zeichen für das Favicon (Standard: Erster Buchstabe des Blog-Namens)
- **Hintergrundfarbe (Hell)**: Hintergrundfarbe für den Hell-Modus (Standard: `#000000`)
- **Hintergrundfarbe (Dunkel)**: Hintergrundfarbe für den Dunkel-Modus (Standard: `#ffffff`)
- **Textfarbe (Hell)**: Textfarbe für den Hell-Modus (Standard: `#ffffff`)
- **Textfarbe (Dunkel)**: Textfarbe für den Dunkel-Modus (Standard: `#000000`)

### Standard-Verhalten

Ohne Konfiguration nutzt das Plugin:
- Den ersten Buchstaben deines Blog-Namens als Text
- Schwarzen Hintergrund mit weißem Text im Hell-Modus
- Weißen Hintergrund mit schwarzem Text im Dunkel-Modus

## Verwendung

Das Plugin funktioniert automatisch nach der Aktivierung. Es fügt die notwendigen `<link>`-Tags zu deinem WordPress `<head>` hinzu:

```html
<link rel="icon" type="image/svg+xml" href="https://deine-website.de/autofavicon.svg">
<link rel="alternate icon" type="image/svg+xml" href="https://deine-website.de/autofavicon.svg">
```

Das SVG-Favicon wird unter folgender URL bereitgestellt:
```
https://deine-website.de/autofavicon.svg
```

## Technische Details

### SVG-Struktur

Das Plugin generiert ein SVG mit folgender Struktur:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
  <style>
    rect { fill: #000000; }
    text { fill: #ffffff; font-family: system-ui, sans-serif; font-size: 60px; font-weight: 700; }
    @media (prefers-color-scheme: dark) {
      rect { fill: #ffffff; }
      text { fill: #000000; }
    }
  </style>
  <rect width="100" height="100" rx="20" />
  <text x="50%" y="50%" text-anchor="middle" dominant-baseline="central">A</text>
</svg>
```

### Browser-Kompatibilität

- ✅ Chrome/Edge (80+)
- ✅ Firefox (67+)
- ✅ Safari (13+)
- ✅ Opera (67+)

Alle modernen Browser unterstützen SVG-Favicons und die `prefers-color-scheme` Media-Query.

## Programmierbare Anpassung

Du kannst die Einstellungen auch programmatisch über WordPress-Optionen ändern:

```php
update_option('wp_autofavicon_settings', array(
    'text' => 'WP',
    'color' => '#1e3a8a',
    'color_dark' => '#3b82f6',
    'text_color' => '#ffffff',
    'text_color_dark' => '#ffffff',
));
```

## Nach dem Update
- bitte `wp rewrite flush` ausführen um die favicon Rewrite-Rules neu zu regestrieren!

## Unterschiede zum Kirby-Original

- Verwendet WordPress Rewrite-Rules statt Kirby-Routen
- Einstellungen über WordPress-Optionen statt `config.php`
- Admin-Oberfläche mit Vorschau im WordPress-Backend
- Automatische Integration in `wp_head` statt Snippet-Aufruf

## Lizenz

MIT License - wie das Original Kirby AutoFavicon Plugin

## Credits

- Original Kirby Plugin: [medienbaecker/kirby-autofavicon](https://github.com/medienbaecker/kirby-autofavicon)
- Entwickelt von Thomas Günther
- WordPress-Port: Jan-Frederik Stieler

## Support

Bei Problemen oder Fragen öffne bitte ein Issue auf GitHub.

## Changelog

### Version 1.0.0
- Erste Version
- Portierung der Basis-Funktionalität von Kirby AutoFavicon
- WordPress Admin-Interface
- Dark-Mode-Unterstützung
- Vorschau-Funktion im Admin-Bereich

- Hinzufügen von Autoupdate zum Plugin

### Version 1.1.1
- Behebung des Problems, dass für Autoupdate vX.X.X statt X.X.X das Release-Tag verwendet wurde.

### Version 1.1.2
- Die Informationen zur neuen Version werden im Plugin-Dashboard nicht angezeigt, wenn die installierte Version neuer ist als die Github-Version.

### Version 1.1.3
- Behebung einiger Warnungen, wenn das Plugin über WP CLI installiert wird

### Version 1.1.4
- Änderung des Dateinamens von autofavicon.svg zu favicon.svg

### Version 1.1.5
- Änderung der Sprachzeichenfolge zur Kernversion für automatische Updates
 
### Version 1.1.6
- Änderung der Umschreibungsregeln für favicon.svg

### Version 1.1.7
- Behebung eines Problems mit WP CLI und des Detail-Links für Github

### Version 1.1.8
- Hinzufügen von Debug-Funktionen und des Detail-Links

### Version 1.2.0
- Hinzufügen von PNG und ico Favicon

### Version 1.2.1
- Die Templateschrift für PNGs nutzen
- Button hinzugefügt um die Favicons zu erneuern

## Zu erledigen
- ~~Generieren von PNG-Favicons~~
- Hinzufügen von Composer für die Installation
- Hinzufügen des Plugins zu https://wordpress.org/plugins/

