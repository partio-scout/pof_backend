<?php
/*
Template Name: JSON vinkit
*/

header('Content-type: application/json');


$post_guid = $_GET["postGUID"];

$args = array(
	'numberposts' => -1,
	'post_type' => array('pof_post_task', 'pof_post_taskgroup', 'pof_post_program', 'pof_post_agegroup' ),
	'meta_key' => 'post_guid',
	'meta_value' => $post_guid
);

$the_query = new WP_Query( $args );

if( $the_query->have_posts() ) {
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		$post = $the_query->post;
	}
}


$jsonItem = new stdClass();

$jsonItem->items = array();

$jsonItem->lastModified = "2015-03-26 18:15:34";

$jsonItem->lang = "FI";

$jsonItem->post = new stdClass();
$jsonItem->post->id = $post->ID;
$jsonItem->post->title = $post->post_title;

$jsonItem->items[0] = new stdClass();
$jsonItem->items[1] = new stdClass();

$jsonItem->items[0]->title = "Vapaaehtoinen otsikko";
$jsonItem->items[1]->title = "Vapaaehtoinen otsikko kakkonen";

$jsonItem->items[0]->content = "T&auml;m&auml; on SP:n julkaisema mallivinkki. Lopullisessa versiossa t&auml;h&auml;n voidaan liitt&auml;&auml; helposti kuvia tai esimerkiksi videoita Youtubesta. Vinkkej&auml; voi olla eri pituisia, ja niill&auml; on vapaehtoinen otsikko, joka on k&auml;tev&auml; tosi pitkill&auml; vinkeill&auml;. Kuten sanottua, vinkki voi olla hyvin hyvin pitk&auml;kin. T&auml;ss&auml; vinkiss&auml; jouduttiin v&auml;h&auml;n jaarittelemaan, ett&auml; siit&auml; saadaan niin pitk&auml; ett&auml; se voidaan katkaista jossain vaiheessa. Jos vinkki on tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi pitk&auml;, se voidaan asjaarittelemaan, ett&auml; siit&auml; saadaan niin pitk&auml; ett&auml; se voidaan katkaista jossain vaiheessa. Jos vinkki on tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi pitk&auml;, se voidaan asjaarittelemaan, ett&auml; siit&auml; saadaan niin pitk&auml; ett&auml; se voidaan katkaista jossain vaiheessa. Jos vinkki on tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi pitk&auml;, se voidaan asjaarittelemaan, ett&auml; siit&auml; saadaan niin pitk&auml; ett&auml; se voidaan katkaista jossain vaiheessa. Jos vinkki on tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi pitk&auml;, se voidaan asjaarittelemaan, ett&auml; siit&auml; saadaan niin pitk&auml; ett&auml; se voidaan katkaista jossain vaiheessa. Jos vinkki on tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi pitk&auml;, se voidaan asjaarittelemaan, ett&auml; siit&auml; saadaan niin pitk&auml; ett&auml; se voidaan katkaista jossain vaiheessa. Jos vinkki on tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi tosi pitk&auml;, se voidaan as";
$jsonItem->items[1]->content = "T&auml;m&auml; on mallivinkki. Lopullisessa versiossa t&auml;h&auml;n voidaan liitt&auml;&auml; helposti kuvia tai esimerkiksi videoita Youtubesta.";

$jsonItem->items[0]->publisher = new stdClass();
$jsonItem->items[1]->publisher = new stdClass();

$jsonItem->items[0]->publisher->nickname = "Suomen partiolaiset";
$jsonItem->items[1]->publisher->nickname = "Nimimerkki";

$jsonItem->items[0]->publisher->id = 1;
$jsonItem->items[1]->publisher->id = 2;

$jsonItem->items[0]->publisher->img = "TÄHÄN URLI SILLE KUVALLE, varmaankin mallia GRAVATAR, tms";
$jsonItem->items[1]->publisher->img = "";

$jsonItem->items[0]->published = "2015-03-26 18:15:34";
$jsonItem->items[1]->published = "2015-03-22 15:07:34";



echo json_encode($jsonItem);



?>