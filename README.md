# WP AutoFavicon
[![Badge](https://forthebadge.com/api/badges/generate?primaryLabel=Lang&secondaryLabel=EN&primaryBGColor=%23555555&secondaryBGColor=%23D05A45)](https://github.com/janstieler/wp-autofavicon/blob/main/README.md)
[![Badge](https://forthebadge.com/api/badges/generate?primaryLabel=Lang&secondaryLabel=DE&primaryBGColor=%23555555&secondaryBGColor=%23D2AA26)](https://github.com/janstieler/wp-autofavicon/blob/main/README.de.md)

A WordPress plugin that automatically generates an SVG favicon with dark mode support. Ported from the [Kirby AutoFavicon Plugin](https://github.com/medienbaecker/kirby-autofavicon).

## Features

- 🎨 Automatically generated SVG favicon
- 🌓 Dark mode support (responds to `prefers-colour-scheme`)
- ⚙️ Easy configuration via WordPress settings
- 🚀 No additional files required
- 💾 SVG is dynamically generated and delivered with cache headers

## Installation

### Manual installation

1. Upload the file `wp-autofavicon.php` to your WordPress `wp-content/plugins/` directory
2. Activate the plugin via the WordPress admin panel under ‘Plugins’
3. Go to ‘Settings’ → ‘AutoFavicon’ to configure the plugin

### Installation via ZIP

1. Create a folder called `wp-autofavicon`
2. Place the file `wp-autofavicon.php` in this folder
3. Compress the folder as a ZIP file
4. Upload the ZIP file via the WordPress admin panel (‘Plugins’ → “Install” → ‘Upload plugin’)

## Configuration

After activation, you will find the settings under **Settings → AutoFavicon**.

### Available options

- **Text**: One or two characters for the favicon (default: first letter of the blog name)
- **Background colour (light)**: Background colour for light mode (default: `#000000`)
- **Background colour (dark)**: Background colour for dark mode (default: `#ffffff`)
- **Text colour (light)**: Text colour for light mode (default: `#ffffff`)
- **Text colour (dark)**: Text colour for dark mode (default: `#000000`)

### Default behaviour

Without configuration, the plugin uses:
- The first letter of your blog name as text
- Black background with white text in light mode
- White background with black text in dark mode

## Usage

The plugin works automatically after activation. It adds the necessary `<link>` tags to your WordPress `<head>`:

```html
<link rel="icon" type="image/svg+xml" href="https://deine-website.de/autofavicon.svg">
<link rel="alternate icon" type="image/svg+xml" href="https://deine-website.de/autofavicon.svg">
```

The SVG favicon is provided at the following URL:
```
https://deine-website.de/autofavicon.svg
```

## Technical details

### SVG structure

The plugin generates an SVG with the following structure:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
  <style>
    rect { fill: #000000; }
    text { fill: #ffffff; font-family: system-ui, sans-serif; font-size: 60px; font-weight: 700; }
    @media (prefers-colour-scheme: dark) {
      rect { fill: #ffffff; }
      text { fill: #000000; }
    }
  </style>
  <rect width="100" height="100" rx="20" />
  <text x="50%" y="50%" text-anchor=‘middle’ dominant-baseline=‘central’>A</text>
</svg>
```

### Browser compatibility

- ✅ Chrome/Edge (80+)
- ✅ Firefox (67+)
- ✅ Safari (13+)
- ✅ Opera (67+)

All modern browsers support SVG favicons and the `prefers-color-scheme` media query.

## Programmable customisation

You can also change the settings programmatically via WordPress options:

```php
update_option(“wp_autofavicon_settings”, array(
    “text” => “WP”,
    “colour” => “#1e3a8a”,
    “colour_dark” => “#3b82f6”,
    “text_colour” => “#ffffff”,
    “text_colour_dark” => “#ffffff”,
));
```

## After the update
- Please run `wp rewrite flush` to re-register the favicon rewrite rules!

## Differences from the original Kirby plugin

- Uses WordPress rewrite rules instead of Kirby routes
- Settings via WordPress options instead of `config.php`
- Admin interface with preview in the WordPress backend
- Automatic integration into `wp_head` instead of snippet call

## Licence

MIT Licence - like the original Kirby AutoFavicon plugin

## Credits

- Original Kirby plugin: [medienbaecker/kirby-autofavicon](https://github.com/medienbaecker/kirby-autofavicon)
- Developed by Thomas Günther
- WordPress port: Jan-Frederik Stieler

## Support

If you have any problems or questions, please open an issue on GitHub.

## To do
- ~~Generate PNG favicons~~
- Add Composer for installation
- Add the plugin to https://wordpress.org/plugins/

## Changelog

### Version 1.0.0
- First version
- Porting of the basic functionality of Kirby AutoFavicon
- WordPress admin interface
- Dark mode support
- Preview function in the admin area

### Version 1.1.0
- Adding autoupdate to the plugin

### Version 1.1.1
- Fix using release tag for autoupdate vX.X.X instead of X.X.X

### Version 1.1.2
- Do not show the new version information on the plugin dashboard if the installed version is newer than the GitHub version

### Version 1.1.3
- Fix some warnings if the plugin is installed via WP CLI

### Version 1.1.4
- Change the autofavicon.svg filename to favicon.svg

### Version 1.1.5
- change the language string to core version for automatic updates
 
### Version 1.1.6
- change rewrite rules for the favicon.svg

### Version 1.1.7
- fix wp cli problem and details link for github

### Version 1.1.8
- Add debug functions and the details link

### Version 1.2.0
- Add  PNG und ico Favicon functionallity

### Version 1.2.1
- Use the template font for PNGs
- Added button to renew the favicons

### Version 1.2.2
- Fallback mechanism for write permission issues added
- Upload directory as alternative storage location
- PHP precision warning in imagettftext() fixed
- Improved error handling and logging