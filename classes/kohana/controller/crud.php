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
		
		$model = $this->crud_execute();
		
		$data = $this->crud_response($model);
		$this->response->body(json_encode($data));
	}
	
	/**
	 * Handles crud request
	 * Extend this method to catch errors for later usage.
	 * 
	 * @return type
	 * @throws Kohana_Exception 
	 */
	protected function crud_execute() {
		
		$values = $this->crud_get_input();
		$id = $this->crud_get_id();
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
		
		return $model;
		
	}
	
	/**
	 * Gets requested elements id.
	 * @return integer | null 
	 */
	protected function crud_get_id() {
		
		return $this->request->param("id");
	}
	
	/**
	 * Prepare response with array of objects
	 * Override this method to add additional response data
	 * 
	 * @param Iterator | array $model 
	 * @return array
	 */
	protected function crud_response_collection($model) {
		
		$data = array();
			
		foreach($model as $row) {

			if(method_exists($row, 'as_array')) {
				$data[] = $row->as_array();
			}
			else {
				$data[] = $row;
			}

		}
		
		return $data;
	}
	
	
	/**
	 * Prepare response object
	 * Override this method to add additional response data
	 * 
	 * @param ORM $model
	 * @return array
	 */
	protected function crud_response($model) {
		
		if(method_exists($model, 'as_array')) {
			$data = $model->as_array();
		}
		else {
			$data = $model;
		}
		
		return $data;
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
		
		// data filtering.
		// This also could be done with orm::__construct, but orm::__construct
		// does not check whether a column exists for filtering.
		$params = $this->request->query();
		$model_columns = $model->table_columns();
		foreach ($params as $column => $value) {
			if(array_key_exists($column, $model_columns)) {
				// Passing an array of column => values
				$model->where($column, '=', $value);
			}
		}
		
		$items = $model->find_all();
		
		// return found models
		$data = $this->crud_response_collection($items);
		$this->response->body(json_encode($data));
		
	}
	
}