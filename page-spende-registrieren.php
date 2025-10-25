<?php
/**
 * Template Name: Spenden-Registrierung
 * Description: Formular zur Registrierung einer Kleiderspende (Übergabe an Geschäftsstelle oder Abholung).
 *
 * Bezug zu den Aufgaben (a–i):
 * b. Struktur und c. Responsivität: durch Theme/Bootstrap (Header, Content, Footer)
 * d. Formular: dieses Template liefert das Registrierungsformular
 * e, f, g. Übergabe an Geschäftsstelle oder Abholung: Auswahl mit den jeweiligen Feldern
 * h. Abholradius-Prüfung: erste zwei Ziffern der PLZ werden verglichen
 * i. Bestätigungsansicht: Zusammenfassung aller Daten nach erfolgreicher Registrierung
 */

defined('ABSPATH') || exit;

// Konfiguration: zentrale Vereins-PLZ für h.
$verein_plz = get_option('verein_plz', '33602');

// Auswahlliste Krisengebiete (Beispielwerte)
$krisengebiete = [
  'Region A',
  'Region B',
  'Region C',
];

// Hilfsfunktionen für Formularwerte (d.)
function spende_field($key){ return isset($_POST[$key]) ? trim((string)$_POST[$key]) : ''; }
function spende_safe($val){ return esc_html($val); }

// Verarbeitung: Validierung, Speichern, Bestätigung (d, e, f, g, h, i)
$errors = [];
$success = false;
$data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['spende_nonce']) && wp_verify_nonce($_POST['spende_nonce'], 'spende_registrieren')) {

  // Pflichtfelder (d.)
  $data['uebergabeart'] = sanitize_text_field(spende_field('uebergabeart'));
  $data['kleiderart']   = sanitize_text_field(spende_field('kleiderart'));
  $data['krisengebiet'] = sanitize_text_field(spende_field('krisengebiet'));

  if (empty($data['uebergabeart']) || empty($data['kleiderart']) || empty($data['krisengebiet'])) {
    $errors[] = 'Bitte alle Pflichtfelder ausfüllen.';
  }

  // Adresse nur bei Abholung (g.)
  if ($data['uebergabeart'] === 'abholung') {
    $data['strasse'] = sanitize_text_field(spende_field('strasse'));
    $data['plz']     = sanitize_text_field(spende_field('plz'));
    $data['ort']     = sanitize_text_field(spende_field('ort'));

    if (empty($data['strasse']) || empty($data['plz']) || empty($data['ort'])) {
      $errors[] = 'Für Abholung bitte Straße, PLZ und Ort angeben.';
    }

    // Format der PLZ (g., h.)
    if (!empty($data['plz']) && !preg_match('/^\d{5}$/', $data['plz'])) {
      $errors[] = 'Bitte eine gültige 5-stellige PLZ angeben.';
    }

    // Abholradius: erste zwei Ziffern vergleichen (h.)
    if (!empty($data['plz']) && substr($data['plz'], 0, 2) !== substr($verein_plz, 0, 2)) {
      $errors[] = 'Die Abholadresse liegt außerhalb des Abholgebiets.';
    }
  }

  // Krisengebiet gegen Liste prüfen (d., f., g.)
  if ($data['krisengebiet'] && !in_array($data['krisengebiet'], $krisengebiete, true)) {
    $errors[] = 'Bitte ein gültiges Krisengebiet auswählen.';
  }

  // Speichern und Bestätigung (d., i.)
  if (!$errors) {
    $title = ($data['uebergabeart']==='abholung' ? 'Abholung' : 'Übergabe')
           . ' – ' . $data['kleiderart'] . ' – ' . $data['krisengebiet'];

    $post_id = wp_insert_post([
      'post_type'   => 'spende',
      'post_status' => 'publish',
      'post_title'  => sanitize_text_field($title),
    ]);

    if (!is_wp_error($post_id)) {
      foreach ($data as $k=>$v) {
        update_post_meta($post_id, $k, $v);
      }
      update_post_meta($post_id, 'created_at', current_time('mysql'));
    }

    $success = true;
    $data['timestamp'] = current_time('timestamp');
  }
}

get_header(); // b.
?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <?php if ($success): ?>
        <!-- i: Bestätigungsansicht mit allen Daten -->
        <div class="card shadow-sm">
          <div class="card-body">
            <h2 class="h4 mb-3">Vielen Dank! Ihre Kleiderspende wurde registriert.</h2>
            <dl class="row">
              <dt class="col-sm-5">Übergabeart</dt>
              <dd class="col-sm-7"><?php echo spende_safe($data['uebergabeart'] === 'abholung' ? 'Abholung' : 'Übergabe an der Geschäftsstelle'); ?></dd>

              <dt class="col-sm-5">Art der Kleidung</dt>
              <dd class="col-sm-7"><?php echo spende_safe($data['kleiderart']); ?></dd>

              <dt class="col-sm-5">Krisengebiet</dt>
              <dd class="col-sm-7"><?php echo spende_safe($data['krisengebiet']); ?></dd>

              <dt class="col-sm-5">Datum und Uhrzeit</dt>
              <dd class="col-sm-7"><?php echo esc_html( wp_date('d.m.Y, H:i', $data['timestamp']) ); ?></dd>

              <dt class="col-sm-5">Ort</dt>
              <dd class="col-sm-7">
                <?php
                  // f, g, i: Ort je nach Übergabeart anzeigen
                  if ($data['uebergabeart'] === 'abholung') {
                    echo spende_safe($data['strasse'] . ', ' . $data['plz'] . ' ' . $data['ort']);
                  } else {
                    echo 'Geschäftsstelle (PLZ ' . spende_safe($verein_plz) . ')';
                  }
                ?>
              </dd>
            </dl>
            <a class="btn btn-primary mt-2" href="<?php echo esc_url( get_permalink() ); ?>">Weitere Spende registrieren</a>
          </div>
        </div>

      <?php else: ?>

        <!-- d: Formular zur Registrierung -->
        <h1 class="h3 mb-4"><?php the_title(); ?></h1>

        <?php if ($errors): ?>
          <!-- d: Fehlermeldungen nach Serverprüfung -->
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?><li><?php echo esc_html($e); ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" class="needs-validation" novalidate>
          <!-- e, f, g: Wahl der Übergabeart -->
          <div class="mb-4">
            <label class="form-label d-block">Übergabeart <span class="text-danger">*</span></label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="uebergabeart" id="ua_stelle" value="geschaeftsstelle" <?php checked(spende_field('uebergabeart') !== 'abholung'); ?> required>
              <label class="form-check-label" for="ua_stelle">Übergabe an der Geschäftsstelle</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="uebergabeart" id="ua_abholung" value="abholung" <?php checked(spende_field('uebergabeart') === 'abholung'); ?> required>
              <label class="form-check-label" for="ua_abholung">Abholung</label>
            </div>
          </div>

          <!-- d, f, g: Auswahlfelder für Kleidung und Krisengebiet -->
          <div class="row g-3">
            <div class="col-md-6">
              <label for="kleiderart" class="form-label">Art der Kleidung <span class="text-danger">*</span></label>
              <select class="form-select" id="kleiderart" name="kleiderart" required>
                <option value="">Bitte wählen…</option>
                <?php
                $arten = ['Jacken','Hosen','Kindersachen','Schuhe','Pullover','Mützen/Schals'];
                $prev  = spende_field('kleiderart');
                foreach ($arten as $a) {
                  printf('<option %s>%s</option>', selected($prev, $a, false), esc_html($a));
                }
                ?>
              </select>
              <div class="invalid-feedback">Bitte Art der Kleidung wählen.</div>
            </div>

            <div class="col-md-6">
              <label for="krisengebiet" class="form-label">Krisengebiet <span class="text-danger">*</span></label>
              <select class="form-select" id="krisengebiet" name="krisengebiet" required>
                <option value="">Bitte wählen…</option>
                <?php
                $prev = spende_field('krisengebiet');
                foreach ($krisengebiete as $kg) {
                  printf('<option %s>%s</option>', selected($prev, $kg, false), esc_html($kg));
                }
                ?>
              </select>
              <div class="invalid-feedback">Bitte Krisengebiet wählen.</div>
            </div>
          </div>

          <!-- g: Adressangaben nur bei Abholung -->
          <div id="adresseBlock" class="row g-3 mt-1"<?php echo (spende_field('uebergabeart') === 'abholung') ? '' : ' style="display:none"'; ?>>
            <div class="col-12">
              <label for="strasse" class="form-label">Straße und Nr.</label>
              <input type="text" class="form-control" id="strasse" name="strasse" value="<?php echo spende_safe(spende_field('strasse')); ?>">
              <div class="invalid-feedback">Bitte Straße und Hausnummer angeben.</div>
            </div>
            <div class="col-md-4">
              <label for="plz" class="form-label">PLZ</label>
              <input type="text" class="form-control" id="plz" name="plz" pattern="\d{5}" inputmode="numeric" value="<?php echo spende_safe(spende_field('plz')); ?>">
              <div class="invalid-feedback">Bitte gültige 5-stellige PLZ angeben.</div>
            </div>
            <div class="col-md-8">
              <label for="ort" class="form-label">Ort</label>
              <input type="text" class="form-control" id="ort" name="ort" value="<?php echo spende_safe(spende_field('ort')); ?>">
              <div class="invalid-feedback">Bitte Ort angeben.</div>
            </div>
          </div>

          <?php
          // d: Nonce-Feld für die Formularverarbeitung
          wp_nonce_field('spende_registrieren','spende_nonce');
          ?>

          <!-- d: Aktionen -->
          <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Spende registrieren</button>
            <a href="<?php echo esc_url( home_url('/') ); ?>" class="btn btn-outline-secondary">Abbrechen</a>
          </div>
        </form>

        <!-- f, g: Client-Logik für Sichtbarkeit und Pflichtfelder -->
        <script>
          (function(){
            const uaStelle = document.getElementById('ua_stelle');
            const uaAbhol  = document.getElementById('ua_abholung');
            const block    = document.getElementById('adresseBlock');

            function setRequired(on) {
              ['strasse','plz','ort'].forEach(function(id){
                const el = document.getElementById(id);
                if (el) el.required = on;
              });
            }

            function toggleAddress() {
              const abholung = uaAbhol.checked;
              block.style.display = abholung ? '' : 'none';
              setRequired(abholung);
            }

            uaStelle.addEventListener('change', toggleAddress);
            uaAbhol.addEventListener('change', toggleAddress);
            toggleAddress();

            // d: Browser-Validation aktivieren
            const form = document.querySelector('form.needs-validation');
            form.addEventListener('submit', function (e) {
              if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
              }
              form.classList.add('was-validated');
            }, false);
          })();
        </script>

      <?php endif; ?>

    </div>
  </div>
</div>

<?php get_footer(); // b. ?>
