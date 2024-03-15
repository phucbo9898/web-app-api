<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\FavoriteProduct;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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

    public function getSlide()
    {
        $slides = Slide::all();
        return $slides;
    }

    public function getCategories()
    {
        $categories = Category::all();
        return $categories;
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

    public function getListFavorite()
    {
        $lstFavorite = Auth::user()->favoriteProduct;
        if (count($lstFavorite) <= 0) {
            return $this->getResponse(false, 'failed', 422);
        }
        return $this->getResponse(true, 'Get data success', 200, $lstFavorite);
    }

    public function removeFavoriteProduct(Request $request)
    {
        try {
            $productId = $request->get('productId');
            $getProductById = Product::where([
                'id' => $productId,
                'status' => 'active'
            ])->whereNull('deleted_at')->first();
            if (empty($getProductById)) {
                return $this->getResponse(false, 'data failed', 422);
            }

            FavoriteProduct::where([
                'user_id' => Auth::id(),
                'product_id' => $productId
            ])->delete();
            return $this->getResponse(true, 'Remove success');
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            return $this->getResponse(false, 'Remove failed', 500);
        }
    }

    public function getDetailProduct($id, Request $request)
    {
        $product = Product::where([
            'id' => $id,
            'status' => 'active'
        ])->with([
            'productAttributeValue' => function ($subQuery1) {
                $subQuery1->select(['attribute_value.attribute_id', 'attribute_value.value'])->with([
                    'attribute' => function ($subQ1) {
                        return $subQ1->select(['attributes.id', 'attributes.name']);
                    }
                ]);
            },
            'ratings' => function ($subQuery2) {
                $subQuery2->select('ratings.*')->with([
                    'user' => function ($subQ2) {
                        return $subQ2->select(['users.id', 'users.email']);
                    }
                ]);
            }
        ])->first();
        $product['avg_score'] = round($product->ratings->avg('number'), 1);
        $rateFiveStar = Rating::where(['product_id' => $id,'number' => 5])->count();
        $rateFourStar = Rating::where(['product_id' => $id,'number' => 4])->count();
        $rateThreeStar = Rating::where(['product_id' => $id,'number' => 3])->count();
        $rateTwoStar = Rating::where(['product_id' => $id,'number' => 2])->count();
        $rateOneStar = Rating::where(['product_id' => $id,'number' => 1])->count();
        $product['rate_five_star'] = $rateFiveStar;
        $product['rate_four_star'] = $rateFourStar;
        $product['rate_three_star'] = $rateThreeStar;
        $product['rate_two_star'] = $rateTwoStar;
        $product['rate_one_star'] = $rateOneStar;
        if (empty($product)) {
            return $this->getResponse(false, 'failed', 422);
        }
        return $this->getResponse(true, 'get product detail success', 200, $product);
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

    public function addFavoriteProduct(Request $request, $id)
    {
        try {
            $checkExistFavoriteProduct = FavoriteProduct::where([
                'product_id' => $id,
                'user_id' => Auth::id()
            ])->first();

            if (!empty($checkExistFavoriteProduct)) {
                return $this->getResponse(true, 'Sản phẩm đã tồn tại trong danh sách sản phẩm ưa thích của bạn!', 200, [
                    'status' => 422,
                    'message' => 'Sản phẩm đã tồn tại trong danh sách sản phẩm ưa thích của bạn!'
                ]);
            }

            FavoriteProduct::create([
                'product_id' => $id,
                'user_id' => Auth::id()
            ]);
            return $this->getResponse(true, 'Thành công! Đã thêm sản phẩm vào sản phẩm yêu thích của bạn!', 200, [
                'status' => 200,
                'message' => 'Thành công! Đã thêm sản phẩm vào sản phẩm yêu thích của bạn!'
            ]);
        } catch (\Exception $exception) {
            Log::debug($exception->getMessage());
            return $this->getResponse(false, 'Thêm sản phẩm yêu thích thất bại', 500);
        }
    }
}
