<?php
function readDirFile(){
	try {
		$file = file("config/directories.xml");
		$file = implode("",$file);
		$file = simplexml_load_string($file);
		$file = json_encode($file);
		$file = json_decode($file,true);
		return $file;
	}catch (Exception $e){
		return false;
	}
}

function readLangFile($file){
	$file = file($file);
	$file = implode("",$file);
	$file = simplexml_load_string($file);
	//$file = json_encode($file);
	//$file = json_decode($file,true);
	return $file;
}

function cleanTranslationStrings($str,$dirs){
	$xml = readLangFile($dirs['structure']['languages'].DIR_SEP."autotranslate.xml");
	$lang = Zend_Registry::get('currentlang');
	$i = 0;
	foreach ($xml->translate as $k=>$v){
		$v=json_encode($v);
		$v = json_decode($v,1);
		if($v['@attributes']['lang']==$lang && $v['@attributes']['value']==$str){
			unset($xml->translate[$i]);
		}
		$i++;
	}
	$xml->asXML($dirs['structure']['languages'].DIR_SEP."autotranslate.xml");
}




function keepTranslationStrings($str,$dirs){
	$xml = readLangFile($dirs['structure']['languages'].DIR_SEP."autotranslate.xml");
	$lang = Zend_Registry::get('currentlang');
	//check if the value already exists...
	foreach ($xml->translate as $k=>$v){
		$v=json_encode($v);
		$v = json_decode($v,1);
		if($v['@attributes']['value']==$str){
			return ;
		}
	}
	$node = $xml->addChild('translate');
	$node->addAttribute('value',$str);
	$node->addAttribute('lang',$lang);
	$xml->asXML($dirs['structure']['languages'].DIR_SEP."autotranslate.xml");
}

function amiInAfolder($dirs){
	if(!is_array($dirs['special']['folder'])){
		$_SESSION['QOOL_FOLDER'] = $dirs['special']['folder'].'/';
	}
}


function setIncludePath($dirs){
	
	if(!is_array($dirs['special']['folder'])){
		set_include_path(get_include_path() . PATH_SEPARATOR .$_SERVER['DOCUMENT_ROOT']."/".$dirs['special']['folder']."/lib/".PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT']);
	}else{
		set_include_path(get_include_path() . PATH_SEPARATOR .$_SERVER['DOCUMENT_ROOT']."/lib/".PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT']);
	}
	return ;
}

function getControllers($dirs,$config){
	$cons = array();
	foreach ($config->addon as $c){
		if(is_object($c)){
			if($c->state=="installed" && $c->level>=$_SESSION['access_level']){
				$cons[$c->name] = $dirs['structure']['addons']."/".$c->name."/controllers";
			}
		}
	}
	return $cons;
}

function getModules($dirs,$config){
	
	$cons = array();
	foreach ($config->addon as $c){
		
		if(is_object($c)){
			if($c->state=="installed" && $c->level>=$_SESSION['access_level']){
				if($c->parent=='none'){
					$cons[$c->name] = $dirs['structure']['modules']."/".$c->name;
				}else{
					$cons[$c->name] = $c->parent;
				}
			}
		}
	}
	
	return $cons;
}

function getWidgets($dirs,$config){
	$cons = array();
	foreach ($config->addon as $c){
		if(is_object($c)){
			if($c->state=="installed" && $c->parent=='none' && $c->level>=$_SESSION['access_level']){
				if($c->parent=='none'){
					$cons[$c->name] = $dirs['structure']['widgets']."/".$c->name;
				}else{
					$cons[$c->name] = $c->parent;
				}
			}
		}
	}
	
	return $cons;
}

function buildLanguage($sys,$user){
	$language = array();
	//d($sys);
	foreach ($sys as $k=>$v){
		$p = json_encode($v);
		$p = json_decode($p,true);
		$language[$p['@attributes']['value']] = $p[0];
	}
	foreach ($user as $k=>$v){
		$p = json_encode($v);
		$p = json_decode($p,true);
		$language[$p['@attributes']['value']] = $p[0];
	}
	return $language;
}

function cleanAutoTranslate($xml,$from){
	$i = 0;
	foreach ($xml->translate as $k=>$v){
		$v=json_encode($v);
		$v = json_decode($v,1);
		if($v['@attributes']['lang']!=$from){
			unset($xml->translate[$i]);
		}
		$i++;
	}
	return $xml;
}



function givemeGuestRights(){
	if($_SESSION['user']){
		return ;
	}
	$_SESSION['user'] = array(
	'username'		=>	'Guest',
	'level'	=>	'8000'
	);
}

function normalizeDbTables($array){
	$tables = array();
	foreach ($array['tables'] as $k=>$v){
		$tables[$k] = $array['prefix'].$v;
	}
	$tables['prefix'] = $array['prefix'];
	return $tables;
}

function d($d){
	echo "<pre>";
	print_r($d);
	echo "</pre>";
}

function deGreek($str){
	//load the greek letters file

	$greeks = array(
	'Α'=>'A',
	'Β'=>'B',
	'Γ'=>'G',
	'Δ'=>'D',
	'Ε'=>'E',
	'Ζ'=>'Z',
	'Η'=>'H',
	'Θ'=>'Th',
	'Ι'=>'I',
	'Κ'=>'K',
	'Λ'=>'L',
	'Μ'=>'M',
	'Ν'=>'N',
	'Ξ'=>'Ks',
	'Ο'=>'O',
	'Π'=>'P',
	'Ρ'=>'R',
	'Σ'=>'S',
	'Τ'=>'T',
	'Υ'=>'Y',
	'Φ'=>'F',
	'Χ'=>'X',
	'Ψ'=>'Ps',
	'Ω'=>'W',
	'α'=>'a',
	'β'=>'b',
	'γ'=>'g',
	'δ'=>'d',
	'ε'=>'e',
	'ζ'=>'z',
	'η'=>'i',
	'θ'=>'th',
	'ι'=>'i',
	'κ'=>'k',
	'λ'=>'l',
	'μ'=>'m',
	'ν'=>'n',
	'ξ'=>'ks',
	'ο'=>'o',
	'π'=>'p',
	'ρ'=>'r',
	'σ'=>'s',
	'τ'=>'t',
	'υ'=>'u',
	'φ'=>'f',
	'χ'=>'x',
	'ψ'=>'ps',
	'ω'=>'w',
	'ς'=>'s',
	'ά'=>'a',
	'έ'=>'e',
	'ή'=>'i',
	'ί'=>'i',
	'ό'=>'o',
	'ύ'=>'u',
	'ώ'=>'w',
	'Ά'=>'A',
	'Έ'=>'E',
	'Ή'=>'H',
	'Ί'=>'I',
	'Ό'=>'O',
	'Ύ'=>'Y',
	'Ώ'=>'W'
	);


	$search = @array_keys($greeks);
	$str = str_replace( $search, $greeks, $str );
	$str = strtolower($str);
	return $str;
}
?>