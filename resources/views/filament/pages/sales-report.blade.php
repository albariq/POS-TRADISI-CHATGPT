<x-filament::page>
    <x-filament::section heading="Summary">
        <div class="fi-prose">
            <table>
                <tbody>
                    <tr>
                        <th>Net Sales</th>
                        <td>{{ number_format($this->summary['net_sales'], 0, ',', '.') }}</td>
                        <th>COGS</th>
                        <td>{{ number_format($this->summary['cogs'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Gross Profit</th>
                        <td>{{ number_format($this->summary['gross_profit'], 0, ',', '.') }}</td>
                        <th>Gross Margin</th>
                        <td>{{ number_format($this->summary['gross_margin'], 2, ',', '.') }}%</td>
                    </tr>
                    <tr>
                        <th>Subtotal</th>
                        <td>{{ number_format($this->summary['subtotal'], 0, ',', '.') }}</td>
                        <th>Discount</th>
                        <td>{{ number_format($this->summary['discount'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Tax + Service</th>
                        <td>{{ number_format($this->summary['tax'] + $this->summary['service'], 0, ',', '.') }}</td>
                        <th>Grand Total</th>
                        <td>{{ number_format($this->summary['grand_total'], 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{ $this->table }}

    <x-filament::section heading="By Product" class="mt-6">
        <div class="fi-prose">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Net Sales</th>
                        <th>COGS</th>
                        <th>Gross Profit</th>
                        <th>Gross Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->byProduct as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->category_name }}</td>
                            <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="By Category" class="mt-6">
        <div class="fi-prose">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Net Sales</th>
                        <th>COGS</th>
                        <th>Gross Profit</th>
                        <th>Gross Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->byCategory as $row)
                        <tr>
                            <td>{{ $row->category_name }}</td>
                            <td>{{ number_format($row->qty, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="By Cashier" class="mt-6">
        <div class="fi-prose">
            <table>
                <thead>
                    <tr>
                        <th>Cashier</th>
                        <th>Transactions</th>
                        <th>Net Sales</th>
                        <th>COGS</th>
                        <th>Gross Profit</th>
                        <th>Gross Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->byCashier as $row)
                        <tr>
                            <td>{{ $row->cashier_name }}</td>
                            <td>{{ number_format($row->transactions, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->net_sales, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->cogs_total, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->gross_profit, 0, ',', '.') }}</td>
                            <td>{{ number_format($row->gross_margin, 2, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament::page>
