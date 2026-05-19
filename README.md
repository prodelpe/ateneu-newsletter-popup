# Newsletter Popup

Popup de subscripció a la newsletter integrat amb **Benchmark Email**, configurable des d'una sola pantalla a l'admin de WordPress. Desenvolupat per **Creagia**.

## Característiques

- Popup de subscripció a partir del codi *embed* de Benchmark Email.
- Configuració d'una sola pantalla: activar/desactivar, codi de Benchmark i dies entre visualitzacions.
- **Compliment RGPD/LSSI:** el script de Benchmark i la cookie `bm_popup_shown` **només es carreguen si l'usuari ha acceptat la categoria de màrqueting** a Complianz. Si no hi ha cap gestor de consentiment instal·lat, manté el comportament clàssic (no trenca webs sense CMP).

## Requisits

- WordPress 5.5+
- Recomanat: plugin **Complianz** (gestió del consentiment de cookies).

## Instal·lació / actualització

WP Admin → **Connectors → Afegeix nou → Puja un connector** → seleccionar el `.zip` → *Substitueix l'actual amb el penjat*.

## Comportament del consentiment

- **Abans del consentiment:** el popup NO apareix i no es carrega cap recurs de Benchmark ni cap cookie.
- **Després d'acceptar màrqueting:** el popup apareix amb normalitat (a la recàrrega o navegació següent).

### Filtres per a personalització

```php
// Desactivar la comprovació de consentiment (no recomanat).
add_filter( 'anp_require_consent', '__return_false' );

// Canviar la categoria de consentiment exigida (per defecte 'marketing').
add_filter( 'anp_consent_category', function () { return 'marketing'; } );
```

## Changelog

### 1.1.0
- El popup (script de Benchmark + cookie `bm_popup_shown`) no es carrega fins que
  l'usuari accepta la categoria de màrqueting a Complianz (RGPD/LSSI).
- Avís a l'admin si no es detecta cap gestor de consentiment.
- Nous filtres `anp_require_consent` i `anp_consent_category`.

### 1.0.2
- Versió inicial: popup de Benchmark Email amb cookie de freqüència.
