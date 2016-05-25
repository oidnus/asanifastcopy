<?php

class AsaniCore extends ObjectModel
{

    public static function checkProductInCategory($id_product,$id_category){
        $sql = 'SELECT COUNT(*)
			FROM '._DB_PREFIX_.'category_product
			WHERE id_product='.$id_product.' and id_category = '.$id_category;

        return Db::getInstance()->getValue($sql)>0?true:false;
    }

    public static function productGetDefaultCategory($id_product){
        $sql = 'SELECT id_category_default
			FROM '._DB_PREFIX_.'product
			WHERE id_product='.$id_product;

        return Db::getInstance()->getValue($sql);
    }

    public static function productSetDefaultCategory($id_product,$id_category){
        Db::getInstance()->update('product',['id_category_default'=>$id_category],'id_product='.$id_product);

    }

    public static function productAddToCategory($id_product,$id_category)
    {
        $position = 0;
        $arr = compact('id_product','id_category','position');

        if (self::checkProductInCategory($id_product, $id_category) == false){
            Db::getInstance()->insert('category_product', $arr);
        }
    }

   



}