<?php

/**
 *
 * @author Mārtiņš Balodis
 */
abstract class Kohana_Controller_CRUD extends Controller {
	
	/**
	 * This method must return model which will be created, updated, read or removed.
	 * If id is not specified then an empty model must be created
	 * @return ORM
	 */
	protected abstract function get_model($id);
	
	/*
	 * Backbone Sync
	 * create -> POST   /collection
	 * read -> GET   /collection[/id]
	 * update -> PUT   /collection/id
	 * delete -> DELETE   /collection/id
	 */
	
	/**
	 * Read input for crud actions
	 * @return array 
	 */
	protected function crud_get_input() {
		
		// read values
		return json_decode($this->request->body(), true);
		
	}
	
	/**
	 * CRUD action
	 * Set this as url for your backbone models
	 */
	public function action_crud() {
		
		$values = $this->crud_get_input();
		$id = $this->crud_get_id($values);
		$model = $this->get_model($id);
		
		switch ($this->request->method()) {
			case 'POST':
				
				$this->crud_create($model, $values);
				
				break;
			case 'GET':
				$this->crud_read($model);
				break;
			case 'PUT':
				
				$this->crud_update($model, $values);
				
				break;
			case 'DELETE':
				if($id === null) {
					throw new Kohana_Exception("ID not passed to controller");
				}
				$this->crud_delete($model);
				return;
			
			default:
				throw new Kohana_Exception("Invalid Crud controller usage");
				break;
		}
		
		
		$this->crud_response($model);
	}
	
	/**
	 * Gets requested elements id.
	 * @return integer | null 
	 */
	protected function crud_get_id(&$values) {
		
		// If updating model then id is sent via json
		if($this->request->method() === "PUT") {
			
			// models primary key must be sent
			if(!isset($values["id"])) {

				throw new Kohana_Exception("no model to load for updating");
			}

			$id = $values["id"];
			// remove id from values as it is not needed to set it again
			unset($values["id"]);
			return $id;
		}
		
		return $this->request->param("id");
	}
	
	/**
	 * Crud response to caller
	 * @param ORM $model 
	 */
	protected function crud_response($model) {
		
		// model is an iterator of objects
		if($model instanceof Database_Result) {
			
			$object = array();
			
			foreach($model as $row) {
				$object[] = $row->as_array();
			}
			
			// return array of models
			$this->response->body(json_encode($object));
			
		}
		else {
			
			// return changed model
			$this->response->body(json_encode($model->as_array()));
		}
		
	}
	
	/**
	 * Creates new model
	 * @param ORM $model
	 * @param type $values 
	 */
	protected function crud_create($model, $values) {
		
		$model->values($values);
		
		// save model
		$model->save();
		
	}
	
	/**
	 * Returns model
	 * @param type $model 
	 */
	protected function crud_read($model) {
		
	}
	
	/**
	 * Updates model
	 * @param ORM $model
	 * @param type $values 
	 */
	protected function crud_update($model, $values) {
		
		$model->values($values);
		
		// save model
		$model->update();
		
	}
	
	/**
	 * Deletes this model
	 * @param type $model 
	 */
	protected function crud_delete($model) {
		
		$model->delete();
		
	}
	
	/**
	 * Multiple object fetching action
	 * @return type 
	 */
	public function action_crud_collection() {
		
		// load model
		$model = $this->get_model(null);
		
		// execute model filtering
		$this->crud_collection($model);
		
	}
	
	/**
	 * Finds all items of model kind.
	 * @param ORM $model 
	 */
	protected function crud_collection($model) {
		
		$items = $model->find_all();
		
		// return found models
		$this->crud_response($items);
		
	}
	
}