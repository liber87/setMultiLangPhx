<?php
		
	if (($modx->event->name=='OnPageNotFound') && ($_REQUEST['q']=='setMultiLangPlaceholders')) {
		if ($_SESSION['mgrRole'] != 1) return;
		header("HTTP/1.1 200 OK");
				
		if (isset($_REQUEST['id'])){
			$id = $_REQUEST['id'];		
			if ($_REQUEST['element']=='chunks'){
				$table = $modx->getFullTableName('site_htmlsnippets');
				$field = 'snippet';
				} else {
				$table = $modx->getFullTableName('site_templates');
				$field = 'content';
			}
		}
		
		if (isset($_POST['text'])) $_POST['text'] = htmlspecialchars_decode($_POST['text']);
		
		if (isset($_GET['getCode'])){		
			echo htmlspecialchars($modx->db->getValue('Select '.$field.' from '.$table.' where id='.$id), ENT_QUOTES);		
			exit();
		}
		
		if ($mode=='blang') $def = $modx->db->getValue('Select `value` from '.$modx->getFullTableName('blang_settings').' where name="default"');
		if ($mode=='evobabel') $def = $modx->db->getValue('Select `code` from '.$modx->getFullTableName('languages_list').' where `gen`=1');
		
		if (isset($_REQUEST['translate'])){
			
			if ($mode=='blang'){
				/*for bLang*/
				$lngs = $modx->db->getValue('Select `value` from '.$modx->getFullTableName('blang_settings').' where name="languages"');
				
				$yet = $modx->db->getValue('Select id from '.$modx->getFullTableName('blang').' where
				'.$def.'="'.$modx->db->escape($_POST['text']).'"');
				
				$name = strip_tags($_POST['text']);
				$name = $modx->stripAlias($name);
				$name = str_replace('-','_',$name);
				$name = substr($name, 0, 8);
				$max = $modx->db->getValue('Select max(id) from '.$modx->getFullTableName('blang'));
				$max = $max+1;
				$name.='_'.$max;
				
				$out='<div class="form-group"><label for="name">Название параметра</label><input name="name" value="'.$name.'" class="form-control"></div>';
				
				if ($yet){			
					$res = $modx->db->query('Select '.str_replace('||',',',$lngs).' from 
					'.$modx->getFullTableName('blang').' where id='.$yet);
					$row = $modx->db->getRow($res);			
					foreach($row as $key => $val) {
						$out.='<div class="form-group"><label for="'.$key.'">'.$key.'</label><textarea name="'.$key.'" class="form-control" id="'.$key.'" rows="3">'.$val.'</textarea></div>';
					}
					echo $out;
					exit();
				}
				/*for bLang*/
			}
			
			if ($mode=='evobabel'){
				/*for evoBabel*/
				$lngs = $modx->db->getValue('Select GROUP_CONCAT(`code` SEPARATOR "||")  from '.$modx->getFullTableName('languages_list'));
				
				$yet = $modx->db->getValue('Select id from '.$modx->getFullTableName('lexicon').' where
				'.$def.'="'.$modx->db->escape($_POST['text']).'"');
				
				$name = strip_tags($_POST['text']);
				$name = $modx->stripAlias($name);
				$name = str_replace('-','_',$name);
				$name = substr($name, 0, 8);
				$max = $modx->db->getValue('Select max(id) from '.$modx->getFullTableName('lexicon'));
				$max = $max+1;
				$name.='_'.$max;
				
				$out='<div class="form-group"><label for="name">Название параметра</label><input name="name" value="'.$name.'" class="form-control"></div>';
				
				if ($yet){			
					$res = $modx->db->query('Select '.str_replace('||',',',$lngs).' from 
					'.$modx->getFullTableName('lexicon').' where id='.$yet);
					$row = $modx->db->getRow($res);			
					foreach($row as $key => $val) {
						$out.='<div class="form-group"><label for="'.$key.'">'.$key.'</label><textarea name="'.$key.'" class="form-control" id="'.$key.'" rows="3">'.$val.'</textarea></div>';
					}
					echo $out;
					exit();
				}
				/*for evoBabel*/
			}
			
			
			$out.= '<div class="form-group"><label for="'.$def.'">'.$def.'</label><textarea name="'.$def.'" class="form-control" id="'.$def.'" rows="2">'.$_POST['text'].'</textarea></div>';
			$tl = explode('||',$lngs);
			
			foreach($tl as $l){
				if ($l!=$def){
					if ($translate){
						
						$data = ['source'=>$_POST['text'],'lang'=>$def.'-'.$l];						
						$data_string = json_encode ($data, JSON_UNESCAPED_UNICODE);
						$ch = curl_init('https://fasttranslator.herokuapp.com/api/v1.0/text/to/text?'.http_build_query($data));
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_HEADER, false);
						$result = curl_exec($ch);
						curl_close($ch);
						$res = json_decode($result,1);
						
					}
					if ($res['data']) $text = $res['data'];
					else $text = $_POST['text'];
					$out.= '<div class="form-group"><label for="'.$l.'">'.$l.'</label><textarea name="'.$l.'" class="form-control" id="'.$l.'" rows="2">'.$text.'</textarea></div>';
				}
			}
			echo $out;
			exit();		
		}
		
		if (isset($_REQUEST['setChanges'])){	
			foreach($_POST as $key => $val) $_POST[$key] = $modx->db->escape($val);
			
			if ($mode=='blang')	{
				$phx = '[(__'.$_POST['name'].')]';
				$modx->db->insert($_POST,$modx->getFullTableName('blang'));
			}
			if ($mode=='evobabel') {
				$phx='[%'.$_POST['name'].'%]';
				$modx->db->insert($_POST,$modx->getFullTableName('lexicon'));
			}
			
			if (!$replace_all) $where = ' where id='.$id;
			
			$modx->db->query("UPDATE ".$modx->getFullTableName('site_htmlsnippets')." SET snippet = REPLACE (snippet, '".$modx->db->escape($_POST[$def])."', '".$phx."')".$where);
			$modx->db->query("UPDATE ".$modx->getFullTableName('site_templates')." SET content = REPLACE (content, '".$modx->db->escape($_POST[$def])."', '".$phx."')".$where);
			
			echo htmlspecialchars($modx->db->getValue('Select '.$field.' from '.$table.' where id='.$id), ENT_QUOTES);		
			exit();		
		}
		exit();
	}
	
	if (($modx->event->name=='OnChunkFormRender') || ($modx->event->name=='OnTempFormRender')) {
		if ($_SESSION['mgrRole'] != 1) return;
		if ($modx->event->name=='OnChunkFormRender') $elem = 'chunks';
		else $elem = 'templates';
		$output = '
		<script type="text/javascript">
		if (!!window.jQuery)
		{
		var jquery = document.createElement("script");			
		jquery.src = "https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js";
		document.getElementsByTagName("head")[0].appendChild(jquery);
		var $j = jQuery.noConflict();
		}
		document.addEventListener(\'DOMContentLoaded\', function(){
		modx = parent.modx;	
		$j(\'#actions > .btn-group\').append(\'<span class="btn btn-secondary lng" id="translate" style="display:none;"><span>Создать плейсхолдер</span></span> \');
		
		var getSelectedText = function() {
		var text = \'\';
		if (window.getSelection) {
		text = window.getSelection().toString();
		} else if (document.selection) {
		text = document.selection.createRange().text;
		}
		return text;
		}
		
		$j(window).on(\'mouseup\', function(){ 
		var text = getSelectedText();
		if (text != \'\') $j(\'#translate\').show();
		else $j(\'#translate\').hide();
		});
		
		$j(document).on(\'click\',\'#translate\',function(e){
		e.preventFefault;
		modx.main.work();
		var text = getSelectedText();					
		$j.ajax({
		type: \'post\', url: \'./../setMultiLangPlaceholders?translate\', data: \'&text=\'+text,
		success: function(result){			
		modx.main.stopWork();
		modx.popup({
		icon: \'fa-language\',
		text: \'<form id="form_sml_phx">\'+result+\'<div class="text-center"><button type="button" class="btn btn-success" id="setChanges">Внести изменения</button></div></form>\',
		width: \'80%\',
		title:\'Вставка плейсхолдеров\',
		draggable:false,			
		hover: 0,
		hide: 0});
		}
		});
		return false;
		
		});	
		$j(document).on(\'click\',\'#setChanges\',function(e){		
		e.preventDefault;
		$.ajax({
		type: \'post\',
		url: \'./../setMultiLangPlaceholders?setChanges&element='.$elem.'&id='.$_GET['id'].'\',
		data: $j(\'#form_sml_phx\').serialize(),					
		success: function(result){	
		//alert(result);
		location.reload();		
		}
		
		});
		});	
		
		});
		
		
		</script>';
		$modx->Event->output($output);
	}	