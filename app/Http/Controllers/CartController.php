<?php

namespace App\Http\Controllers;

use App\Models\CartTemporary;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function getList()
    {
        $lstProductInCart = CartTemporary::where('user_id', Auth::id())->first();
        return !empty($lstProductInCart) ? $lstProductInCart['product'] : [];
    }

    public function addProductToCart(Request $request)
    {
        try {
            DB::beginTransaction();
            $productInformation = $request->get('products');
            $getCartTemporaryByUser = $request->user()->CartTemporary()->first();
            if (empty($getCartTemporaryByUser)) {
                $request->user()->CartTemporary()->create([
                    'user_id' => Auth::id(),
                    'product' => $productInformation
                ]);
            } else {
                $request->user()->CartTemporary()->update([
                    'product' => $productInformation
                ]);
            }
            DB::commit();
            return $this->getResponse(true, 'Add product to cart success', 200, json_decode($getCartTemporaryByUser['product'] ?? '', true));
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::debug($exception->getMessage());
            return $this->getResponse(false, 'Add product to cart failed', 500);
        }
    }

    public function getVoucher(Request $request)
    {
        $coupon = Coupon::where('code', $request->voucher)->first();
        if (empty($coupon)) {
            return $this->getResponse(false, 'Get information coupon found', 500);
        }
        return $this->getResponse(true, 'Get information coupon success', 200, $coupon);
    }

    public function checkout(Request $request)
    {
//        $informationUserPayment = $request->get('informationUserPayment');
//        return $informationUserPayment['name'];
        try {
            DB::beginTransaction();
            $informationUserPayment = $request->get('informationUserPayment');
            $productInCart = $request->get('productInCart');
            $totalMoney = $request->get('totalMoney');

            // Save order to database
            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'customer_name' => $informationUserPayment['name'] ?? '',
                'total' => $totalMoney ?? 0,
                'note' => $informationUserPayment['note'] ?? '',
                'address' => $informationUserPayment['address'] ?? '',
                'phone' => $informationUserPayment['phone'] ?? '',
                'status' => Transaction::PENDING,
                'type_payment' => 'normal',
                'status_payment' => 'Paуment not received',
            ]);
//            $transaction = new Transaction();
//            $transaction->user_id = Auth::id();
//            $transaction->customer_name = $informationUserPayment['name'] ?? '';
//            $transaction->total = $totalMoney ?? 0;
//            $transaction->note = $informationUserPayment['note'] ?? '';
//            $transaction->address = $informationUserPayment['address'] ?? '';
//            $transaction->phone = $informationUserPayment['phone'] ?? '';
//            $transaction->status = Transaction::PENDING;
//            $transaction->type_payment = 'normal';
//            $transaction->status_payment = 'Paуment not received';

            if (!empty($transaction)) {
                Transaction::where('id', $transaction->id)->update(['payment_code' => "MGD" . "-" . $transaction->id]);
                $dataInsert = [];
                foreach ($productInCart as $product) {
                    $dataInsert[] = [
                        'transaction_id' => $transaction->id ?? '',
                        'product_id' => $product['id'] ?? '',
                        'quantity' => $product['qty_pay'] ?? '',
                        'price' => $product['price'] ?? '',
                        'sale' => $product['sale'] ?? '',
                        'payment_code' => "MGD" . "-" . $transaction->id ?? '',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
                TransactionDetail::insert($dataInsert);
            }
            CartTemporary::where('user_id', Auth::id())->delete();

            DB::commit();
            return $this->getResponse(true, 'Đặt hàng thành công', 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::debug($exception);
            return $this->getResponse(false, 'Error', 500);
        }
    }
}
