angularjs-cakephp
=================

by Nicolas Iglesias <nico@webpolis.com.ar>


AngularJs <-> CakePHP Adapter/Helper
------------------------------------

 This Helper will do basically a few things:
 
*   Load AngularJS (from Google CDN by default) and all required libs (resource, controller, bootstrap, etc)
*   Generate a DIV tag which AngularJS scope is set to the controller you specify 
in $options['controller']. The tag / scope is closed by using the "end" method 
(so beware not to leave it open).
*   Inject CakePHP formatted data ($options['data']) into AngularJS controller's scope. 
The data is stored into $scope._data so you can easily make:

```
<li ng-repeat="model in _data">{{model.id}}</li>
```

... and use all the magic from AngularJs over that data.


*Example*:

In our view we do:

```
    <?php
    echo $this->AngularJs->begin(
            array(
                'bootstrap' => 'angular/bootstrap',
                'controller' => 'angular/mymodel',
            )
    );
    ?>
```

We initialized AngularJS (by taking required files from Google CDN).
We set a 'bootstrap' file, which is located in app/webroot/js/angular/bootstrap.js.
We have placed our AngularJs controller in the same folder, but we avoid the '.js' extension 
and '_controller' suffix (for sake of simplicity, is the only filename change request i do).

We then insert some AngularJs compatible markup code:

```
    <form ng-submit="saveAll()" >
        <div ng-init="list()">
            <div ng-repeat="(i,model) in _data">
                <label>{{model.Mymodel.id}}</label>
                <input required ng-model="model.Mymodel.title" type="text" />
            </div>
        </div>
        <input type="submit" value="<?php echo __('Save'); ?>" />
    </form>
```

And we stop the Helper from helping us:

```
<?php echo $this->AngularJs->end();?>
```

In this example, i choose to save all records at once, so in my 'mymodel_controller.js' 
i have:

```
    function MymodelController($scope, $resource){
        $scope.res_mymodel = $resource('/Mymodels/:action.json',{
            'action' : '@action'
        });

        $scope.list = function(){
            $scope.res_mymodel.query({
                action: 'list'
            }, function(mymodels){
                $scope._data = mymodels;
            });
        }

        $scope.saveAll = function(){
            var t = new $scope.res_mymodel(0);
            t.mymodels = $scope._data;
            t.action = 'saveAll';
            t.$save(function(r){
                if(r.success){
                    console.log('ok');
                }else{
                    console.log('error');
                }
            });
        }
    }

    MymodelController.$inject = ['$scope', '$resource'];
```

You can do it however you want it; i choose to use the AngularJs 'resource' component to 
transfer data but you can do your own AngularJs controller's logic.

For this example, we use REST capabilities from CakePHP, so we enable parsing of extensions from URLs;
go to your Cake's routes.php and add (if you don't have it :)

<code>Router::parseExtensions();</code>

So we can end by having the following "short" methods in our CakePHP controller without requiring any view:

```
    public function list()
    {
        $mymodels = $this->Mymodel->find('all');
        $this->set(compact('mymodels'));
        $this->set('_serialize', 'mymodels');
    }

    public function saveAll()
    {
        $success = $this->Setting->saveAll($this->request->data['mymodels']);
        $this->set(compact('success'));
        $this->set('_serialize', array('success'));
    }
```

Simple, right? That's why i wanted to share this small contribution with the people.


Installation
============

Place 'AngularJsHelper' in your Cake's 'app/View/Helper/' folder and load the helper by adding 'AngularJs' to 
the $helpers array in your controller.
