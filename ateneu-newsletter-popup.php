<?php
/**
 * Plugin Name: Newsletter Popup
 * Description: Mostra un popup de subscripció a la newsletter integrat amb Benchmark Email. Configurable des d'una sola pantalla.
 * Version: 1.1.2
 * Author: Creagia
 * License: GPL-2.0+
 * Text Domain: ateneu-newsletter-popup
 *
 * Changelog:
 *  1.1.2 - Versió de prova del sistema d'auto-actualització.
 *  1.1.1 - Auto-actualització des de GitHub (plugin-update-checker).
 *  1.1.0 - El popup (script de Benchmark + cookie bm_popup_shown) NO es carrega
 *          fins que l'usuari accepta la categoria de màrqueting a Complianz
 *          (compliment RGPD/LSSI). Si no hi ha gestor de consentiment, manté
 *          el comportament anterior.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ANP_VERSION', '1.1.2' );
define( 'ANP_PLUGIN_FILE', __FILE__ );
define( 'ANP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ANP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ANP_OPTION_KEY', 'anp_settings' );

/* --------------------------------------------------------------------------
 * Auto-actualització des de GitHub (plugin-update-checker)
 * -------------------------------------------------------------------------- */
if ( file_exists( ANP_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php' ) ) {
    require_once ANP_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
    $anp_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/prodelpe/ateneu-newsletter-popup/',
        ANP_PLUGIN_FILE,
        'ateneu-newsletter-popup'
    );
    // Fer servir el zip adjunt a la release (estructura de carpeta correcta).
    $anp_update_checker->getVcsApi()->enableReleaseAssets();
}

/**
 * Retorna les opcions del plugin amb defaults aplicats.
 */
function anp_get_settings() {
    $defaults = array(
        'enabled'           => 0,
        'benchmark_script'  => '',
        'cookie_days'       => 30,
    );
    $saved = get_option( ANP_OPTION_KEY, array() );
    return wp_parse_args( $saved, $defaults );
}

/**
 * Extreu la URL del form de Benchmark (lbformnew.js) del codi enganxat.
 * Retorna la URL o null si no la troba.
 */
function anp_extract_benchmark_url( $script ) {
    if ( empty( $script ) ) {
        return null;
    }
    if ( preg_match( '#src\s*=\s*[\'"]([^\'"]*lbformnew\.js[^\'"]*)[\'"]#i', $script, $m ) ) {
        return $m[1];
    }
    return null;
}

/**
 * Comprova si es pot carregar l'script de Benchmark (tercer) i posar-ne la cookie.
 *
 * RGPD/LSSI: cap script de tercer ni cookie no necessària abans del consentiment.
 * Si Complianz és actiu, exigeix el consentiment de la categoria de màrqueting.
 * Si no hi ha cap gestor de consentiment instal·lat, manté el comportament
 * anterior per no trencar la subscripció en webs sense CMP.
 *
 * Filtres:
 *  - anp_require_consent  : retorna false per desactivar la comprovació.
 *  - anp_consent_category : canvia la categoria (per defecte 'marketing').
 */
function anp_marketing_consent_ok() {
    if ( ! apply_filters( 'anp_require_consent', true ) ) {
        return true;
    }
    if ( function_exists( 'cmplz_has_consent' ) ) {
        $category = apply_filters( 'anp_consent_category', 'marketing' );
        return (bool) cmplz_has_consent( $category );
    }
    return true;
}

/* --------------------------------------------------------------------------
 * Admin — pantalla de configuració
 * -------------------------------------------------------------------------- */

add_action( 'admin_menu', 'anp_register_admin_menu' );
function anp_register_admin_menu() {
    add_menu_page(
        'Newsletter Popup',
        'Newsletter Popup',
        'manage_options',
        'ateneu-newsletter-popup',
        'anp_render_settings_page',
        'dashicons-email-alt',
        80
    );
}

add_action( 'admin_init', 'anp_register_settings' );
function anp_register_settings() {
    register_setting(
        'anp_settings_group',
        ANP_OPTION_KEY,
        array(
            'sanitize_callback' => 'anp_sanitize_settings',
            'default'           => array(),
        )
    );
}

function anp_sanitize_settings( $input ) {
    $output = array();
    $output['enabled']          = ! empty( $input['enabled'] ) ? 1 : 0;
    $output['benchmark_script'] = isset( $input['benchmark_script'] ) ? trim( wp_unslash( $input['benchmark_script'] ) ) : '';
    $cookie_days                = isset( $input['cookie_days'] ) ? (int) $input['cookie_days'] : 30;
    $output['cookie_days']      = max( 1, min( 365, $cookie_days ) );

    if ( $output['enabled'] && $output['benchmark_script'] !== '' ) {
        if ( anp_extract_benchmark_url( $output['benchmark_script'] ) === null ) {
            add_settings_error(
                ANP_OPTION_KEY,
                'anp_bad_script',
                'No s\'ha pogut detectar la URL del form de Benchmark (lbformnew.js) dins del codi. Revisa que has enganxat el codi correcte.',
                'error'
            );
        }
    }

    return $output;
}

function anp_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $settings = anp_get_settings();
    ?>
    <div class="wrap">
        <h1>Newsletter Popup</h1>
        <p>Configura el popup de subscripció a la newsletter. Enganxa el codi de Benchmark Email i activa'l.</p>

        <?php if ( ! function_exists( 'cmplz_has_consent' ) ) : ?>
            <div class="notice notice-warning"><p>
                <strong>Avís:</strong> no s'ha detectat Complianz. El popup es mostrarà sense esperar el consentiment de cookies. Instal·la un gestor de consentiment per complir el RGPD.
            </p></div>
        <?php endif; ?>

        <?php settings_errors( ANP_OPTION_KEY ); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'anp_settings_group' ); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="anp_enabled">Activar popup</label>
                    </th>
                    <td>
                        <input
                            type="checkbox"
                            id="anp_enabled"
                            name="<?php echo esc_attr( ANP_OPTION_KEY ); ?>[enabled]"
                            value="1"
                            <?php checked( 1, $settings['enabled'] ); ?>
                        />
                        <label for="anp_enabled">Mostra el popup als visitants del web</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="anp_benchmark_script">Codi embed de Benchmark</label>
                    </th>
                    <td>
                        <textarea
                            id="anp_benchmark_script"
                            name="<?php echo esc_attr( ANP_OPTION_KEY ); ?>[benchmark_script]"
                            rows="8"
                            cols="80"
                            class="large-text code"
                            placeholder="&lt;!-- BEGIN: Benchmark Email Signup Form Code --&gt;&#10;&lt;script type=&quot;text/javascript&quot;&gt;...&lt;/script&gt;"
                        ><?php echo esc_textarea( $settings['benchmark_script'] ); ?></textarea>
                        <p class="description">Enganxa aquí el codi HTML que et dóna Benchmark Email per al formulari (tipus "Popup"). El plugin n'extreu automàticament la URL i afegeix la lògica de cookie.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="anp_cookie_days">Dies entre visualitzacions</label>
                    </th>
                    <td>
                        <input
                            type="number"
                            id="anp_cookie_days"
                            name="<?php echo esc_attr( ANP_OPTION_KEY ); ?>[cookie_days]"
                            value="<?php echo esc_attr( $settings['cookie_days'] ); ?>"
                            min="1"
                            max="365"
                            step="1"
                        />
                        <p class="description">Un cop un usuari veu el popup, no li tornarà a aparèixer fins passats aquests dies. Per defecte: 30.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <hr>
        <h2>Accions ràpides</h2>
        <p>
            <button type="button" class="button" id="anp-reset-cookie">Restablir cookie (previsualitzar)</button>
            <span id="anp-reset-cookie-msg" style="margin-left:10px;color:#2271b1;"></span>
        </p>
        <script>
        (function(){
            var btn = document.getElementById('anp-reset-cookie');
            if (!btn) return;
            btn.addEventListener('click', function(){
                document.cookie = 'bm_popup_shown=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                document.getElementById('anp-reset-cookie-msg').textContent = 'Cookie esborrada. Obre el web en una pestanya nova per veure el popup.';
            });
        })();
        </script>
    </div>
    <?php
}

/* --------------------------------------------------------------------------
 * Frontend — injecció de scripts i estils
 * -------------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', 'anp_enqueue_assets' );
function anp_enqueue_assets() {
    $settings = anp_get_settings();
    if ( ! $settings['enabled'] ) {
        return;
    }
    $url = anp_extract_benchmark_url( $settings['benchmark_script'] );
    if ( ! $url ) {
        return;
    }
    if ( ! anp_marketing_consent_ok() ) {
        return;
    }

    wp_enqueue_style(
        'anp-popup',
        ANP_PLUGIN_URL . 'assets/popup.css',
        array(),
        ANP_VERSION
    );

    wp_enqueue_script(
        'anp-popup',
        ANP_PLUGIN_URL . 'assets/popup.js',
        array(),
        ANP_VERSION,
        false // load in <head> so window.alert override is set early
    );
}

/**
 * Inject the Benchmark script tag synchronously after <body> via output buffering.
 * Benchmark's lbformnew.js uses document.write() internally, so the <script> tag
 * MUST be present in the HTML at parse time (not added async via JS).
 * Output buffering is theme-independent (works regardless of wp_body_open support).
 */
add_action( 'template_redirect', 'anp_maybe_start_buffer', 1 );
function anp_maybe_start_buffer() {
    if ( is_admin() || is_feed() || ( function_exists( 'is_robots' ) && is_robots() ) ) {
        return;
    }
    if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
        return;
    }

    $settings = anp_get_settings();
    if ( empty( $settings['enabled'] ) ) {
        return;
    }
    if ( isset( $_COOKIE['bm_popup_shown'] ) ) {
        return;
    }
    if ( ! anp_extract_benchmark_url( $settings['benchmark_script'] ) ) {
        return;
    }
    if ( ! anp_marketing_consent_ok() ) {
        return;
    }

    ob_start( 'anp_inject_after_body_tag' );
}

function anp_inject_after_body_tag( $html ) {
    $settings = anp_get_settings();
    $url      = anp_extract_benchmark_url( $settings['benchmark_script'] );
    if ( ! $url ) {
        return $html;
    }

    $max_age = (int) $settings['cookie_days'] * 24 * 60 * 60;

    $injection = sprintf(
        '<!-- Newsletter Popup: Benchmark loader -->' .
        '<script>document.cookie="bm_popup_shown=1; path=/; max-age=%d";</script>' .
        '<script type="text/javascript" src="%s"></script>',
        $max_age,
        esc_url( $url )
    );

    return preg_replace( '/(<body[^>]*>)/i', '$1' . $injection, $html, 1 );
}
