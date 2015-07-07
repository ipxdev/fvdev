<?php namespace ninja\repositories;

use Product;

class ProductRepository
{
	public function find($filter = null)
	{
    	$query = \DB::table('products')
    		->join('categories', 'categories.id', '=', 'products.category_id')
			->where('products.account_id', '=', \Auth::user()->account_id)
			->select('products.public_id', 'products.product_key', 'products.notes', 'products.cost','categories.name as category_name', 'products.deleted_at');


    	if (!\Session::get('show_trash:product'))
    	{
    		$query->where('products.deleted_at', '=', null);
    	}

    	if ($filter)
    	{
    		$query->where(function($query) use ($filter)
            {
            	$query->where('products.product_key', 'like', '%'.$filter.'%');

            });
    	}

    	return $query;
	}

	public function getErrors($data)
	{
		// $price = isset($data['prices']) ? (array)$data['prices'][0] : (isset($data['prices']) ? $data['prices'] : []);
		// $validator = \Validator::make($price, ['product_key' => 'required']);
		// if ($validator->fails()) {
		// 	return $validator->messages();
		// }
		
		return false;		
	}

	public function bulk($ids, $action)
	{
		$products = Product::withTrashed()->scope($ids)->get();
		
		foreach ($products as $product) 
		{	
            if ($action == 'restore') {
                $product->restore();
                $product->save();
            }
            else 
            {
                if ($action == 'delete') {
					$product->delete();
                }
                
            }			
		}

		return count($products);
	}	
}