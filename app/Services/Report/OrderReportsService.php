<?php

namespace App\Services\Report;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderReportsService
{
    private const ORDER_STATUSES = [
        'novo' => 'Novo',
        'em_preparo' => 'Em preparo',
        'saiu_para_entrega' => 'Saiu para entrega',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
    ];

    public function dashboard(array $filters): array
    {
        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end = Carbon::parse($filters['end_date'])->endOfDay();
        $orders = $this->ordersQuery($filters, $start, $end);
        $paidOrders = (clone $orders)->where('payment_status', 'pago');
        $paidOrderCount = (clone $paidOrders)->count();
        $periodSales = (float) (clone $paidOrders)->sum('total');

        $todayOrders = Order::query()
            ->whereBetween('created_at', [now()->copy()->startOfDay(), now()->copy()->endOfDay()])
            ->where('payment_status', 'pago');

        if ($filters['customer_id']) {
            $todayOrders->where('user_id', $filters['customer_id']);
        }

        $statusCounts = (clone $orders)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusTotal = (int) $statusCounts->sum();
        $statuses = [];
        foreach (self::ORDER_STATUSES as $status => $label) {
            $count = (int) $statusCounts->get($status, 0);
            $statuses[] = [
                'key' => $status,
                'label' => $label,
                'count' => $count,
                'percent' => $statusTotal > 0 ? (int) round(($count / $statusTotal) * 100) : 0,
            ];
        }

        foreach ($statusCounts as $status => $count) {
            if (! isset(self::ORDER_STATUSES[$status])) {
                $statuses[] = [
                    'key' => $status,
                    'label' => ucfirst(str_replace('_', ' ', $status)),
                    'count' => (int) $count,
                    'percent' => $statusTotal > 0 ? (int) round(($count / $statusTotal) * 100) : 0,
                ];
            }
        }

        $customerQuery = $this->customerReportQuery($start, $end);
        $customers = (clone $customerQuery)
            ->orderByDesc('paid_total')
            ->orderBy('users.name')
            ->paginate(8, ['*'], 'customers_page')
            ->withQueryString();

        $customerId = $filters['customer_id'];
        $selectedCustomer = $customerId
            ? User::withTrashed()->select('id', 'name', 'email')->find($customerId)
            : null;

        $orderRows = (clone $orders)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'orders_page')
            ->withQueryString();

        return [
            'filters' => $filters,
            'selectedCustomer' => $selectedCustomer,
            'metrics' => [
                'today_sales' => (float) $todayOrders->sum('total'),
                'period_sales' => $periodSales,
                'order_count' => (int) (clone $orders)->count(),
                'average_ticket' => $paidOrderCount > 0 ? $periodSales / $paidOrderCount : 0,
                'customer_count' => (int) (clone $orders)->distinct()->count('user_id'),
            ],
            'salesTrend' => $this->salesTrend($filters, $start, $end),
            'statuses' => $statuses,
            'topProducts' => $this->topProducts($filters, $start, $end),
            'customers' => $customers,
            'orders' => $orderRows,
        ];
    }

    public function orderExportRows(array $filters): array
    {
        $orders = $this->ordersQuery(
            $filters,
            Carbon::parse($filters['start_date'])->startOfDay(),
            Carbon::parse($filters['end_date'])->endOfDay()
        )->with('user')->orderByDesc('created_at')->get();

        $rows = [[
            'Número do pedido', 'Data e hora', 'Cliente', 'E-mail', 'Status do pedido',
            'Status do pagamento', 'Forma de pagamento', 'Recebimento', 'Subtotal',
            'Taxa de entrega', 'Total',
        ]];

        foreach ($orders as $order) {
            $rows[] = [
                (string) $order->order_number,
                $order->created_at->format('d/m/Y H:i'),
                optional($order->user)->name ?: 'Cliente removido',
                optional($order->user)->email ?: '',
                $this->orderStatusLabel($order->status),
                $this->paymentStatusLabel($order->payment_status),
                $this->paymentMethodLabel($order->payment_method),
                $order->delivery_method === 'retirada' ? 'Retirada' : 'Entrega',
                (float) $order->subtotal,
                (float) $order->delivery_fee,
                (float) $order->total,
            ];
        }

        return $rows;
    }

    public function customerExportRows(array $filters): array
    {
        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end = Carbon::parse($filters['end_date'])->endOfDay();
        $customers = $this->customerReportQuery($start, $end)
            ->orderByDesc('paid_total')
            ->orderBy('users.name')
            ->get();

        $rows = [[
            'Cliente', 'E-mail', 'Telefone', 'Pedidos no período',
            'Total pago no período', 'Último pedido',
        ]];

        foreach ($customers as $customer) {
            $rows[] = [
                $customer->name,
                $customer->email,
                $customer->phone ?: '',
                (int) $customer->orders_count,
                (float) $customer->paid_total,
                $customer->last_order_at ? Carbon::parse($customer->last_order_at)->format('d/m/Y H:i') : '',
            ];
        }

        return $rows;
    }

    private function ordersQuery(array $filters, Carbon $start, Carbon $end)
    {
        return Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($filters['customer_id'], function ($query, $customerId) {
                $query->where('user_id', $customerId);
            });
    }

    private function customerReportQuery(Carbon $start, Carbon $end): Builder
    {
        return DB::table('users')
            ->leftJoin('orders', function ($join) use ($start, $end) {
                $join->on('orders.user_id', '=', 'users.id')
                    ->whereBetween('orders.created_at', [$start, $end]);
            })
            ->select(
                'users.id', 'users.name', 'users.email', 'users.phone',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw("COALESCE(SUM(CASE WHEN orders.payment_status = 'pago' THEN orders.total ELSE 0 END), 0) as paid_total"),
                DB::raw('MAX(orders.created_at) as last_order_at')
            )
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->havingRaw('COUNT(orders.id) > 0');
    }

    private function topProducts(array $filters, Carbon $start, Carbon $end): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.payment_status', 'pago')
            ->when($filters['customer_id'], function ($query, $customerId) {
                $query->where('orders.user_id', $customerId);
            })
            ->select(
                'order_items.product_name_snapshot as name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.subtotal) as sales_total')
            )
            ->groupBy('order_items.product_name_snapshot')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get();
    }

    private function salesTrend(array $filters, Carbon $start, Carbon $end): array
    {
        $dailyRows = $this->ordersQuery($filters, $start, $end)
            ->where('payment_status', 'pago')
            ->selectRaw('DATE(created_at) as period_date, SUM(total) as revenue')
            ->groupBy('period_date')
            ->orderBy('period_date')
            ->get();

        $byDate = [];
        foreach ($dailyRows as $row) {
            $byDate[$row->period_date] = (float) $row->revenue;
        }

        $monthly = $start->diffInDays($end) > 60;
        $points = [];
        $max = 0;

        if ($monthly) {
            $monthTotals = [];
            foreach ($byDate as $date => $amount) {
                $month = Carbon::parse($date)->format('Y-m');
                $monthTotals[$month] = ($monthTotals[$month] ?? 0) + $amount;
            }

            $period = new \DatePeriod(
                $start->copy()->startOfMonth(),
                new \DateInterval('P1M'),
                $end->copy()->startOfMonth()->addMonth()
            );

            foreach ($period as $date) {
                $key = $date->format('Y-m');
                $value = (float) ($monthTotals[$key] ?? 0);
                $points[] = ['label' => $date->format('m/Y'), 'value' => $value];
                $max = max($max, $value);
            }
        } else {
            $period = new \DatePeriod(
                $start->copy()->startOfDay(),
                new \DateInterval('P1D'),
                $end->copy()->startOfDay()->addDay()
            );
            $dayCount = $start->diffInDays($end) + 1;
            $index = 0;

            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $value = (float) ($byDate[$key] ?? 0);
                $points[] = [
                    'label' => $date->format('d/m'),
                    'show_label' => $dayCount <= 14 || $index % 7 === 0 || $index === $dayCount - 1,
                    'value' => $value,
                ];
                $max = max($max, $value);
                $index++;
            }
        }

        foreach ($points as &$point) {
            $point['percent'] = $max > 0 ? max(3, (int) round(($point['value'] / $max) * 100)) : 0;
        }
        unset($point);

        return ['points' => $points, 'monthly' => $monthly, 'total' => array_sum(array_column($points, 'value'))];
    }

    private function orderStatusLabel(string $status): string
    {
        return self::ORDER_STATUSES[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    private function paymentStatusLabel(string $status): string
    {
        $labels = ['pago' => 'Pago', 'pendente' => 'Pendente', 'falhou' => 'Falhou', 'estornado' => 'Estornado'];

        return $labels[$status] ?? ucfirst($status);
    }

    private function paymentMethodLabel(string $method): string
    {
        $labels = ['pix' => 'Pix', 'cartao_credito' => 'Cartão de crédito', 'credit_card' => 'Cartão de crédito'];

        return $labels[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }
}
