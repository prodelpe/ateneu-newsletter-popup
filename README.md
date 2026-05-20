# Newsletter Popup

Popup de subscripció a la newsletter integrat amb **Benchmark Email**, configurable des d'una sola pantalla a l'admin de WordPress. Desenvolupat per **Creagia**.

## Característiques

- Popup de subscripció a partir del codi *embed* de Benchmark Email.
- Configuració d'una sola pantalla: activar/desactivar, codi de Benchmark i dies entre visualitzacions.
- Cookie `bm_popup_shown` per controlar la freqüència entre visualitzacions per usuari.

## Requisits

- WordPress 5.5+

## Instal·lació / actualització

WP Admin → **Connectors → Afegeix nou → Puja un connector** → seleccionar el `.zip` → *Substitueix l'actual amb el penjat*.

## Changelog

### 1.1.3
- Es retira el gate de consentiment de cookies: el popup torna a mostrar-se sempre la primera vegada. La cookie `bm_popup_shown` continua controlant la freqüència entre visualitzacions.
- S'eliminen els filtres `anp_require_consent` i `anp_consent_category` (ja no s'usen).

### 1.1.2
- Versió de prova del sistema d'auto-actualització.

### 1.1.1
- Auto-actualització des de GitHub (plugin-update-checker).

### 1.1.0
- El popup (script de Benchmark + cookie `bm_popup_shown`) no es carrega fins que
  l'usuari accepta la categoria de màrqueting a Complianz (RGPD/LSSI).
- Avís a l'admin si no es detecta cap gestor de consentiment.
- Nous filtres `anp_require_consent` i `anp_consent_category`.

### 1.0.2
- Versió inicial: popup de Benchmark Email amb cookie de freqüència.
