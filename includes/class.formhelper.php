<?php
	class FormHelper {
		
		public $form;
		public $action;
		public $method;
		public $extra;
		public $class;
		public $wrapper;
		
		function __construct($formname='form',$action='',$method='post',$wrapper='{body}<input type="submit" name="submit" value="Submit">',$class='',$extra='')
		{
			$this->form = $formname;
			$this->action = $action;
			$this->method = $method;
			$this->wrapper = $wrapper;
			empty($class) ? $this->class = $this->form : $this->class = $class;
			$this->extra = '';
		}
		
		// You can store result in variable or echo it out.
		function display($outputs,$fieldinfo)
		{
	
			$_SESSION[$this->form] = $fieldinfo;
	
			$return = $outputs;
		
			$format = explode('{body}',$this->wrapper);
			
			$return = $format['0'].'<form name="'.$this->form.'" class="'.$this->class.'" action="'.$this->action.
					  '" method="'.$this->method.'" enctype="multipart/form-data"'.$this->extra.'>'.$return.$format['1'].'</form>';
			
			return $return;
		}

		// CURRENTLY DOESN'T SUPPORT VALIDATION TYPES
		// WITH EXTRA OPTIONS SUCH AS RANGE
		function validate()
		{
			global $Error;
		
			foreach($_SESSION[$this->form] as $iterator => $fields) :
			
				foreach($fields as $name => $value) :
					
					if(!empty($value['validation'])){
						$vtypes = explode(',',$value['validation']);
							foreach($vtypes as $vfunction):
								$Error->$vfunction($value['value'],$name);
							endforeach;
					}
					
				endforeach;
			
			endforeach;
			
		}
	}
?>