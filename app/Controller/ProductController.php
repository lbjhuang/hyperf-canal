<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 */
class ProductController
{
    public function getList(RequestInterface $request)
    {

        $id = $request->input('id', '');
        $products = Db::select('SELECT * FROM `product_detail` WHERE id = ?',[$id]);  //  返回array

        foreach($products as $product){
            return $product->spu_name;
        }
    }

}
