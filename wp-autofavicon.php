<?php
/**
 * Plugin Name: WP AutoFavicon
 * Plugin URI: https://github.com/yourusername/wp-autofavicon
 * Description: Automatisch generiertes SVG-Favicon mit Dark-Mode-Unterstützung - portiert von Kirby AutoFavicon
 * Version: 1.0.0
 * Author: Dein Name
 * Author URI: https://deine-website.de
 * License: MIT
 * Text Domain: wp-autofavicon
 */

// Verhindere direkten Zugriff
if (!defined('ABSPATH')) {
    exit;
}

class WP_AutoFavicon {
    
    private $options;
    
    public function __construct() {
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
    }
    
    /**
     * Holt die Plugin-Optionen
     */
    private function get_options() {
        $saved_options = get_option('wp_autofavicon_settings', array());
        return wp_parse_args($saved_options, $this->options);
    }
    
    /**
     * Fügt die Favicon-Tags zum <head> hinzu
     */
    public function add_favicon_tags() {
        $home_url = home_url('/');
        
        echo "\n<!-- WP AutoFavicon -->\n";
        echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($home_url . 'autofavicon.svg') . '">' . "\n";
        echo '<link rel="alternate icon" type="image/svg+xml" href="' . esc_url($home_url . 'autofavicon.svg') . '">' . "\n";
        echo '<!-- /WP AutoFavicon -->' . "\n";
    }
    
    /**
     * Registriert einen benutzerdefinierten Endpoint für das Favicon
     */
    public function add_favicon_endpoint() {
        add_rewrite_rule('^autofavicon\.svg$', 'index.php?autofavicon=1', 'top');
        
        // Füge Query-Var hinzu
        add_filter('query_vars', function($vars) {
            $vars[] = 'autofavicon';
            return $vars;
        });
    }
    
    /**
     * Liefert das SVG-Favicon aus
     */
    public function serve_favicon() {
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
    private function generate_svg() {
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
    public function add_settings_page() {
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
    public function register_settings() {
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
    public function render_section_info() {
        echo '<p>Konfiguriere dein automatisch generiertes SVG-Favicon. Das Favicon passt sich automatisch dem Dark-Mode des Browsers an.</p>';
    }
    
    /**
     * Rendert das Text-Feld
     */
    public function render_text_field() {
        $options = $this->get_options();
        echo '<input type="text" name="wp_autofavicon_settings[text]" value="' . esc_attr($options['text']) . '" maxlength="2" />';
        echo '<p class="description">Ein oder zwei Zeichen für das Favicon (Standard: Erster Buchstabe des Blog-Namens)</p>';
    }
    
    /**
     * Rendert ein Farbfeld
     */
    public function render_color_field($args) {
        $options = $this->get_options();
        $field = $args['field'];
        echo '<input type="color" name="wp_autofavicon_settings[' . esc_attr($field) . ']" value="' . esc_attr($options[$field]) . '" />';
    }
    
    /**
     * Rendert die Einstellungsseite
     */
    public function render_settings_page() {
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
                <code><?php echo esc_html(home_url('/autofavicon.svg')); ?></code></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Generiert eine Vorschau des SVG (ohne Media-Query für die Admin-Seite)
     */
    private function generate_svg_preview($mode = 'light') {
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
}

// Plugin initialisieren
function wp_autofavicon_init() {
    new WP_AutoFavicon();
}
add_action('plugins_loaded', 'wp_autofavicon_init');

// Aktivierungs-Hook für die Rewrite-Rules
register_activation_hook(__FILE__, function() {
    wp_autofavicon_init();
    flush_rewrite_rules();
});

// Deaktivierungs-Hook
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
