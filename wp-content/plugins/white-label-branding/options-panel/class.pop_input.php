<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class pop_input {
	var $uid = 0;
	var $farbtastic = true;
	function pop_input($args=array()){
		$defaults = array(
			'farbtastic'	=> true
		);
		foreach($defaults as $property => $default){
			$this->$property = isset($args[$property])?$args[$property]:$default;
		}
		//---	
	}
	function get_el_id($tab,$i,$o){
		$id = property_exists($o,'id')?$o->id:(property_exists($o,'name')?$o->name:$tab->type.$this->uid++);
		if(strpos($id,'[]')){
			$id = str_replace('[]',$this->uid++,$id);
		}
		return $id;
	}
	
	function get_el_name($tab,$i,$o){
		return property_exists($o,'name')?$o->name:(property_exists($o,'id')?$o->id:$tab->type.$this->uid++);
	}	
	
	function get_option_name($tab,$i,$o){
		return property_exists($o,'option_name')?$o->option_name:str_replace("[]", "", $this->get_el_name($tab,$i,$o) );
	}
	
	function get_el_properties($tab,$i,$o){
		$elp = array();
		if(count(@$o->el_properties)>0){
			foreach($o->el_properties as $prop => $val){
				$elp[] = sprintf("%s=\"%s\"",$prop,$val);
			}
		}
		return implode(' ',$elp);
	}	
	function _subtitle($tab,$i,$o,&$save_fields){
		return sprintf("<h3 class=\"option-panel-subtitle\">%s</h3>",@$o->label);
	}
	
	function translucent_description($description=''){
		return trim($description)==''?'':sprintf('<div class="pt-clear"></div><div class="description-holder"><div class="description">%s</div><div class="description-bg">%s</div></div>',$description,$description);
	}
	
	function _description($tab,$i,$o){
		return $this->translucent_description(@$o->description);
	}
	
	function _hr(){
		return sprintf("<hr class=\"hr\" />");
	}
	
	function _textarea($tab,$i,$o,&$save_fields){
		if(true===$o->save_option){
			$save_fields[]= $this->get_option_name($tab,$i,$o);	
		}
		$str = '';
		if(!@$o->nolabel){
			$str.=sprintf("<div class=\"slider-label\">%s</div>",@$o->label);
		}
		$str .= sprintf("<textarea id=\"%s\" name=\"%s\" %s>%s</textarea>",
			$this->get_el_id($tab,$i,$o),
			$this->get_el_name($tab,$i,$o), 
			$this->get_el_properties($tab,$i,$o), 
			$o->value
			);
		return $str;
	}
	
	function _label($tab,$i,$o,&$save_fields){
		$label = property_exists($o,'value')?$o->value:(property_exists($o,'label')?$o->label:false);
		return $label?sprintf('<label>%s</label>',$label ):'';
	}
	
	function _farbtastic($tab,$i,$o,&$save_fields){
		if ( !$this->farbtastic ){
			return $this->_colorpicker($tab,$i,$o,$save_fields);
		}
		//---
		$farbtastic_id = 'pop-farbtastic-'.$this->uid++;
		
		$show_label = property_exists($o,'show_label')?$o->show_label:__('Choose color','pop');
		$hide_label = property_exists($o,'hide_label')?$o->hide_label:__('Close','pop');
		$o->value = trim($o->value)==''?'#':$o->value;
		//return "<div class=\"farbtastic-holder\">".$this->_text($tab,$i,$o,$save_fields).sprintf('<a class="farbtastic-choosecolor" href="javascript:void(0);" rel="%s">%s</a><div id="%s" rel="#%s" class="pop-farbtastic"></div>',$hide_label,$show_label,$farbtastic_id,$this->get_el_id($tab,$i,$o))."</div>";
		return sprintf('<div class="farbtastic-holder">%s<a title="%s" class="farbtastic-choosecolor" href="javascript:void(0);" rel="%s">%s</a><div id="%s" rel="#%s" class="pop-farbtastic"></div></div><div class="pop-float-separator">&nbsp;</div>',
			$this->_text($tab,$i,$o,$save_fields),
			$show_label,
			$hide_label,
			$show_label,
			$farbtastic_id,
			$this->get_el_id($tab,$i,$o)
		);	
	}
	
	function _colorpicker($tab,$i,$o,&$save_fields){
		$o->el_properties = property_exists($o,'el_properties')&&is_array($o->el_properties)?$o->el_properties:array();
		$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' pop-colorpicker':'pop-colorpicker';
		return $this->_text($tab,$i,$o,$save_fields);
	}
	
	function _text($tab,$i,$o,&$save_fields){
		$str = sprintf('<input type="text" id="%s" name="%s" value="%s" %s />',
			$this->get_el_id($tab,$i,$o),
			$this->get_el_name($tab,$i,$o),
			$o->value, 
			$this->get_el_properties($tab, $i, $o) 
		);		
		if(true===$o->save_option){
			$save_fields[]=$this->get_option_name($tab,$i,$o);	
		}
		return $str;
	}
	
	function _input_range($tab,$i,$o,&$save_fields){
		foreach(array('step'=>1,'min'=>0,'max'=>1,'nolabel'=>false) as $field => $default){
			$o->$field = property_exists($o,$field)?$o->$field:$default;
		}
		$str = '';
		if(!$o->nolabel){
			$str.=sprintf("<div class=\"slider-label\">%s</div>",@$o->label);
		}
		
		//$o->el_properties = property_exists($o,'el_properties')?$o->el_properties:array();
		//$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' pop_rangeinput':'pop_rangeinput';	
		
		$str .= sprintf('<input type="range" id="%s" name="%s" value="%s"  min="%s" max="%s" step="%s" %s /><div class="pop-sep">&nbsp;</div>',$this->get_el_id($tab,$i,$o),$this->get_el_name($tab,$i,$o),$o->value , $o->min, $o->max, $o->step, $this->get_el_properties($tab, $i, $o) );		
		if(true===$o->save_option){
			$save_fields[]=$this->get_option_name($tab,$i,$o);	
		}
		return $str;
	}
	
	function _range($tab,$i,$o,&$save_fields){
		$o->el_properties = property_exists($o,'el_properties')?$o->el_properties:array();
		$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' pop_rangeinput':'pop_rangeinput';	
		return $this->_input_range($tab,$i,$o,$save_fields);				
	}
	
	function _arrowslider($tab,$i,$o,&$save_fields){
		$o->el_properties = property_exists($o,'el_properties')?$o->el_properties:array();
		$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' arrow-slider':'arrow-slider';	
		$o->nolabel=true;
		return $this->_input_range($tab,$i,$o,$save_fields);				
	}
	
	function hidden($tab,$i,$o,&$save_fields){
		$str = sprintf('<input type="hidden" id="%s" name="%s" value="%s" %s />',$this->get_el_id($tab,$i,$o),$this->get_el_name($tab,$i,$o),$o->value, $this->get_el_properties($tab, $i, $o) );		
		if(true===$o->save_option){
			$save_fields[]=$this->get_option_name($tab,$i,$o);	
		}
		return $str;
	}
	
	function _checkbox($tab,$i,$o,&$save_fields){
		$o->option_value=(property_exists($o,'option_value'))?$o->option_value:1;
		if(is_array($o->value)){
			$checked = in_array($o->option_value,$o->value)?'checked="checked"':'';
		}else{	
			$checked = $o->value==$o->option_value?'checked="checked"':'';
		}
		$str = sprintf('<input type="checkbox" id="%s" name="%s" value="%s" %s %s />',$this->get_el_id($tab,$i,$o),$this->get_el_name($tab,$i,$o),$o->option_value, $this->get_el_properties($tab, $i, $o) , $checked);	
		if(true===$o->save_option){
			$save_fields[]=$this->get_option_name($tab,$i,$o);	
		}
		return $str;
	}
	
	function _select($tab,$i,$o,&$save_fields){
		if(property_exists($o,'hidegroup')){
			$hide_values = property_exists($o,'hidevalues')&&is_array($o->hidevalues)?$o->hidevalues:array('1');
			$fn = sprintf(';pop_groupcontrol(this,\'%s\',%s);', $o->hidegroup, str_replace('"',"'",json_encode($hide_values)) );
			$o->el_properties = property_exists($o,'el_properties')&&is_array($o->el_properties)?$o->el_properties:array();
			$o->el_properties['OnChange'] = isset($o->el_properties['OnChange'])?$o->el_properties['OnChange'].' '.$fn:'javascript:'.$fn;			
			$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' pop_groupcontrol':'pop_groupcontrol';	
		}
		//---		
		$str = sprintf('<select id="%s" name="%s"  %s />',$this->get_el_id($tab,$i,$o),$this->get_el_name($tab,$i,$o), $this->get_el_properties($tab, $i, $o) );
		if(!empty($o->options)){
			foreach($o->options as $value => $label){
				$selected = $o->value==$value?'selected="selected"':'';
				$str.=sprintf("<option %s value=\"%s\">%s</option>", $selected, $value, $label);
			}
		}
		$str.="</select>";
		if(true===$o->save_option){
			$save_fields[]=$this->get_option_name($tab,$i,$o);	
		}
		return $str;
	}
	
	function _yesno($tab,$i,$o,&$save_fields){
		$o->options = array(
			'1'=>__('Yes','pop'),
			'0'=>__('No','pop')
		);
		if(property_exists($o,'hidegroup')){
			$hide_values = property_exists($o,'hidevalues')&&is_array($o->hidevalues)?$o->hidevalues:array('1');
			$fn = sprintf(';pop_groupcontrol(this,\'%s\',%s);', $o->hidegroup, str_replace('"',"'",json_encode($hide_values)) );
			$o->el_properties = property_exists($o,'el_properties')&&is_array($o->el_properties)?$o->el_properties:array();
			$o->el_properties['OnClick'] = isset($o->el_properties['OnClick'])?$o->el_properties['OnClick'].' '.$fn:'javascript:'.$fn;			
			$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' pop_groupcontrol':'pop_groupcontrol';	
		}
		return $this->_radio($tab,$i,$o,$save_fields);
	}
	
	function _radio($tab,$i,$o,&$save_fields){
		$str = '';
		if(!empty($o->options)){
			$k=0;
			foreach($o->options as $value => $label){
				$id = $this->get_el_id($tab,$i,$o).'_'.($k++);
				$name = $this->get_el_name($tab,$i,$o);
				$selected = $o->value==$value?'checked':'';
				$str.=sprintf("<input %s id=\"%s\" name=\"%s\" type=\"radio\" %s value=\"%s\" />&nbsp;<label>%s</label>&nbsp;&nbsp;", $this->get_el_properties($tab, $i, $o),$id, $name, $selected, $value, $label);
			}
			if(true===$o->save_option){
				$save_fields[]=$this->get_option_name($tab,$i,$o);	
			}
		}
		return $str;
	}
	//---
	function _div_start($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$o->el_properties = property_exists($o,'el_properties')&&is_array($o->el_properties)?$o->el_properties:array();
		$o->el_properties['class'] = isset($o->el_properties['class'])?$o->el_properties['class'].' pop-option-group':'pop-option-group';
		$str = sprintf('<div id="%s" %s >',$id, $this->get_el_properties($tab, $i, $o) );		
		return $str;
	}
	function _div_end($tab,$i,$o,&$save_fields){
		return "</div>";
	}
	//---
	function _saved_settings_list($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$group = property_exists($o,'anygroup')&&$o->anygroup==true?'':$tab->id;
		$o->fields = property_exists($o,'fields')?$o->fields:array('name'=>__('Name','pop'),'version'=>__('Version','pop'),'date'=>__('Date','pop'));
		$labels='';
		$o->link_restore = property_exists($o,'link_restore')?$o->link_restore:false;
		$o->link_load = property_exists($o,'link_load')?$o->link_load:true;
		$o->class = property_exists($o,'class')?$o->class:'';
		$o->class = $o->link_restore ? $o->class.' with-link-restore':$o->class;
		$o->class = $o->link_load ? $o->class.' with-link-load':$o->class;
		foreach($o->fields as $key => $label){
			$labels.="<th>$label</th>";
		}
		$out = "<table id=\"$id\" class=\"widefat\">";
		$out.= "<thead>$labels</thead>";
		$out.= sprintf('<tbody id="%s" class="popex-list %s" rel="%s"></tbody>',$id,@$o->class,$group);
		$out.="<tfoot>$labels</tfoot>";
		$out.= "</table>";
		if(property_exists($o,'debug')&&$o->debug){
			$out.='<input type="button" class="popex-btn-refresh" value="Refresh" />';
		}
		return $out;
	}	
	//---
	/*
	function _saved_settings_list($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$group = property_exists($o,'anygroup')&&$o->anygroup==true?'':$tab->id;
		return sprintf('<div id="%s" class="popex-list %s" rel="%s"></div>',$id,@$o->class,$group);	
	}	
	*/
	function _save_settings($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$o->button_label = property_exists($o,'button_label')?$o->button_label:__('Save settings backup','pop');
		$group = property_exists($o,'anygroup')&&$o->anygroup==true?'':$tab->id;
		$o->export_fields = property_exists($o,'export_fields')&&is_array($o->export_fields)?implode(',',$o->export_fields):@$o->export_fields;
		//$out = sprintf('<label class="export-label %s" >%s</label>',@$o->label_class,@$o->label);
		$out= sprintf('<input type="text" value="" class="popex-label inp-export-label" id="popex-label-%s">',$id);
		$out.= sprintf('<input type="button" value="%s" id="popex-save-%s" class="button-secondary popex-save %s"  rel="%s" />',$o->button_label,$id,@$o->button_class,@$o->list_id);
		$out.= sprintf('<input id="pop-export-fields-%s" class="popex-fields" type="hidden" value="%s" />',$id,@$o->export_fields);
		$out.= sprintf('<input id="pop-export-group-%s" class="popex-group" type="hidden" value="%s" />',$id,$group);
		$out.= sprintf('<div id="popex-status-%s" class="popex-status btn-saving-status"></div>',$id);
		return $out;
	}
	
	function _clear($tab,$i,$o){
		return sprintf("<div class=\"pt-clear\" %s></div>",$this->get_el_properties($tab,$i,$o));
	}
	
	function _submit($tab,$i,$o){
		return sprintf("<input class=\"%s\" type=\"submit\" name=\"theme_options_submit\" value=\"%s\" />",$o->class, $o->label);
	}
	
	function _button($tab,$i,$o){
		$id = $this->get_el_id($tab,$i,$o);
		return sprintf("<input class=\"%s\" type=\"button\" id=\"%s\" name=\"%s\" value=\"%s\" %s />",$o->class, $id, $id, $o->label, $this->get_el_properties($tab,$i,$o) );
	}	
	
	function _callback($tab,$i,$o,&$save_fields){
		if(is_callable($o->callback)){
			return call_user_func($o->callback,$tab,$i,$o,$save_fields);
		}
		return '';
	}	
	
	function _preview($tab,$i,$o,&$save_fields){
		if(!property_exists($o,'items')||empty($o->items))return '';
		$out = sprintf('<div class="pop-preview"><div class="pop-preview-items">');
		foreach($o->items as $item){
			$out.=sprintf('<div class="pop-preview-item" rel="%s|%s">%s<img src="%s" />%s</div>',
				@$item->focus_target,
				@$items->click_target,
				(property_exists($item,'label')&&$item->label!=''?'<div class="pop-preview-label">'.$item->label.'</div>':''),
				$o->path.$item->src,
				(property_exists($item,'description')&&$item->description!=''?'<div class="pop-preview-description">'.$item->description.'</div>':'')
			);
		}	
		$out.= '</div></div>';
		return $out;
	}	
	
	function _fileuploader($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$o->el_properties = property_exists($o,'el_properties')?$o->el_properties:array();
		$o->el_properties['class']=isset($o->el_properties['class'])?$o->el_properties['class'].' pop-input-fileuploader':'pop-input-fileuploader';
		//$o->preview_selector = property_exists($o,'preview_selector')?$o->preview_selector:sprintf('%s_preview',$id);
		$o->el_properties['rel']=property_exists($o,'preview_selector')?$o->preview_selector:sprintf('#%s_preview',$id);
		$out = $this->_text($tab,$i,$o,$save_fields);
		$out.= sprintf('<div id="%s_fileuploader" class="pop-fileuploader" rel="#%s"></div><span id="%s_msg" class="pop-fileuploader-msg"></span><div id="%s_preview" class="pop-uploader-preview-holder"></div>',$id,$id,$id,$id);
		$out.= sprintf('<input type="hidden" id="%s_dcurl" value="%s" />',$o->id, (property_exists($o,'dcurl')?$o->dcurl:'') );
		$out.= '<div class="pop-float-separator">&nbsp;</div>';
		return $out;
	}
	

}
?>