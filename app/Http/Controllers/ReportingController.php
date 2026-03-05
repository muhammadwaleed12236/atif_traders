<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function onhand()
    {
        $rows = Product::leftJoin('v_stock_onhand as soh', 'soh.product_id', '=', 'products.id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->selectRaw('
                products.id,
                products.item_code,
                products.item_name,
                COALESCE(brands.name, "") as brand_name,
                COALESCE(units.name, "") as unit_name,
                COALESCE(soh.onhand_qty, 0) as onhand_qty
            ')
            ->orderBy('products.item_name')
            ->get();

        return view('admin_panel.Reporting.onhand', compact('rows'));
    }

    public function item_stock_report()
    {
        $products = Product::orderBy('item_name')->get();

        return view('admin_panel.reporting.item_stock_report', compact('products'));
    }

    // AJAX endpoint to fetch report rows
    public function fetchItemStock(Request $request)
    {
        $productId = $request->product_id;

        $productsQuery = Product::query();
        if ($productId && $productId !== 'all') {
            $productsQuery->where('id', $productId);
        }
        $products = $productsQuery->orderBy('item_name')->get();

        $rows = [];
        $grandTotalValue = 0;

        foreach ($products as $product) {
            // Initial stock = sum of INIT type stock movements (entered at product creation)
            $initial = (float) DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('ref_type', 'INIT')
                ->sum('qty');

            // Purchased qty & amount
            $purchaseData = DB::table('purchase_items')
                ->where('product_id', $product->id)
                ->select(DB::raw('COALESCE(SUM(qty),0) as total_qty'), DB::raw('COALESCE(SUM(line_total),0) as total_amount'))
                ->first();

            $purchased = (float) $purchaseData->total_qty;
            $purchaseAmount = (float) $purchaseData->total_amount;

            // Sold qty & amount
            $saleStats = DB::table('sale_items')
                ->where('product_id', $product->id)
                ->selectRaw('COALESCE(SUM(qty),0) as total_qty, COALESCE(SUM(total),0) as total_amount')
                ->first();

            $sold = (float) $saleStats->total_qty;
            $saleAmount = (float) $saleStats->total_amount;

            // Balance = Initial Stock + Purchased - Sold
            $balance = $initial + $purchased - $sold;

            // Determine the product's default purchase price per piece based on size_mode
            $productPurchPrice = 0;
            if ($product->size_mode === 'by_size') {
                // For by_size: purchase_price_per_m2 × m2_per_piece (pieces_per_m2 stores m2_per_piece)
                $m2PerPiece = (float) ($product->pieces_per_m2 ?? 0);
                $purchPerM2 = (float) ($product->purchase_price_per_m2 ?? 0);
                $productPurchPrice = $m2PerPiece * $purchPerM2;
            } else {
                // For by_cartons / by_pieces
                $productPurchPrice = (float) ($product->purchase_price_per_piece ?? 0);
            }

            // Calculate Weighted Average Purchase Price
            // Combine initial stock (valued at product's default purchase price) with actual purchases
            $initialAmount = $initial * $productPurchPrice;
            $totalQtyIn = $initial + $purchased;
            $totalAmountIn = $initialAmount + $purchaseAmount;

            $averagePrice = $totalQtyIn > 0 ? ($totalAmountIn / $totalQtyIn) : $productPurchPrice;

            // Stock value = Balance × Average Purchase Price
            $stockValue = $balance * $averagePrice;
            $grandTotalValue += $stockValue;

            $rows[] = [
                'id'              => $product->id,
                'item_code'       => $product->item_code,
                'item_name'       => $product->item_name,
                'initial_stock'   => $initial,
                'purchased'       => $purchased,
                'purchase_amount' => $purchaseAmount,
                'sold'            => $sold,
                'sale_amount'     => $saleAmount,
                'balance'         => $balance,
                'average_price'   => $averagePrice,
                'stock_value'     => $stockValue,
            ];
        }

        return response()->json([
            'data'        => $rows,
            'grand_total' => $grandTotalValue,
        ]);
    }



    public function purchase_report()
    {
        return view('admin_panel.reporting.purchase_report');
    }

    public function fetchPurchaseReport(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = DB::table('purchases')
            ->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->join('vendors', 'purchases.vendor_id', '=', 'vendors.id') // join vendor table
            ->select(
                'purchases.purchase_date',
                'purchases.invoice_no',
                'vendors.name as vendor_name', // vendor name
                'products.item_code',
                'products.item_name',
                'purchase_items.qty',
                'purchase_items.unit',
                'purchase_items.price',
                'purchase_items.item_discount',
                'purchase_items.line_total',
                'purchases.subtotal',
                'purchases.discount',
                'purchases.extra_cost',
                'purchases.net_amount',
                'purchases.paid_amount',
                'purchases.due_amount'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('purchases.purchase_date', [$startDate, $endDate]);
        }

        $data = $query->orderBy('purchases.purchase_date', 'asc')->get();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function sale_report()
    {
        return view('admin_panel.reporting.sale_report');
    }

    public function fetchsaleReport(Request $request)
    {
        if ($request->ajax()) {
            $start = $request->start_date;
            $end = $request->end_date;

            // Use Eloquent to handle relations and new table structure
            $query = \App\Models\Sale::with(['customer_relation', 'items.product', 'returns']);

            if ($start && $end) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$start, $end]);
            }

            $sales = $query->orderBy('created_at', 'asc')->get();

            // Transform to match the structure expected by the frontend (CSV strings)
            $transformed = $sales->map(function ($sale) {
                // Construct comma-separated strings for legacy frontend support
                $productNames = $sale->items->map(function ($item) {
                    return $item->product ? $item->product->item_name : 'Unknown';
                })->implode(',');

                // Use SKU or Name as per preference, usually Name for reports
                $productCodes = $sale->items->map(function ($item) {
                    return $item->product ? $item->product->item_code : '-';
                })->implode(',');

                $qtys = $sale->items->pluck('qty')->implode(',');
                $prices = $sale->items->pluck('price')->implode(','); // Unit Price
                $totals = $sale->items->pluck('total')->implode(','); // Line Total

                return [
                    'id' => $sale->id,
                    'reference' => $sale->reference ?? '-',
                    'product' => $productNames,      // Names
                    'product_code' => $productCodes, // Codes
                    'brand' => '-',                  // Could extract from items if needed
                    'unit' => '-',                   // Could extract
                    'per_price' => $prices,
                    'per_discount' => 0,             
                    'qty' => $qtys,
                    'per_total' => $totals,
                    'total_net' => $sale->total_net,
                    'created_at' => $sale->created_at->format('Y-m-d H:i:s'),
                    'customer_name' => $sale->customer_relation ? $sale->customer_relation->customer_name : 'Walk-in',
                    'returns' => $sale->returns->map(function($ret) {
                         // Simplify return object for frontend
                         return [
                            'product' => $ret->product ?? '-', // Legacy return might just store string?
                            // New return system uses SalesReturn table. 
                            // If `SalesReturn` model has items relation we need to check.
                            // Assuming `returns` relation on Sale model returns rows from `sales_returns`
                            'qty' => $ret->qty ?? 0,
                            'per_total' => $ret->total_net ?? 0 // best guess based on return table
                         ];
                    })
                ];
            });

            return response()->json($transformed);
        }

        return view('admin_panel.reporting.sale_report');
    }

    public function customer_ledger_report()
    {
        $customers = DB::table('customers')->select('id', 'customer_name')->get();

        return view('admin_panel.reporting.customer_ledger_report', compact('customers'));
    }

    public function fetch_customer_ledger(Request $request)
    {
        $customerId = $request->customer_id;
        $start = $request->start_date ?: '2000-01-01';
        $end = $request->end_date ?: date('Y-m-d');

        $balanceService = app(\App\Services\BalanceService::class);

        // If "all" or empty, fetch for ALL customers
        if (!$customerId || $customerId === 'all') {
            // Get all customers who have journal entries
            $customerIds = \App\Models\JournalEntry::where('party_type', \App\Models\Customer::class)
                ->distinct()
                ->pluck('party_id')
                ->toArray();

            // Also include customers with opening balance
            $obCustomerIds = \App\Models\Customer::where('opening_balance', '>', 0)
                ->pluck('id')
                ->toArray();

            $allIds = array_unique(array_merge($customerIds, $obCustomerIds));

            $allTransactions = [];
            $totalOpening = 0;
            $totalClosing = 0;

            foreach ($allIds as $cid) {
                $ledgerData = $balanceService->getCustomerLedger($cid, $start, $end);
                $customerName = $ledgerData['customer']->customer_name ?? 'Unknown';
                $totalOpening += $ledgerData['opening_balance'];

                foreach ($ledgerData['transactions'] as $row) {
                    $desc = $row['description'] ?? '';

                    // Try to find payment account name for receipt entries
                    $accountName = '';
                    if ($row['credit'] > 0 && $row['source_type']) {
                        $accountName = $this->getPaymentAccountName($row['source_type'], $row['source_id']);
                    }
                    if ($accountName) {
                        $desc .= ' [A/C: ' . $accountName . ']';
                    }

                    $ref = '-';
                    if (preg_match('/Invoice #(\S+)/', $desc, $matches)) {
                        $ref = $matches[1];
                    } elseif (preg_match('/Receipt #(\S+)/', $desc, $matches)) {
                        $ref = $matches[1];
                    }

                    $entryDate = $row['date'];
                    if ($entryDate instanceof \Carbon\Carbon) {
                        $formattedDate = $entryDate->format('d-M-Y');
                        $sortDate = $entryDate->format('Y-m-d');
                    } else {
                        $formattedDate = \Carbon\Carbon::parse($entryDate)->format('d-M-Y');
                        $sortDate = \Carbon\Carbon::parse($entryDate)->format('Y-m-d');
                    }

                    $allTransactions[] = [
                        'sort_date' => $sortDate,
                        'date' => $formattedDate,
                        'invoice' => $ref,
                        'description' => $desc,
                        'customer_name' => $customerName,
                        'debit' => $row['debit'] ?? 0,
                        'credit' => $row['credit'] ?? 0,
                        'balance' => $row['balance'] ?? 0,
                    ];
                }

                $totalClosing += $ledgerData['closing_balance'] ?? $ledgerData['opening_balance'];
            }

            // Sort by date
            usort($allTransactions, function ($a, $b) {
                return strcmp($a['sort_date'], $b['sort_date']);
            });

            // Recalculate running balance across all
            $running = $totalOpening;
            foreach ($allTransactions as &$t) {
                $running += ($t['debit'] - $t['credit']);
                $t['balance'] = $running;
            }

            return response()->json([
                'customer' => (object)['customer_name' => 'All Customers'],
                'opening_balance' => $totalOpening,
                'closing_balance' => $totalClosing,
                'transactions' => $allTransactions,
                'report_period' => "$start to $end",
            ]);
        }

        // Single customer
        $customer = DB::table('customers')->where('id', $customerId)->first();
        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 400);
        }

        $ledgerData = $balanceService->getCustomerLedger($customerId, $start, $end);

        $transactions = collect($ledgerData['transactions'])->map(function ($row) {
            $desc = $row['description'] ?? '';

            // Try to find payment account name for receipt entries
            $accountName = '';
            if ($row['credit'] > 0 && ($row['source_type'] ?? null)) {
                $accountName = $this->getPaymentAccountName($row['source_type'], $row['source_id']);
            }
            if ($accountName) {
                $desc .= ' [A/C: ' . $accountName . ']';
            }

            $ref = '-';
            if (preg_match('/Invoice #(\S+)/', $desc, $matches)) {
                $ref = $matches[1];
            } elseif (preg_match('/Receipt #(\S+)/', $desc, $matches)) {
                $ref = $matches[1];
            }

            $entryDate = $row['date'];
            if ($entryDate instanceof \Carbon\Carbon) {
                $formattedDate = $entryDate->format('d-M-Y');
            } else {
                $formattedDate = \Carbon\Carbon::parse($entryDate)->format('d-M-Y');
            }

            return [
                'date' => $formattedDate,
                'invoice' => $ref,
                'description' => $desc,
                'debit' => $row['debit'] ?? 0,
                'credit' => $row['credit'] ?? 0,
                'balance' => $row['balance'] ?? 0,
            ];
        });

        return response()->json([
            'customer' => $customer,
            'opening_balance' => $ledgerData['opening_balance'],
            'closing_balance' => $ledgerData['closing_balance'] ?? $ledgerData['opening_balance'],
            'transactions' => $transactions,
            'report_period' => "$start to $end",
        ]);
    }

    /**
     * Get the payment account name from voucher source
     */
    private function getPaymentAccountName($sourceType, $sourceId)
    {
        try {
            if ($sourceType && $sourceId) {
                // Look at VoucherDetail for the debit side (cash/bank account)
                $voucherDetail = \App\Models\VoucherDetail::where('voucher_master_id', $sourceId)
                    ->where('debit', '>', 0)
                    ->first();
                if ($voucherDetail && $voucherDetail->account_id) {
                    $account = \App\Models\Account::find($voucherDetail->account_id);
                    return $account ? $account->title : '';
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        return '';
    }
}
