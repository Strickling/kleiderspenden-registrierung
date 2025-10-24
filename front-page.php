<?php
/**
 * Front Page – Startseite mit Hero (Hintergrundvideo) und Ribbon-Links.
 *
 * Bezug zu den Aufgaben (a–i):
 * a. Titel/Logo: Branding über Header, Titel im Hero.
 * b. Struktur: Header, Navigation, Content, Footer.
 * c. Responsivität: Bootstrap-Grid und flexible Hero-Sektion.
 * d. Formular: nicht hier, Navigation führt dorthin.
 * e. Übergabe an Geschäftsstelle: Link führt zum Formular.
 * f. Tablet an Geschäftsstelle: große, klar erkennbare Buttons.
 * g. Abholung: Link führt zum Formular.
 * h. PLZ-Prüfung: nicht hier, im Formular-Template.
 * i. Bestätigungsseite: nicht hier, im Formular-Template.
 */

get_header();

/**
 * Hilfsfunktion: Medien-URL aus Customizer-Setting laden.
 * Akzeptiert Anhang-ID oder URL. Liefert Fallback, wenn nichts gesetzt ist.
 */
function ak_get_media_url(string $mod_key, string $fallback_url = '') : string {
  $val = get_theme_mod($mod_key);
  if (empty($val)) return $fallback_url;

  if (is_numeric($val)) {
    $url = wp_get_attachment_url((int)$val);
    return $url ? $url : $fallback_url;
  }

  $url = esc_url_raw($val);
  return $url ? $url : $fallback_url;
}

/**
 * Hilfsfunktion: Icon inline einbinden.
 * Bevorzugt SVG aus dem Customizer. Fällt auf Bild-Tag oder Datei-Fallback zurück.
 */
function ak_get_icon_inline(int $index, string $fallback_rel) : string {
  $attach_id = (int) get_theme_mod("ak_hero_icon_$index", 0);

  if ($attach_id) {
    $file = get_attached_file($attach_id);
    $url  = wp_get_attachment_url($attach_id);

    if ($file && file_exists($file)) {
      $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
      if ($ext === 'svg') {
        $svg = @file_get_contents($file);
        if ($svg) return $svg;
      }
      if ($url) return '<img src="'.esc_url($url).'" alt="" width="60" height="60">';
    }
  }

  $path = get_stylesheet_directory() . $fallback_rel;
  if (is_readable($path)) {
    $svg = @file_get_contents($path);
    if ($svg) return $svg;
  }

  return '';
}

/* Daten für den Hero vorbereiten.
 * a, b, c, f: Bild/Video, Titel, Ribbon-Links.
 */
$hero_fallback = get_header_image() ?: get_stylesheet_directory_uri() . '/assets/img/hero.jpg';
$vid_mp4  = ak_get_media_url('ak_hero_video_mp4',  '');
$vid_webm = ak_get_media_url('ak_hero_video_webm', '');
$poster   = ak_get_media_url('ak_hero_poster',     $hero_fallback);

/* Hero-Buttons aus Menü laden.
 * d, e, g: Verweise auf zentrale Aktionen.
 */
$locs    = get_nav_menu_locations();
$menu_id = isset($locs['hero_ribbon']) ? (int)$locs['hero_ribbon'] : 0;
$items   = $menu_id ? wp_get_nav_menu_items($menu_id, ['update_post_term_cache' => false]) : [];

usort($items, fn($a,$b) => ($a->menu_order <=> $b->menu_order));
$tops = array_values(array_filter($items, fn($i) => empty($i->menu_item_parent)));

if (empty($tops)) {
  $tops = [
    (object)['title'=>'Telefon', 'url'=>'tel:+49123456789'],
    (object)['title'=>'Spenden', 'url'=>home_url('/spendenregistrierung/')],
    (object)['title'=>'Mail',    'url'=>'mailto:'.antispambot('kleiderReg@pulli.de')],
  ];
}
$tops = array_slice($tops, 0, 3);

/* Standard-Icons, falls nichts im Customizer geändert wurde. */
$icon_defaults = [
  '/assets/icons/box.svg',
  '/assets/icons/tshirt.svg',
  '/assets/icons/phone.svg',
];
?>

<section class="hero hero--video" style="background-image:url('<?php echo esc_url($poster ?: $hero_fallback); ?>');">
  <?php if ($vid_mp4 || $vid_webm): ?>
    <video class="hero__video"
           autoplay muted loop playsinline preload="metadata"
           poster="<?php echo esc_url($poster ?: $hero_fallback); ?>">
      <?php if ($vid_webm): ?>
        <source src="<?php echo esc_url($vid_webm); ?>" type="video/webm">
      <?php endif; ?>
      <?php if ($vid_mp4): ?>
        <source src="<?php echo esc_url($vid_mp4); ?>" type="video/mp4">
      <?php endif; ?>
    </video>
  <?php endif; ?>

  <div class="container text-center position-relative hero__inner">
    <!-- a: Titel im Hero für Wiedererkennung -->
    <h1 class="hero__title">
      PulliPunkt e.V.
      <span class="hero__subtitle">Gutes tun. Kleidung weitergeben.</span>
    </h1>

    <!-- d, e, g: drei zentrale Handlungsoptionen als Buttons -->
    <div class="ribbon mx-auto px-2 py-2">
      <div class="row g-2 justify-content-center align-items-center">
        <?php for ($i = 0; $i < 3; $i++):
          $btn   = $tops[$i] ?? null;
          $title = $btn ? $btn->title : ['Standorte','Was spenden?','Kontakt'][$i];
          $url   = $btn ? $btn->url   : [home_url('/container/'), home_url('/annahme/'), home_url('/kontakt/')][$i];
          $icon  = ak_get_icon_inline($i + 1, $icon_defaults[$i]);
        ?>
          <div class="col-auto">
            <a class="d-grid text-center" href="<?php echo esc_url($url); ?>">
              <span class="icon mb-2"><?php echo $icon; ?></span>
              <span class="label"><?php echo esc_html($title); ?></span>
            </a>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</section>

<main class="container altkleider-content" id="main">
  <!-- b, c, i: Inhalt aus dem WP-Loop -->
  <?php if (have_posts()) : while (have_posts()) : the_post();
    the_title('<h2 class="mb-3 text-center">','</h2>');
    echo '<hr class="mb-4">';
    the_content();
  endwhile; endif; ?>
</main>

<?php
/* b: Footereinbindung */
get_footer();
