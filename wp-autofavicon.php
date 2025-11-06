<?php
/**
 * Plugin Name: WP AutoFavicon
 * Plugin URI: https://github.com/janstieler/wp-autofavicon
 * Description: Automatisch generiertes SVG-Favicon mit Dark-Mode-Unterstützung
 * Version: v1.1.3
 * Author: Kommunikationsdesign Jan-Frederik Stieler
 * Author URI: https://janstieler.de
 * License: MIT
 * Text Domain: wp-autofavicon
 * Requires at least: 5.0
 * Tested up to: 6.8.3
 * Requires PHP: 7.4
 */

// Verhindere direkten Zugriff
if (!defined('ABSPATH')) {
    exit;
}

class WP_AutoFavicon
{

    private $options;

    public function __construct()
    {
        // Standard-Optionen
        $this->options = array(
            'text' => substr(get_bloginfo('name'), 0, 1),
            'color' => '#000000',
            'color_dark' => '#ffffff',
            'text_color' => '#ffffff',
            'text_color_dark' => '#000000',
        );

        // Hooks
        add_action('wp_head', array($this, 'add_favicon_tags'));
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('init', array($this, 'add_favicon_endpoint'));
        add_action('template_redirect', array($this, 'serve_favicon'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_action_links'));
        
        // Textdomain laden
        add_action('init', array($this, 'load_textdomain'));

        // Update-System initialisieren (nach init, um Textdomain-Probleme zu vermeiden)
        add_action('init', array($this, 'init_updater'));
    }

    /**
     * Lädt das Textdomain für Übersetzungen
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('wp-autofavicon', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Initialisiert das Update-System
     */
    public function init_updater()
    {
        new WP_AutoFavicon_Updater(__FILE__, get_plugin_data(__FILE__)['Version'], 'janstieler', 'wp-autofavicon');
    }

    /**
     * Holt die Plugin-Optionen
     */
    private function get_options()
    {
        $saved_options = get_option('wp_autofavicon_settings', array());
        return wp_parse_args($saved_options, $this->options);
    }

    /**
     * Fügt die Favicon-Tags zum <head> hinzu
     */
    public function add_favicon_tags()
    {
        $home_url = home_url('/');

        echo "\n<!-- WP AutoFavicon -->\n";
        echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($home_url . 'autofavicon.svg') . '">' . "\n";
        echo '<link rel="alternate icon" type="image/svg+xml" href="' . esc_url($home_url . 'autofavicon.svg') . '">' . "\n";
        echo '<!-- /WP AutoFavicon -->' . "\n";
    }

    /**
     * Registriert einen benutzerdefinierten Endpoint für das Favicon
     */
    public function add_favicon_endpoint()
    {
        add_rewrite_rule('^autofavicon\.svg$', 'index.php?autofavicon=1', 'top');

        // Füge Query-Var hinzu
        add_filter('query_vars', function ($vars) {
            $vars[] = 'autofavicon';
            return $vars;
        });
    }

    /**
     * Liefert das SVG-Favicon aus
     */
    public function serve_favicon()
    {
        if (get_query_var('autofavicon')) {
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=31536000');

            echo $this->generate_svg();
            exit;
        }
    }

    /**
     * Generiert das SVG-Favicon
     */
    private function generate_svg()
    {
        $options = $this->get_options();

        $text = esc_attr($options['text']);
        $color = esc_attr($options['color']);
        $color_dark = esc_attr($options['color_dark']);
        $text_color = esc_attr($options['text_color']);
        $text_color_dark = esc_attr($options['text_color_dark']);

        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">';
        $svg .= '<style>';
        $svg .= 'rect { fill: ' . $color . '; }';
        $svg .= 'text { fill: ' . $text_color . '; font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 60px; font-weight: 700; }';
        $svg .= '@media (prefers-color-scheme: dark) {';
        $svg .= '  rect { fill: ' . $color_dark . '; }';
        $svg .= '  text { fill: ' . $text_color_dark . '; }';
        $svg .= '}';
        $svg .= '</style>';
        $svg .= '<rect width="100" height="100" rx="20" />';
        $svg .= '<text x="50%" y="50%" text-anchor="middle" dominant-baseline="central">' . $text . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Fügt die Einstellungsseite hinzu
     */
    public function add_settings_page()
    {
        add_options_page(
            'AutoFavicon Einstellungen',
            'AutoFavicon',
            'manage_options',
            'wp-autofavicon',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Registriert die Einstellungen
     */
    public function register_settings()
    {
        register_setting('wp_autofavicon_settings', 'wp_autofavicon_settings');

        add_settings_section(
            'wp_autofavicon_main',
            'AutoFavicon Konfiguration',
            array($this, 'render_section_info'),
            'wp-autofavicon'
        );

        add_settings_field(
            'text',
            'Text',
            array($this, 'render_text_field'),
            'wp-autofavicon',
            'wp_autofavicon_main'
        );

        add_settings_field(
            'color',
            'Hintergrundfarbe (Hell)',
            array($this, 'render_color_field'),
            'wp-autofavicon',
            'wp_autofavicon_main',
            array('field' => 'color')
        );

        add_settings_field(
            'color_dark',
            'Hintergrundfarbe (Dunkel)',
            array($this, 'render_color_field'),
            'wp-autofavicon',
            'wp_autofavicon_main',
            array('field' => 'color_dark')
        );

        add_settings_field(
            'text_color',
            'Textfarbe (Hell)',
            array($this, 'render_color_field'),
            'wp-autofavicon',
            'wp_autofavicon_main',
            array('field' => 'text_color')
        );

        add_settings_field(
            'text_color_dark',
            'Textfarbe (Dunkel)',
            array($this, 'render_color_field'),
            'wp-autofavicon',
            'wp_autofavicon_main',
            array('field' => 'text_color_dark')
        );
    }

    /**
     * Rendert die Sektion-Info
     */
    public function render_section_info()
    {
        echo '<p>Konfiguriere dein automatisch generiertes SVG-Favicon. Das Favicon passt sich automatisch dem Dark-Mode des Browsers an.</p>';
    }

    /**
     * Rendert das Text-Feld
     */
    public function render_text_field()
    {
        $options = $this->get_options();
        echo '<input type="text" name="wp_autofavicon_settings[text]" value="' . esc_attr($options['text']) . '" maxlength="2" />';
        echo '<p class="description">Ein oder zwei Zeichen für das Favicon (Standard: Erster Buchstabe des Blog-Namens)</p>';
    }

    /**
     * Rendert ein Farbfeld
     */
    public function render_color_field($args)
    {
        $options = $this->get_options();
        $field = $args['field'];
        echo '<input type="color" name="wp_autofavicon_settings[' . esc_attr($field) . ']" value="' . esc_attr($options[$field]) . '" />';
    }

    /**
     * Rendert die Einstellungsseite
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div style="margin: 20px 0; padding: 15px; background: #fff; border-left: 4px solid #2271b1;">
                <h3>Vorschau</h3>
                <div style="display: flex; gap: 20px; align-items: center;">
                    <div style="flex: 0 0 auto;">
                        <p style="margin: 0 0 10px 0;"><strong>Hell-Modus:</strong></p>
                        <div style="width: 100px; height: 100px; border: 1px solid #ddd;">
                            <?php echo $this->generate_svg_preview('light'); ?>
                        </div>
                    </div>
                    <div style="flex: 0 0 auto;">
                        <p style="margin: 0 0 10px 0;"><strong>Dunkel-Modus:</strong></p>
                        <div style="width: 100px; height: 100px; border: 1px solid #ddd; background: #1e1e1e;">
                            <?php echo $this->generate_svg_preview('dark'); ?>
                        </div>
                    </div>
                </div>
                <p style="margin-top: 15px;">
                    <a href="<?php echo esc_url(home_url('/autofavicon.svg')); ?>" target="_blank">
                        Favicon direkt anzeigen →
                    </a>
                </p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('wp_autofavicon_settings');
                do_settings_sections('wp-autofavicon');
                submit_button();
                ?>
            </form>

            <div style="margin-top: 20px; padding: 15px; background: #fff; border-left: 4px solid #72aee6;">
                <h3>Verwendung</h3>
                <p>Das Plugin fügt automatisch die Favicon-Tags zu deinem <code>&lt;head&gt;</code> hinzu. Du musst nichts weiter tun!</p>
                <p>Das Favicon wird unter dieser URL bereitgestellt:<br>
                    <code><?php echo esc_html(home_url('/autofavicon.svg')); ?></code>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Generiert eine Vorschau des SVG (ohne Media-Query für die Admin-Seite)
     */
    private function generate_svg_preview($mode = 'light')
    {
        $options = $this->get_options();

        $text = esc_attr($options['text']);
        $color = ($mode === 'dark') ? esc_attr($options['color_dark']) : esc_attr($options['color']);
        $text_color = ($mode === 'dark') ? esc_attr($options['text_color_dark']) : esc_attr($options['text_color']);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">';
        $svg .= '<rect width="100" height="100" rx="20" fill="' . $color . '" />';
        $svg .= '<text x="50%" y="50%" text-anchor="middle" dominant-baseline="central" fill="' . $text_color . '" font-family="system-ui, -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif" font-size="60" font-weight="700">' . $text . '</text>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Fügt Einstellungen-Link in der Plugin-Übersicht hinzu
     */
    public function add_plugin_action_links($links)
    {
        $settings_link = '<a href="' . admin_url('options-general.php?page=wp-autofavicon') . '">Einstellungen</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

/**
 * Update-Checker für GitHub-basierte Updates
 */
class WP_AutoFavicon_Updater
{
    private $file;
    private $plugin;
    private $basename;
    private $active;
    private $username;
    private $repository;
    private $authorize_token;
    private $github_response;

    public function __construct($file, $version, $username, $repository, $authorize_token = '')
    {
        $this->file = $file;
        $this->plugin = get_plugin_data($this->file);
        $this->basename = plugin_basename($this->file);
        $this->active = is_plugin_active($this->basename);
        $this->username = $username;
        $this->repository = $repository;
        $this->authorize_token = $authorize_token;

        add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
        add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);

        // Auto-Update Unterstützung
        add_filter('auto_update_plugin', array($this, 'enable_auto_update'), 10, 2);
        add_filter('plugin_auto_update_setting_html', array($this, 'auto_update_setting_html'), 10, 3);

        // Debug-Funktionen (nur für Administratoren)
        if (current_user_can('manage_options')) {
            add_action('wp_ajax_check_autofavicon_updates', array($this, 'ajax_check_updates'));
            add_action('admin_footer', array($this, 'add_debug_script'));
        }
    }

    public function modify_transient($transient)
    {
        if (property_exists($transient, 'checked')) {
            if ($checked = $transient->checked) {
                $this->get_repository_info();
                $github_version = $this->normalize_version($this->github_response['tag_name']);
                $current_version = $this->normalize_version($checked[$this->basename]);
                $out_of_date = version_compare($github_version, $current_version, 'gt');
                
                // Debug-Log (nur für Admins)
                if (current_user_can('manage_options')) {
                    error_log("WP AutoFavicon Update Check:");
                    error_log("- GitHub Version (raw): " . $this->github_response['tag_name']);
                    error_log("- GitHub Version (normalized): " . $github_version);
                    error_log("- Current Version (raw): " . $checked[$this->basename]);
                    error_log("- Current Version (normalized): " . $current_version);
                    error_log("- Out of date: " . ($out_of_date ? 'true' : 'false'));
                }
                
                if ($out_of_date) {
                    $new_files = $this->github_response['zipball_url'];
                    $slug = current(explode('/', $this->basename));
                    $plugin = array(
                        'url' => $this->plugin["PluginURI"],
                        'slug' => $slug,
                        'package' => $new_files,
                        'new_version' => $github_version
                    );
                    $transient->response[$this->basename] = (object) $plugin;
                } else {
                    // Entferne Update-Benachrichtigung wenn lokale Version neuer ist
                    if (isset($transient->response[$this->basename])) {
                        unset($transient->response[$this->basename]);
                    }
                }
            }
        }
        return $transient;
    }

    public function plugin_popup($result, $action, $args)
    {
        if (!empty($args->slug)) {
            if ($args->slug == current(explode('/', $this->basename))) {
                $this->get_repository_info();
                $plugin = array(
                    'name' => $this->plugin["Name"],
                    'slug' => $this->basename,
                    'requires' => '5.0',
                    'tested' => '6.8.3',
                    'requires_php' => '7.4',
                    'rating' => '100.0',
                    'num_ratings' => '10823',
                    'downloaded' => '14249',
                    'added' => '2024-01-01',
                    'version' => $this->normalize_version($this->github_response['tag_name']),
                    'author' => $this->plugin["AuthorName"],
                    'author_profile' => $this->plugin["AuthorURI"],
                    'last_updated' => $this->github_response['published_at'],
                    'homepage' => $this->plugin["PluginURI"],
                    'short_description' => $this->plugin["Description"],
                    'sections' => array(
                        'Description' => $this->plugin["Description"],
                        'Updates' => $this->github_response['body'],
                    ),
                    'download_link' => $this->github_response['zipball_url']
                );
                return (object) $plugin;
            }
        }
        return $result;
    }

    public function after_install($response, $hook_extra, $result)
    {
        global $wp_filesystem;
        $install_directory = plugin_dir_path($this->file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;
        if ($this->active) {
            activate_plugin($this->basename);
        }
        return $result;
    }

    private function get_repository_info()
    {
        if (is_null($this->github_response)) {
            $request_uri = sprintf('https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository);
            if ($this->authorize_token) {
                $request_uri = add_query_arg('access_token', $this->authorize_token, $request_uri);
            }
            $response = json_decode(wp_remote_retrieve_body(wp_remote_get($request_uri)), true);
            if (is_array($response)) {
                $response = current($response);
            }
            if ($this->authorize_token) {
                $response['zipball_url'] = add_query_arg('access_token', $this->authorize_token, $response['zipball_url']);
            }
            $this->github_response = $response;
        }
    }

    public function ajax_check_updates()
    {
        $this->get_repository_info();
        $current_version_raw = $this->plugin['Version'];
        $current_version = $this->normalize_version($current_version_raw);
        $latest_version_raw = $this->github_response['tag_name'] ?? 'Keine Releases gefunden';
        $latest_version = $this->normalize_version($latest_version_raw);

        $response = array(
            'current_version_raw' => $current_version_raw,
            'current_version_normalized' => $current_version,
            'latest_version_raw' => $latest_version_raw,
            'latest_version_normalized' => $latest_version,
            'repository' => $this->username . '/' . $this->repository,
            'update_available' => version_compare($latest_version, $current_version, 'gt'),
            'github_response' => $this->github_response
        );

        wp_send_json($response);
    }

    private function normalize_version($version)
    {
        // Entferne "v" Präfix und andere nicht-numerische Zeichen am Anfang
        return ltrim($version, 'vV');
    }

    public function add_debug_script()
    {
        if (get_current_screen()->id === 'plugins') {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    // Füge Debug-Button zur Plugin-Zeile hinzu
                    var pluginRow = $('tr[data-slug="wp-autofavicon"]');
                    if (pluginRow.length) {
                        var debugButton = '<a href="#" id="check-autofavicon-updates" style="margin-left: 10px;">Updates prüfen</a>';
                        pluginRow.find('.plugin-version-author-uri').append(' | ' + debugButton);
                    }

                    $('#check-autofavicon-updates').on('click', function (e) {
                        e.preventDefault();
                        var button = $(this);
                        button.text('Prüfe...');

                        $.post(ajaxurl, {
                            action: 'check_autofavicon_updates'
                        }, function (response) {
                            console.log('Update Check Response:', response);

                            var message = 'Aktuelle Version (raw): ' + response.current_version_raw +
                                '\nAktuelle Version (normalized): ' + response.current_version_normalized +
                                '\nGitHub Version (raw): ' + response.latest_version_raw +
                                '\nGitHub Version (normalized): ' + response.latest_version_normalized +
                                '\nUpdate verfügbar: ' + (response.update_available ? 'Ja' : 'Nein');

                            alert(message);
                            button.text('Updates prüfen');

                            if (response.update_available) {
                                location.reload();
                            }
                        }).fail(function () {
                            alert('Fehler beim Prüfen der Updates');
                            button.text('Updates prüfen');
                        });
                    });
                });
            </script>
            <?php
        }
    }

    public function enable_auto_update($update, $item)
    {
        // Ermögliche Auto-Updates für unser Plugin
        if (isset($item->plugin) && $item->plugin === $this->basename) {
            return true;
        }
        return $update;
    }

    public function auto_update_setting_html($html, $plugin_file, $plugin_data)
    {
        // Füge Auto-Update Toggle für unser Plugin hinzu
        if ($plugin_file === $this->basename) {
            $auto_updates_enabled = in_array($plugin_file, (array) get_site_option('auto_update_plugins', array()));

            if ($auto_updates_enabled) {
                $html = '<a href="' . wp_nonce_url(admin_url('plugins.php?action=disable-auto-update&amp;plugin=' . urlencode($plugin_file)), 'updates') . '" class="plugin-auto-update-disable" aria-label="' . esc_attr('Disable auto-updates') . '">Disable auto-updates</a>';
            } else {
                $html = '<a href="' . wp_nonce_url(admin_url('plugins.php?action=enable-auto-update&amp;plugin=' . urlencode($plugin_file)), 'updates') . '" class="plugin-auto-update-enable" aria-label="' . esc_attr('Enable auto-updates') . '">Enable auto-updates</a>';
            }
        }
        return $html;
    }
}

// Plugin initialisieren
function wp_autofavicon_init()
{
    new WP_AutoFavicon();
}
add_action('plugins_loaded', 'wp_autofavicon_init');

// Aktivierungs-Hook für die Rewrite-Rules
register_activation_hook(__FILE__, function () {
    wp_autofavicon_init();
    flush_rewrite_rules();
});

// Deaktivierungs-Hook
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});
