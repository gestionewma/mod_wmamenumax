# mod_wmamenumax

Joomla 6 module for a multi-column mega menu.

These instructions are intended for the GitHub repository and the release workflow.

## What it does

- Reads items from a Joomla Menu Manager menu.
- Shows top-level items in the main bar.
- Splits child items into configurable columns.
- Shows a right-side image panel with dynamic preview.
- Generates physical square thumbnails on disk.
- Supports mobile accordion, JSON-LD, cache cleaning, and rebuild.

## How it works

1. The module reads the `menutype` selected in the configuration.
2. It keeps only items between `startLevel` and `endLevel`.
3. Top-level items become the main bar.
4. Child items are distributed into columns.
5. Each child can have a thumbnail and a hover image.
6. The side panel shows the top-level default image or the child preview.

The frontend uses only PHP, CSS, and vanilla JS. The layout receives ready-to-render data from the dispatcher.

## Data model

- Items come from the Joomla Menu Manager.
- The module respects access level, language, published state, and `menu_show`.
- The `current/active` state follows the Joomla core menu logic.

## Configuration

### Menu tab

- `menutype`: source menu.
- `startLevel`: starting level.
- `endLevel`: final level, `0` = no limit.

### Columns tab

- `columns`: maximum number of columns.
- `distribution`: `auto` or `manual`.
- In `manual` mode, `col1_count` ... `col5_count` define the distribution.
- `coltitles`: column titles with visibility, start column, and span options.

### Desktop colors tab

Colors for the desktop menu: column titles, border, background, top level, hover, current, and submenu.

### Mobile colors tab

Mobile menu colors with values separate from desktop.

### Panel tab

- `panel_pct`: panel height relative to the tallest column.
- `fallback_image`: fallback image.
- Fixed thumbnail size: 64 px.
- Fixed panel/hover image size: 300 px.

### SEO tab

- `title_tag`: HTML tag for column titles.
- `seo_jsonld`: enable/disable the `SiteNavigationElement` JSON-LD.

### Maintenance tab

Buttons for:

- cleaning the thumbnail cache;
- rebuilding all thumbnails.

The action goes through `com_ajax` with GET CSRF token.

### Information tab

Shows module data, author, email, website, license, and copyright.

## Operational notes

- Thumbnails are stored in `images/miniature`.
- Remote HTTP/HTTPS images are left as pass-through.
- The panel and columns adapt to mobile below 992px.
- JSON-LD is printed only when enabled and when the menu is not empty.

## Updates

The module uses a remote update server through `mod_wmamenumax_update.xml` in the repository root.

## Demo online

You can see the module in action on the WMA site:

- https://www.wma.ovh

## Requirements

- Joomla 6
- PHP compatible with Joomla 6
- GD or Imagick for thumbnail generation

## Version

Current version: `1.0.14`

---

# ITALIANO

## mod_wmamenumax

Modulo Joomla 6 per megamenu multi-colonna.

Queste istruzioni sono pensate per il repository GitHub e per il flusso di release.

## Cosa fa

- Legge le voci da un menu del Menu Manager di Joomla.
- Mostra le voci di primo livello nella barra principale.
- Distribuisce i figli in colonne configurabili.
- Mostra un pannello immagine a destra con anteprima dinamica.
- Genera miniature quadrate fisiche su disco.
- Supporta mobile/accordion, JSON-LD, pulizia cache e rigenerazione.

## Come funziona

1. Il modulo legge il `menutype` scelto nella configurazione.
2. Prende solo le voci tra `startLevel` e `endLevel`.
3. Le voci top-level diventano la barra principale.
4. I figli vengono distribuiti nelle colonne.
5. Ogni figlio puo avere una miniatura e un'immagine hover.
6. Il pannello laterale mostra l'immagine di default del top level o l'anteprima del figlio.

Il frontend usa solo PHP, CSS e JS vanilla. Il layout riceve gia i dati pronti dal dispatcher.

## Struttura dati

- Le voci arrivano dal Menu Manager di Joomla.
- Il modulo rispetta access level, lingua, stato pubblicato e `menu_show`.
- Il campo `current/active` segue la logica del menu core Joomla.

## Configurazione

### Tab Menu

- `menutype`: menu sorgente.
- `startLevel`: livello iniziale.
- `endLevel`: livello finale, `0` = nessun limite.

### Tab Colonne

- `columns`: numero massimo di colonne.
- `distribution`: `auto` oppure `manual`.
- In modalita `manual` i campi `col1_count` ... `col5_count` definiscono la distribuzione.
- `coltitles`: titoli delle colonne, con opzioni di visibilita, colonna iniziale e span.

### Tab Colori Desktop

Colori del menu desktop: titoli colonna, bordo, sfondo, top level, hover, current e submenu.

### Tab Colori Mobile

Colori del menu mobile con valori separati dal desktop.

### Tab Pannello

- `panel_pct`: altezza del pannello rispetto alla colonna piu alta.
- `fallback_image`: immagine di fallback.
- Dimensione fissa miniatura: 64 px.
- Dimensione fissa immagine pannello/hover: 300 px.

### Tab SEO

- `title_tag`: tag HTML per i titoli colonna.
- `seo_jsonld`: abilita/disabilita il JSON-LD `SiteNavigationElement`.

### Tab Manutenzione

Pulsanti per:

- pulire la cache delle miniature;
- rigenerare tutte le miniature.

L'azione passa via `com_ajax` con token CSRF GET.

### Tab Informazioni

Mostra dati del modulo, autore, email, sito, licenza e copyright.

## Note operative

- Le miniature vengono salvate in `images/miniature`.
- Le immagini remote HTTP/HTTPS vengono lasciate pass-through.
- Il pannello e le colonne si adattano al mobile sotto i 992px.
- Il JSON-LD viene stampato solo se abilitato e se il menu non e vuoto.

## Aggiornamenti

Il modulo usa un update server remoto tramite `mod_wmamenumax_update.xml` nella root del repository.

## Demo online

Puoi vedere il modulo in azione sul sito WMA:

- https://www.wma.ovh

## Requisiti

- Joomla 6
- PHP compatibile con Joomla 6
- GD oppure Imagick per la generazione delle miniature

## Versione

Versione corrente: `1.0.13`
