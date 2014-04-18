<?php

/*
 * For auto-generating form sections based off a model.
 * 
 */
class EE_Model_Form_Section extends EE_Form_Section_Proper{
	
	/**
	 *
	 * @var EEM_Base
	 */
	protected $_model = NULL;
	
	/**
	 *
	 * @var EE_Base_Class
	 */
	protected $_model_object = NULL;
	/**
	 * 
	 * @param array $options_array keys: {
	 *	@type EEM_Base $model
	 *	@type EE_Base_Class $model_object
	 *	@type array $subsection_args array keys should be subsection names (that either do or will exist), and values are the arrays as you would pass them to that subsection
	 * }
	 * @throws EE_Error
	 */
	public function __construct($options_array = array()){
		if(isset($options_array['model']) && $options_array['model'] instanceof EEM_Base){
			$this->_model = $options_array['model'];
		}
		if( ! $this->_model || ! $this->_model instanceof EEM_Base ){
			throw new EE_Error(sprintf(__("Model Form Sections must first specify the _model property to be a subcalss of EEM_Base", "event_espresso")));
		}
		
		if(isset($options_array['subsection_args'])){
			$subsection_args = $options_array['subsection_args'];
		}else{
			$subsection_args = array();
		}
		
		$model_fields = $this->_model->field_settings();
		//calculate what fields to include
		$this->_subsections = array_merge(
				$this->_convert_model_fields_to_inputs($model_fields),
				$this->_convert_model_relations_to_inputs($this->_model->relation_settings(),$subsection_args),
				$this->_subsections);
		parent::__construct($options_array);
		if(isset($options_array['model_object']) && $options_array['model_object'] instanceof EE_Base_Class){
			$this->populate_model_obj($options_array['model_object']);
		}
		parent::__construct($options_array);
	}
	
	/**
	 * For now, just makes inputs for only HABTM relations
	 * @param EE_Model_Relation_Base[] $relations
	 * @param array $subsection_args keys should be existing or soon-to-be-existing input names, and their values are {
	 *	@type array {
	 *		@type EE_Base_Class[] $model_objects if the subsection is an EE_Select_Multi_Model_Input
	 *	}
	 * }
	 */
	protected function _convert_model_relations_to_inputs($relations,$subsection_args = array()){
		$inputs = array();
		foreach($relations as $relation_name => $relation_obj){
			$input_constructor_args = array(
				array_merge(array(
					'required'=> $relation_obj instanceof EE_Belongs_To_Relation,
					'html_label_text'=>$relation_obj instanceof EE_Belongs_To_Relation ? $relation_obj->get_other_model()->item_name(1) : $relation_obj->get_other_model()->item_name(2),
				),
						$subsection_args));
			$input = NULL;
			switch(get_class($relation_obj)){
				case 'EE_HABTM_Relation':
					if(isset($subsection_args[$relation_name]) &&
							isset($subsection_args[$relation_name]['model_objects'])){
						$model_objects = $subsection_args[$relation_name]['model_objects'];
					}else{
						$model_objects = $relation_obj->get_other_model()->get_all();
					}
					$input = new EE_Select_Multi_Model_Input($model_objects,$input_constructor_args);
					break;
				default:
			}
			if($input){
				$inputs[$relation_name] = $input;
			}
		}
		return $inputs;
	}
	/**
	 * Changes model fields into form section inputs
	 * @param EE_Model_Field_Base $model_fields keys are the model's name
	 * @return EE_Form_Input_Base
	 */
	protected function _convert_model_fields_to_inputs($model_fields){
		$inputs = array();
		foreach($model_fields as $field_name=>$model_field){
			$input_constructor_args = array(array(
				'required'=> ! $model_field->is_nullable() && $model_field->get_default_value() === NULL,
				'html_label_text'=>$model_field->get_nicename(),
				'default'=>$model_field->get_default_value(),
			));
			switch(get_class($model_field)){
				case 'EE_All_Caps_Text_Field':
				case 'EE_Any_Foreign_Model_Name_Field':
					$input_class = 'EE_Text_Input';
					break;
				case 'EE_Boolean_Field':
					$input_class = 'EE_Yes_No_Input';
					break;
				case 'EE_Datetime_Field':
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				case 'EE_Email_Field':
					$input_class = 'EE_Email_Input';
					break;
				case 'EE_Enum_Integer_Field':
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				case 'EE_Enum_Text_Field':
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				case 'EE_Float_Field':
					$input_class = 'EE_Float_Input';
					break;
				case 'EE_Foreign_Key_Int_Field':
				case 'EE_Foreign_Key_String_Field':
					$models_pointed_to = $model_field->get_model_class_names_pointed_to();
					if(true || is_array($models_pointed_to) && count($models_pointed_to) > 1){
						$input_class = 'EE_Text_Input';
					}else{
						if($model_field->is_nullable()){
							$model_names = array(0=>  __("Please Select", 'event_espresso'));
						}
						//so its just one model
						$model_name = is_array($models_pointed_to) ? reset($models_pointed_to) : $models_pointed_to;
						$model = EE_Registry::instance()->load_model($model_name);
						$model_names = $model->get_all_names(array('limit'=>10));
						$input_constructor_args[1] = $input_constructor_args[0];
						$input_constructor_args[0] = $model_names;
						$input_class = 'EE_Select_Input';
					}					
					break;
				case 'EE_Full_HTML_Field':
					$input_class = 'EE_Text_Area_Input';
					break;
				case 'EE_Infinite_Integer':
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				case 'EE_Integer_Field':
					$input_class = 'EE_Text_Input';
					break;
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				case 'EE_Maybe_Serialized_Text_Field':
					$input_class = 'EE_Text_Area_Input';
					break;
				case 'EE_Money_Field':
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				case 'EE_Plain_Text_Field':
					$input_class = 'EE_Text_Input';
					break;
				case 'EE_Primary_Key_Int_Field':
					$input_class = 'EE_Hidden_Input';
					$input_constructor_args['normalization_strategy'] = new EE_Int_Normalization();
					break;
				case 'EE_Primary_Key_String_Field':
					$input_class = 'EE_Hidden_Input';
					break;
				case 'EE_Serialized_Text_Field':
					$input_class = 'EE_Text_Area_Input';
					break;
				case 'EE_Simple_HTML_Field':
					$input_class = 'EE_Text_Area_Input';
					break;
				case 'EE_Slug_Field':
					$input_class = 'EE_Text_Input';
					break;
				case 'EE_Trashed_Flag_Field':
					$input_class = 'EE_Yes_No_Input';
					break;
				case 'EE_WP_Post_Status_Field':
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				case 'EE_WP_Post_Type_Field':
					throw new EE_Error(sprintf(__("Model field '%s' does not yet have a known conversion to form input", "event_espresso"),get_class($model_field)));
					break;
				default:
					throw new EE_Error(sprintf(__("Model field of type '%s' does not convert to any known Form Input. Please add a case to EE_Model_Form_section's _convert_model_fields_to_inputs switch statement", "event_espresso"),get_class($model_field)));
			}
			$reflection = new ReflectionClass($input_class); 
			$input = $reflection->newInstanceArgs($input_constructor_args); 
			$inputs[$field_name] = $input;
		}
		return $inputs;
	}
	/**
	 * Mostly the same as populate_defaults , except takes a model object as input, not an array,
	 * and also sets the form's _model_object
	 * @param EE_Base_Class $model_obj
	 * @return void
	 */
	public function populate_model_obj($model_obj){
		$model_obj = $this->_model->ensure_is_obj($model_obj);
		$this->_model_object = $model_obj;
		$defaults = $model_obj->model_field_array();
		foreach($this->_model->relation_settings() as $relation_name => $relation_obj){
			$form_inputs = $this->inputs();
			if(isset($form_inputs[$relation_name])){
				if($relation_obj instanceof EE_Belongs_To_Relation){
					//then we only expect there to be one
					$related_item = $this->_model_object->get_first_related($relation_name);
					$defaults[$relation_name] = $related_item->ID();
				}else{
					$related_items = $this->_model_object->get_many_related($relation_name);
					$ids = array();
					foreach($related_items as $related_item){
						$ids[] = $related_item->ID();
					}
					$defaults[$relation_name] = $ids;
				}
			}
		}
		$this->populate_defaults($defaults);
	}
	/**
	 * Gets all the input values that correspond to model fields. Keys are the input/field names,
	 * values are their normalized values
	 * @return array
	 */
	public function inputs_values_corresponding_to_model_fields(){
		return array_intersect_key($this->input_values(),$this->_model->field_settings());
	}
	public function receive_form_submission($req_data = NULL) {
		parent::receive_form_submission($req_data);
		//create or set the model object, if it isn't already
		if( ! $this->_model_object ){
			//check to see if the form indicates a PK, in which case we want to only retrieve it and update it
			$pk_name = $this->_model->primary_key_name();
			$model_obj = $this->_model->get_one_by_ID($this->get_input_value($pk_name));
			if($model_obj){
				$this->_model_object = $model_obj;
			}else{
				$this->_model_object = EE_Registry::instance()->load_class($this->_model->get_this_model_name() );
			}
		}
		//ok so the model object is set. Just set it with the submitted form data (don't save yet though)
		foreach($this->inputs_values_corresponding_to_model_fields() as $field_name=>$field_value){
			//only set the non-primary key
			if($field_name != $this->_model->primary_key_name()){
				$this->_model_object->set($field_name,$field_value);
			}
		}
		
	}
	/**
	 * After this form has been initialized and is verified to be valid,
	 * either creates a model object from its data and saves it, or updates
	 * the model object its data represents
	 * @return int, 1 on a successful update, the ID of
	 *					the new entry on insert; 0 on failure	
	 */
	public function save(){
		if( ! $this->_model_object){
			throw new EE_Error(sprintf(__("Cannot save the model form's model object (model is '%s') because there is no model object set. You must either set it, or call receive_form_submission where it is set automatically", "event_espresso"),get_class($this->_model)));
		}
		$success =  $this->_model_object->save();
		foreach($this->_model->relation_settings() as $relation_name => $relation_obj){
			if(isset($this->_subsections[$relation_name])){
				$success = $this->_save_related_info($relation_name);
			}
		}
		return $success;
	}
	
	/**
	 * 
	 * @param type $relation_name
	 */
	protected function _save_related_info($relation_name){
		$relation_obj = $this->_model->related_settings_for($relation_name);
		if($relation_obj instanceof EE_Belongs_To_Relation){
			//there is just a foreign key on this model pointing to that one
			$this->_model_object->_add_relation_to($this->get_input_value($relation_name), $relation_name);
		}elseif($relation_obj instanceof EE_Has_Many_Relation){
			//then we want to consider all of its currenlty-related things.
			//if they're in this list, keep them
			//if they're not in this list, remove them
			//and lastly add all the new items
			throw new EE_Error(sprintf(__("Automatic saving of related info across a hasmany relation is not yet supported", "event_espresso")));
		}elseif($relation_obj instanceof EE_HABTM_Relation){
			//delete everything NOT in this list
			$where_query_params = array(
				$relation_obj->get_other_model()->primary_key_name() => array('NOT_IN',$this->get_input_value($relation_name)));
			$relation_obj->remove_relations($this->_model_object, $where_query_params);
			foreach($this->get_input_value($relation_name) as $id){
				$relation_obj->add_relation_to($this->_model_object, $id);
			}
		}
	}
	/**
	 * Gets the model of this model form
	 * @return EEM_Base
	 */
	public function get_model(){
		return $this->_model;
	}
	/**
	 * Gets the model object for this model form, which was either set
	 * upon construction (using the $options_array arg 'model_object'), by using
	 * set_model_object($model_obj), or implicitly
	 * when receive_form_submission($req_data) was called.
	 * @return EE_Base_Class
	 */
	public function get_model_object(){
		return $this->_model_object;
	}
	
	/**
	 * gets teh default name of this form section if none is specified
	 * @return string
	 */
	protected function _set_default_name_if_empty(){
		if( ! $this->_name ){
			$default_name = str_replace("EEM_", "", get_class($this->_model)) . "_Model_Form";
			$this->_name =  $default_name;
		}
	}
	
}