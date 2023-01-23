<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
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
}
