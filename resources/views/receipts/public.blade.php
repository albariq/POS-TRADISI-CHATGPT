<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->receipt_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print { .no-print { display: none; } }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="max-w-md mx-auto p-4">
        <div class="no-print mb-4 text-center">
            <button onclick="window.print()" class="text-sm bg-slate-900 text-white rounded px-3 py-1">Print</button>
        </div>
        @include('receipts.partials.receipt', ['sale' => $sale])
    </div>
</body>
</html>
