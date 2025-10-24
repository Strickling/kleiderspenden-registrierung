<?php
/**
 * Footer-Template
 *
 * Bezug zu den Aufgaben (a–i):
 * a. Titel/Logo: Vereinsname und Claim werden gezeigt
 * b. Struktur: Footer mit drei Widget-Spalten und Menüs
 * c. Responsivität: Layout über Bootstrap
 * d, e, f, g, h, i: nicht im Footer, gehören in die jeweiligen Templates
 */
?>
<footer class="site-footer bg-accent text-white py-5">
  <div class="container">

    <!-- b: dreispaltiges Raster für Footer-Inhalte -->
    <div class="row gy-4">
      <div class="col-md-4">
        <?php
        // b: redaktioneller Bereich (z. B. Kontakt)
        if (is_active_sidebar('footer-1')) { dynamic_sidebar('footer-1'); }

        // b: Footer-Menü Spalte 1
        if (has_nav_menu('footer_col_1')) {
          wp_nav_menu([
            'theme_location' => 'footer_col_1',
            'container'      => '',
            'menu_class'     => 'list-unstyled mb-0',
          ]);
        }
        ?>
      </div>

      <div class="col-md-4">
        <?php
        // b: weiterer redaktioneller Bereich
        if (is_active_sidebar('footer-2')) { dynamic_sidebar('footer-2'); }

        // b: Footer-Menü Spalte 2
        if (has_nav_menu('footer_col_2')) {
          wp_nav_menu([
            'theme_location' => 'footer_col_2',
            'container'      => '',
            'menu_class'     => 'list-unstyled mb-0',
          ]);
        }
        ?>
      </div>

      <div class="col-md-4">
        <?php
        // b: dritte Spalte für zusätzliche Inhalte
        if (is_active_sidebar('footer-3')) { dynamic_sidebar('footer-3'); }
        ?>
      </div>
    </div>

    <!-- b: rechtlicher Hinweis aus dem Customizer -->
    <div class="border-top border-white-25 mt-4 pt-3 text-center">
      <small class="opacity-75">
        <?php echo wp_kses_post( get_theme_mod('ak_copyright') ); ?>
      </small>
    </div>
    
    <p class="text-center mt-3">
      <a href="<?php echo esc_url( admin_url() ); ?>" class="text-white-50 small">Admin Login</a>
    </p>

    <!-- a, b: Vereinsname und optionaler Claim -->
    <div class="border-top border-white-25 mt-2 pt-3 text-center">
      <small class="opacity-75">
        © <?php echo date('Y'); ?> <?php bloginfo('name'); ?>
        <?php if ($claim = get_theme_mod('ak_footer_claim')) : ?>
          – <?php echo esc_html($claim); ?>
        <?php endif; ?>
      </small>
    </div>
  </div>
</footer>

<?php
// b, c: Skripte am Seitenende laden (Theme und Plugins)
wp_footer();
?>
</body>
</html>
