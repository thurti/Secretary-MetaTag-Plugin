<?php
/**
* 	Meta Data Plugin
*	by Thomas Hurtig
*	Version: 1.1
*
*	changelog:
*
*	1.1
*	- "Use Project Tags as Keyswords" option added under settings general
*	- added html linebreak between description and keyword tag
*
*	1.0
*	- adds description & keywords tags to site
*	- change description & keywords in backend under settings general
**/


/*
* Render meta tags on site
*/

hook('meta_frontend', 'addMetaData');	// use this to put the meta tags with call_anchor('meta_frontend'); into your template


function addMetaData(){	
	$meta = getMetaData();
	$desc = '<meta name="description" content="'. $meta['description'] .'" />';
	$keys = '<meta name="keywords" content="'. $meta['keywords'];

	if($meta['project_tags'] && projectHasTags()) {
		$keys .= ', '. implode(", ", projectTagsArray());
	}
	
	$keys .= '" />';
	
	echo $desc."\n".$keys."\n";
}


/*
* insert meta data into backend settings general
*/

hook('settings-general', 'addMetaSettings');

function addMetaSettings(){
	hook('prefsMisc', 'setMetaForm');
	hook( "form_process", "processMetaForm" );
}


/*
* display form in backend setting general
*/

function setMetaForm(){
	global $manager;

	//get meta data from database
	if($manager->clerk->settingExists('site_metaTags')){
		$meta = array(
			'description' => $manager->clerk->getSetting('site_metaTags',1),
			'keywords' => $manager->clerk->getSetting('site_metaTags',2),
			'project_tags' => $manager->clerk->getSetting('site_metaTags',3)
			);
	}else{
		$meta = array(
			'description' => '',
			'keywords' => '',
			'project_tags' => ''
			);
	}
	
	//tooltips
	$descrTooltip = 'This text is displayed by the search engines. Find a short and good description for your page. Use your main keywords in it. <br /><strong>Note:</strong> Google shows up to 160 characters in the search results and stores up to 200 characters in the index.';
	$keyTooltip = 'Write in your keywords seperated by a comma.';

	$manager->form->add_fieldset('Meta-Tags');
	$manager->form->add_textarea('description', 'Site Description', 5, 100, $meta['description'], '', $descrTooltip);
	$manager->form->add_textarea('keywords', 'Keywords', 2, 100, $meta['keywords'], '', $keyTooltip);
	$manager->form->add_input( "checkbox", "project_tags", " ", $meta['project_tags'], array( "Use Project Tags as Keywords" => 1 ) );
	$manager->form->close_fieldset();
	
	//check form input with regular expression		
	$descrError = 'Not allowed characters: < > ? & % \ / "';
	$descrRegex = '/^[^<>?&%\/\\\"\']*$/';

	$manager->form->add_rule('description', $descrRegex, '', $descrError);
	$manager->form->add_rule('keywords', $descrRegex, '', $descrError);
	
}


/*
* save meta data to databse
*/

function processMetaForm(){
	global $manager;

	
	// Check if setting already exists 
	if (!$manager->clerk->settingExists('site_metaTags')){ 
		// No, it doesn't - create it 
		$manager->clerk->addSetting( 'site_metaTags', array($_POST['description'], $_POST['keywords'], $_POST['project_tags'])); 
	}else{
		$manager->clerk->updateSetting('site_metaTags', array($_POST['description'], $_POST['keywords'], $_POST['project_tags']));
	}

}
?>