<?php

/**
 * AngularJS <-> CakePHP adapter
 * 
 * This Helper will do basically a few things:
 * 
 * - Load AngularJS (from Google CDN by default) and all required libs (resource, controller, bootstrap, etc)
 * - Generate a DIV tag which AngularJS scope is set to the controller you specify 
 * in $options['controller']. The tag / scope is closed by using the "end" method 
 * (so beware not to leave it open).
 * - Inject CakePHP formatted data ($options['data']) into AngularJS controller's scope. 
 * The data is stored into $scope._data so you can easily make:
 * 
 * <li ng-repeat="model in _data">{{model.id}}</li>
 * 
 * ... and use all the magic from AngularJs over that data.
 *
 * @author Nicolas Iglesias <nico@webpolis.com.ar>
 */
class AngularJsHelper extends HtmlHelper
{

    private $_options = null;
    private $_bootstrap = null;
    private $_controller = null;
    private $_id = null;

    public function begin($options = array())
    {
        if (empty($options))
            return null;

        extract($options);

        $this->_id = sha1(microtime());
        $this->_options = $options;

        echo $this->script('http://ajax.googleapis.com/ajax/libs/angularjs/1.0.2/angular.min.js',
                array('inline' => false));
        echo $this->script('http://ajax.googleapis.com/ajax/libs/angularjs/1.0.2/angular-resource.min.js',
                array('inline' => false));

        if (isset($bootstrap)) {
            $this->_bootstrap = trim(strtolower(preg_replace('/^.*\/(.+)$/si',
                                    '$1', $bootstrap)));
            echo $this->script($bootstrap, array('inline' => false));
        }
        if (isset($controller)) {
            $this->_controller = trim(strtolower(preg_replace('/^.*\/(.+)$/si',
                                    '$1', $controller)));
            echo $this->script($controller . '_controller',
                    array('inline' => false));
        }

        if (!empty($this->_bootstrap))
            echo '<div ng-app="' . $this->_bootstrap . '">';

        if (!empty($this->_controller)) {
            $data = isset($data) ? json_encode($data) : 'null';
            $js = <<<JS
   window.document.onload = function(e){
            angular.element('#{$this->_id}').scope()._data = {$data};
            angular.element('#{$this->_id}').scope().\$apply();
    };
JS;
            echo $this->scriptBlock($js);
            echo '<div ng-cloak ng-controller="' . ucwords($this->_controller) . 'Controller" id="' . $this->_id . '">';
        }
    }

    public function end()
    {
        if (empty($this->_controller))
            return null;

        echo '</div>';

        if (!empty($this->_bootstrap))
            echo '</div>';
    }

}

?>
