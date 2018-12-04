<?php
/**
 * @package POF Settings
 */
/*
Plugin Name: POF Settings
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/* Helper functions */
include( plugin_dir_path( __FILE__ ) . 'pof-settings-helpers.php');

add_action( 'admin_menu', 'pof_settings_menu' );

global $pof_settings_db_version;
$pof_settings_db_version = '1.0';


function pof_settings_get_table_name_languages() {
	global $wpdb;
	return $wpdb->prefix . 'pof_settings_languages';
}

function pof_settings_install() {
	global $wpdb;
	global $pof_settings_db_version;

	$table_name_languages = pof_settings_get_table_name_languages();

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name_languages (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		lang_title varchar(255) DEFAULT '' NOT NULL,
		lang_code varchar(255) DEFAULT '' NOT NULL,
		is_active smallint(4) DEFAULT 0 NOT NULL,
		is_default smallint(4) DEFAULT 0 NOT NULL,
		UNIQUE KEY id (id),
		KEY lang_title (lang_title),
		KEY lang_code (lang_code),
		KEY lang_active (is_active),
		KEY lang_default (is_default)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'pof_settings_db_version', $pof_settings_db_version );
}
pof_settings_install();

function pof_settings_menu() {
	add_menu_page('POF Settings', 'POF Asetukset', 'pof_manage_options', 'pof_settings_frontpage-handle', 'pof_settings_frontpage', 'dashicons-admin-settings');
	add_submenu_page( 'pof_settings_frontpage-handle', 'Yleiset asetukset', 'Yleiset asetukset', 'pof_manage_options', 'pof_settings_general-handle', 'pof_settings_general');
	add_submenu_page( 'pof_settings_frontpage-handle', 'Kielet', 'Kielet', 'pof_manage_options', 'pof_settings_languages-handle', 'pof_settings_languages');
  add_submenu_page( 'pof_settings_frontpage-handle', 'Ikonit', 'Ikonit', 'pof_manage_options', 'pof_settings_icons-handle', 'pof_settings_icons');
}


function pof_settings_frontpage() {
	if ( !current_user_can( 'pof_manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h1>POF Asetukset</h1>';
	echo '<p>Valitse vasemmasta valikosta, mit&auml; haluat muokata.</p>';
	echo '</div>';
}

function pof_settings_general() {
	if ( !current_user_can( 'pof_manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}


	if(isset($_POST['Submit'])) {
		if (isset($_POST["google_api_certificate_name"])) {
			update_option("pof_settings_google_api_certificate_name", $_POST["google_api_certificate_name"]);
		}

		if (isset($_POST["google_api_user"])) {
			update_option("pof_settings_google_api_user", $_POST["google_api_user"]);
		}

		if (isset($_POST["google_api_password"])) {
			update_option("pof_settings_google_api_password", $_POST["google_api_password"]);
		}

		if (isset($_POST["suggestions_emails"])) {
			update_option("pof_settings_suggestions_emails", $_POST["suggestions_emails"]);
		}

    if (isset($_POST["suggestions_email_interval"])) {
      update_option("pof_settings_suggestions_email_interval", $_POST["suggestions_email_interval"]);
    }

    if (isset($_POST["suggestions_email_sender_name"])) {
			update_option("pof_settings_suggestions_email_sender_name", $_POST["suggestions_email_sender_name"]);
		}

    if (isset($_POST["suggestions_email_sender_email"])) {
			update_option("pof_settings_suggestions_email_sender_email", $_POST["suggestions_email_sender_email"]);
		}

    if (isset($_POST["suggestions_allowed_domains"])) {
			update_option("pof_settings_suggestions_allowed_domains", $_POST["suggestions_allowed_domains"]);
		}

    if (isset($_POST["suggestions_allowed_file_types"])) {
			update_option("pof_settings_suggestions_allowed_file_types", $_POST["suggestions_allowed_file_types"]);
		}

    if (isset($_POST["lastupdate_overwrite"])) {
			update_option("pof_settings_lastupdate_overwrite", $_POST["lastupdate_overwrite"]);
		}

    if (isset($_POST["fulljson_cache_ttl"])) {
			update_option("pof_settings_fulljson_cache_ttl", $_POST["fulljson_cache_ttl"]);
		}
	}

	echo '<div class="wrap">';
	echo '<h1>POF Settings, yleiset</h1>';

?>

	<form method="post" action="">
		<?php settings_fields( 'pof_settings-general' ); ?>
		<?php do_settings_sections( 'pof_settings-general' ); ?>

		<h2>Google Drive API</h2>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Certifikaatin nimi (sijoita kyseinen tiedosto plugins\pof-settings\certificates-kansioon</th>
				<td><input style="width: 600px;" id="google_api_certificate_name_search" autocomplete="off" type="text" name="google_api_certificate_name" value="<?php echo esc_attr( get_option('pof_settings_google_api_certificate_name') ); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row">Api k&auml;ytt&auml;j&auml;</th>
				<td><input style="width: 600px;" id="google_api_user_search" autocomplete="off" type="text" name="google_api_user" value="<?php echo esc_attr( get_option('pof_settings_google_api_user') ); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row">Salasana</th>
				<td><input style="width: 600px;" id="google_api_password_search" autocomplete="off" type="password" name="google_api_password" value="<?php echo esc_attr( get_option('pof_settings_google_api_password') ); ?>" /></td>
			</tr>
		</table>
        <h2>Vinkit</h2>
        <table class="form-table">
      			<tr valign="top">
      				<th scope="row">Kelle l&auml;hetet&auml;&auml;n s&auml;hk&ouml;postia uuista vinkeist&auml;. Erottele pilkulla</th>
      				<td><input style="width: 600px;" id="suggestions_emails" autocomplete="off" type="text" name="suggestions_emails" value="<?php echo esc_attr( get_option('pof_settings_suggestions_emails') ); ?>" /></td>
      			</tr>
            <tr valign="top">
                <th scope="row">Sähköpostien maksimi lähetysväli (min) ?</th>
                <td><input style="width: 600px;" id="suggestions_email_interval" autocomplete="off" type="number" min="0" name="suggestions_email_interval" value="<?php echo esc_attr( get_option('pof_settings_suggestions_email_interval') ); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Mill&auml; nimell&auml; l&auml;hetet&auml;&auml;n</th>
                <td><input style="width: 600px;" id="suggestions_email_sender_name" autocomplete="off" type="text" name="suggestions_email_sender_name" value="<?php echo esc_attr( get_option('pof_settings_suggestions_email_sender_name') ); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Mist&auml; osoitteesta l&auml;hetet&auml;&auml;n.</th>
                <td><input style="width: 600px;" id="suggestions_email_sender_email" autocomplete="off" type="text" name="suggestions_email_sender_email" value="<?php echo esc_attr( get_option('pof_settings_suggestions_email_sender_email')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Domainit, joista saa l&auml;hett&auml; vinkkej&auml; liitteineen (jokainen omalle rivilleen, koko domain, http:n kanssa).</th>
                <td>
                    <textarea id="suggestions_allowed_domains" rows="10" cols="80" autocomplete="off" name="suggestions_allowed_domains"><?php echo esc_attr( get_option('pof_settings_suggestions_allowed_domains')); ?></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                  Sallitut tiedostotyypit vinkkien liitteille (yksi per rivi, ilman pistettä)<br>
                  Huom! Käytä MIME-tyyppiä, esim. image/png tai application/pdf
                </th>
                <td>
                    <textarea id="suggestions_allowed_file_types" rows="10" cols="80" autocomplete="off" name="suggestions_allowed_file_types"><?php echo esc_attr( get_option('pof_settings_suggestions_allowed_file_types')); ?></textarea>
                </td>
            </tr>
        </table>
        <h2>Itemit</h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Yliaja viimeisimm&auml;n muokkauksen p&auml;iv&auml;m&auml;&auml;r&auml; (Muoto: YYYY-MM-DD HH:mm:ss (esim. 2016-08-29 12:00:00)).</th>
                <td>
                    <input style="width: 600px;" id="lastupdate_overwrite" autocomplete="off" type="text" name="lastupdate_overwrite" value="<?php echo esc_attr( get_option('pof_settings_lastupdate_overwrite') ); ?>" />
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">T&auml;yden jsonin cache sekuntteina.</th>
                <td>
                    <input style="width: 600px;" id="fulljson_cache_ttl" autocomplete="off" type="number" min="60" name="fulljson_cache_ttl" value="<?php echo esc_attr( get_option('pof_settings_fulljson_cache_ttl') ); ?>" />
                </td>
            </tr>
        </table>

		<input type="submit" name="Submit" value="Submit" />

	</form>
	<?php

	echo '</div>';
}

function pof_settings_languages() {
	if ( !current_user_can( 'pof_manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	$languages = pof_settings_get_all_languages();



	if(isset($_POST['Submit'])) {

		global $wpdb;
		global $pof_settings_db_version;

		$table_name_languages = pof_settings_get_table_name_languages(false);

		if (   isset($_POST["pof_settings_add_lang"])
			&& !empty($_POST["pof_settings_add_lang"])
			&& isset($_POST["pof_settings_add_lang"]["title"])
			&& !empty($_POST["pof_settings_add_lang"]["title"])
			&& isset($_POST["pof_settings_add_lang"]["code"])
			&& !empty($_POST["pof_settings_add_lang"]["code"])) {

			$lang_active = 0;

			if (   isset($_POST["pof_settings_add_lang"]["active"])
				&& !empty($_POST["pof_settings_add_lang"]["active"])
				&& $_POST["pof_settings_add_lang"]["active"] == "1") {

				$lang_active = 1;
			}

			$default = 0;

			if (empty($languages)) {
				$default = 1;
				$lang_active = 1;
			}

			$tmp = $wpdb->insert(
				$table_name_languages,
				array(
					'lang_title' => $_POST["pof_settings_add_lang"]["title"],
					'lang_code' => $_POST["pof_settings_add_lang"]["code"],
					'is_active' => (int) $lang_active,
					'is_default' => (int) $default
				),
				array(
					'%s',
					'%s',
					'%d',
				)
			);

		}

		if (   isset($_POST["pof_settings_language"])
			&& !empty($_POST["pof_settings_language"])) {

			foreach ($_POST["pof_settings_language"] as $lang_id => $lang) {
				if (   isset($lang["title"])
					&& !empty($lang["title"])
					&& isset($lang["code"])
					&& !empty($lang["code"])) {

					$lang_active = 0;

					if (   isset($lang["active"])
						&& !empty($lang["active"])
						&& $lang["active"] == "1") {

						$lang_active = 1;
					}


					$tmp = $wpdb->update(
						$table_name_languages,
						array(
							'lang_title' => $lang["title"],
							'lang_code' => $lang["code"],
							'is_active' => (int) $lang_active
						),
						array(
							'id' => $lang_id
						),
						array(
							'%s',
							'%s',
							'%d'
						),
						array(
							'%d'
						)
					);
				}

			}
		}

		$languages = pof_settings_get_all_languages(false);
	}

	echo '<div class="wrap">';
	echo '<h1>POF Settings, kielet</h1>';

	echo '<form method="post" action="">';

	echo '<table cellpadding="2" cellspacing="2" border="2">';
	echo '<thead>';
	echo '<tr>';
	echo '<th><h2>Kieli</h2></th>';
	echo '<th><h2>Koodi</h2></th>';
	echo '<th><h2>Aktiivinen</h2></th>';
	echo '<th><h2>Default</h2></th>';
	echo '<tr>';
	echo '</thead>';
	echo '<tbody>';

	foreach ($languages as $language) {
?>
	<tr>
		<td><input type="text" name="pof_settings_language[<?php echo $language->id; ?>][title]" value="<?php echo $language->lang_title; ?>" /></td>
		<td><input type="text" name="pof_settings_language[<?php echo $language->id; ?>][code]"  value="<?php echo $language->lang_code; ?>" /></td>
<?php
if ($language->is_default) {
?>
		<td>X</td>
		<td>X</td>

<?php
}
else {
?>
		<td><input type="checkbox" name="pof_settings_language[<?php echo $language->id; ?>][active]" value="1" <?php if ( $language->is_active) { echo " checked='checked'"; } ?> /></td>
		<td></td>
<?php
}
?>
	</tr>

<?php

	}
?>

	<tr>
		<td><input type="text" name="pof_settings_add_lang[title]" /></td>
		<td><input type="text" name="pof_settings_add_lang[code]" /></td>
		<td><input type="checkbox" name="pof_settings_add_lang[active]" value="1" /></td>
	</tr>

<?php

	echo '</tbody>';
	echo '</table>';
	echo '<br /><input type="submit" name="Submit" value="Submit" />';
	echo '</form>';

	echo '</div>';

}

function pof_settings_icons() {
  if (isset($_POST["pof_settings_icons_sizes"])) {
    update_option("pof_settings_icons_sizes", $_POST["pof_settings_icons_sizes"]);
  }
  ?>
  <div class="wrap">
  <h1>POF Settings, ikonit</h1>
  <form method="post" action="">
      <table class="form-table">
          <tr valign="top">
              <th scope="row">
                Ikoneista generoitavat kuvakoot (Syöttäminen muodossa 64x64 per rivi)<br>
                <br>
                <a href="/wp-admin/tools.php?page=regenerate-thumbnails#/">Thumbnailien regenerointi</a>
              </th>
              <td>
                  <textarea id="pof_settings_icons_sizes" rows="10" cols="80" autocomplete="off" name="pof_settings_icons_sizes"><?php echo esc_attr( get_option('pof_settings_icons_sizes')); ?></textarea>
              </td>
          </tr>
      </table>
      <br /><input type="submit" name="Submit" value="Submit" />
  </form>
  </div>
<?php }