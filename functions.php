<?php
/**
 * Funktionen des WordPress-Themes – Altkleidersammlung
 *
 * Bezug zur Aufgabenliste (a–i), kurze Zuordnung:
 * a. Titel/Logo: title-tag, custom-logo
 * b. Struktur: Menüs, Widgets, Footer
 * c. Responsivität: Bootstrap und eigenes CSS
 * d. Formular: eigenes Seiten-Template, diese Datei liefert Infrastruktur
 * e, f, g. Übergabe/Abholung: Erfassung im Formular, CPT unterstützt
 * h. PLZ-Prüfung: zentrale Option verein_plz
 * i. Bestätigung und Export: Anzeige im Template, CSV-Export hier
 */

/* Assets: CSS und JS
 * b, c: Grundgerüst und Responsivität
 */
add_action('wp_enqueue_scripts', function () {
  $theme_uri = get_stylesheet_directory_uri();

  // c: Bootstrap CSS
  wp_enqueue_style('bootstrap', $theme_uri . '/assets/bootstrap/css/bootstrap.min.css');

  // b, c: Theme CSS nach Bootstrap
  wp_enqueue_style('altkleider', $theme_uri . '/style.css', ['bootstrap']);

  // c: Bootstrap JS im Footer
  wp_enqueue_script('bootstrap', $theme_uri . '/assets/bootstrap/js/bootstrap.bundle.min.js', [], false, true);
});


/* Basis-Theme-Support
 * a, b, c: Titel, Logo, Struktur, Editor
 */
add_action('after_setup_theme', function () {

  // a: dynamischer Titel
  add_theme_support('title-tag');

  // a: Logo im Customizer
  add_theme_support('custom-logo', [
    'flex-width'  => true,
    'flex-height' => true,
    'height'      => 60,
  ]);

  // b: großes Headerbild
  add_theme_support('custom-header', [
    'width'         => 1920,
    'height'        => 600,
    'flex-width'    => true,
    'flex-height'   => true,
    'uploads'       => true,
    'default-image' => get_stylesheet_directory_uri() . '/assets/img/hero.jpg',
  ]);

  // c: weite und volle Ausrichtung im Block-Editor
  add_theme_support('align-wide');

  // b, c: Editor optisch an Frontend annähern
  add_theme_support('editor-styles');
  add_editor_style('assets/css/main.css');

  // b: Menüs im Backend verwaltbar
  register_nav_menus([
    'hauptmenue'   => 'Hauptmenü',
    'hero_ribbon'  => 'Hero-Buttons',
    'footer_col_1' => 'Footer-Menü 1',
    'footer_col_2' => 'Footer-Menü 2',
  ]);
});


/* Menüs und Bootstrap-Klassen
 * b, c: Navigationsausgabe in Bootstrap-Form
 */
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
  if (($args->theme_location ?? '') === 'hauptmenue') {
    // c: Link-Klasse
    $atts['class'] = trim(($atts['class'] ?? '') . ' nav-link');

    // b, c: Dropdown-Kennzeichnung bei Unterpunkten
    if (in_array('menu-item-has-children', (array) $item->classes, true)) {
      $atts['class']          .= ' dropdown-toggle';
      $atts['data-bs-toggle']  = 'dropdown';
      $atts['aria-expanded']   = 'false';
      $atts['href']            = $atts['href'] ?? '#';
    }
  }
  return $atts;
}, 10, 3);

add_filter('nav_menu_css_class', function ($classes, $item, $args) {
  if (($args->theme_location ?? '') === 'hauptmenue') {
    // c: Listenelement
    $classes[] = 'nav-item';

    // b, c: Dropdown-Eltern markieren
    if (in_array('menu-item-has-children', (array) $item->classes, true)) {
      $classes[] = 'dropdown';
    }
  }
  return $classes;
}, 10, 3);

add_filter('nav_menu_submenu_css_class', function ($classes, $args, $depth) {
  if (($args->theme_location ?? '') === 'hauptmenue') {
    // c: Submenü als Dropdown-Menü
    $classes = ['dropdown-menu'];
  }
  return $classes;
}, 10, 3);


/* Widgets im Footer
 * b: redaktionell pflegbarer Footer für rechtliche Hinweise und Links
 */
add_action('widgets_init', function () {
  $wrap = [
    'before_widget' => '<section class="footer-widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h5 class="fw-bold mb-2">',
    'after_title'   => '</h5>',
  ];
  register_sidebar(array_merge(['name' => 'Footer Spalte 1', 'id' => 'footer-1'], $wrap));
  register_sidebar(array_merge(['name' => 'Footer Spalte 2', 'id' => 'footer-2'], $wrap));
  register_sidebar(array_merge(['name' => 'Footer Spalte 3', 'id' => 'footer-3'], $wrap));
});


/* Customizer: Footer und Startseite
 * a, b: Branding und Footertext; b, c: Startseiten-Elemente
 */
add_action('customize_register', function ($c) {

  // b: Footer-Bereich
  $c->add_section('ak_footer', ['title' => 'Footer', 'priority' => 160]);

  // b: Copyright-Zeile
  $c->add_setting('ak_copyright', [
    'default'           => '© ' . date('Y') . ' Altkleidersammlung',
    'sanitize_callback' => 'wp_kses_post',
  ]);
  $c->add_control('ak_copyright', [
    'label'   => 'Copyright-Zeile',
    'type'    => 'textarea',
    'section' => 'ak_footer',
  ]);

  // a, b: kurzer Claim im Footer
  $c->add_setting('ak_footer_claim', [
    'default'           => 'wo jede Spende passt und Wärme weitergibt',
    'sanitize_callback' => 'sanitize_text_field',
  ]);
  $c->add_control('ak_footer_claim', [
    'label'   => __('Footer-Claim', 'textdomain'),
    'section' => 'ak_footer',
    'type'    => 'text',
  ]);

  // b: Hero-Icons
  $c->add_section('ak_hero', ['title' => 'Startseite: Hero-Icons', 'priority' => 35]);
  for ($i = 1; $i <= 3; $i++) {
    $key = "ak_hero_icon_$i";
    $c->add_setting($key, ['default' => 0, 'sanitize_callback' => 'absint']);
    $c->add_control(new WP_Customize_Media_Control($c, $key, [
      'label'     => "Icon $i (SVG/PNG)",
      'section'   => 'ak_hero',
      'mime_type' => 'image',
    ]));
  }
});

add_action('customize_register', function ($c) {
  // b, c: Hero-Video
  $c->add_section('ak_hero_media', [
    'title'    => 'Startseite: Hero-Video',
    'priority' => 36,
  ]);

  $c->add_setting('ak_hero_video_mp4', ['default' => 0, 'sanitize_callback' => 'absint']);
  $c->add_control(new WP_Customize_Media_Control($c, 'ak_hero_video_mp4', [
    'label'     => 'Hero Video (MP4)',
    'section'   => 'ak_hero_media',
    'mime_type' => 'video',
  ]));

  $c->add_setting('ak_hero_video_webm', ['default' => 0, 'sanitize_callback' => 'absint']);
  $c->add_control(new WP_Customize_Media_Control($c, 'ak_hero_video_webm', [
    'label'     => 'Hero Video (WebM, optional)',
    'section'   => 'ak_hero_media',
    'mime_type' => 'video',
  ]));

  $c->add_setting('ak_hero_poster', ['default' => 0, 'sanitize_callback' => 'absint']);
  $c->add_control(new WP_Customize_Media_Control($c, 'ak_hero_poster', [
    'label'     => 'Hero Poster (Fallback-Bild)',
    'section'   => 'ak_hero_media',
    'mime_type' => 'image',
  ]));
});


/* Upload-Formate
 * c: WebM und SVG (SVG nur für Administratoren)
 */
add_filter('upload_mimes', function ($mimes) {
  $mimes['webm'] = 'video/webm';
  if (current_user_can('manage_options')) {
    $mimes['svg'] = 'image/svg+xml';
  }
  return $mimes;
});


/* Custom Post Type: Spenden
 * d, e, f, g, i: strukturierte Erfassung der Registrierungen
 */
add_action('init', function () {
  register_post_type('spende', [
    'label' => 'Spenden',
    'labels' => [
      'name'          => 'Spenden',
      'singular_name' => 'Spende',
      'add_new_item'  => 'Neue Spende',
      'edit_item'     => 'Spende bearbeiten',
      'menu_name'     => 'Spenden',
    ],
    'public'       => false,
    'show_ui'      => true,
    'supports'     => ['title'],
    'menu_position'=> 20,
    'menu_icon'    => 'dashicons-archive',
  ]);
});


/* Admin-Liste: Spalten für Spenden
 * i: Übersicht im Backend
 */
add_filter('manage_spende_posts_columns', function($cols){
  return [
    'cb'            => $cols['cb'],
    'title'         => 'Spende',
    'uebergabeart'  => 'Übergabeart',
    'kleiderart'    => 'Kleiderart',
    'krisengebiet'  => 'Krisengebiet',
    'ort'           => 'Ort/PLZ',
    'date'          => 'Datum',
  ];
});
add_action('manage_spende_posts_custom_column', function($col, $post_id){
  $get = fn($k)=>get_post_meta($post_id,$k,true);
  if($col==='uebergabeart')  echo $get('uebergabeart')==='abholung' ? 'Abholung' : 'Geschäftsstelle';
  if($col==='kleiderart')    echo esc_html($get('kleiderart'));
  if($col==='krisengebiet')  echo esc_html($get('krisengebiet'));
  if($col==='ort'){
    if($get('uebergabeart')==='abholung'){
      echo esc_html($get('strasse')).'<br>'.esc_html($get('plz')).' '.esc_html($get('ort'));
    } else {
      echo 'Geschäftsstelle';
    }
  }
}, 10, 2);


/* Filter-UI und CSV-Export
 * i: Export für externe Nutzung
 */
add_action('restrict_manage_posts', function($post_type){
  if ($post_type !== 'spende') return;

  $from = isset($_GET['spende_from']) ? esc_attr($_GET['spende_from']) : '';
  $to   = isset($_GET['spende_to'])   ? esc_attr($_GET['spende_to'])   : '';

  echo '<input type="date" name="spende_from" value="'.$from.'" placeholder="Von" /> ';
  echo '<input type="date" name="spende_to" value="'.$to.'" placeholder="Bis" /> ';

  $export_url = add_query_arg([
    'spende_export' => 1,
    'spende_from'   => $from,
    'spende_to'     => $to,
  ]);
  echo ' <a href="'.esc_url($export_url).'" class="button button-primary">Export als CSV</a>';
});

add_action('admin_init', function(){
  if (!isset($_GET['spende_export'])) return;
  if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');

  $from = !empty($_GET['spende_from']) ? sanitize_text_field($_GET['spende_from']) : '';
  $to   = !empty($_GET['spende_to'])   ? sanitize_text_field($_GET['spende_to'])   : '';

  $args = [
    'post_type'      => 'spende',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
  ];

  if ($from || $to) {
    $dq = ['inclusive'=>true];
    if ($from) $dq['after']  = $from.' 00:00:00';
    if ($to)   $dq['before'] = $to.' 23:59:59';
    $args['date_query'] = [$dq];
  }

  $q = new WP_Query($args);

  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=spenden-'.date('Y-m-d').'.csv');
  $out = fopen('php://output', 'w');

  fputcsv($out, ['Datum','Übergabeart','Kleiderart','Krisengebiet','Adresse/Ort']);

  while($q->have_posts()){ $q->the_post();
    $id       = get_the_ID();
    $ueberg   = get_post_meta($id,'uebergabeart',true);
    $datum    = get_the_date('Y-m-d H:i',$id);
    $kleid    = get_post_meta($id,'kleiderart',true);
    $krise    = get_post_meta($id,'krisengebiet',true);
    $adresse  = ($ueberg==='abholung')
      ? (get_post_meta($id,'strasse',true).', '.get_post_meta($id,'plz',true).' '.get_post_meta($id,'ort',true))
      : 'Geschäftsstelle';

    fputcsv($out, [$datum, ($ueberg==='abholung'?'Abholung':'Geschäftsstelle'), $kleid, $krise, $adresse]);
  }
  wp_reset_postdata();
  exit;
});


/* Option: Verein-PLZ im Bereich Allgemein
 * h: wird im Formular für den PLZ-Abgleich genutzt
 */
add_action('admin_init', function(){
  register_setting('general', 'verein_plz', [
    'type' => 'string',
    'sanitize_callback' => function($v){ return preg_match('/^\d{5}$/',$v) ? $v : ''; },
    'default' => '12345',
  ]);

  add_settings_field('verein_plz', 'Geschäftsstelle PLZ', function(){
    $val = esc_attr( get_option('verein_plz','12345') );
    echo '<input type="text" name="verein_plz" value="'.$val.'" class="regular-text" pattern="\d{5}">';
  }, 'general');
});
