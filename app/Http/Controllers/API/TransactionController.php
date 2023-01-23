<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request) {
        $id = $request->input('id');
        $limit = $request->input('limit', 12);
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['details.product'])->find($id);

            if ($transaction) {
                return ResponFormatter::success(
                    $transaction,
                    'Success get data transactions'
                );
            } else {
                return ResponFormatter::error(
                    null,
                    'Data transaction not found',
                    404
                );
            }
        }

        $transaction = Transaction::with(['details.product'])->where('users_id', Auth::user()->id);

        if ($status) {
            $transaction->where('status', $status);
        }
        return ResponFormatter::success(
            $transaction->paginate($limit),
            'Success get data transaction'
        );
    }

    public function checkout(Request $request) {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPED',
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status
        ]);

        foreach($request->items as $product) {
            TransactionDetail::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }
    }
}
