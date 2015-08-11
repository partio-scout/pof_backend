if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_ikakauden-tiedot',
		'title' => 'Ikäkauden tiedot',
		'fields' => array (
			array (
				'key' => 'field_54f5be1e0f962',
				'label' => 'Minimi-ikä',
				'name' => 'agegroup_min_age',
				'type' => 'number',
				'required' => 1,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => 1,
			),
			array (
				'key' => 'field_54f5be440f963',
				'label' => 'Maksimi-ikä',
				'name' => 'agegroup_max_age',
				'type' => 'number',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => '',
				'max' => '',
				'step' => 1,
			),
			array (
				'key' => 'field_55a23db0a099e',
				'label' => 'Suoritepakettien yläkäsite',
				'name' => 'agegroup_subtaskgroup_term',
				'type' => 'select',
				'choices' => array (
					'jalki' => 'Jälki',
					'kasvatusosio' => 'Kasvatusosio',
					'ilmansuunta' => 'Ilmansuunta',
					'taitomerkki' => 'Taitomerkki',
					'tarppo' => 'Tarppo',
					'ryhma' => 'Ryhmä',
					'aktiviteetti' => 'Aktiviteetti',
					'aihe' => 'Aihe',
					'tasku' => 'Tasku',
					'rasti' => 'Rasti',
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_agegroup',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_ikakausi-ref',
		'title' => 'Ikäkausi-ref',
		'fields' => array (
			array (
				'key' => 'field_54f5bd8750835',
				'label' => 'Ikäkausi',
				'name' => 'ikakausi',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'pof_post_agegroup',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 1,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_taskgroup',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_ingressi-suomi',
		'title' => 'Ingressi suomi',
		'fields' => array (
			array (
				'key' => 'field_5593127f743f5',
				'label' => 'Ingressi',
				'name' => 'ingress',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'formatting' => 'br',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_program',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_agegroup',
					'order_no' => 0,
					'group_no' => 1,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_taskgroup',
					'order_no' => 0,
					'group_no' => 2,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_task',
					'order_no' => 0,
					'group_no' => 3,
				),
			),
		),
		'options' => array (
			'position' => 'acf_after_title',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_ohjelman-tiedot',
		'title' => 'Ohjelman tiedot',
		'fields' => array (
			array (
				'key' => 'field_559837d9283d1',
				'label' => 'Omistaja',
				'name' => 'program_owner',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_5598381b22ac2',
				'label' => 'Kieli',
				'name' => 'program_lang',
				'type' => 'select',
				'required' => 1,
				'choices' => array (
					'fi' => 'Suomi',
					'sv' => 'Ruotis',
					'en' => 'Englanti',
					'so' => 'Somalia',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_program',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_sisaltokuvat',
		'title' => 'Sisältökuvat',
		'fields' => array (
			array (
				'key' => 'field_558a31f188be8',
				'label' => 'Logo',
				'name' => 'logo_image',
				'type' => 'image',
				'save_format' => 'object',
				'preview_size' => 'thumbnail',
				'library' => 'all',
			),
			array (
				'key' => 'field_558a38b65e824',
				'label' => 'Iso kuva',
				'name' => 'main_image',
				'type' => 'image',
				'save_format' => 'object',
				'preview_size' => 'thumbnail',
				'library' => 'all',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_program',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_agegroup',
					'order_no' => 0,
					'group_no' => 1,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_taskgroup',
					'order_no' => 0,
					'group_no' => 2,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_task',
					'order_no' => 0,
					'group_no' => 3,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_suoritepaketin-tiedot',
		'title' => 'Suoritepaketin tiedot',
		'fields' => array (
			array (
				'key' => 'field_558fbbde2db43',
				'label' => 'Vapaavalintaisten lukumäärä',
				'name' => 'taskgroup_additional_tasks_count',
				'type' => 'number',
				'default_value' => 0,
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => 0,
				'max' => 100,
				'step' => '',
			),
			array (
				'key' => 'field_55a23b63a2b03',
				'label' => 'Suoritteiden yläkäsite',
				'name' => 'taskgroup_subtask_term',
				'type' => 'select',
				'choices' => array (
					'askel' => 'Askel',
					'aktiviteetti' => 'Aktiviteetti',
					'aktiviteettitaso' => 'Aktiviteettitaso',
					'suoritus' => 'suoritus',
					'paussi' => 'paussi',
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_55a23d07a2b04',
				'label' => 'Suoritepakettien yläkäsite',
				'name' => 'taskgroup_subtaskgroup_term',
				'type' => 'select',
				'choices' => array (
					'jalki' => 'Jälki',
					'kasvatusosio' => 'Kasvatusosio',
					'ilmansuunta' => 'Ilmansuunta',
					'taitomerkki' => 'Taitomerkki',
					'tarppo' => 'Tarppo',
					'ryhma' => 'Ryhmä',
					'aktiviteetti' => 'Aktiviteetti',
					'aihe' => 'Aihe',
					'tasku' => 'Tasku',
					'rasti' => 'Rasti',
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_55a24129b9b67',
				'label' => 'Suoritepaketin yläkäsite',
				'name' => 'taskgroup_taskgroup_term',
				'type' => 'select',
				'choices' => array (
					'jalki' => 'Jälki',
					'kasvatusosio' => 'Kasvatusosio',
					'ilmansuunta' => 'Ilmansuunta',
					'taitomerkki' => 'Taitomerkki',
					'tarppo' => 'Tarppo',
					'ryhma' => 'Ryhmä',
					'aktiviteetti' => 'Aktiviteetti',
					'aihe' => 'Aihe',
					'tasku' => 'Tasku',
					'rasti' => 'Rasti',
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_taskgroup',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_suoritepaketti-ref',
		'title' => 'Suoritepaketti-ref',
		'fields' => array (
			array (
				'key' => 'field_54f5bde393d24',
				'label' => 'Suoritepaketti',
				'name' => 'suoritepaketti',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'pof_post_taskgroup',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 1,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_taskgroup',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_task',
					'order_no' => 0,
					'group_no' => 1,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_suorituksen-lisatiedot',
		'title' => 'Suorituksen lisätiedot',
		'fields' => array (
			array (
				'key' => 'field_558fba02ff3ac',
				'label' => 'Pakollinen',
				'name' => 'task_mandatory',
				'type' => 'true_false',
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_558fba1cff3ad',
				'label' => 'Pakollinen meripartiolaisille',
				'name' => 'task_mandatory_seascouts',
				'type' => 'true_false',
				'message' => '',
				'default_value' => 0,
			),
			array (
				'key' => 'field_558fbb0d4a808',
				'label' => 'Ryhmäkoko',
				'name' => 'task_groupsize',
				'type' => 'select',
				'required' => 1,
				'choices' => array (
					'one' => 'Yksin',
					'two' => 'Kaksin',
					'few' => 'Muutama',
					'group' => 'Laumassa tai vartiossa',
					'big' => 'Isommassa porukassa',
				),
				'default_value' => 'group',
				'allow_null' => 0,
				'multiple' => 1,
			),
			array (
				'key' => 'field_559ad68b4035f',
				'label' => 'Paikka',
				'name' => 'task_place_of_performance',
				'type' => 'select',
				'required' => 1,
				'choices' => array (
					'meeting_place' => 'Kolo',
					'hike' => 'Retki',
					'camp' => 'Leiri',
					'boat' => 'Vene',
					'other' => 'Muu',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 1,
			),
			array (
				'key' => 'field_55a7a817a4c69',
				'label' => 'Suorituksen yläkäsite',
				'name' => 'task_task_term',
				'type' => 'select',
				'choices' => array (
				),
				'default_value' => '',
				'allow_null' => 1,
				'multiple' => 0,
			),
			array (
				'key' => 'field_55ca532407e0b',
				'label' => 'Suorituksen kesto',
				'name' => 'task_duration',
				'type' => 'select',
				'required' => 1,
				'choices' => array (
					10 => '10 min',
					20 => '20 min',
					30 => '30 min',
					45 => '45 min',
					60 => '1 h',
					90 => '1,5 h',
					120 => '2 h',
					180 => '3 h',
					240 => '4 h',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
			array (
				'key' => 'field_55ca534f07e0c',
				'label' => 'Suorituksen valmistelun kesto',
				'name' => 'task_preparationduration',
				'type' => 'select',
				'required' => 1,
				'choices' => array (
					10 => '10 min',
					20 => '20 min',
					30 => '30 min',
					45 => '45 min',
					60 => '1 h',
					90 => '1,5 h',
					120 => '2 h',
					180 => '3 h',
					240 => '4 h',
				),
				'default_value' => '',
				'allow_null' => 0,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_task',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_suoritusohjelma-ref',
		'title' => 'Suoritusohjelma-ref',
		'fields' => array (
			array (
				'key' => 'field_54f428f4fa209',
				'label' => 'Suoritusohjelma',
				'name' => 'suoritusohjelma',
				'type' => 'post_object',
				'post_type' => array (
					0 => 'pof_post_program',
				),
				'taxonomy' => array (
					0 => 'all',
				),
				'allow_null' => 1,
				'multiple' => 0,
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_agegroup',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
					'order_no' => 0,
					'group_no' => 1,
				),
				array (
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-xml-full.php',
					'order_no' => 1,
					'group_no' => 1,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
					'order_no' => 0,
					'group_no' => 2,
				),
				array (
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-json-full.php',
					'order_no' => 1,
					'group_no' => 2,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'page',
					'order_no' => 0,
					'group_no' => 3,
				),
				array (
					'param' => 'page_template',
					'operator' => '==',
					'value' => 'page-linktree.php',
					'order_no' => 1,
					'group_no' => 3,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'no_box',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
	register_field_group(array (
		'id' => 'acf_ruotsinkieliset-sisallot',
		'title' => 'Ruotsinkieliset sisällöt',
		'fields' => array (
			array (
				'key' => 'field_5509a3ccb20b2',
				'label' => 'Otsikko',
				'name' => 'title_sv',
				'type' => 'text',
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'formatting' => 'html',
				'maxlength' => '',
			),
			array (
				'key' => 'field_55931206ea34c',
				'label' => 'Ingressi',
				'name' => 'ingress_sv',
				'type' => 'textarea',
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'formatting' => 'br',
			),
			array (
				'key' => 'field_5509a3e2b20b3',
				'label' => 'Sisältö',
				'name' => 'content_sv',
				'type' => 'wysiwyg',
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_program',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_agegroup',
					'order_no' => 0,
					'group_no' => 1,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_taskgroup',
					'order_no' => 0,
					'group_no' => 2,
				),
			),
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'pof_post_task',
					'order_no' => 0,
					'group_no' => 3,
				),
			),
		),
		'options' => array (
			'position' => 'normal',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 999,
	));
}
