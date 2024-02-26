<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function getDataHome()
    {
        $banners = Slide::all();
        $categories = Category::where('status', 'active')->get();
        $product_news = Product::where('status', 'active')->orderBy('created_at', 'DESC')->limit(4)->get();
        $articles = Article::with([
            'user' => function ($subQ) {
                return $subQ->where('status', 'active')->whereNull('deleted_at')->select(['users.id', 'users.name']);
            }
        ])->where('status', 'active')->orderBy('updated_at', 'DESC')->take(3)->get();
        $product_best_pays = Product::where('status', 'active')->orderBy('qty_pay', 'DESC')->limit(4)->get();
        $arrayProductByCategories = [];

        foreach ($categories as $category) {
            $products = $category->products()->where('status', 'active')->orderBy('updated_at', 'desc')->take(4)->get();
            $arrayProductByCategories[$category['id']] = [
                'category_name' => $category->name,
                'products' => $products
            ];
//            $arrayProductByCategory['category'] = $category->name;
//            $arrayProductByCategory['products'] = $products;
        }
        $data = [
            'banners' => $banners,
            'categories' => $categories,
            'product_news' => $product_news,
            'articles' => $articles,
            'product_best_pays' => $product_best_pays,
            'arrayProductByCategories' => $arrayProductByCategories
        ];

        return $this->getResponse(true, 'Update setting language success', 200, $data);
    }

    public function getListArticles()
    {
        $articles = Article::with([
            'user' => function ($subQ) {
                return $subQ->where('status', 'active')->whereNull('deleted_at')->select(['users.id', 'users.name']);
            }
        ])->where('status', 'active')->whereNull('deleted_at')->get();
        return $this->getResponse(true, 'Update setting language success', 200, $articles);
    }

    public function getDetailArticle($id, Request $request)
    {
        $article = Article::with([
            'user' => function ($subQ) {
                return $subQ->where('status', 'active')->whereNull('deleted_at')->select(['users.id', 'users.name']);
            }
        ])->where(['status' => 'active','id' => $id])->first();
        if (empty($article)) {
            return $this->getResponse(false, 'failed', 422);
        }
        return $this->getResponse(true, 'Update setting language success', 200, $article);
    }
}
