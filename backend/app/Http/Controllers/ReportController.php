<?php

namespace App\Http\Controllers;

use App\Service\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
class ReportController extends Controller
{

    public function summary(Request $request, ReportService $reports)
    {
        $data = $reports->summary([
            'dateFrom' => $request->query('dateFrom'),
            'dateTo'   => $request->query('dateTo'),
            'q'        => $request->query('q'),
            'userId'   => $request->integer('userId') ?: null,
            'page'     => max(1, (int)$request->query('page', 1)),
            'perPage'  => max(1, (int)$request->query('perPage', 10)),
        ]);

        return response()->json($data);
    }

    public function summaryPdf(Request $request, ReportService $reports)
    {
        $data = $reports->summary([
            'dateFrom' => $request->query('dateFrom'),
            'dateTo'   => $request->query('dateTo'),
            'q'        => $request->query('q'),
            'userId'   => $request->integer('userId') ?: null,
            'page'     => 1,
            'perPage'  => 1000,
        ]);

        $pdf = Pdf::loadView('reports.summary', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('relatorio.pdf');
    }
}
