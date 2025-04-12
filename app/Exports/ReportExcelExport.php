<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportExcelExport implements FromCollection, WithHeadings, WithMapping
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
            case 'orders':
                return collect($this->data['orders']);
            case 'projects':
                return collect($this->data['projects']);
            case 'tasks':
                return collect($this->data['tasks']);
            case 'users':
                return collect($this->data['users']);
            default:
                return collect();
        }
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'orders':
                return ['ID', 'Cliente', 'Total', 'Estado', 'Fecha'];
            case 'projects':
                return ['ID', 'Nombre', 'Cliente', 'Estado', 'Fecha Inicio', 'Fecha Fin'];
            case 'tasks':
                return ['ID', 'Título', 'Asignado a', 'Estado', 'Prioridad', 'Fecha'];
            case 'users':
                return ['ID', 'Nombre', 'Email', 'Tareas Asignadas', 'Proyectos'];
            default:
                return [];
        }
    }

    public function map($row): array
    {
        switch ($this->type) {
            case 'orders':
                return [
                    $row->id,
                    $row->customer->name,
                    $row->total,
                    $row->status,
                    $row->created_at->format('d/m/Y')
                ];
            case 'projects':
                return [
                    $row->id,
                    $row->name,
                    $row->client->name,
                    $row->status,
                    $row->start_date->format('d/m/Y'),
                    $row->end_date ? $row->end_date->format('d/m/Y') : 'N/A'
                ];
            case 'tasks':
                return [
                    $row->id,
                    $row->title,
                    $row->assignedUser->name,
                    $row->status,
                    $row->priority,
                    $row->created_at->format('d/m/Y')
                ];
            case 'users':
                return [
                    $row->id,
                    $row->name,
                    $row->email,
                    $row->tasks->count(),
                    $row->projects->count()
                ];
            default:
                return [];
        }
    }
} 