<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportPdfExport
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function generate()
    {
        $pdf = PDF::loadView('exports.report-pdf', [
            'data' => $this->data,
            'type' => $this->type,
            'date' => now()->format('d/m/Y H:i:s')
        ]);

        return $pdf->download('reporte_' . $this->type . '_' . now()->format('Y-m-d') . '.pdf');
    }
} 