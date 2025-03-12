<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    /**
     * Display a receipt for printing.
     *
     * @param int $transactionId
     * @return \Illuminate\View\View
     */
    public function print($transactionId): View
    {
        $transaction = Transaction::with(['items.product', 'user'])
            ->findOrFail($transactionId);
        // dd ($transaction);
        return view('receipts.print', [
            'transaction' => $transaction,
        ]);
    }
}