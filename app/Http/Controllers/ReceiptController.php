<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;

class ReceiptController extends Controller
{
    /**
     * Display a receipt for printing.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function print($id)
    {
        // Find the transaction
        $transaction = Transaction::findOrFail($id);
        
        // Get transaction details with products
        $details = TransactionDetail::where('transaction_id', $id)
            ->with('product')
            ->get();
            
        // Format items for the receipt
        $items = [];
        foreach ($details as $detail) {
            $items[] = [
                'nama' => $detail->product->nama,
                'harga' => $detail->price,
                'jumlah' => $detail->quantity,
                'subtotal' => $detail->subtotal
            ];
        }
        
        return view('receipt.print', [
            'invoice' => $transaction->invoice_number,
            'items' => $items,
            'total' => $transaction->total,
            'date' => $transaction->transaction_date
        ]);
    }
    
    /**
     * Print the latest receipt for a session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function printLatest(Request $request)
    {
        // For session-based cart, we can store the last transaction temporarily
        if ($request->session()->has('last_transaction')) {
            $data = $request->session()->get('last_transaction');
            
            return view('receipt.print', [
                'invoice' => $data['invoice'],
                'items' => $data['items'],
                'total' => $data['total'],
                'date' => now()
            ]);
        }
        
        return redirect()->back()->with('error', 'No transaction found to print');
    }
}