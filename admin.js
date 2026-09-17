jQuery(document).ready(function($) {
    $('#regenerate-favicons').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var statusDiv = $('#regenerate-status');
        var originalText = button.html();
        
        // Button deaktivieren und Loading-Zustand anzeigen
        button.prop('disabled', true);
        button.html('<span class="dashicons dashicons-update spin" style="margin-top: 3px;"></span> Regeneriere...');
        statusDiv.html('');
        
        // AJAX Request
        $.ajax({
            url: wpAutofavicon.ajaxurl,
            type: 'POST',
            data: {
                action: 'regenerate_favicons',
                nonce: wpAutofavicon.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<div class="notice notice-success inline"><p><strong>Erfolg:</strong> ' + response.data + '</p></div>');
                } else {
                    statusDiv.html('<div class="notice notice-error inline"><p><strong>Fehler:</strong> ' + response.data + '</p></div>');
                }
            },
            error: function() {
                statusDiv.html('<div class="notice notice-error inline"><p><strong>Fehler:</strong> Netzwerkfehler beim Regenerieren der Favicons.</p></div>');
            },
            complete: function() {
                // Button wieder aktivieren
                button.prop('disabled', false);
                button.html(originalText);
                
                // Erfolgs-/Fehlermeldung nach 5 Sekunden ausblenden
                setTimeout(function() {
                    statusDiv.fadeOut();
                }, 5000);
            }
        });
    });
});

// CSS für Spin-Animation
if (!document.getElementById('wp-autofavicon-admin-css')) {
    var css = document.createElement('style');
    css.id = 'wp-autofavicon-admin-css';
    css.innerHTML = `
        .spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .notice.inline {
            margin: 0;
            padding: 10px 15px;
        }
    `;
    document.head.appendChild(css);
}