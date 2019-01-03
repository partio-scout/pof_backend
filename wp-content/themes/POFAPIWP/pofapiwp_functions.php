<?php

add_action( 'admin_footer', 'pof_validation_script' );
function pof_validation_script() {
  global $post;
  if ( $post->post_type != 'pof_post_task' && $post->post_type != 'pof_post_taskgroup'){
    return;
  } ?>
  <script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery('#publish').click(function() {

      if(jQuery(this).data("valid")) {
        return true;
      }

      var form_data = jQuery('#post').serializeArray();
      var data = {
          action: 'pof_custom_validation',
          security: '<?php echo wp_create_nonce( 'pre_publish_validation' ); ?>',
          form_data: jQuery.param(form_data),
      };

      jQuery.post(ajaxurl, data, function(response) {
        if (response.indexOf('true') > -1 || response == true) {
            jQuery('#publish').data("valid", true).trigger('click');
        } else {
            jQuery('.pof-error').remove();
            jQuery('.wp-header-end').after('<div id="message" class="notice notice-error pof-error"><p>Virhe: ' + response + '</p></div>');
            jQuery("#post").data("valid", false);
          }
      });

      return false;
    });
  });
  </script> <?php
}

add_action( 'wp_ajax_pof_custom_validation', 'pof_custom_validation' );
function pof_custom_validation() {
  check_ajax_referer( 'pre_publish_validation', 'security' );

  parse_str( $_POST['form_data'], $vars );

  $post_type = $vars['post_type'];

  // Task:
  // - Check that additional taskgroups are from same program as primary taskgroup
  // - Check that additional taskgroup is not same as primary taskgroup
  if($post_type = 'pof_post_task') {
    $primary_taskgroup = $vars['acf']['field_54f5bde393d24'];
    //$additional_taskgroups = $vars['acf']['field_5be2d365716ff'];
    $additional_taskgroups = $vars['acf']['field_5beac55381ef1'];

    $object = (object) ['ID' => $primary_taskgroup, 'post_type' => $post_type];
    $primary_taskgroup_program = end(pof_get_parent_tree($object, array()))->ID;

    foreach($additional_taskgroups as $taskgroup) {
      $taskgroup_object = (object) ['ID' => $taskgroup, 'post_type' => 'pof_post_taskgroup'];
      $taskgroup_program = end(pof_get_parent_tree($taskgroup_object, array()))->ID;

      if($taskgroup_program != $primary_taskgroup_program) {
        _e("Ylimääräisten aktiviteettipakettien täytyy kuulua samaan ohjelmaan, kuin ensisijaisen aktiviteettipaketin");
        die();
      }

      if($taskgroup_object->ID == $object->ID) {
        _e("Sama aktiviteettipaketti ei voi olla asetettuna sekä ensisijaiseksi että toissijaiseksi");
        die();
      }

    }
  }

  // Taskgroups: Check that taskgroup doesn't have itself as a parent
  if($post_type = 'pof_post_taskgroup') {
    $taskgroup_id = $vars['acf']['field_5634d05b4db69'];
    $post_id = $vars['post_ID'];

    if($taskgroup_id === $post_id) {
      _e("Aktiviteettipaketti ei voi olla itsensä aktiviteettipaketti");
      die();
    }

  }

  echo 'true';
  die();
}

add_action( 'admin_footer', 'jquery_sortable' );
function jquery_sortable() { ?>
  <script type="text/javascript">
  jQuery(document).ready(function(){
    if(jQuery("#post-children").length) {
      jQuery("#post-children").sortable({
        cursor:'move',
        update: function( event, ui ) {
          var order = jQuery('#post-children').sortable('toArray');
          var data = {
            'action': 'update_menu_order',
            'order': order
          };

          jQuery.post(ajaxurl, data, function(response) {
            console.log(response);
          });

        }
      });
      jQuery("#post-children").disableSelection();
    }
  });
  </script> <?php
}

add_action( 'wp_ajax_update_menu_order', 'update_menu_order' );
function update_menu_order() {
	$order = array_reverse($_POST['order']);

  for($i=0; $i < count($order); $i++) {
    $post_id = intval($order[$i]);
    $selected_post = get_post($post_id);
    $post_data = array(
        'ID'         => $post_id,
        'menu_order' => $i
    );
    $updated_post = wp_update_post( $post_data, true );

    if (is_wp_error($updated_post)) {
      $errors = $post_id->get_error_messages();
      foreach ($errors as $error) {
        echo $error;
      }
    }
  }

  echo "Order updated";

	wp_die();
}

function get_post_custom_attributes($post) {

	$ret = array();

	switch ($post->post_type) {
		case "pof_post_program":

			$languages = get_field("kielet", $post->ID);

			$langs = array();
			if (!empty($languages)) {
				foreach ($languages as $language) {
					array_push ($langs, array('name'=>'language', 'attributes' => array('value' => $language)));
				}
			}

			$ret = 	array(
				'name'=>'leaf',
				array(
					'name'=>'languages',
					$langs
				)
			);

			break;

		case "pof_post_agegroup":
			$ret = 	array(
				'name'=>'leaf',
				array(
					'name'=>'minAge',
					'attributes' => array(
						'value' => get_field("agegroup_min_age")
					),
				),
				array(
					'name'=>'maxAge',
					'attributes' => array(
						'value' => get_field("agegroup_max_age")
					),
				)
			);

			break;
	}

	return $ret;

}

function get_post_tags_XML($post_id) {
	$ret = array(
		'name'=>'tags'
	);

	$taitoalueet_tags = wp_get_post_terms($post_id, 'pof_tax_skillarea');

	$taitoalueet = array(
		'name'=>'taitoalueet'
	);

	foreach ($taitoalueet_tags as $taitoalue_tag) {
		$taitoalue = array(
			'name'=>'taitoalue',
			'value' => $taitoalue_tag->name,
			'attributes' => array(
				'slug' => $taitoalue_tag->slug,
				'id' => $taitoalue_tag->term_taxonomy_id
			),
		);
		array_push($taitoalueet, $taitoalue);
	}

	array_push($ret, $taitoalueet);

	$suoritus_kesto_tags = wp_get_post_terms($post_id, 'pof_tax_taskduration');

	$suoritus_kestot = array(
		'name'=>'task_duration'
	);

	foreach ($suoritus_kesto_tags as $suoritus_kesto_tag) {
		$suoritus_kesto = array(
			'name'=>'duration',
			'value' => $suoritus_kesto_tag->name,
			'attributes' => array(
				'slug' => $suoritus_kesto_tag->slug,
				'id' => $suoritus_kesto_tag->term_taxonomy_id
			),
		);
		array_push($suoritus_kestot, $suoritus_kesto);
	}

	array_push($ret, $suoritus_kestot);

	$suoritus_valmistelu_kesto_tags = wp_get_post_terms($post_id, 'pof_tax_taskpreparationduration');

	$suoritus_valmistelu_kestot = array(
		'name'=>'task_preaparation_duration'
	);

	foreach ($suoritus_valmistelu_kesto_tags as $suoritus_valmistelu_kesto_tag) {
		$suoritus_valmistelu_kesto = array(
			'name'=>'duration',
			'value' => $suoritus_valmistelu_kesto_tag->name,
			'attributes' => array(
				'slug' => $suoritus_valmistelu_kesto_tag->slug,
				'id' => $suoritus_valmistelu_kesto_tag->term_taxonomy_id
			),
		);
		array_push($suoritus_valmistelu_kestot, $suoritus_valmistelu_kesto);
	}

	array_push($ret, $suoritus_valmistelu_kestot);

	$tarvike_tags = wp_get_post_terms($post_id, 'pof_tax_equipment');

	$tarvikkeet = array(
		'name'=>'equipments'
	);

	foreach ($tarvike_tags as $tarvike_tag) {
		$tarvike = array(
			'name'=>'equipment',
			'value' => $tarvike_tag->name,
			'attributes' => array(
				'slug' => $tarvike_tag->slug,
				'id' => $tarvike_tag->term_taxonomy_id
			),
		);
		array_push($tarvikkeet, $tarvike);
	}

	array_push($ret, $tarvikkeet);

	return $ret;
}

function get_post_images_XML($post_id) {
	$ret = array(
		'name'=>'images'
	);

	$logo = get_field('logo_image', $post_id);

	if ($logo) {
		$logo_arr = array(
			'name'=>'logo',
			'value' => $logo['title'],
			'attributes' => array(
				'mime_type' => $logo['mime_type'],
				'height' => $logo['height'],
				'width' => $logo['width'],
				'url' => $logo['url']
			),
		);
	} else {
		$logo_arr = array(
			'name'=>'logo'
		);
	}


	array_push($ret, $logo_arr);

	$main_image = get_field('main_image', $post_id);


	if ($main_image) {
		$mainimage_arr = array(
			'name'=>'main_image',
			'value' => $main_image['title'],
			'attributes' => array(
				'mime_type' => $main_image['mime_type'],
				'height' => $main_image['height'],
				'width' => $main_image['width'],
				'url' => $main_image['url']
			),
		);
	} else {
		$mainimage_arr = array(
			'name'=>'main_image'
		);
	}


	array_push($ret, $mainimage_arr);

	return $ret;
}

function get_post_additional_content_XML($post_id) {
	$ret = array(
		'name'=>'additional_content'
	);

	$images = simple_fields_fieldgroup("additional_images_fg", $post_id);

	$images_arr = array(
		'name'=>'images'
	);

	if ($images) {
		foreach ($images as $additional_image) {
			if ($additional_image['additional_image']) {
				$image = $additional_image['additional_image'];
				$image_arr = array(
					'name'=>'image',
					'value' => $additional_image['additional_image_text'],
					'attributes' => array(
						'mime_type' => $image['mime'],
						'height' => $image['metadata']['height'],
						'width' => $image['metadata']['width'],
						'url' => $image['url']
					),
				);
				array_push($images_arr, $image_arr);
			}
		}
	}

	array_push($ret, $images_arr);

	$files = simple_fields_fieldgroup("additional_files_fg", $post_id);

	$files_arr = array(
		'name'=>'files'
	);

	if ($files) {
		foreach ($files as $additional_file) {
			if ($additional_file['additional_file']) {

				$file = $additional_file['additional_file'];

				$file_arr = array(
					'name'=>'file',
					'value' => $additional_file['additional_file_text'],
					'attributes' => array(
						'mime_type' => $file['mime'],
						'url' => $file['url']
					),
				);
				array_push($files_arr, $file_arr);
			}
		}
	}

	$links = simple_fields_fieldgroup("additional_links_fg", $post_id);

	$links_arr = array(
		'name'=>'links'
	);

	if ($links) {
		foreach ($links as $additional_link) {
			if ($additional_link['additional_link_url']) {

				$link = $additional_link['additional_link_url'];

				$link_arr = array(
					'name'=>'link',
					'value' => $additional_link['additional_link_text'],
					'attributes' => array(
						'url' => $link
					),
				);
				array_push($links_arr, $link_arr);
			}
		}
	}

	array_push($ret, $links_arr);

	array_push($ret, $files_arr);


	return $ret;
}

function generate_xml_element( $dom, $data ) {
	$dom->formatOutput = true; // Add whitespace to make easier to read XML
	if ( empty( $data['name'] ) )
		return false;

	// Create the element
	$element_value = ( ! empty( $data['value'] ) ) ? $data['value'] : null;
	$element = $dom->createElement( $data['name'], $element_value );

	// Add any attributes
	if ( ! empty( $data['attributes'] ) && is_array( $data['attributes'] ) ) {
		foreach ( $data['attributes'] as $attribute_key => $attribute_value ) {
			$element->setAttribute( $attribute_key, $attribute_value );
		}
	}

	// Any other items in the data array should be child elements
	foreach ( $data as $data_key => $child_data ) {
		if ( ! is_numeric( $data_key ) )
			continue;

		$child = generate_xml_element( $dom, $child_data );
		if ( $child )
			$element->appendChild( $child );
	}

	return $element;
}

function getXML($data) {
	$doc = new DOMDocument();
	$doc->formatOutput = true; // Add whitespace to make easier to read XML
	$doc->preserveWhiteSpace = false;
	$child = generate_xml_element( $doc, $data );
	if ( $child )
		$doc->appendChild( $child );
	$outXml = $doc->saveXML();

	$xml = new DOMDocument();
	$xml->preserveWhiteSpace = false;
	$xml->formatOutput = true;
	$xml->loadXML($outXml);
	$outXml = $xml->saveXML();
	return $outXml;
}



/** JSON FUNCTIONS */


//$pof_available_languages = array('sv', 'en');
$pof_settings_langs = array();
$pof_available_languages = pof_settings_get_active_lang_codes();

$pof_settings_lastupdate_overwrite = pof_settings_get_lastupdate_overwrite();


function getLastModifiedBy($userId) {
	$tmp = new stdClass();
	$tmp->id = $userId;
	if (!empty($userId)) {
		$tmp->name = get_userdata($userId)->display_name;
	}
	return $tmp;
}


function getJsonItemBaseDetails($jsonItem, $post) {
	global $pof_available_languages;
    global $pof_settings_lastupdate_overwrite;

    if ($pof_settings_lastupdate_overwrite == null) {
	    $jsonItem->lastModified = $post->post_modified;
    } else {
	    $jsonItem->lastModified = $pof_settings_lastupdate_overwrite;
    }


//	$jsonItem->lastModifiedBy = getLastModifiedBy(get_post_meta( $post->ID, '_edit_last', true));

	$post_guid = get_post_meta( $post->ID, "post_guid", true );

	$jsonItem->guid = $post_guid;

	$lang_obj = new stdClass();
	$lang_obj->lang = 'fi';
	$lang_obj->title = $post->post_title;
	$lang_obj->details = get_site_url() . "/item-json/?postGUID=".$post_guid."&lang=fi";


    if ($pof_settings_lastupdate_overwrite == null) {
	    $lang_obj->lastModified = $post->post_modified;
    } else {
	    $lang_obj->lastModified = $pof_settings_lastupdate_overwrite;
    }

	if (empty($jsonItem->languages)) {
		$jsonItem->languages = array();
	}
	array_push($jsonItem->languages, $lang_obj);

	foreach ($pof_available_languages as $available_language) {
		$tmp = get_field("title_".strtolower($available_language), $post->ID);
		if (!empty($tmp)) {
			$lang_obj = new stdClass();
			$lang_obj->lang = $available_language;
			$lang_obj->title = $tmp;
			$lang_obj->details = get_site_url() . "/item-json/?postGUID=".$post_guid."&lang=".$available_language;
			if ($pof_settings_lastupdate_overwrite == null) {
                $lang_obj->lastModified = $post->post_modified;
            } else {
                $lang_obj->lastModified = $pof_settings_lastupdate_overwrite;
            }
			array_push($jsonItem->languages, $lang_obj);
		}

	}

	return $jsonItem;
}

function getJsonItemBaseDetailsItem($jsonItem, $post) {
	global $pof_available_languages;
    global $pof_settings_lastupdate_overwrite;

    if ($pof_settings_lastupdate_overwrite == null) {
	    $jsonItem->lastModified = $post->post_modified;
    } else {
	    $jsonItem->lastModified = $pof_settings_lastupdate_overwrite;
    }

	$jsonItem->lastModifiedBy = getLastModifiedBy(get_post_meta( $post->ID, '_edit_last', true));

	$post_guid = get_post_meta( $post->ID, "post_guid", true );

	$jsonItem->guid = $post_guid;

	$lang_obj = new stdClass();
	$lang_obj->lang = 'fi';
	$lang_obj->details = get_site_url() . "/item-json/?postGUID=".$post_guid."&lang=fi";
	if ($pof_settings_lastupdate_overwrite == null) {
	    $lang_obj->lastModified = $post->post_modified;
    } else {
	    $lang_obj->lastModified = $pof_settings_lastupdate_overwrite;
    }
	array_push($jsonItem->languages, $lang_obj);

	foreach ($pof_available_languages as $available_language) {
		$tmp = get_field("title_".strtolower($available_language), $post->ID);
		if (!empty($tmp)) {
			$lang_obj = new stdClass();
			$lang_obj->lang = $available_language;
			$lang_obj->details = get_site_url() . "/item-json/?postGUID=".$post_guid."&lang=".$available_language;
			if ($pof_settings_lastupdate_overwrite == null) {
                $lang_obj->lastModified = $post->post_modified;
            } else {
                $lang_obj->lastModified = $pof_settings_lastupdate_overwrite;
            }
			array_push($jsonItem->languages, $lang_obj);
		}

	}

	return $jsonItem;
}

function getJsonItemDetailsProgram($jsonItem, $post) {
	$jsonItem->owner = get_post_meta($post->ID, "program_owner", true);
	$jsonItem->lang = get_post_meta($post->ID, "program_lang", true);
	return $jsonItem;
}

function getJsonItemDetailsAgegroup($jsonItem, $post, $lang) {
	$jsonItem->minAge = get_post_meta($post->ID, "agegroup_min_age", true);
	$jsonItem->maxAge = get_post_meta($post->ID, "agegroup_max_age", true);
	$jsonItem->subtaskgroup_term = getJsonSubtaskgroupTerm(get_field("agegroup_subtaskgroup_term", $post->ID), $lang);
	return $jsonItem;
}

function getJsonItemDetailsTaskgroup($jsonItem, $post, $lang) {
	$jsonItem->additional_tasks_count = get_post_meta($post->ID, "taskgroup_additional_tasks_count", true);
	$jsonItem->subtask_term = getJsonTaskTerm(get_post_meta($post->ID, "taskgroup_subtask_term", true), $lang);
	return $jsonItem;
}


function getJsonSubtaskgroupTerm($term, $lang = 'fi') {

    if ($term != "" && $term != false && $term != "null") {

	    $ret = new stdClass();
	    $ret->name = $term;

	    $tmp_name_single = pof_taxonomy_translate_get_translation('taskgroup_term', $term.'_single', 0, $lang, true);
	    $tmp_name_plural = pof_taxonomy_translate_get_translation('taskgroup_term', $term.'_plural', 0, $lang, true);

	    if (   !empty($tmp_name_single)
		    && !empty($tmp_name_plural)) {
		    $ret->single = $tmp_name_single[0]->content;
		    $ret->plural = $tmp_name_plural[0]->content;
	    } else {
		    switch ($term) {
			    default:
			    case "":
				    return null;
				    break;
			    case "jalki":
				    $ret->single = mb_convert_encoding("Jälki","UTF-8", "auto");
				    $ret->plural = mb_convert_encoding("Jäljet","UTF-8", "auto");
				    break;
			    case "kasvatusosio":
				    $ret->single = "Kasvatusosio";
				    $ret->plural = "Kasvatusosiot";
				    break;
			    case "ilmansuunta":
				    $ret->single = "Ilmansuunta";
				    $ret->plural = "Ilmansuunnat";
				    break;
			    case "taitomerkki":
				    $ret->single = "Taitomerkki";
				    $ret->plural = "Taitomerkit";
				    break;
			    case "tarppo":
				    $ret->single = "Tarppo";
				    $ret->plural = "Tarpot";
				    break;
			    case "ryhma":
				    $ret->single = mb_convert_encoding("Ryhmä","UTF-8", "auto");
				    $ret->plural = mb_convert_encoding("Ryhmät","UTF-8", "auto");
				    break;
			    case "aktiviteetti":
				    $ret->single = "Aktiviteetti";
				    $ret->plural = "Aktiviteetit";
				    break;
			    case "aihe":
				    $ret->single = "Aihe";
				    $ret->plural = "Aiheet";
				    break;
			    case "tasku":
				    $ret->single = "Tasku";
				    $ret->plural = "Taskut";
				    break;
			    case "rasti":
				    $ret->single = "Rasti";
				    $ret->plural = "Rastit";
				    break;

		    }
	    }

	    return $ret;
    }

    return null;
}

function getJsonTaskTerm($term, $lang = 'fi') {
    if ($term != "" && $term != false && $term != "null" && $term != null) {
	    $ret = new stdClass();
	    $ret->name = $term;

        $tmp_name_single = pof_taxonomy_translate_get_translation('task_term', $term.'_single', 0, $lang, true);
        $tmp_name_plural = pof_taxonomy_translate_get_translation('task_term', $term.'_plural', 0, $lang, true);

        if (   !empty($tmp_name_single)
            && !empty($tmp_name_plural)) {
            $ret->single = $tmp_name_single[0]->content;
            $ret->plural = $tmp_name_plural[0]->content;
        } else {

            switch ($term) {
                default:
                case "":
                    return null;
                    break;
                case "askel":
                    $ret->single = "Askel";
                    $ret->plural = "Askeleet";
                    break;
                case "aktiviteetti":
                    $ret->single = "Aktiviteetti";
                    $ret->plural = "Aktiviteetit";
                    break;
                case "aktiviteettitaso":
                    $ret->single = "Aktiviteettitaso";
                    $ret->plural = "Aktiviteettitasot";
                    break;
                case "suoritus":
                    $ret->single = "Aktiviteetti";
                    $ret->plural = "Aktiviteetit";
                    break;
                case "paussi":
                    $ret->single = "Paussi";
                    $ret->plural = "Paussit";
                    break;

            }
        }
        return $ret;
    }
    return null;

}

$mandatory_task_guids = array();

function getJsonItemDetailsTask($jsonItem, $post) {
	global $pof_available_languages;
	global $mandatory_task_guids;
    global $pof_settings_lastupdate_overwrite;

	if (get_post_meta($post->ID, "task_mandatory", true)) {
		array_push($mandatory_task_guids, get_post_meta( $post->ID, "post_guid", true ));
	}

	$post_guid = get_post_meta( $post->ID, "post_guid", true );


	$suggestiongs_tmp = pof_order_post_suggestions_by_lang($post);

	foreach ($pof_available_languages as $available_language) {
		if (isset($suggestiongs_tmp[$available_language])) {
			$tmp = get_post_meta($post->ID, "title_".strtolower($available_language), true);
			if (!empty($tmp) || $available_language == 'fi') {
				$lang_obj = new stdClass();
				$lang_obj->lang = $available_language;
				$lang_obj->details = get_site_url() . "/item-json-vinkit/?postGUID=".$post_guid."&lang=".$available_language;

                if ($pof_settings_lastupdate_overwrite == null) {
                    $lang_obj->lastModified = $suggestiongs_tmp[$available_language]->modified;
                } else {
                    $lang_obj->lastModified = $pof_settings_lastupdate_overwrite;
                }

				$lang_obj->count= $suggestiongs_tmp[$available_language]->count;
				array_push($jsonItem->suggestions_details, $lang_obj);
			}
		}
	}

	return $jsonItem;
}

function pof_order_post_suggestions_by_lang($post) {
	$suggestions = pof_get_suggestions($post);

	$ret = array();

	foreach ($suggestions as $suggestion) {
		$suggestiong_lang = get_post_meta( $suggestion->ID, "pof_suggestion_lang", true );
		if (!isset($ret[$suggestiong_lang])) {
			$tmp = new stdClass();
			$tmp->count = 1;
			$tmp->modified = $suggestion->post_modified;
			$ret[$suggestiong_lang] = $tmp;
		}
		else {
			$ret[$suggestiong_lang]->count = $ret[$suggestiong_lang]->count + 1;
			if (strtotime($ret[$suggestiong_lang]->modified) < strtotime($suggestion->post_modified)) {
				$ret[$suggestiong_lang]->modified = $suggestion->post_modified;
			}
		}
	}

	return $ret;
}



function get_post_tags_JSON($post_id, $agegroup_id, $lang, $program = 0) {
	$ret = new stdClass();


	$pakollisuus = array();

	if (get_post_meta($post_id, "task_mandatory", true)) {
		$pakollinen = new stdClass();
		$tmp_name = pof_taxonomy_translate_get_translation('mandatory', 'mandatory', $agegroup_id, $lang, true);

		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$pakollinen->name = $tmp_name[0]->content;
		} else {
			$pakollinen->name = 'Pakollinen';
		}
		$pakollinen->slug = 'mandatory';
		$icon = pof_taxonomy_icons_get_icon('mandatory', 'mandatory', $agegroup_id, true);

		if (!empty($icon)) {
			$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
			if (!empty($icon_src)) {
				$pakollinen->icon = $icon_src[0];
			}
		}
		array_push($pakollisuus, $pakollinen);
	}

	if (count($pakollisuus) > 0) {
		$ret->pakollisuus = $pakollisuus;
	} else {
		$pakollinen = new stdClass();
		$tmp_name = pof_taxonomy_translate_get_translation('mandatory', 'not_mandatory', $agegroup_id, $lang, true);

		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$pakollinen->name = $tmp_name[0]->content;
		} else {
			$pakollinen->name = 'Ei pakollinen';
		}
		$pakollinen->slug = 'not_mandatory';
		$icon = pof_taxonomy_icons_get_icon('mandatory', 'not_mandatory', $agegroup_id, true);

		if (!empty($icon)) {
			$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
			if (!empty($icon_src)) {
				$pakollinen->icon = $icon_src[0];
			}
		}
		array_push($pakollisuus, $pakollinen);
		$ret->pakollisuus = $pakollisuus;
	}
    /*
	$groupsizes = get_field("task_groupsize", $post_id);

	$ret_groupsizes = array();

	if (empty($groupsizes)) {
		$groupsize = new stdClass();

		$tmp_name = pof_taxonomy_translate_get_translation('groupsize', 'group', $agegroup_id, $lang, true);

		if (!empty($tmp_name)) {
			$groupsize->name = $tmp_name[0]->content;
		} else {
			$groupsize->name = 'Laumassa';
		}
		$groupsize->name = 'Laumassa';
		$groupsize->slug = 'group';

		array_push($ret_groupsizes, $groupsize);

	} else {
		foreach ($groupsizes as $tmp_groupsize) {
			$groupsize = new stdClass();

			$icon = pof_taxonomy_icons_get_icon('groupsize', $tmp_groupsize, $agegroup_id, true);

			if (!empty($icon)) {
				$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
				if (!empty($icon_src)) {
					$groupsize->icon = $icon_src[0];
				}
			}

			$tmp_name = pof_taxonomy_translate_get_translation('groupsize', $tmp_groupsize, $agegroup_id, $lang, true);


			if (!empty($tmp_name)) {
				$groupsize->name = $tmp_name[0]->content;
			} else {
				switch ($tmp_groupsize) {
					default:
						$groupsize->name = $tmp_groupsize;
						break;
					case "one":
						$groupsize->name = 'Yksin';
						break;
					case "two":
						$groupsize->name = 'Kaksin';
						break;
					case "few":
						$groupsize->name = 'Muutama';
						break;
					case "group":
						$groupsize->name = 'Laumassa';
						break;
					case "big":
						$groupsize->name = 'Isommassa porukassa';
						break;
				}
			}

			$groupsize->slug = $tmp_groupsize;

			array_push($ret_groupsizes, $groupsize);

		}
	}

	if (count($ret_groupsizes) > 0) {
		$ret->ryhmakoko = $ret_groupsizes;
	}
    */
//	$place_of_performance = get_field("task_place_of_performance", $post_id);

  $place_of_performance = get_post_meta($post_id, "task_place_of_performance", true);

	$ret_places = array();

	if (empty($place_of_performance)) {
		$place = new stdClass();
		$tmp_name = pof_taxonomy_translate_get_translation('place_of_performance', 'meeting_place', $agegroup_id, $lang, true);

		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$place->name = $tmp_name[0]->content;
		} else {
			$place->name = 'Kolo';
		}
		$place->slug = 'meeting_place';

		array_push($ret_places, $place);

	} else {
		foreach ($place_of_performance as $tmp_place) {
			$place = new stdClass();
			$icon = pof_taxonomy_icons_get_icon('place_of_performance',$tmp_place, $agegroup_id, true);

			if (!empty($icon)) {
				$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
				if (!empty($icon_src)) {
					$place->icon = $icon_src[0];
				}
			}

			$tmp_name = pof_taxonomy_translate_get_translation('place_of_performance', $tmp_place, $agegroup_id, $lang, true);

			if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
				$place->name = trim($tmp_name[0]->content);
			} else {
				$place->name = trim($tmp_place);
			}

			$place->slug = trim($tmp_place);

      //make sure that there are no duplicates
      foreach ($ret_places as $tmp_ret_place) {
          if ($tmp_ret_place->slug == $place->slug) {
              break;
          }
      }

			array_push($ret_places, $place);

		}
	}

	if (count($ret_places) > 0) {
		$ret->paikka = $ret_places;
	}

	$taitoalueet_tags = wp_get_post_terms($post_id, 'pof_tax_skillarea');

	$taitoalueet = array();

	foreach ($taitoalueet_tags as $taitoalue_tag) {
		$taitoalue = new stdClass();
		$tmp_name = pof_taxonomy_translate_get_translation('skillarea', $taitoalue_tag->slug, $agegroup_id, $lang, true);
		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$taitoalue->name = $tmp_name[0]->content;
		} else {
			$taitoalue->name = $taitoalue_tag->name;
		}
		$taitoalue->slug = $taitoalue_tag->slug;

		array_push($taitoalueet, $taitoalue);
	}
	if (count($taitoalueet) > 0) {
		$ret->taitoalueet = $taitoalueet;
	}

    $johtamistaito_tags = wp_get_post_terms($post_id, 'pof_tax_leadership');

	$johtamistaidot = array();

	foreach ($johtamistaito_tags as $johtamistaito_tag) {
		$johtamistaito = new stdClass();
		$tmp_name = pof_taxonomy_translate_get_translation('leadership', $johtamistaito_tag->slug, $agegroup_id, $lang, true);
		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$johtamistaito->name = $tmp_name[0]->content;
		} else {
			$johtamistaito->name = $johtamistaito_tag->name;
		}
		$johtamistaito->slug = $johtamistaito_tag->slug;

		array_push($johtamistaidot, $johtamistaito);
	}
	if (count($johtamistaidot) > 0) {
		$ret->johtamistaito = $johtamistaidot;
	}

//	$suoritus_kesto_tmp = get_field("task_duration", $post_id);

    $suoritus_kesto_tmp = get_post_meta($post_id, "task_duration", true);

	if ($suoritus_kesto_tmp) {
		$suoritus_kesto = new stdClass();

        $tmp_name = pof_taxonomy_translate_get_translation('taskduration', $suoritus_kesto_tmp, $agegroup_id, $lang, true);
		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$suoritus_kesto->name = $tmp_name[0]->content;
		} else {
			$suoritus_kesto->name = $suoritus_kesto_tmp;
		}

		$suoritus_kesto->slug = $suoritus_kesto_tmp;
		$icon = pof_taxonomy_icons_get_icon('taskduration', $suoritus_kesto_tmp, $agegroup_id, true);

		if (!empty($icon)) {
			$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
			if (!empty($icon_src)) {
				$suoritus_kesto->icon = $icon_src[0];
			}
		}
		$ret->suoritus_kesto = $suoritus_kesto;
	}
    /*
	$suoritus_valmistelu_kesto_tmp = get_field("task_preparationduration", $post_id);
	if ($suoritus_valmistelu_kesto_tmp) {
		$suoritus_valmistelu_kesto = new stdClass();
		$suoritus_valmistelu_kesto->name = $suoritus_valmistelu_kesto_tmp;
		$suoritus_valmistelu_kesto->slug = $suoritus_valmistelu_kesto_tmp;
		$icon = pof_taxonomy_icons_get_icon('taskpreaparationduration', $suoritus_valmistelu_kesto_tmp, $agegroup_id, true);

		if (!empty($icon)) {
			$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
			if (!empty($icon_src)) {
				$suoritus_valmistelu_kesto->icon = $icon_src[0];
			}
		}
		$ret->suoritus_valmistelu_kesto = $suoritus_valmistelu_kesto;
	}
    */
	$tarvike_tags = wp_get_post_terms($post_id, 'pof_tax_equipment');

	$tarvikkeet = array();

	foreach ($tarvike_tags as $tarvike_tag) {
		$tarvike = new stdClass();

		$tmp_name = pof_taxonomy_translate_get_translation('equpment', $tarvike_tag->slug, $agegroup_id, $lang, true);
		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$tarvike->name = $tmp_name[0]->content;
		} else {
			$tarvike->name = $tarvike_tag->name;
		}
		$tarvike->slug = $tarvike_tag->slug;
		array_push($tarvikkeet, $tarvike);
	}
	if (count($tarvikkeet)) {
		$ret->tarvikkeet = $tarvikkeet;
	}

    $growth_target_tags = wp_get_post_terms($post_id, 'pof_tax_growth_target');

	$growth_targets = array();

	foreach ($growth_target_tags as $growth_target_tag) {
		$growth_target = new stdClass();

		$tmp_name = pof_taxonomy_translate_get_translation('growth_target', $growth_target_tag->slug, $agegroup_id, $lang, true);
		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$growth_target->name = $tmp_name[0]->content;
		} else {
			$growth_target->name = $growth_target_tag->name;
		}
		$growth_target->slug = $growth_target_tag->slug;
		array_push($growth_targets, $growth_target);
	}
	if (count($growth_targets)) {
		$ret->kasvatustavoitteet = $growth_targets;
  }

  $teema_tags = wp_get_post_terms($post_id, 'pof_tax_theme');

  $teemat = array();

  foreach ($teema_tags as $teema_tag) {
      $teema = new stdClass();

      $tmp_name = pof_taxonomy_translate_get_translation('theme', $teema_tag->slug, $agegroup_id, $lang, true);
      if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
          $teema->name = $tmp_name[0]->content;
      } else {
          $teema->name = $teema_tag->name;
      }
      $teema->slug = $teema_tag->slug;
      array_push($teemat, $teema);
  }
  if (count($teemat)) {
      $ret->teemat = $teemat;
  }

	return $ret;
}

function get_post_tags_taskgroup_JSON($post_id, $agegroup_id, $lang) {
	$ret = new stdClass();

	$pakollisuus = array();

	if (get_post_meta($post_id, "taskgroup_mandatory", true)) {
		$pakollinen = new stdClass();
		$tmp_name = pof_taxonomy_translate_get_translation('mandatory', 'mandatory', $agegroup_id, $lang, true);

		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$pakollinen->name = $tmp_name[0]->content;
		} else {
			$pakollinen->name = 'Pakollinen';
		}
		$pakollinen->slug = 'mandatory';
		$icon = pof_taxonomy_icons_get_icon('mandatory', 'mandatory', $agegroup_id, true);

		if (!empty($icon)) {
			$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
			if (!empty($icon_src)) {
				$pakollinen->icon = $icon_src[0];
			}
		}
		array_push($pakollisuus, $pakollinen);
	}

    if (count($pakollisuus) > 0) {
		$ret->pakollisuus = $pakollisuus;
	} else {
		$pakollinen = new stdClass();
		$tmp_name = pof_taxonomy_translate_get_translation('mandatory', 'not_mandatory', $agegroup_id, $lang, true);

		if (!empty($tmp_name) && !empty($tmp_name[0]->content)) {
			$pakollinen->name = $tmp_name[0]->content;
		} else {
			$pakollinen->name = 'Ei pakollinen';
		}
		$pakollinen->slug = 'not_mandatory';
		$icon = pof_taxonomy_icons_get_icon('mandatory', 'not_mandatory', $agegroup_id, true);

		if (!empty($icon)) {
			$icon_src = wp_get_attachment_image_src($icon[0]->attachment_id);
			if (!empty($icon_src)) {
				$pakollinen->icon = $icon_src[0];
			}
		}
		array_push($pakollisuus, $pakollinen);
		$ret->pakollisuus = $pakollisuus;
	}

	return $ret;
}

function get_post_images_JSON($post_id) {
	$ret = new stdClass();
	$ret->logo = new stdClass();
	$ret->main_image = new stdClass();

	$logo = get_field('logo_image', $post_id);
	if ($logo) {

		$ret->logo->type = 'logo';
		$ret->logo->title = $logo['title'];
		$ret->logo->mime_type = $logo['mime_type'];
		$ret->logo->height = $logo['height'];
		$ret->logo->width = $logo['width'];
		$ret->logo->url = $logo['url'];
		$ret->logo->id = $logo['id'];

        $thumb_url = "";

		if (!empty($logo['sizes'])) {
			if (!empty($logo['sizes']['thumbnail'])) {
				$thumbnail = new stdClass();
				$thumbnail->height = $logo['sizes']['thumbnail-height'];
				$thumbnail->width = $logo['sizes']['thumbnail-width'];
				$thumbnail->url = $logo['sizes']['thumbnail'];
                $thumb_url = $logo['sizes']['thumbnail'];
				$ret->logo->thumbnail = $thumbnail;
			}
            if (!empty($logo['sizes']['thumbnailcropped'])
                && $logo['sizes']['thumbnailcropped'] != $logo['url']
                && $logo['sizes']['thumbnailcropped'] != $thumb_url) {
				$thumbnailcropped = new stdClass();
				$thumbnailcropped->height = $logo['sizes']['thumbnailcropped-height'];
				$thumbnailcropped->width = $logo['sizes']['thumbnailcropped-width'];
				$thumbnailcropped->url = $logo['sizes']['thumbnailcropped'];
				$ret->logo->thumbnailcropped = $thumbnailcropped;
			}
			if (!empty($logo['sizes']['medium']) && $logo['sizes']['medium'] != $logo['url']) {
				$medium = new stdClass();
				$medium->height = $logo['sizes']['medium-height'];
				$medium->width = $logo['sizes']['medium-width'];
				$medium->url = $logo['sizes']['medium'];
				$ret->logo->medium = $medium;
			}
			if (!empty($logo['sizes']['large']) && $logo['sizes']['large'] != $logo['url']) {
				$large = new stdClass();
				$large->height = $logo['sizes']['large-height'];
				$large->width = $logo['sizes']['large-width'];
				$large->url = $logo['sizes']['large'];
				$ret->logo->large = $large;
			}
		}

	}

	$main_image = get_field('main_image', $post_id);


	if ($main_image) {
		$ret->main_image->type = 'main_image';
		$ret->main_image->title = $main_image['title'];
		$ret->main_image->mime_type = $main_image['mime_type'];
		$ret->main_image->height = $main_image['height'];
		$ret->main_image->width = $main_image['width'];
		$ret->main_image->url = $main_image['url'];
		$ret->main_image->id = $main_image['id'];

        $thumb_url = "";

		if (!empty($main_image['sizes'])) {
			if (!empty($main_image['sizes']['thumbnail'])) {
				$thumbnail = new stdClass();
				$thumbnail->height = $main_image['sizes']['thumbnail-height'];
				$thumbnail->width = $main_image['sizes']['thumbnail-width'];
				$thumbnail->url = $main_image['sizes']['thumbnail'];
                $thumb_url = $main_image['sizes']['thumbnail'];
				$ret->main_image->thumbnail = $thumbnail;
			}
            if (!empty($main_image['sizes']['thumbnailcropped'])
                && $main_image['sizes']['thumbnailcropped'] != $main_image['url']
                && $main_image['sizes']['thumbnailcropped'] != $thumb_url) {
				$thumbnailcropped = new stdClass();
				$thumbnailcropped->height = $main_image['sizes']['thumbnailcropped-height'];
				$thumbnailcropped->width = $main_image['sizes']['thumbnailcropped-width'];
				$thumbnailcropped->url = $main_image['sizes']['thumbnailcropped'];
				$ret->main_image->thumbnailcropped = $thumbnailcropped;
			}
			if (!empty($main_image['sizes']['medium']) && $main_image['sizes']['medium'] != $main_image['url']) {
				$medium = new stdClass();
				$medium->height = $main_image['sizes']['medium-height'];
				$medium->width = $main_image['sizes']['medium-width'];
				$medium->url = $main_image['sizes']['medium'];
				$ret->main_image->medium = $medium;
			}
			if (!empty($main_image['sizes']['large']) && $main_image['sizes']['large'] != $main_image['url']) {
				$large = new stdClass();
				$large->height = $main_image['sizes']['large-height'];
				$large->width = $main_image['sizes']['large-width'];
				$large->url = $main_image['sizes']['large'];
				$ret->main_image->large = $large;
			}
		}

	}

	return $ret;
}


// Simple fields plugin doesn't allow us to define dropdown select value to differ from content (key and value are the same), so we need to match those
// Returns boolean, if langs match
function pof_match_pof_lang_to_simple_fields_lang_dropdown($pof_lang, $simple_fields_lang)
{
    if ($pof_lang == null || $pof_lang == "") {
        $pof_lang = "fi";
    }
    if ($simple_fields_lang == null || $simple_fields_lang == "") {
        $simple_fields_lang = "Suomi";
    }

    switch($pof_lang) {
        case "fi":
            if ($simple_fields_lang == "Suomi") { return true; }
            break;
        case "sv":
            if ($simple_fields_lang == "Ruotsi") { return true; }
            break;
        case "en":
            if ($simple_fields_lang == "Englanti") { return true; }
            break;
    }

    return false;
}

function get_post_additional_content_JSON($post_id, $lang = null) {
	$ret = new stdClass();

	$images = simple_fields_fieldgroup("additional_images_fg", $post_id);

	$images_arr = array();

	if ($images) {
		foreach ($images as $additional_image) {
			if ($additional_image['additional_image']) {
				$image = $additional_image['additional_image'];
                $image_lang = null;

                $image_lang_arr = $additional_image['additional_image_lang'];

                if (is_array($image_lang_arr)) {
                    $image_lang = $image_lang_arr["selected_value"];
                }

                $lang_match = pof_match_pof_lang_to_simple_fields_lang_dropdown($lang, $image_lang);

                if (!$lang_match) {
                    continue;
                }

				$image_obj = new stdClass();
				$image_obj->description = $additional_image['additional_image_text'];
                if (!is_array($image['metadata'])) {
                    continue;
                }
				$image_obj->mime_type = $image['mime'];
				$image_obj->height = $image['metadata']['height'];
				$image_obj->width = $image['metadata']['width'];
				$image_obj->url = $image['url'];
				$image_obj->id = $image['id'];

                $thumb_url = "";

				if (!empty($image['image_src'])) {
					if (!empty($image['image_src']['thumbnail'])) {
						$thumbnail = new stdClass();
						$thumbnail->height = $image['image_src']['thumbnail'][1];
						$thumbnail->width = $image['image_src']['thumbnail'][2];
						$thumbnail->url = $image['image_src']['thumbnail'][0];
                        $thumb_url = $image['image_src']['thumbnail'][0];
						$image_obj->thumbnail = $thumbnail;
					}
                    if (   !empty($image['image_src']['thumbnailcropped'])
                        && $image['image_src']['thumbnailcropped'][0] != $image['url']
                        && $image['image_src']['thumbnailcropped'][0] != $thumb_url) {
						$thumbnailcropped = new stdClass();
						$thumbnailcropped->height = $image['image_src']['thumbnailcropped'][1];
						$thumbnailcropped->width = $image['image_src']['thumbnailcropped'][2];
						$thumbnailcropped->url = $image['image_src']['thumbnailcropped'][0];
						$image_obj->thumbnailcropped = $thumbnailcropped;
					}
					if (!empty($image['image_src']['medium']) && $image['image_src']['medium'][0] != $image['url']) {
						$medium = new stdClass();
						$medium->height = $image['image_src']['medium'][1];
						$medium->width = $image['image_src']['medium'][2];
						$medium->url = $image['image_src']['medium'][0];
						$image_obj->medium = $medium;
					}
					if (!empty($image['image_src']['large']) && $image['image_src']['large'][0] != $image['url']) {
						$large = new stdClass();
						$large->height = $image['image_src']['large'][1];
						$large->width = $image['image_src']['large'][2];
						$large->url = $image['image_src']['large'][0];
						$image_obj->large = $large;
					}
				}


				array_push($images_arr, $image_obj);
			}
		}
	}

	if (count($images_arr) > 0) {
		$ret->images = $images_arr;
	}

	$files = simple_fields_fieldgroup("additional_files_fg", $post_id);

	$files_arr = array();

	if ($files) {
		foreach ($files as $additional_file) {

            if ($additional_file['additional_file']) {

				$file = $additional_file['additional_file'];

                if ($file['url'] == false) {
                    continue;
                }

                $file_lang = null;

                $file_lang_arr = $additional_file['additional_file_lang'];

                if (is_array($file_lang_arr)) {
                    $file_lang = $file_lang_arr["selected_value"];
                }

                $lang_match = pof_match_pof_lang_to_simple_fields_lang_dropdown($lang, $file_lang);

                if (!$lang_match) {
                    continue;
                }



				$file_obj = new stdClass();
				$file_obj->description = $additional_file['additional_file_text'];
				$file_obj->mime_type = $file['mime'];
				$file_obj->url = $file['url'];
				array_push($files_arr, $file_obj);
			}
		}
	}

	if (count($files_arr) > 0) {
		$ret->files = $files_arr;
	}

	$links = simple_fields_fieldgroup("additional_links_fg", $post_id);

	$links_arr = array();

	if ($links) {
		foreach ($links as $additional_link) {
			if ($additional_link['additional_link_url']) {

				$link = $additional_link['additional_link_url'];

                $link_lang = null;

                $link_lang_arr = $additional_link['additional_link_lang'];

                if (is_array($link_lang_arr)) {
                    $link_lang = $link_lang_arr["selected_value"];
                }

                $lang_match = pof_match_pof_lang_to_simple_fields_lang_dropdown($lang, $link_lang);

                if (!$lang_match) {
                    continue;
                }

				$link_obj = new stdClass();
				$link_obj->description = $additional_link['additional_link_text'];
				$link_obj->url = $link;
				array_push($links_arr, $link_obj);
			}
		}
	}

	if (count($links_arr) > 0) {
		$ret->links = $links_arr;
	}

	return $ret;
}


function getMandatoryTasksForTaskGroup($parent_id) {
	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_task',
		'meta_key' => 'suoritepaketti',
		'meta_value' => $parent_id
	);

	$the_query = new WP_Query( $args );

	$ret = new stdClass();

	$ret->ids = array();
	$ret->hashes = array();

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			if (get_post_meta($the_query->post->ID, "task_mandatory")) {
				array_push($ret->ids, $the_query->post->ID);
				array_push($ret->hashes, wp_hash($the_query->post->ID));
			}
		}
	}

	return $ret;
}

function pof_get_parent_tree($post_item, $tree_array) {
	$post_type = str_replace('pof_post_', '', $post_item->post_type);
	$post_id = $post_item->ID;

	switch ($post_type) {
		case "program":
//			$post_class = $classProgram;
		break;
		case "agegroup":
			$ohjelma_id = get_post_meta( $post_id, "suoritusohjelma", true );
			if (!is_null($ohjelma_id) && $ohjelma_id != "null" && $ohjelma_id != "" && !empty($ohjelma_id)) {
				$ohjelma = get_post($ohjelma_id);
				array_push($tree_array, $ohjelma);
//				$tree_array = pof_get_parent_tree($ohjelma, $tree_array);
			}
		break;
		case "taskgroup":
			$taskgroup_id = get_post_meta( $post_id, "suoritepaketti", true );
			if (!is_null($taskgroup_id) && $taskgroup_id != "null" && $taskgroup_id != "" && !empty($taskgroup_id)) {
				$taskgroup = get_post($taskgroup_id);
				array_push($tree_array, $taskgroup);
				$tree_array = pof_get_parent_tree($taskgroup, $tree_array);
			} else {
				$ikaryhma_id = get_post_meta( $post_id, "ikakausi", true );
				if (!is_null($ikaryhma_id) && $ikaryhma_id != "null" && !empty($ikaryhma_id)) {
					$ikaryhma = get_post($ikaryhma_id);
					array_push($tree_array, $ikaryhma);
					$tree_array = pof_get_parent_tree($ikaryhma, $tree_array);
				}
			}
		break;
		case "task":
			$taskgroup_id = get_post_meta( $post_id, "suoritepaketti", true );

			if (!is_null($taskgroup_id) && $taskgroup_id != "null" && !empty($taskgroup_id)) {
				$taskgroup = get_post($taskgroup_id);
				array_push($tree_array, $taskgroup);
				$tree_array = pof_get_parent_tree($taskgroup, $tree_array);
			}
		break;
	}


	return $tree_array;
}


function pof_save_post_hook($post_id) {
	// If this is a revision, get real post ID
	if ( $parent_id = wp_is_post_revision( $post_id ) ) {
		$post_id = $parent_id;
	}


	$post_guid = get_post_meta( $post_id, "post_guid", true );

	if (!$post_guid) {
		remove_action( 'save_post', 'pof_save_post_hook' );
		update_post_meta($post_id, "post_guid", wp_hash($post_id));
		add_action( 'save_post', 'pof_save_post_hook' );
	}

//	$tmp_post = get_post($post_id);
}


add_action( 'save_post', 'pof_save_post_hook' );

function pof_item_guid_add_meta_box() {

	$screens = array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup', 'pof_post_suggestion' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'pof_item_guid_add_meta_box_sectionid',
			__( 'GUID', 'pof' ),
			'pof_item_guid_add_meta_box_callback',
			$screen, 'side', 'high'
		);
	}
}

function pof_item_guid_add_meta_box_callback($post) {
    $guid = get_post_meta( $post->ID, "post_guid", true );
    echo $guid;
    echo "<br />";
    if ($post->post_type != 'pof_post_suggestion') {
        echo 'JSON: <a href="/item-json/?postGUID='.$guid.'&lang=fi" target="_blank">fi</a>';

        $langs = pof_settings_get_all_languages();

        foreach ($langs as $lang) {
            if ($lang->lang_code == 'fi') { continue; }
            $title = get_post_meta($post->ID, "title_".$lang->lang_code, true);
            echo ', <a href="/item-json/?postGUID=' . $guid . '&lang=' . $lang->lang_code . '" target="_blank">';
            if ($title != null && $title != "") {
                echo $lang->lang_code;
            } else {
                echo "(" . $lang->lang_code . ")";
            }

            echo "</a>";
        }

    } else {
        $parent_post_id = get_post_meta( $post->ID, "pof_suggestion_task", true );
        $lang = get_post_meta( $post->ID, "pof_suggestion_lang", true );
        if (strlen($lang) < 2) {
            $lang = 'fi';
        }
        $parent_guid = get_post_meta( $parent_post_id, "post_guid", true );
        echo '<a href="/item-json-vinkit/?postGUID='.$parent_guid.'&lang='.$lang.'" target="_blank">View JSON</a>';
    }

}

add_action( 'add_meta_boxes', 'pof_item_guid_add_meta_box' );

function pof_item_task_details_add_meta_box() {

	$screens = array('pof_post_task');

	foreach ( $screens as $screen ) {

		add_meta_box(
			'pof_item_task_details_add_meta_box_sectionid',
			__( 'Import details', 'pof' ),
			'pof_item_task_details_add_meta_box_callback',
			$screen, 'side', 'high'
		);
	}
}

function pof_item_task_details_add_meta_box_callback($post) {
    $langs = pof_settings_get_all_languages();

    $guid = get_post_meta( $post->ID, "post_guid", true );

    $isEmpty = true;

    foreach ($langs as $lang) {
        $dt = get_post_meta($post->ID, "content_imported_".$lang->lang_code, true);
        if ($dt != null && $dt != "") {
            echo '<a href="/item-json/?postGUID=' . $guid . '&lang=' . $lang->lang_code . '" target="_blank">';
            echo $lang->lang_title . "</a>: " . $dt;
            echo "<br />";
            $isEmpty = false;
        }
    }
    if ($isEmpty) {
        echo "Ei importoitu.";
    }

}

add_action( 'add_meta_boxes', 'pof_item_task_details_add_meta_box' );


function pof_item_task_parenttree_meta_box() {

	$screens = array('pof_post_task', 'pof_post_taskgroup', 'pof_post_agegroup', 'pof_post_program');

	foreach ( $screens as $screen ) {

		add_meta_box(
			'pof_item_task_parenttree_meta_box_sectionid',
			__( 'Parent tree', 'pof' ),
			'pof_item_task_parenttree_meta_box_callback',
			$screen, 'side', 'core'
		);
	}
}

function pof_item_task_parenttree_meta_box_callback($post) {
	$tree_array = array();
	array_push($tree_array, $post);
	$tree_array = array_reverse(pof_get_parent_tree($post, $tree_array));

	foreach ($tree_array as $tree_key => $tree_post) {
		echo "<ul style=\"margin-left: 10px; list-style-type: round;\">";
		echo "<li>";
		echo "<a href=\"/wp-admin/post.php?post=" . $tree_post->ID . "&action=edit\" target=\"_blank\">" . $tree_post->post_title . "</a>";

	}

	foreach ($tree_array as $tree_post) {
		echo "</li>";
		echo "</ul>";
	}
}

function pof_item_siblings_meta_box() {

	$screens = array('pof_post_task', 'pof_post_taskgroup', 'pof_post_agegroup', 'pof_post_program');

	foreach ( $screens as $screen ) {

		add_meta_box(
			'pof_item_siblings_meta_box_sectionid',
			__( 'Siblings', 'pof' ),
			'pof_item_siblings_meta_box_callback',
			$screen, 'side', 'core'
		);
	}
}

function pof_item_siblings_meta_box_callback($post) {
	$siblings = pof_get_siblings($post);


	foreach ($siblings as $sibling_key => $sibling_post) {
		echo "<ul style=\"margin-left: 10px; list-style-type: round;\">";
		echo "<li>";
		echo "<a href=\"/wp-admin/post.php?post=" . $sibling_post->ID . "&action=edit\" target=\"_blank\">" . $sibling_post->post_title . "</a>";
		echo "</li>";
		echo "</ul>";
	}
}

function pof_get_siblings($post_item) {
	$post_type = str_replace('pof_post_', '', $post_item->post_type);
	$post_id = $post_item->ID;

	$to_ret = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => $post_item->post_type,
		'meta_value' => null
	);

	switch ($post_type) {
		case "agegroup":

			$args['meta_key'] = "suoritusohjelma";
			$args['meta_value'] = get_post_meta( $post_id, "suoritusohjelma", true );

		break;
		case "taskgroup":
			$taskgroup_id = get_post_meta( $post_id, "suoritepaketti", true );
			if (!is_null($taskgroup_id) && $taskgroup_id != "null" && !empty($taskgroup_id)) {
				$args['meta_key'] = "suoritepaketti";
				$args['meta_value'] = $taskgroup_id;
			} else {
				$args['meta_key'] = "ikakausi";
				$args['meta_value'] = get_post_meta( $post_id, "ikakausi", true );
			}

		break;
		case "task":
			$taskgroup_id = get_post_meta( $post_id, "suoritepaketti", true );

			$args['meta_key'] = "suoritepaketti";
			$args['meta_value'] = $taskgroup_id;
		break;
	}

	if (is_null($args['meta_value']) || $args['meta_value'] == 0 || $args['meta_value'] == 'null') {
		return $to_ret;
	}

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			if ($post_id != $the_query->post->ID) {
				array_push($to_ret, $the_query->post);
			}
		}
	}


	return $to_ret;
}

function pof_item_childs_meta_box() {

	$screens = array('pof_post_task', 'pof_post_taskgroup', 'pof_post_agegroup', 'pof_post_program');

	foreach ( $screens as $screen ) {

		add_meta_box(
			'pof_item_childs_meta_box_sectionid',
			__( 'Childs', 'pof' ),
			'pof_item_childs_meta_box_callback',
			$screen, 'side', 'core'
		);
	}
}

function pof_item_childs_meta_box_callback($post) {
	$childs = pof_get_childs($post);

  echo "<ul id=\"post-children\" style=\"margin-left: 10px; list-style-type: round;\">";
	foreach ($childs as $child_key => $child_post) {
		echo "<li id=\"$child_post->ID\" style=\"display:flex; align-items:center; justify-content: space-between; padding-bottom: 10px;\">";
		echo "<a href=\"/wp-admin/post.php?post=" . $child_post->ID . "&action=edit\" target=\"_blank\">" . $child_post->post_title . "</a>";
    echo '<span class="dashicons dashicons-move" style="float: right;"></span>';
		echo "</li>";
	}
	echo "</ul>";
}

function pof_get_childs($post_item) {
	$post_type = str_replace('pof_post_', '', $post_item->post_type);
	$post_id = $post_item->ID;

	$to_ret = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
    'meta_value' => null,
    'orderby' => 'menu_order'
	);

	switch ($post_type) {
		case "program":
			$args['post_type'] = array('pof_post_agegroup');
			$args['meta_key'] = "suoritusohjelma";
			$args['meta_value'] = $post_id;

		break;
		case "agegroup":
			$args['post_type'] = array('pof_post_taskgroup');
			$args['meta_key'] = "ikakausi";
			$args['meta_value'] = $post_id;

		break;
		case "taskgroup":
			$args['post_type'] = array('pof_post_taskgroup', 'pof_post_task');
			$args['meta_key'] = "suoritepaketti";
			$args['meta_value'] = $post_id;
		break;
	}

	if (is_null($args['meta_value']) || $args['meta_value'] == 0 || $args['meta_value'] == 'null') {
		return $to_ret;
	}

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			if ($post_id != $the_query->post->ID) {
				array_push($to_ret, $the_query->post);
			}
		}
	}


	return $to_ret;
}

function pof_item_suggestions_meta_box() {

	$screens = array('pof_post_task');

    // Show suggestions metabox for all content types to clean up those that are linked to other content types than tasks
	$screens = array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup' );


	foreach ( $screens as $screen ) {

		add_meta_box(
			'pof_item_suggestions_meta_box_sectionid',
			__( 'Suggestions', 'pof' ),
			'pof_item_suggestions_meta_box_callback',
			$screen, 'side', 'core'
		);
	}
}

function pof_item_suggestions_meta_box_callback($post) {
	$suggestions = pof_get_suggestions($post);


    echo "<ul style=\"margin-left: 10px; list-style-type: round;\">";
	foreach ($suggestions as $suggestion_key => $suggestion_post) {
		echo "<li>";
		echo "<a href=\"/wp-admin/post.php?post=" . $suggestion_post->ID . "&action=edit\" target=\"_blank\">" . $suggestion_post->post_title . "</a> (".get_post_meta( $suggestion_post->ID, "pof_suggestion_lang", true ).")";
		echo "</li>";
	}
    echo "</ul>";
}

function pof_get_suggestions($post_item) {
	$post_id = $post_item->ID;

	$to_ret = array();

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => 'pof_post_suggestion',
		'meta_key' => 'pof_suggestion_task',
		'meta_value' => $post_id
	);

	$the_query = new WP_Query( $args );

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			if ($post_id != $the_query->post->ID) {
				array_push($to_ret, $the_query->post);
			}
		}
	}


	return $to_ret;
}

function pof_item_suggestion_meta_box() {

	$screens = array('pof_post_suggestion');

	foreach ( $screens as $screen ) {

		add_meta_box(
			'pof_item_suggestion_meta_box_sectionid',
			__( 'Task', 'pof' ),
			'pof_item_suggestion_meta_box_callback',
			$screen, 'side', 'high'
		);
	}
}

function pof_item_suggestion_meta_box_callback($post) {
	$task_post_id = get_post_meta( $post->ID, "pof_suggestion_task", true );

	$task_post = get_post($task_post_id);

	echo "<a href=\"/wp-admin/post.php?post=" . $task_post->ID . "&action=edit\" target=\"_blank\">" . $task_post->post_title . "</a>";
}


add_action( 'add_meta_boxes', 'pof_item_task_parenttree_meta_box' );
add_action( 'add_meta_boxes', 'pof_item_siblings_meta_box' );
add_action( 'add_meta_boxes', 'pof_item_childs_meta_box' );
add_action( 'add_meta_boxes', 'pof_item_suggestions_meta_box' );
add_action( 'add_meta_boxes', 'pof_item_suggestion_meta_box' );

function pof_output_parents_arr_json($tree_array) {
	$ret = array();
	foreach ($tree_array as $tree_item) {
		$tmp = new stdClass();
		$tmp->type = str_replace('pof_post_', '',$tree_item->post_type);
		$tmp->title = $tree_item->post_title;
		$tmp = getJsonItemBaseDetails($tmp, $tree_item);
		array_push($ret, $tmp);
	}

	return $ret;
}

function pof_get_additional_taskgroups($post_id) {
  $additional_taskgroups = get_field( 'suoritepaketti_muut', $post_id );
  $ret = array();
	foreach ($additional_taskgroups as $taskgroup) {
		$tmp = new stdClass();
		$tmp->type = 'pof_post_taskgroup';
		$tmp->title = $taskgroup->post_title;
		$tmp = getJsonItemBaseDetails($tmp, $taskgroup);
		array_push($ret, $tmp);
	}

  return $ret;
}

function pof_get_agegroup_from_tree_arr($tree_array) {
	$agegropup = null;

	foreach ($tree_array as $tree_item) {
		if (empty($tree_item)) {
			continue;
		}
		if ($tree_item->post_type == 'pof_post_agegroup') {
			$agegroup = $tree_item;
			break;
		}
	}

	return $agegroup;
}


function pof_item_task_get_childs($post) {

	$args = array(
		'numberposts' => -1,
		'posts_per_page' => -1,
		'post_type' => array('pof_post_task', 'pof_post_taskgroup', 'pof_post_agegroup'),
		'meta_key' => 'suoritepaketti',
		'meta_value' => $post->ID
	);

	switch ($post->post_type) {
		case "pof_post_program":
			$args['meta_key'] = 'suoritusohjelma';
		break;

		case "pof_post_agegroup":
			$args['meta_key'] = 'ikakausi';
		break;

		case "pof_post_taskgroup":
			$args['meta_key'] = 'suoritepaketti';
		break;
	}

	$the_query = new WP_Query( $args );

	$ret = array();

	if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
			$the_query->the_post();

			array_push($ret, $the_query->post);
		}
	}

	return $ret;
}

function pof_item_task_get_menu($post) {

	$post_type = $post->post_type;

	$tree_array = array();
	array_push($tree_array, $post);
	$tree_array = array_reverse(pof_get_parent_tree_for_menu($post, $tree_array));

	$counter = 0;

	foreach ($tree_array as $tree_key => $tree_post) {
		$counter++;

		echo "<ul>";

		$siblings = pof_get_siblings($tree_post);
		foreach ($siblings as $sibling) {
			echo "<li>";
			echo "<a href=\"" . $sibling->guid . "\">" . $sibling->post_title . "</a>";
			echo "</li>";
		}

		echo "<li>";
		echo "<a class=\"active_item\" href=\"" . $tree_post->guid . "\">" . $tree_post->post_title . "</a>";

		if ($counter == count($tree_array) && $post_type != 'pof_post_task') {
			$childs = pof_item_task_get_childs($post);

			if (count($childs) > 0) {
				echo "<ul>";
				foreach ($childs as $child) {
					echo "<li>";
					echo "<a href=\"" . $child->guid . "\">" . $child->post_title . "</a>";
					echo "</li>";

				}
				echo "<ul>";
			}
		}

	}

	foreach ($tree_array as $tree_post) {
		echo "</li>";
		echo "</ul>";
	}
}

function pof_get_parent_tree_for_menu($post_item, $tree_array) {
	$post_type = str_replace('pof_post_', '', $post_item->post_type);
	$post_id = $post_item->ID;

	switch ($post_type) {
		case "program":
		break;
		case "agegroup":
			$ohjelma_id = get_post_meta( $post_id, "suoritusohjelma", true );
			if (!is_null($ohjelma_id) && $ohjelma_id != "null" && $ohjelma_id != "" && !empty($ohjelma_id)) {
				$ohjelma = get_post($ohjelma_id);
				array_push($tree_array, $ohjelma);

			}
		break;
		case "taskgroup":
			$taskgroup_id = get_post_meta( $post_id, "suoritepaketti", true );
			if (!is_null($taskgroup_id) && $taskgroup_id != "null" && $taskgroup_id != "" && !empty($taskgroup_id)) {
				$taskgroup = get_post($taskgroup_id);
				array_push($tree_array, $taskgroup);
				$tree_array = pof_get_parent_tree_for_menu($taskgroup, $tree_array);
			} else {
				$ikaryhma_id = get_post_meta( $post_id, "ikakausi", true );
				if (!is_null($ikaryhma_id) && $ikaryhma_id != "null" && !empty($ikaryhma_id)) {
					$ikaryhma = get_post($ikaryhma_id);
					array_push($tree_array, $ikaryhma);
					$tree_array = pof_get_parent_tree_for_menu($ikaryhma, $tree_array);
				}
			}
		break;
		case "task":
			$taskgroup_id = get_post_meta( $post_id, "suoritepaketti", true );

			if (!is_null($taskgroup_id) && $taskgroup_id != "null" && !empty($taskgroup_id)) {
				$taskgroup = get_post($taskgroup_id);
				array_push($tree_array, $taskgroup);
				$tree_array = pof_get_parent_tree_for_menu($taskgroup, $tree_array);
			}
		break;
	}


	return $tree_array;
}

function pof_normalize_task_level($level_str) {
	if (!empty($level_str) && $level_str != "") {
		return $level_str;
	}

	return "0";
}


function pof_checkDatetime($post_to_check) {
	global $lastModified;
	global $lastModifiedBy;
    global $pof_settings_lastupdate_overwrite;

	$tmpTime = strtotime($post_to_check->post_modified);
	if ($tmpTime > $lastModified) {
        if ($pof_settings_lastupdate_overwrite == null) {
            $lastModified = $tmpTime;
        } else {
            $lastModified = $pof_settings_lastupdate_overwrite;
        }

		$lastModifiedBy = get_post_meta( $post_to_check->ID, '_edit_last', true);
	}
}

function pof_full_url( $s, $use_forwarded_host = false )
{
    return pof_url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
}

function pof_url_origin( $s, $use_forwarded_host = false )
{
    $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
    $sp       = strtolower( $s['SERVER_PROTOCOL'] );
    $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
    $port     = $s['SERVER_PORT'];
    $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
    $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
    $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

function pof_curl_post_async($url, $params = array()){

    $post_params = array();

    foreach ($params as $key => &$val) {
        if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts=parse_url($url);

    $fp = fsockopen($parts['host'],
        isset($parts['port'])?$parts['port']:80,
        $errno, $errstr, 30);

    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
    $out.= "Host: ".$parts['host']."\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    fwrite($fp, $out);

    fclose($fp);
}

function pof_get_programs() {
  $args = array(
    'numberposts' => -1,
    'posts_per_page' => -1,
    'post_type' => 'pof_post_program',
    'orderby' => 'title',
    'order' => 'ASC'
  );

  $the_query = new WP_Query( $args );
  $ret = array();

  if( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) {
      $the_query->the_post();

      $tmp = new stdClass();
			$tmp->id = $the_query->post->ID;
			$tmp->title = $the_query->post->post_title;

      $ret[] = $tmp;
    }
  }

  return $ret;

}

function pof_display_program_filter(){
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    $programs = pof_get_programs();

    //only add filter to post type you want
    if ('pof_post_suggestion' == $type){
        ?>
        <select name="pof_program">
        <option value="">Suodata ohjelman mukaan</option>
        <?php
            $current_v = isset($_GET['pof_program'])? $_GET['pof_program']:'';
            foreach ($programs as $program) {
                printf
                    (
                        '<option value="%s"%s>%s</option>',
                        $program->id,
                        $program->id == $current_v ? ' selected="selected"':'',
                        $program->title
                    );
                }
        ?>
        </select>
        <?php
    }
}
add_action( 'restrict_manage_posts', 'pof_display_program_filter' );

/*
 * Return agegroup of given taskgroup
 */
function pof_get_agegroup($post_id) {
  $agegroup = get_post_meta($post_id, 'ikakausi', true);
  $taskgroup = get_post_meta($post_id, 'suoritepaketti', true);

  if(!$agegroup && !$taskgroup) {
    return array();
  }

  // ACF saves empty values as 'null' string in some versions
	if(empty($agegroup) || $agegroup == 'null') {
		$agegroup = pof_get_agegroup($taskgroup);
	}

	return $agegroup;
}

/*
 * Filter suggestions on admin view based of given program
 */
function pof_filter_suggestions($query) {
    global $wpdb, $pagenow;

    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    if ( 'pof_post_suggestion' == $type && is_admin() && $pagenow=='edit.php' && isset($_GET['pof_program']) && $_GET['pof_program'] != '') {
        $to_remove = array();
        $results = $wpdb->get_results( "SELECT ID FROM wp_posts WHERE post_type = 'pof_post_suggestion'", OBJECT );

        foreach($results as $key => $post) {
          $task_id = get_post_meta($post->ID, 'pof_suggestion_task', true);
          if(!$task_id) {
            continue;
          }
          $task = (object) ['ID' => $task_id, 'post_type' => 'pof_post_task'];

          $task_taskgroup = get_post_meta($task_id, 'suoritepaketti', true);
          $task_agegroup = pof_get_agegroup($task_taskgroup);
          $task_program = get_post_meta($task_agegroup, 'suoritusohjelma', true);

          if($task_program != $_GET['pof_program'])  {
            $to_remove[] = $post->ID;
          }
        }

        $query->set( 'post__not_in', $to_remove );
        return;
    }

}
add_action('pre_get_posts', 'pof_filter_suggestions');