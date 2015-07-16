<?php
/*
Template Name: XML item
*/

header('Content-type: application/xml');

$post = get_post($_GET["postID"]);

$lang = "FI";

if (!empty($_GET["lang"])) {
	switch (strtolower($_GET["lang"])) {
		case "fi":
			$lang = "FI";
		break;
		case "sv":
			$lang = "SV";
		break;
		case "en":
			$lang = "EN";
		break;
	}
}

$title = $post->post_title;
$content = $post->post_content;

if ($lang != "FI") {
	$title = get_field("title_".strtolower($lang));
	$content = get_field("content_".strtolower($lang));
}

$data = array(
	'name'=>'nodes',
	array(
		'name'=>'node',
		'attributes' => array(
			'type' => str_replace('pof_post_', '', $post->post_type)
		),
        array(
			'name' => 'lang',
            'value' => $lang
        ),
		array(
			'name' => 'guid',
			'value' => wp_hash($post->id),
		),
		array(
			'name' => 'id',
			'value' => $post->ID,
		),
		array(
			'name' => 'lastModified',
			'value' => $post->post_modified,
		),
		array(
			'name' => 'lastModifiedBy',
			'attributes' => array(
				'userId' => $post->post_author
			),
			'value' => get_userdata($post->post_author)->display_name,
		),
		array(
			'name' => 'leaf',
			array(
				'name' => 'title',
				'value' => "<![CDATA[".$title."]]>",
			),
			array(
				'name' => 'content',
				'value' => "<![CDATA[".$content."]]>",
			),
			get_post_tags_XML($post->ID),
			get_post_images_XML($post->ID),
			get_post_additional_content_XML($post->ID)
		),		
	),
);

echo getXML($data);
/*
echo "<!--";
//print_r($post);

echo $post->ID;

$images = simple_fields_fieldgroup("additional_images_fg", $post->ID);
echo "..";
print_r($images);
echo "..";

foreach ($images as $image) {
	print_r($image);
}

echo "-->"
*/

?>