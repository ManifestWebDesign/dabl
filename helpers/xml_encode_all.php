<?php

function xml_encode_all($array) {
	if (!is_object($array) && !is_array($array))
		throw new Exeption('xml_encode_all can only convert arrays and objects');
	$array = object_to_array($array);

	$dom = new DOMDocument('1.0', 'utf-8');

	foreach($array as $key => $value){
		$dom->appendChild(create_xml_node($key, $value, $dom));
	}
	$dom->formatOutput = true;
	return $dom->saveXML();
}

function create_xml_node($name, $value, DomDocument $dom = null){
	if(!is_string($name))
		$name = 'element';
	$element = $dom->createElement($name);
	if(is_array($value)){
		foreach($value as $child_name => $child_value)
			$element->appendChild(create_xml_node($child_name, $child_value, $dom));
	}
	else{
		$element->appendChild($dom->createTextNode(utf8_encode($value)));
	}
	return $element;
}