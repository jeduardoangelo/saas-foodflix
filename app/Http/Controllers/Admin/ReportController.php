<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Report\OrderReportsService;
use App\Services\Report\XlsxExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    private $reports;

    private $exports;

    public function __construct(OrderReportsService $reports, XlsxExportService $exports)
    {
        $this->reports = $reports;
        $this->exports = $exports;
    }

    public function index(Request $request): View
    {
        return view('admin.reports.index', $this->reports->dashboard($this->filters($request)));
    }

    public function exportOrders(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);
        $rows = $this->reports->orderExportRows($filters);

        return $this->exports->download('pedidos-foodflix.xlsx', 'Pedidos', $rows);
    }

    public function exportCustomers(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);
        $rows = $this->reports->customerExportRows($filters);

        return $this->exports->download('clientes-foodflix.xlsx', 'Clientes', $rows);
    }

    private function filters(Request $request): array
    {
        $request->merge([
            'start_date' => $request->input('start_date') ?: now()->startOfMonth()->toDateString(),
            'end_date' => $request->input('end_date') ?: now()->toDateString(),
        ]);

        $filters = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'customer_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (Carbon::parse($filters['start_date'])->diffInDays(Carbon::parse($filters['end_date'])) > 365) {
            throw ValidationException::withMessages([
                'end_date' => 'Selecione um período de até 366 dias.',
            ]);
        }

        $filters['customer_id'] = $filters['customer_id'] ?? null;

        return $filters;
    }
}
