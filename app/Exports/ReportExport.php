<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class ReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        switch ($this->type) {
            case 'general':
                return $this->formatGeneralData();
            case 'orders':
                return $this->formatOrdersData();
            case 'tasks':
                return $this->formatTasksData();
            case 'users':
                return $this->formatUsersData();
            default:
                return new Collection();
        }
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'general':
                return ['Métrica', 'Valor'];
            case 'orders':
                return ['ID', 'Cliente', 'Total', 'Estado', 'Fecha'];
            case 'tasks':
                return ['Tarea', 'Orden', 'Asignado a', 'Estado', 'Fecha Límite'];
            case 'users':
                return ['Usuario', 'Tareas Asignadas', 'Proyectos', 'Fecha Registro'];
            default:
                return [];
        }
    }

    private function formatGeneralData()
    {
        return new Collection([
            ['Total Pedidos', $this->data['total_orders']],
            ['Ingresos Totales', number_format($this->data['total_revenue'], 2)],
            ['Valor Promedio', number_format($this->data['average_order_value'], 2)],
        ]);
    }

    private function formatOrdersData()
    {
        return $this->data['orders']->map(function ($order) {
            return [
                $order->id,
                $order->customer->name,
                number_format($order->total_amount, 2),
                ucfirst($order->status),
                $order->created_at->format('d/m/Y')
            ];
        });
    }

    private function formatTasksData()
    {
        return $this->data['tasks']->map(function ($task) {
            return [
                $task->title,
                $task->order ? $task->order->order_number : 'Sin orden',
                $task->assignees->pluck('name')->join(', '),
                ucfirst($task->status),
                $task->due_date->format('d/m/Y')
            ];
        });
    }

    private function formatUsersData()
    {
        return $this->data['users']->map(function ($user) {
            return [
                $user->name,
                $user->tasks->count(),
                isset($user->projects) ? $user->projects->count() : 0,
                $user->created_at->format('d/m/Y')
            ];
        });
    }
}