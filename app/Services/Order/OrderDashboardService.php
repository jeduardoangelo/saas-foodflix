<?php

namespace App\Services\Order;

use App\Models\Order;
use DomainException;
use Illuminate\Support\Facades\DB;

class OrderDashboardService
{
    public const COLUMNS = [
        'novo' => 'Novos Pedidos',
        'em_preparo' => 'Em Preparo',
        'saiu_para_entrega' => 'Saiu para Entrega',
        'concluido' => 'Concluídos',
    ];

    private const NEXT_STATUS = [
        'novo' => 'em_preparo',
        'em_preparo' => 'saiu_para_entrega',
        'saiu_para_entrega' => 'concluido',
    ];

    public function dashboard(): array
    {
        $orders = Order::query()
            ->with(['user', 'items', 'payment'])
            ->whereBetween('created_at', $this->todayPeriod())
            ->whereIn('status', array_keys(self::COLUMNS))
            ->orderBy('created_at')
            ->get();

        $grouped = $orders->groupBy('status');
        $columns = [];

        foreach (self::COLUMNS as $status => $label) {
            $columnOrders = $grouped->get($status, collect());
            $columns[$status] = [
                'status' => $status,
                'label' => $label,
                'orders' => $columnOrders,
                'count' => $columnOrders->count(),
            ];
        }

        return [
            'metrics' => $this->metrics(),
            'columns' => $columns,
        ];
    }

    public function metrics(): array
    {
        $statusCounts = Order::query()
            ->whereBetween('created_at', $this->todayPeriod())
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $revenue = Order::query()
            ->whereBetween('created_at', $this->todayPeriod())
            ->where('payment_status', 'pago')
            ->sum('total');

        return [
            'new_orders' => (int) $statusCounts->get('novo', 0),
            'revenue_cents' => (int) round(((float) $revenue) * 100),
            'completed_orders' => (int) $statusCounts->get('concluido', 0),
            'in_progress_orders' => (int) $statusCounts->get('em_preparo', 0)
                + (int) $statusCounts->get('saiu_para_entrega', 0),
        ];
    }

    public function advance(int $orderId): Order
    {
        return DB::transaction(function () use ($orderId) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            $nextStatus = self::NEXT_STATUS[$order->status] ?? null;

            if ($nextStatus === null) {
                throw new DomainException('Este pedido já está concluído e não pode mais avançar.');
            }

            $order->update(['status' => $nextStatus]);

            return $order->fresh(['user', 'items', 'payment']);
        });
    }

    public function statusLabel(string $status): string
    {
        return self::COLUMNS[$status] ?? $status;
    }

    public function nextStatus(string $status): ?string
    {
        return self::NEXT_STATUS[$status] ?? null;
    }

    private function todayPeriod(): array
    {
        return [now()->copy()->startOfDay(), now()->copy()->endOfDay()];
    }
}
