<?php
/*
Plugin Name: Mon Plugin
Description: Plugin qui stocke les données d'un formulaire et les affiche sur une page WordPress
*/

// Création de la table de base de données
function mon_plugin_install() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'mon_plugin_data';
  $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    nom varchar(255) NOT NULL,
    prenom varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    sujet varchar(255) NOT NULL,
    message text NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY  (id)
  ) $charset_collate;";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
}
register_activation_hook( __FILE__, 'mon_plugin_install' );

function contact_form_delete_table()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'mon_plugin_data';
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'contact_form_delete_table');

// Affichage du formulaire
function mon_plugin_form() {
  ob_start();
  ?>
  <form method="post">
    <label for="name">Nom :</label>
    <input type="text" name="nom" required>
    <br>
    <label for="name">Prénom :</label>
    <input type="text" name="prenom" required>
    <br>
    <label for="email">Email :</label>
    <input type="email" name="email" required>
    <br>
    <label for="name">Sujet :</label>
    <input type="text" name="sujet" required>
    <br>
    <label for="message">Message :</label>
    <textarea name="message" required></textarea>
    <br>
    <input type="submit" name="submit" value="Envoyer">
  </form>
  <?php
  return ob_get_clean();
}
add_shortcode( 'mon_plugin_form', 'mon_plugin_form' );

// Traitement du formulaire
function mon_plugin_process_form() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'mon_plugin_data';
  if ( isset( $_POST['submit'] ) ) {
    $name = sanitize_text_field( $_POST['nom'] );
    $prenom = sanitize_text_field( $_POST['prenom'] );
    $email = sanitize_email( $_POST['email'] );
    $sujet = sanitize_text_field( $_POST['sujet'] );
    $message = sanitize_textarea_field( $_POST['message'] );
    $wpdb->insert(
      $table_name,
      array(
        'nom' => $name,
        'prenom' => $prenom,
        'email' => $email,
        'sujet' => $sujet,
        'message' => $message,
      )
    );
  }
}
add_action( 'init', 'mon_plugin_process_form' );

// Affichage des données sur une page
function cf_add_menu_page()
{
  add_menu_page('contact-form', 'contact-form', 'manage_options', 'cf_responses_page', 'cf_render_responses_page', 'dashicons-email-alt', 1);
}
add_action('admin_menu', 'cf_add_menu_page');

function cf_render_responses_page()
{
  if (!current_user_can('manage_options')) {
    return;
  }

  global $wpdb;
  $table_name = $wpdb->prefix . 'mon_plugin_data';
  $results = $wpdb->get_results("SELECT * FROM $table_name");

  echo '<div class="wrap bg-dark">';
  echo '<h1>' . esc_html__('Contact Form Responses', 'contact-form') . '</h1>';
  echo '<p>' . esc_html__('View and manage responses submitted through the contact form.') . '</p>';
  echo '<table class="wp-list-table widefat fixed striped">';
  echo '<thead>';
  echo '<tr>';
  echo '<th style="width: 2rem;">' . esc_html__('ID', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('nom', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('prenom', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('Email', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('sujet', 'contact-form') . '</th>';
  echo '<th>' . esc_html__('Message', 'contact-form') . '</th>';
  echo '</tr>';
  echo '</thead>';
  echo '<tbody>';
  foreach ($results as $row) {
    echo '<tr>';
    echo '<td>' . $row->id . '</td>';
    echo '<td>' . $row->nom . '</td>';
    echo '<td>' . $row->prenom . '</td>';
    echo '<td>' . $row->email . '</td>';
    echo '<td>' . $row->sujet . '</td>';
    echo '<td>' . $row->message . '</td>';
    echo '</tr>';
  }
  echo '</tbody>';
  echo '</table>';

  echo '</div>';
}

