<?php namespace Dmrch\PagSeguro\Components;

use Cms\Classes\ComponentBase;
use Session;

class Cart extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Cart Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onClear(){
        Session::forget('cart_items');
        Session::forget('cart_total');
    }

    public function onAdd(){

        $cart = [];
        $item = input('item');

        if(Session::has('cart_items'))
            $cart = Session::get('cart_items');       

        if (array_key_exists($item['id'], $cart)) {
            $product = $cart[$item['id']];          
            $product->quantity += 1;
        }else{
            $product = new \stdClass;
            $product->id = $item['id']; 
            $product->name = $item['name']; 
            $product->price = $item['price']; 
            $product->quantity = 1;
        }

        $cart[$product->id] = $product;

        Session::put('cart_items',$cart);
    }

    public function onDelete() {
       
        $cart = [];
        $item = input('item');     

        if (array_key_exists($item['id'], $cart)) {
            unset($cart[$item['id']]);          
        }

        Session::put('cart_items',$cart);
    }

}
