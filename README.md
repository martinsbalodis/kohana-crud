## installation
add as git submodule
`git submodule add git@github.com:martinsbalodis/kohana-crud.git modules/crud`
add module in bootstrap
`'crud'  		=> MODPATH.'crud',		 // crud controller`

## usage
```php
<?php
class Controller_Test extends Controller_CRUD {

  
	protected function get_model($id) {
		
		// Return an ORM model
		return new Model_User($id);
		
	}

}

```

## notes

Works well with backbone.js. Override crud class methods to add filtering features. Also there is crud action for fetching multiple objects