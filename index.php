<?php 
/**
 * index.php – Fallback-Template – nicht notwendig in dieser Fallstudie
 *
 * Bezug zu den Aufgaben theoretisch (a–i):
 * b. Struktur: Grundgerüst mit Header, Content, Footer
 * c. Responsivität: Layout über Theme/Bootstrap
 * d. Formular: nicht hier, eigene Vorlage
 * e, f, g. Übergabe/Abholung: nicht hier, im Formular
 * h. PLZ-Prüfung: nicht hier, im Formular
 * i. Bestätigungsseite: nicht hier, eigene Vorlage
 *
 * Zweck dieser Datei:
 * - Pflichtdatei jedes Themes (WordPress-Standard), wird aber in dieser Fallstudie nicht gebraucht.
 * - Allgemeine Vorlage, wenn keine spezifischere Datei greift.
 */

get_header(); // b
?>

<main class="container altkleider-content">
  <?php 
  // b, c: Standard-Loop zur Ausgabe von Beiträgen/Seiten
  if (have_posts()): 
    while (have_posts()): the_post(); 
      the_title('<h2>', '</h2>');   // b: Überschrift
      the_content();                // b: Inhalt
    endwhile; 
  else: 
    // b: Hinweis, falls keine Inhalte vorhanden sind
    echo '<p>Keine Inhalte vorhanden.</p>'; 
  endif; 
  ?>
</main>

<?php 
get_footer(); // b
?>
