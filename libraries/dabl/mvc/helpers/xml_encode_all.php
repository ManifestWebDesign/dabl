<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

/**
 * @param array|object $array
 * @return string
 */
function xml_encode_all(&$array) {
	$dom = new DOMDocument('1.0', 'utf-8');
	$doc = create_xml_node('data', null, $dom);

	foreach (object_to_array($array) as $key => $value) {
		$doc->appendChild(create_xml_node($key, $value, $dom));
	}

	$dom->appendChild($doc);
	$dom->formatOutput = true;
	return $dom->saveXML();
}

/**
 * @param string $name
 * @param mixed $value
 * @param DomDocument $dom
 * @return DOMElement
 */
function create_xml_node($name, $value, DomDocument $dom = null) {
	$name = str_replace(' ', '_', $name);

	if ((string) (int) $name === (string) $name) {
		$name = 'item';
	}

	$element = $dom->createElement($name);

	if (is_array($value)) {
		foreach ($value as $child_name => &$child_value) {
			$element->appendChild(create_xml_node($child_name, $child_value, $dom));
		}
	} else {
		$element->appendChild($dom->createTextNode(htmlspecialchars($value, ENT_QUOTES)));
	}
	return $element;
}