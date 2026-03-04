<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_checkbox')):

class acfe_field_checkbox extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'checkbox';
        
    }
    
    
    /**
     * validate_front_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return false
     */
    function validate_front_value($valid, $value, $field, $input, $form){
        
        // bail early
        if(!$this->pre_validate_front_value($valid, $value, $field, $form)){
            return $valid;
        }
        
        // custom value allowed
        if(!empty($field['allow_custom'])){
            return $valid;
        }
        
        // vars
        $value = acf_get_array($value);
        $choices = acf_get_array($field['choices']);
        
        // empty choices
        if(empty($choices)){
            return false; // value is always invalid as there no choice is allowed
        }
        
        // check values against choices
        if(!empty(array_diff($value, array_keys($choices)))){
            return false;
        }
        
        // return
        return $valid;
        
    }
    
}

acf_new_instance('acfe_field_checkbox');

endif;