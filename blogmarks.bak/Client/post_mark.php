<?php

/* construit le fichier Atom */

$doc = domxml_new_doc('1.0');

/* élément racine */
$root = $doc->create_element_ns('http://www.blogmarks.net/ns/', 
				'blogmark',
				'bm');
$root->add_namespace('http://purl.org/atom/ns', 'atom');
$root = $doc->append_child($root);
$root->set_attribute('xml:lang', 'fr'); //****** LANG A CHANGER

/* link */
$link = $doc->create_element('atom:link');
$link = $doc->append_child($link);
$link->set_attribute('href', $_GET['link']);

/* via */
$via = $doc->create_element('bm:via');
$via = $doc->append_child($via);
$via->set_attribute('href', $_GET['via']);

/* author */
$author    = $doc->create_element('atom:author');
$name      = $doc->create_element('atom:name');
$name      = $author->append_child($name);
$name_txt  = $doc->create_cdata_section($_GET['name']);
$name_txt  = $name->append_child($name_txt);
$email     = $doc->create_element('atom:email');
$email     = $author->append_child($email);
$email_txt = $doc->create_cdata_section($_GET['email']);
$email_txt = $email->append_child($email_txt);
$author    = $doc->append_child($author);

/* title */
$title     = $doc->create_element('atom:title');
$title_txt = $doc->create_cdata_section($_GET['title']);
$title_txt = $title->append_child($title_txt);
$title     = $doc->append_child($title);

/* summary */
$summary     = $doc->create_element('atom:summary');
$summary_txt = $doc->create_cdata_section($_GET['summary']);
$summary_txt = $summary->append_child($summary_txt);
$summary     = $doc->append_child($summary);

/* created */
$created     = $doc->create_element('atom:created');
$created_txt = $doc->create_text_node($_GET['created']);
$created_txt = $created->append_child($created_txt);
$created     = $doc->append_child($created);

/* issued */
$issued     = $doc->create_element('atom:issued');
$issued_txt = $doc->create_text_node($_GET['issued']);
$issued_txt = $issued->append_child($issued_txt);
$issued     = $doc->append_child($issued);

/* tags */
$tags_node = $doc->create_element('bm:tags');
$tags_node = $doc->append_child($tags_node);

/* tags publics */
$tags = split(' ', $_GET['publicTags']);
foreach($tags as $tag) {
  if ( $tag != '' ) {
    $tag_node       = $doc->create_element('bm:tag');
    $tag_title      = $doc->create_element('bm:title');
    $tag_title_txt  = $doc->create_text_node($tag); 
    $tag_title_txt  = $tag_title->append_child($tag_title_txt);
    $tag_title      = $tag_node->append_child($tag_title);
    $tag_status     = $doc->create_element('bm:status');
    $tag_status_txt = $doc->create_text_node('public');
    $tag_status_txt = $tag_status->append_child($tag_status_txt);
    $tag_status     = $tag_node->append_child($tag_status);
    $tag_node       = $tags_node->append_child($tag_node);
  }
}

/* tags privés */
$tags = split(' ', $_GET['privateTags']);
foreach($tags as $tag) {
  if ( $tag != '' ) {
    $tag_node       = $doc->create_element('bm:tag');
    $tag_title      = $doc->create_element('bm:title');
    $tag_title_txt  = $doc->create_text_node($tag); 
    $tag_title_txt  = $tag_title->append_child($tag_title_txt);
    $tag_title      = $tag_node->append_child($tag_title);
    $tag_status     = $doc->create_element('bm:status');
    $tag_status_txt = $doc->create_text_node('private');
    $tag_status_txt = $tag_status->append_child($tag_status_txt);
    $tag_status     = $tag_node->append_child($tag_status);
    $tag_node       = $tags_node->append_child($tag_node);
  }
}

$xml = $doc->dump_mem(TRUE, "UTF-8");

echo post (BM_SERVER, BM_POST_URI, $_GET['name'], $_GET['pass'], $xml)
?>