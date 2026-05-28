<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Receipt Voucher - Vahla Mill Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #f0f0f0;
            margin: 0;
            font-size: 13px;
        }

        .print-btn-wrap {
            text-align: right;
            width: 820px;
            margin: 14px auto 8px;
        }

        .print-btn-wrap button {
            background: #0b5a2b;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 4px;
        }

        .page {
            width: 820px;
            margin: 0 auto 30px;
            padding: 20px 24px 16px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* ── Header ── */
        .vch-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .vch-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #0b5a2b;
            line-height: 1;
        }
        .vch-badge {
            border: 2px solid #222;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.04em;
        }

        hr.sep {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0 8px;
        }

        /* ── Meta row ── */
        .meta-row {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }
        .party-box {
            flex: 1;
            border: 1.5px solid #333;
            padding: 7px 10px;
            line-height: 1.6;
        }
        .party-box .prow {
            display: flex;
        }
        .party-box .plabel {
            min-width: 110px;
            font-weight: 700;
            font-size: 12px;
        }
        .party-box .pval {
            font-size: 12px;
        }

        .voucher-box {
            width: 200px;
            border: 1.5px solid #0b5a2b;
            padding: 7px 10px;
            font-size: 12px;
        }
        .voucher-box .vrow {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-weight: 600;
        }

        /* ── Payment Table ── */
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0b5a2b;
            border-bottom: 1.5px solid #0b5a2b;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }

        .pay-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 6px;
        }
        .pay-table thead tr {
            background: #1e293b;
            color: #fff;
        }
        .pay-table thead th {
            padding: 5px 8px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .pay-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }
        .pay-table tbody tr:last-child { border-bottom: none; }
        .pay-table tbody td {
            padding: 5px 8px;
        }
        .pay-table tfoot td {
            padding: 5px 8px;
            font-weight: 700;
            border-top: 2px solid #000;
            background: #f8f9fa;
        }

        /* ── Amount words ── */
        .amount-words {
            font-size: 12px;
            font-style: italic;
            font-weight: 600;
            margin-bottom: 8px;
        }

        /* ── Summary ── */
        .summary-box {
            border: 2px solid #0b5a2b;
            padding: 7px 10px;
            margin-bottom: 8px;
        }
        .summary-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .summary-box td {
            padding: 3px 4px;
            font-weight: 600;
        }
        .summary-box td:last-child {
            text-align: right;
            font-weight: 700;
        }
        .summary-box tr.balance-row td {
            font-size: 14px;
            font-weight: 700;
            color: #0b5a2b;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        /* ── Footer ── */
        .vch-footer {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            color: #444;
            margin-top: 4px;
        }
        .vch-footer .thank { font-weight: 700; color: #0b5a2b; }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .page {
                margin: 0;
                box-shadow: none;
                padding: 12px 18px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="print-btn-wrap no-print">
        <button onclick="window.print()">
            <i class="fa fa-print"></i> Print Voucher
        </button>
    </div>

    <div class="page">

        {{-- ── HEADER ── --}}
        <div class="vch-header">
            <h1>Vahla Mill Store</h1>
            <span class="vch-badge">RECEIPT VOUCHER</span>
        </div>
        <hr class="sep">

        {{-- ── META ROW ── --}}
        <div class="meta-row">
            <div class="party-box">
                @if ($party)
                    @if(in_array($voucher->type, ['customer', 'walkin']))
                        <div class="prow">
                            <div class="plabel">Received From:</div>
                            <div class="pval"><strong>{{ $party->customer_name ?? $party->name ?? '-' }}</strong></div>
                        </div>
                        @if(!empty($party->mobile))
                        <div class="prow">
                            <div class="plabel">Phone:</div>
                            <div class="pval">{{ $party->mobile }}</div>
                        </div>
                        @endif
                    @elseif($voucher->type === 'vendor')
                        <div class="prow">
                            <div class="plabel">Received From:</div>
                            <div class="pval"><strong>{{ $party->name ?? '-' }}</strong></div>
                        </div>
                        @if(!empty($party->phone))
                        <div class="prow">
                            <div class="plabel">Phone:</div>
                            <div class="pval">{{ $party->phone }}</div>
                        </div>
                        @endif
                    @else
                        <div class="prow">
                            <div class="plabel">Account:</div>
                            <div class="pval"><strong>{{ $party->name ?? '-' }}</strong></div>
                        </div>
                    @endif
                @else
                    <div class="prow"><div class="plabel">Party:</div><div class="pval">-</div></div>
                @endif
            </div>

            <div class="voucher-box">
                <div class="vrow">
                    <span>Voucher No:</span>
                    <span>{{ $voucher->rvid }}</span>
                </div>
                <div class="vrow">
                    <span>Date:</span>
                    <span>{{ \Carbon\Carbon::parse($voucher->receipt_date)->format('d-M-Y') }}</span>
                </div>
            </div>
        </div>

        {{-- ── PAYMENT DETAILS TABLE ── --}}
        <div class="section-title">Received Into</div>
        <table class="pay-table">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">#</th>
                    <th>Account (Where Payment Received)</th>
                    <th style="text-align:right; width:130px;">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $key => $row)
                    <tr>
                        <td style="text-align:center;">{{ $key + 1 }}</td>
                        <td>
                            <strong>{{ $row['account_name'] ?? '-' }}</strong>
                            @if(!empty($row['narration']))
                                <span style="color:#666; font-size:11px; margin-left:4px;">({{ $row['narration'] }})</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:700;">
                            {{ number_format($row['amount'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right;">Total Received:</td>
                    <td style="text-align:right;">{{ number_format($voucher->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="amount-words">
            In Words: <strong id="amountInWords">{{ $voucher->total_amount }}</strong>
        </div>

        {{-- ── BALANCE SUMMARY ── --}}
        @php
            $balanceAfter = $previousBalance - $voucher->total_amount;
        @endphp
        <div class="summary-box">
            <table>
                <tr>
                    <td>Previous Balance</td>
                    <td>{{ number_format(abs($previousBalance), 2) }} {{ $previousBalance >= 0 ? 'Dr' : 'Cr' }}</td>
                </tr>
                <tr>
                    <td>Amount Received (−)</td>
                    <td>{{ number_format($voucher->total_amount, 2) }} Cr</td>
                </tr>
                <tr class="balance-row">
                    <td>Balance Remaining</td>
                    <td>{{ number_format(abs($balanceAfter), 2) }} {{ $balanceAfter >= 0 ? 'Dr' : 'Cr' }}</td>
                </tr>
            </table>
        </div>

        {{-- ── FOOTER ── --}}
        <div class="vch-footer">
            <span>Printed: {{ now()->format('d-M-Y') }} | {{ now()->format('H:i') }}</span>
            <span class="thank">Thank You ✓</span>
        </div>

    </div>

    <script>
        function numberToWords(num) {
            const a = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
                'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
            const b = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
            if ((num = num.toString()).length > 9) return 'Overflow';
            let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
            if (!n) return '';
            let str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + ' Crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + ' Lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + ' Thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + ' Hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + ' ' : '';
            return str.trim() + ' Only';
        }
        document.addEventListener("DOMContentLoaded", function () {
            let el = document.getElementById("amountInWords");
            if (el) {
                let amount = parseInt(el.innerText);
                el.innerText = numberToWords(amount) || el.innerText;
            }
        });
    </script>

</body>
</html>
