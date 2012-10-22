<?php

$plugin_info = array(
    'pi_name'       => 'Simple cart',
    'pi_version'        => '1.0',
    'pi_author'     => 'FDCore Studio',
    'pi_author_url'     => 'http://fdcore.com/',
    'pi_description'    => '',
    'pi_usage'      => Simple_cart::usage()
);

class Simple_cart{
    function __construct(){
        $this->EE = & get_instance();
    }

    function insert(){

        $id     = $this->EE->TMPL->fetch_param('id', '');
        $qty    = $this->EE->TMPL->fetch_param('qty', 1);
        $price  = $this->EE->TMPL->fetch_param('price');
        $name   = $this->EE->TMPL->fetch_param('name');
        $entry_id   = $this->EE->TMPL->fetch_param('entry_id');

        if(!$id || $id == '') $id = 'sku_'.time();

        $hash = hash_hmac('sha256', "$entry_id$price$qty$name", 'simpleCart');

        $data = array(
                'id'      => $id,
                'qty'     => $qty,
                'price'   => $price,
                'name'    => $name,
                'entry_id'=> $entry_id,
                'hash'    => $hash
        );

        if(isset($_COOKIE['exp_scart'])){

            $cc = $_COOKIE['exp_scart'];

            $cc = json_decode($cc, true);

        } else $cc = array();

        if(!$cc || $cc == '') $cc = array();

        $data = array_merge(array($data), $cc);
        $data = $this->_append_items($data);
        $data = json_encode($data);

        $this->EE->functions->set_cookie('scart', addslashes($data), 86500);

        return 'ok';
    }

    function get(){

        if(isset($_COOKIE['exp_scart'])){
            $cc = $_COOKIE['exp_scart'];
            $cc = json_decode($cc, true);
        } else $cc = array();

        $tagdata = $this->EE->TMPL->tagdata;
        $items = array();

        if(count($cc) == 0 ) return $this->EE->TMPL->no_results();

        foreach($cc as $item){

            if(isset($item['hash'])){
                $hash = hash_hmac('sha256', $item['entry_id'].$item['price'].$item['qty'].$item['name'], 'simpleCart');

                $item['full_price'] = $item['qty']*$item['price'];

                if($item['hash'] == $hash){
                    $items[]=$item;
                }
            }

        }

        if(count($items) == 0 ) return $this->EE->TMPL->no_results();
        return $this->EE->TMPL->parse_variables($tagdata, $items);
    }

    function _append_items($items){

        $items_names = array();

        foreach($items as $item){

            if(isset($items_names[$item['name']])){

                if(!isset($item['entry_id'], $item['price'], $item['name'])) continue;
                $items_names[$item['name']]['qty'] = $items_names[$item['name']]['qty'] + $item['qty'];
                $items_names[$item['name']]['hash']= hash_hmac('sha256', $item['entry_id'].$item['price'].$items_names[$item['name']]['qty'].$item['name'], 'simpleCart');
            } else {
                $items_names[$item['name']] = $item;
            }
        }

        $items = array();
        foreach($items_names as $i) $items[]=$i;
        return $items;

    }

    function delete(){

        $id = $this->EE->TMPL->fetch_param('id');

        if(isset($_COOKIE['exp_scart'])){
            $cc     = $_COOKIE['exp_scart'];
            $items  = json_decode($cc, true);
        } else return;

        foreach($items as $key=>$item)
            if($item['id'] == $id) unset($items[$key]);

        $data = json_encode($items);

        $this->EE->functions->set_cookie('scart', addslashes($data), 86500);
    }

    function full_price(){

        if(isset($_COOKIE['exp_scart'])){
            $cc     = $_COOKIE['exp_scart'];
            $items  = json_decode($cc, true);
        } else return;

        $total = 0;

        foreach($items as $key=>$item){

            $sum = $item['qty']*$item['price'];
            $total+=$sum;
        }

        return $total;
    }

    function clean(){
        $this->EE->functions->set_cookie('scart', '');
    }

    public function usage(){

        ob_start();  ?>
{exp:simple_cart:insert name="test" price="500" entry_id="1"}

    id -
    qty - item quality
    price - price in int
    name - name item
    entry_id - entry id

{exp:simple_cart:get}
    <h1>{name}: {entry_id}</h1>
    <p>цена {price} кол: {qty}</p>
    <hr>
{/exp:simple_cart:get}

{exp:simple_cart:clean}

{exp:simple_cart:delete id="sku_2324234"}

 <?php
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

}