<?php

	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);

	$help_context = elgg_extract("help_context", $vars);
	$form_data = "";
	
	if($entity = elgg_extract("entity", $vars, false)){
		$title = $entity->title;
		$desc = $entity->description;
		$access_id = (int) $entity->access_id;
		$container_guid = $entity->getContainerGUID();
		
		$tags = $entity->tags;
		$comments = $entity->allow_comments;
		$context = $entity->help_context;
		if(!empty($context) && !is_array($context)){
			$context = array($context);
		} elseif(empty($context)){
			$context = array();
		}
		
		$submit_text = elgg_echo("edit");
		
		$form_data = elgg_view("input/hidden", array("name" => "guid", "value" => (int) $entity->getGUID()));
	} else {
		$title = "";
		$desc = "";
		$access_id = get_default_access();
		$container_guid = elgg_get_page_owner_guid();
		
		$tags = array();
		$comments = "no";
		$context = array();
		
		$submit_text = elgg_echo("submit");
	}

	$form_data .= "<div>";
	$form_data .= "<label>" . elgg_echo("user_support:question") . "</label>";
	$form_data .= elgg_view("input/text", array("name" => "title", "value" => $title));
	$form_data .= "</div>";
	
	$form_data .= "<div>";
	$form_data .= "<label>" . elgg_echo("user_support:anwser") . "</label>";
	$form_data .= elgg_view("input/longtext", array("name" => "description", "value" => $desc));
	$form_data .= "</div>";

	$form_data .= "<div>";
	$form_data .= "<label>" . elgg_echo("tags") . "<label>";
	$form_data .= elgg_view("input/tags", array("name" => "tags", "value" => $tags));
	$form_data .= "</div>";
	
	if(elgg_is_admin_logged_in() && !empty($help_context)){
		$form_data .= "<div>";
		$form_data .= "<label>" . elgg_echo("user_support:help_context") . "</label><br />";
		
		$form_data .= "<select name='help_context[]' multiple='multiple' size='" . min(count($help_context), 5) . "'>";
		foreach($help_context as $hc){
			$selected = "";
			if(in_array($hc, $context)){
				$selected = "selected='selected'";
			}
			$form_data .= "<option value='" . $hc . "' " . $selected . ">" . $hc . "</option>";
		}
		$form_data .= "</select>";
		$form_data .= "</div>";
	}
	
	$form_data .= "<div>";
	$form_data .= "<label>" . elgg_echo("access") . "</label>";
	$form_data .= "&nbsp;" . elgg_view("input/access", array("name" => "access_id", "value" => $access_id));
	$form_data .= "</div>";

	$form_data .= "<div>";
	$form_data .= "<label>" . elgg_echo("user_support:allow_comments") . "</label>";
	$form_data .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "allow_comments", "options_values" => $noyes_options, "value" => $comments));
	$form_data .= "</div>";
	
	$form_data .= "<div class='elgg-foot'>";
	$form_data .= elgg_view("input/hidden", array("name" => "container_guid", "value" => $container_guid));
	$form_data .= elgg_view("input/submit", array("value" => $submit_text));
	$form_data .= "</div>";

	echo $form_data;