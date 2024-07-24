<?php
/**
 * spellcheck.php
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

require('./includes/Engine.php');
require('./OlcsEngine.php');
require('./includes/EnchantEngine.php');
require('./includes/PSpellEngine.php');

$tinymceSpellCheckerConfig = array(
	"engine" => "pspell", // enchant, pspell

	// // Enchant options
	// "enchant_dicts_path" => "./dicts",

	// PSpell options
	"PSpell.mode" => "fast",
	"pspell.spelling" => "British",
	"pspell.jargon" => "",
	"pspell.encoding" => "utf-8"
);

OlcsEngine::processRequest($tinymceSpellCheckerConfig);
