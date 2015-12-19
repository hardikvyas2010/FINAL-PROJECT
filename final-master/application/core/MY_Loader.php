<?php

class MY_Loader extends CI_Loader {
   
  
  function customView($folder, $view, $vars = array(), $return = FALSE) {

    $this->_ci_view_paths = array_merge(array($folder . '/' => TRUE), $this->_ci_view_paths);
    return $this->_ci_load(array(
                '_ci_view' => $view,
                '_ci_vars' => $this->_ci_object_to_array($vars),
                '_ci_return' => $return
            ));
  }
  
}
