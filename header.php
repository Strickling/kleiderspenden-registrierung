<?php
/**
 * Header-Template – globaler Seitenkopf mit Marke und Navigation (WordPress)
 *
 * Bezug zu den Aufgaben (a–i):
 * a. Titel/Logo: Ausgabe über .navbar-brand und the_custom_logo (Fallback: bloginfo('name'))
 * b. Struktur: Dieser Datei stellt den Header mit Hauptnavigation bereit
 * c. Responsivität: meta viewport und Bootstrap-Navigation (expand-lg, Toggler) mit passenden Klassen
 * d. Formular: nicht hier, Verlinkung erfolgt über die Navigation
 * e. Übergabe an Geschäftsstelle: Verlinkung erfolgt über die Navigation
 * f. Tablet an Geschäftsstelle: große, gut klickbare Header-Elemente
 * g. Abholung per Sammelfahrzeug: Verlinkung erfolgt über die Navigation
 * h. PLZ-Prüfung: nicht im Header, gehört in das Formular-Template
 * i. Bestätigungsseite: nicht im Header, gehört in das Formular-Template
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <!-- c. Responsives Verhalten -->
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="visually-hidden-focusable" href="#main">Zum Inhalt springen</a>

<!-- b. Header mit globaler Navigation / Struktur -->
<header class="sticky-top bg-white border-bottom">

  <!-- b, c. Responsives Navigationsmuster -->
  <nav class="navbar navbar-expand-lg navbar-light" aria-label="Hauptnavigation">
    <div class="container">
      
      <!-- a. Titel und Logo -->
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo esc_url(home_url('/')); ?>">
        <?php
        // a. Branding über den Customizer änderbar
        if ( function_exists('the_custom_logo') && has_custom_logo() ) {
          the_custom_logo();
        } else {
          bloginfo('name');
        }
        ?>
      </a>

      <!-- c. Responsive Navigation (Hamburger-Menü) -->
      <button class="navbar-toggler" type="button"
              data-bs-toggle="collapse" data-bs-target="#nav"
              aria-controls="nav" aria-expanded="false"
              aria-label="Menü umschalten">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- b. Navigationselemente im Header -->
      <div class="collapse navbar-collapse" id="nav">
        <?php
        wp_nav_menu([
          'theme_location' => 'hauptmenue',              // b. Hauptnavigation
          'container'      => false,                     // b. klare Struktur ohne zusätzlichen Wrapper
          'menu_class'     => 'navbar-nav mx-auto fs-5 fw-semibold mb-2 mb-lg-0', // c. responsive Layoutklassen
          'fallback_cb'    => function () {
            echo '<ul class="navbar-nav mx-auto fs-5 fw-semibold mb-2 mb-lg-0">'
               . '<li class="nav-item"><a class="nav-link" href="'
               . esc_url(admin_url('nav-menus.php'))
               . '">'
               . esc_html__('Menü erstellen', 'altkleider')
               . '</a></li></ul>';
          },
        ]);
        ?>
      </div>
    </div>
  </nav>
</header>

<!-- b. Bootstrap wird nicht hier, sondern in functions.php per wp_enqueue_* eingebunden -->
<!-- c. sorgt für responsives Design auf allen Geräten -->
