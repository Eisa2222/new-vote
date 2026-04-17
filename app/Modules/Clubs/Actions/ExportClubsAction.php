<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Actions;

use App\Modules\Clubs\Models\Club;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportClubsAction
{
    public const COLUMNS = [
        'name_ar', 'name_en', 'short_name', 'status',
        'sports_en', 'leagues_en',
    ];

    public function execute(): StreamedResponse
    {
        $filename = 'clubs-'.now()->format('Y-m-d-His').'.csv';

        return new StreamedResponse(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::COLUMNS);

            Club::with(['sports', 'leagues'])->orderBy('id')->chunk(500, function ($clubs) use ($out) {
                foreach ($clubs as $c) {
                    fputcsv($out, [
                        $c->name_ar,
                        $c->name_en,
                        $c->short_name ?? '',
                        $c->status?->value ?? 'active',
                        $c->sports->pluck('name_en')->implode('|'),
                        $c->leagues->pluck('name_en')->implode('|'),
                    ]);
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function template(): StreamedResponse
    {
        return new StreamedResponse(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::COLUMNS);
            fputcsv($out, [
                'الهلال', 'Al-Hilal', 'HIL', 'active', 'Football', 'Roshn League',
            ]);
            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="clubs-template.csv"',
        ]);
    }
}
