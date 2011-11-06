<?php
	class InputHelper {
		
		public $fields = array();
		
		public $name;
		public $wrapper;
		public $format;
		public $iterations;
		public $output;
		public $validation;
		
		
		// Build all your info first and then send it to constructor for display
		function __construct($name='form',$iterations='1',$wrapper='<div id="{group}">{body}</div>',$format='<p class="clearfix"><label for="{id}">{name}:</label>{field}</p>')
		{
			$this->name = $name;
			$this->wrapper = str_replace("{group}",$this->name,$wrapper);
			$this->format = $format;
			$this->iterations = $iterations;
		
			if(isset($_SESSION['forms'][$this->name]) && submit()){
				if(!empty($_SESSION['forms'][$this->name])){
					$this->fields = safe_unserialize($_SESSION['forms'][$this->name]['fields']);
					$this->iterations = $_SESSION['forms'][$this->name]['iterations'];
				}
			}
		}
		
		// You can echo out the result or store it in a variable,
		// if using form helper, you should store it sucka
		function display()
		{
			$format = explode('{body}',$this->wrapper);
			
			// For < to work
			$temp_int = $this->iterations+1;
	
			$output = '';
			$oldfields = $this->fields;
		
				// Loop through iterations and build new field names
				for($i=1;$i<$temp_int;$i++){
					
					$field_output = '';
					
					foreach($oldfields as $name => $value) :
					
						$a = ($temp_int > 2) ? '_'.$i : '';
					
						$this->fields[$name.$a] = $this->fields[$name];
						$this->fields[$name.$a]['id'] = $this->fields[$name.$a]['id'].$a;
						$this->fields[$name.$a]['value'] = isset($_POST[$name.$a]) ? $_POST[$name.$a] : $this->fields[$name]['value'];
						
									$function_name = $this->fields[$name]['type'];
	
									$field_output .= $this->format;
	
									$field_output = str_replace("%id%",$this->fields[$name.$a]['id'],$field_output);
									$field_output = str_replace("%name%",$this->fields[$name.$a]['display'],$field_output);
									$field_output = str_replace("%field%",$this->$function_name($name.$a),$field_output);
						
					endforeach;
					unset($name,$value);
					
					$output .= $format['0'].$field_output.$format['1'];
					unset($field_output);
					
				}
				
				$this->output = $output;
				
				$_SESSION['forms'][$this->name]['fields'] = safe_serialize($oldfields);
				$_SESSION['forms'][$this->name]['iterations'] = $this->iterations;
				
			return $output;
		
		}
		
		// EXAMPLES
		// $this->addfield('name','name','Your Name:','Robert','text','blank','Array ex: array($name => $value)');
		function addfield($type,$name,$validation='',$display='',$id='',$value='',$options=''){
					
			$this->fields[$name]['display'] = (!empty($display)) ? $display : ucwords(str_replace('_',' ',$name));
			$this->fields[$name]['id'] = !empty($id) ? str_replace(' ','_',$id) : str_replace(' ','_',$name);
			$this->fields[$name]['value'] = !empty($value) ? $value : '';
			isset($_POST[$name]) ? $this->fields[$name]['value'] = $_POST[$name] : '';
			
			
			$this->fields[$name]['type'] = $type;
			$this->fields[$name]['name'] = str_replace(' ','_',$name);
			$this->fields[$name]['validation'] = $validation;
			$this->fields[$name]['options'] = $options;
			
		}
	
	
		// CURRENTLY DOESN'T SUPPORT VALIDATION TYPES
		// WITH EXTRA OPTIONS SUCH AS RANGE
		function validate()
		{
			global $Error;
			
			
				// For < to work
				$temp_int = $this->iterations+1;
				$oldfields = $this->fields;
	
					// Loop through iterations and build new field names
					for($i=1;$i<$temp_int;$i++){
	
						foreach($this->fields as $name => $value) :
							
							if(!empty($value['validation'])){
								$vtypes = explode(',',$value['validation']);
									foreach($vtypes as $vfunction):
									
										$a = ($temp_int > 2) ? '_'.$i : '';
									
											$this->fields[$name.$a]['value'] = isset($_FILES[$name.$a]) ? $_FILES[$name.$a]['name'] : '';
											
											if(empty($this->fields[$name.$a]['value']))
												$this->fields[$name.$a]['value'] = isset($_POST[$name.$a]) ? $_POST[$name.$a] : '';
											
											$Error->$vfunction($this->fields[$name.$a]['value'],$name.$a,$this->fields[$name]['display']);
											
									endforeach;
					
							}
	
						endforeach;
						unset($name,$value);
	
					}
					
				$this->fields = $oldfields;
		}
		
		
	// ALL THE INPUT TYPES ARE LISTED BELOW...
		
		
		// OTHER FUNCTIONS SUCH AS TEXT,HIDDEN, AND CHECKBOX ARE BASED OFF OF THIS
		function basicinput($name,$type)
		{
			isset($this->fields[$name]['options']['size']) ? $size = ' size="'.$this->fields[$name]['options']['size'].'"' : $size = '';
			isset($this->fields[$name]['options']['image']) ? $image = ' src="'.$this->fields[$name]['options']['image'].'"' : $image = '';
			isset($this->fields[$name]['options']['class']) ? $class = ' class="'.$this->fields[$name]['options']['class'].'"' : $class = '';
			isset($this->fields[$name]['options']['title']) ? $title = ' title="'.$this->fields[$name]['options']['title'].'"' : $title = '';
			
			
			$out = '<input type="'.$type.'" value="'.$this->fields[$name]['value'].'"'.$size.$image.$class.$title.' name="'.$name.'"'.' id="'.$this->fields[$name]['id'].'" />';
			
			if($type === 'file' && !empty($this->fields[$name]['value']))
				$out .= '<span class="current">Current File: '.$this->fields[$name]['value'].'</span>';
	
			if(isset($this->fields[$name]['options']['extra']))
				$out .= $this->fields[$name]['options']['extra'];
	
			return $out;
		}
	
		function text($name){
			return $this->basicinput($name,'text');
		}
	
		// Requires jquery slug plugin
		function slug($name){
			$this->fields[$name]['options']['class'] = 'slug';
			return $this->basicinput($name,'text');
		}
	
		function hidden($name){
			return $this->basicinput($name,'hidden');
		}
	
		function checkbox($name){
			return $this->basicinput($name,'checkbox');
		}
	
		function password($name){
			return $this->basicinput($name,'password');
		}
	
		function file($name){
			return $this->basicinput($name,'file');
		}
	
		function submit($name){
			return $this->basicinput($name,'submit');
		}
	
		function image($name){
			return $this->basicinput($name,'image');
		}
	
		function textarea($name){
			
			if(!isset($this->fields[$name]['options']['cols'])){ $this->fields[$name]['options']['cols'] = 50; }
			if(!isset($this->fields[$name]['options']['rows'])){ $this->fields[$name]['options']['rows'] = 5; }
	
			$out = '<textarea name="'.$name.'" id="'.$this->fields[$name]['id'].'" cols="'.$this->fields[$name]['options']['cols'].'" rows="'.$this->fields[$name]['options']['rows'].'">';
			$out .= $this->fields[$name]['value'];
			$out .= '</textarea>';
			
			return $out;
		}
		
		// TIMESTAMP
		function timestamp($name,$type='timestamp')
		{
			isset($this->fields[$name]['options']['size']) ? $size = ' size="'.$this->fields[$name]['options']['size'].'"' : $size = '';
			isset($this->fields[$name]['options']['image']) ? $image = ' src="'.$this->fields[$name]['options']['image'].'"' : $image = '';
			isset($this->fields[$name]['options']['class']) ? $class = ' class="'.$this->fields[$name]['options']['class'].'"' : $class = '';
			empty($this->fields[$name]['value']) ? $this->fields[$name]['value'] = date('Y-m-d H:i:s') : $this->fields[$name]['value'] = $this->fields[$name]['value'];
			
			$out = '<input type="'.$type.'" value="'.$this->fields[$name]['value'].'"'.$size.$image.$class.' name="'.$name.'"'.' id="'.$this->fields[$name]['id'].'" />';
			
			if($type === 'file' && !empty($this->fields[$name]['value']))
				$out .= '<span class="current">Current File: '.$this->fields[$name]['value'].'</span>';
	
			if(isset($this->fields[$name]['options']['extra']))
				$out .= $this->fields[$name]['options']['extra'];
	
			return $out;
		}
	
		function dropdown($name){
			
			$out = '<select name="'.$name.'" id="'.$this->fields[$name]['id'].'">';
			
				// Pass selectName as option to change default view
				if(isset($this->fields[$name]['options']['selectName']))
					$out .= '<option value="">'.$this->fields[$name]['options']['selectName'].'</option>';
				
				if(!empty($this->fields[$name]['options'])){
					foreach($this->fields[$name]['options'] as $key => $value) :
						if($key !== 'selectName'){
							if($this->fields[$name]['value'] === $key){ $selected = ' selected'; }
							else{ $selected = ''; }
								$out .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
						}
					endforeach;
				}
		
			$out .= '</select>';
			
			return $out;
		}
	
		// Build a select menu using the get_options function in functions.inc
		function related($name){
	
			$out = '<select name="'.$name.'" id="'.$this->fields[$name]['id'].'">';
	
			// Pass selectName as option to change default view
			if(isset($this->fields[$name]['options']['selectName']))
				$out .= '<option value="">'.$this->fields[$name]['options']['selectName'].'</option>';
	
				if(!isset($this->fields[$name]['sql']))
					$this->fields[$name]['sql'] = '';
	
				$out .=  get_options($this->fields[$name]['options']['table'],$this->fields[$name]['options']['val'],$this->fields[$name]['options']['text'],$this->fields[$name]['value']);
	
			$out .= '</select>';
	
			return $out;
		}
		
	///////////////// FUNCTIONS THAT USE OTHER FORM TYPES ////////////////////////////////
	
	
		function tables($name){
			global $db;
			
			$arrTables = array();
			$result = $db->query('SHOW TABLES');
			while($row = mysql_fetch_array($result)) $this->fields[$name]['options'][$row['0']] = $row['0'];
			$out = $this->dropdown($name);
			
			return $out;
		}
	
	
		function states($name){
			$this->fields[$name]['options'] = array('AL'=>"Alabama",
			                'AK'=>"Alaska",
			                'AZ'=>"Arizona",
			                'AR'=>"Arkansas",
			                'CA'=>"California",
			                'CO'=>"Colorado",
			                'CT'=>"Connecticut",
			                'DE'=>"Delaware",
			                'DC'=>"District Of Columbia",
			                'FL'=>"Florida",
			                'GA'=>"Georgia",
			                'HI'=>"Hawaii",
			                'ID'=>"Idaho",
			                'IL'=>"Illinois",
			                'IN'=>"Indiana",
			                'IA'=>"Iowa",
			                'KS'=>"Kansas",
			                'KY'=>"Kentucky",
			                'LA'=>"Louisiana",
			                'ME'=>"Maine",
			                'MD'=>"Maryland",
			                'MA'=>"Massachusetts",
			                'MI'=>"Michigan",
			                'MN'=>"Minnesota",
			                'MS'=>"Mississippi",
			                'MO'=>"Missouri",
			                'MT'=>"Montana",
			                'NE'=>"Nebraska",
			                'NV'=>"Nevada",
			                'NH'=>"New Hampshire",
			                'NJ'=>"New Jersey",
			                'NM'=>"New Mexico",
			                'NY'=>"New York",
			                'NC'=>"North Carolina",
			                'ND'=>"North Dakota",
			                'OH'=>"Ohio",
			                'OK'=>"Oklahoma",
			                'OR'=>"Oregon",
			                'PA'=>"Pennsylvania",
			                'RI'=>"Rhode Island",
			                'SC'=>"South Carolina",
			                'SD'=>"South Dakota",
			                'TN'=>"Tennessee",
			                'TX'=>"Texas",
			                'UT'=>"Utah",
			                'VT'=>"Vermont",
			                'VA'=>"Virginia",
			                'WA'=>"Washington",
			                'WV'=>"West Virginia",
			                'WI'=>"Wisconsin",
			                'WY'=>"Wyoming");
				
			$out = $this->dropdown($name);
	
			return $out;
		}
	
		// requires javascript files
		function dater($name){
		 
			$out = $this->text($name);
		
			$out .=
		    '<script type="text/javascript">
		    /*<[CDATA[*/
		     var dpck	= new DatePicker({
		      relative	: \''.$name.'\',
		      language	: \'en\',
			  keepFieldEmpty : \'true\',
			  disablePastDate : \'true\',
			  disableFutureDate : \'false\',
			enableShowEffect : true,
			enableCloseOnBlur : true,
			closeEffect	: \'blindUp\'
			});
			  dpck.setDateFormat([ "yyyy","mm","dd" ], "-");
		    /*]]>*/
		    </script>';
	
		return $out;
		
		}
		
	
		function hour($name)
		{
			if(empty($this->fields[$name]['value']))
				$this->fields[$name]['value'] = date('h');
			
			$hours = array(12, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11);
	
			foreach($hours as $hour) :
				$this->fields[$name]['options'][$hour] = $hour;
			endforeach;
		
			$output = $this->dropdown($name);
		
			return $output;
			
		}
		
	
		function minute($name)
		{
			if(empty($this->fields[$name]['value']))
				$this->fields[$name]['value'] = date('i');
			
			$minutes = array('00', 15, 30, 45);
	
			foreach($minutes as $minute) :
				$this->fields[$name]['options'][$minute] = $minute;
			endforeach;
		
			$output = $this->dropdown($name);
		
			return $output;
		}
		
	
		function ampm($name)
		{
			if(empty($this->fields[$name]['value']))
				$this->fields[$name]['value'] = date('a');
			
			$ampms = array('am','pm');
	
			foreach($ampms as $ampm) :
				$this->fields[$name]['options'][$ampm] = $ampm;
			endforeach;
		
			$output = $this->dropdown($name);
		
			return $output;
		}
		
		
		// TO USE THIS YOU WOULD NEED TO MAKE SURE THE
		// TINYMCE JAVASCRIPT LIBRARY IS SET UP
		function htmleditor($name){
			
				$out = '<script language="javascript" type="text/javascript">
					tinyMCE.init({
						mode : "exact",
						elements : "'.$name.'",
						theme : "advanced",
						plugins : "style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,flash,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable",
				theme_advanced_disable : "charmap,styleselect",
						theme_advanced_buttons4 : "",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_path_location : "bottom",
						content_css : "example_full.css",
					    plugin_insertdate_dateFormat : "%Y-%m-%d",
					    plugin_insertdate_timeFormat : "%H:%M:%S",
					valid_elements : ""
				+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev"
				  +"|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
				+"abbr[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"acronym[class|dir<ltr?rtl|id|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"address[class|align|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"applet[align<bottom?left?middle?right?top|alt|archive|class|code|codebase"
				  +"|height|hspace|id|name|object|style|title|vspace|width],"
				+"area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup"
				  +"|shape<circle?default?poly?rect|style|tabindex|title|target],"
				+"base[href|target],"
				+"basefont[color|face|id|size],"
				+"bdo[class|dir<ltr?rtl|id|lang|style|title],"
				+"big[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"blockquote[dir|style|cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick"
				  +"|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|style|title],"
				+"body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|onunload|style|title|text|vlink],"
				+"br[class|clear<all?left?none?right|id|style|title],"
				+"button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|onblur"
				  +"|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|tabindex|title|type"
				  +"|value],"
				+"caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"center[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"cite[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"code[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title"
				  +"|valign<baseline?bottom?middle?top|width],"
				+"colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl"
				  +"|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title"
				  +"|valign<baseline?bottom?middle?top|width],"
				+"dd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"del[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"dfn[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"dir[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"dl[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"dt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"em/i[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"fieldset[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"font[class|color|dir<ltr?rtl|face|id|lang|size|style|title],"
				+"form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang"
				  +"|method<get?post|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit"
				  +"|style|title|target],"
				+"frame[class|frameborder|id|longdesc|marginheight|marginwidth|name"
				  +"|noresize<noresize|scrolling<auto?no?yes|src|style|title],"
				+"frameset[class|cols|id|onload|onunload|rows|style|title],"
				+"h1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h5[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h6[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"head[dir<ltr?rtl|lang|profile],"
				+"hr[align<center?left?right|class|dir<ltr?rtl|id|lang|noshade<noshade|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|size|style|title|width],"
				+"html[dir<ltr?rtl|lang|version],"
				+"iframe[align<bottom?left?middle?right?top|class|frameborder|height|id"
				  +"|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style"
				  +"|title|width],"
				+"img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height"
				  +"|hspace|id|ismap<ismap|lang|longdesc|name|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|src|style|title|usemap|vspace|width],"
				+"input[accept|accesskey|align<bottom?left?middle?right?top|alt"
				  +"|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang"
				  +"|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect"
				  +"|readonly<readonly|size|src|style|tabindex|title"
				  +"|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text"
				  +"|usemap|value],"
				+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"isindex[class|dir<ltr?rtl|id|lang|prompt|style|title],"
				+"kbd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"label[accesskey|class|dir<ltr?rtl|for|id|lang|onblur|onclick|ondblclick"
				  +"|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|style|title],"
				+"legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang"
				  +"|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"li[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type"
				  +"|value],"
				+"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type],"
				+"map[class|dir<ltr?rtl|id|lang|name|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"menu[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme],"
				+"noframes[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"noscript[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"object[align<bottom?left?middle?right?top|archive|border|class|classid"
				  +"|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name"
				  +"|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|standby|style|tabindex|title|type|usemap"
				  +"|vspace|width],"
				+"ol[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|start|style|title|type],"
				+"optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick"
				  +"|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|selected<selected|style|title|value],"
				+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"param[id|name|type|value|valuetype<DATA?OBJECT?REF],"
				+"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|onclick|ondblclick"
				  +"|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|style|title|width],"
				+"q[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"s[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"samp[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"script[charset|defer|language|src|type],"
				+"select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style"
				  +"|tabindex|title],"
				+"small[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"strike[class|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"strong/b[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"style[dir<ltr?rtl|lang|media|title|type],"
				+"sub[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"sup[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"table[align<center?left?right|bgcolor|border|cellpadding|cellspacing|class"
				  +"|dir<ltr?rtl|frame|height|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rules"
				  +"|style|summary|title|width],"
				+"tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
				  +"|valign<baseline?bottom?middle?top],"
				+"td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class"
				  +"|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup"
				  +"|style|title|valign<baseline?bottom?middle?top|width],"
				+"textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect"
				  +"|readonly<readonly|rows|style|tabindex|title],"
				+"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
				  +"|valign<baseline?bottom?middle?top],"
				+"th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class"
				  +"|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup"
				  +"|style|title|valign<baseline?bottom?middle?top|width],"
				+"thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
				  +"|valign<baseline?bottom?middle?top],"
				+"title[dir<ltr?rtl|lang],"
				+"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class"
				  +"|rowspan|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title|valign<baseline?bottom?middle?top],"
				+"tt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"u[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"ul[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title|type],"
				+"var[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title]",
						external_link_list_url : "example_link_list.js",
						external_image_list_url : "example_image_list.js",
						flash_external_list_url : "example_flash_list.js",
						file_browser_callback : "fileBrowserCallBack",
						theme_advanced_resize_horizontal : false,
						theme_advanced_resizing : true
					});
	
					function fileBrowserCallBack(field_name, url, type, win) {
						// This is where you insert your custom filebrowser logic
						alert("Example of filebrowser callback: field_name: " + field_name + ", url: " + url + ", type: " + type);
	
						// Insert new URL, this would normaly be done in a popup
						win.document.forms[0].elements[field_name].value = "someurl.htm";
					}
				</script>';
				
			$this->fields[$name]['options']['cols'] = '80';
			$this->fields[$name]['options']['rows'] = '15';
			$out .= $this->textarea($name);
		
			return $out;
		}
		
		
		// TO USE THIS YOU WOULD NEED TO MAKE SURE THE
		// TINYMCE JAVASCRIPT LIBRARY IS SET UP
		function htmleditorsimple($name){
			
				$out = '<script language="javascript" type="text/javascript">
					tinyMCE.init({
						mode : "exact",
						elements : "'.$name.'",
						theme : "simple",
						plugins : "style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,flash,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable",
				theme_advanced_disable : "charmap,styleselect",
						theme_advanced_buttons4 : "",
						theme_advanced_toolbar_location : "top",
						theme_advanced_toolbar_align : "left",
						theme_advanced_path_location : "bottom",
						content_css : "example_full.css",
					    plugin_insertdate_dateFormat : "%Y-%m-%d",
					    plugin_insertdate_timeFormat : "%H:%M:%S",
					valid_elements : ""
				+"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev"
				  +"|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
				+"abbr[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"acronym[class|dir<ltr?rtl|id|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"address[class|align|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"applet[align<bottom?left?middle?right?top|alt|archive|class|code|codebase"
				  +"|height|hspace|id|name|object|style|title|vspace|width],"
				+"area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup"
				  +"|shape<circle?default?poly?rect|style|tabindex|title|target],"
				+"base[href|target],"
				+"basefont[color|face|id|size],"
				+"bdo[class|dir<ltr?rtl|id|lang|style|title],"
				+"big[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"blockquote[dir|style|cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick"
				  +"|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|style|title],"
				+"body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onload|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|onunload|style|title|text|vlink],"
				+"br[class|clear<all?left?none?right|id|style|title],"
				+"button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|onblur"
				  +"|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|tabindex|title|type"
				  +"|value],"
				+"caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"center[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"cite[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"code[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title"
				  +"|valign<baseline?bottom?middle?top|width],"
				+"colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl"
				  +"|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|span|style|title"
				  +"|valign<baseline?bottom?middle?top|width],"
				+"dd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"del[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"dfn[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"dir[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"dl[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"dt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"em/i[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"fieldset[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"font[class|color|dir<ltr?rtl|face|id|lang|size|style|title],"
				+"form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang"
				  +"|method<get?post|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit"
				  +"|style|title|target],"
				+"frame[class|frameborder|id|longdesc|marginheight|marginwidth|name"
				  +"|noresize<noresize|scrolling<auto?no?yes|src|style|title],"
				+"frameset[class|cols|id|onload|onunload|rows|style|title],"
				+"h1[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h2[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h3[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h4[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h5[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"h6[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"head[dir<ltr?rtl|lang|profile],"
				+"hr[align<center?left?right|class|dir<ltr?rtl|id|lang|noshade<noshade|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|size|style|title|width],"
				+"html[dir<ltr?rtl|lang|version],"
				+"iframe[align<bottom?left?middle?right?top|class|frameborder|height|id"
				  +"|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style"
				  +"|title|width],"
				+"img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height"
				  +"|hspace|id|ismap<ismap|lang|longdesc|name|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|src|style|title|usemap|vspace|width],"
				+"input[accept|accesskey|align<bottom?left?middle?right?top|alt"
				  +"|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang"
				  +"|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect"
				  +"|readonly<readonly|size|src|style|tabindex|title"
				  +"|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text"
				  +"|usemap|value],"
				+"ins[cite|class|datetime|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"isindex[class|dir<ltr?rtl|id|lang|prompt|style|title],"
				+"kbd[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"label[accesskey|class|dir<ltr?rtl|for|id|lang|onblur|onclick|ondblclick"
				  +"|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|style|title],"
				+"legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang"
				  +"|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"li[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title|type"
				  +"|value],"
				+"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type],"
				+"map[class|dir<ltr?rtl|id|lang|name|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"menu[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"meta[content|dir<ltr?rtl|http-equiv|lang|name|scheme],"
				+"noframes[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"noscript[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"object[align<bottom?left?middle?right?top|archive|border|class|classid"
				  +"|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name"
				  +"|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|standby|style|tabindex|title|type|usemap"
				  +"|vspace|width],"
				+"ol[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|start|style|title|type],"
				+"optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick"
				  +"|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|selected<selected|style|title|value],"
				+"p[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|style|title],"
				+"param[id|name|type|value|valuetype<DATA?OBJECT?REF],"
				+"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|onclick|ondblclick"
				  +"|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout"
				  +"|onmouseover|onmouseup|style|title|width],"
				+"q[cite|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"s[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"samp[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"script[charset|defer|language|src|type],"
				+"select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style"
				  +"|tabindex|title],"
				+"small[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"span[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"strike[class|class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title],"
				+"strong/b[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"style[dir<ltr?rtl|lang|media|title|type],"
				+"sub[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"sup[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title],"
				+"table[align<center?left?right|bgcolor|border|cellpadding|cellspacing|class"
				  +"|dir<ltr?rtl|frame|height|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rules"
				  +"|style|summary|title|width],"
				+"tbody[align<center?char?justify?left?right|char|class|charoff|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
				  +"|valign<baseline?bottom?middle?top],"
				+"td[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class"
				  +"|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup"
				  +"|style|title|valign<baseline?bottom?middle?top|width],"
				+"textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name"
				  +"|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect"
				  +"|readonly<readonly|rows|style|tabindex|title],"
				+"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
				  +"|valign<baseline?bottom?middle?top],"
				+"th[abbr|align<center?char?justify?left?right|axis|bgcolor|char|charoff|class"
				  +"|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|onclick"
				  +"|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove"
				  +"|onmouseout|onmouseover|onmouseup|rowspan|scope<col?colgroup?row?rowgroup"
				  +"|style|title|valign<baseline?bottom?middle?top|width],"
				+"thead[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
				  +"|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown"
				  +"|onmousemove|onmouseout|onmouseover|onmouseup|style|title"
				  +"|valign<baseline?bottom?middle?top],"
				+"title[dir<ltr?rtl|lang],"
				+"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class"
				  +"|rowspan|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title|valign<baseline?bottom?middle?top],"
				+"tt[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"u[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress|onkeyup"
				  +"|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style|title],"
				+"ul[class|compact<compact|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown"
				  +"|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover"
				  +"|onmouseup|style|title|type],"
				+"var[class|dir<ltr?rtl|id|lang|onclick|ondblclick|onkeydown|onkeypress"
				  +"|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|style"
				  +"|title]",
						external_link_list_url : "example_link_list.js",
						external_image_list_url : "example_image_list.js",
						flash_external_list_url : "example_flash_list.js",
						file_browser_callback : "fileBrowserCallBack",
						theme_advanced_resize_horizontal : false,
						theme_advanced_resizing : true
					});
	
					function fileBrowserCallBack(field_name, url, type, win) {
						// This is where you insert your custom filebrowser logic
						alert("Example of filebrowser callback: field_name: " + field_name + ", url: " + url + ", type: " + type);
	
						// Insert new URL, this would normaly be done in a popup
						win.document.forms[0].elements[field_name].value = "someurl.htm";
					}
				</script>';
				
			
			$out .= $this->textarea($name);
		
			return $out;
		}
	
	
	}
?>