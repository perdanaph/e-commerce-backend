<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if ($id) {
            $category = ProductCategory::with(['products'])->find($id);

            if ($category) {
                return ResponFormatter::success(
                    $category,
                    'Data Category Berhasil di Dapat'
                );
            } else {
                return ResponFormatter::error(
                    null,
                    'Data Category Tidak Ada',
                    404
                );
            }
        }

        $category = ProductCategory::query();

        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }

        if ($show_product) {
            $category->with('products');
        }

        return ResponFormatter::success(
            $category->paginate($limit),
            'Data List Category Berhasil di Dapat'
        );
    }
}
