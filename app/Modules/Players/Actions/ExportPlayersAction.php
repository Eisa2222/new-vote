<?php

declare(strict_types=1);

namespace App\Modules\Players\Actions;

use App\Modules\Players\Models\Player;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams every active/inactive player to a UTF-8 CSV (with BOM so
 * Excel opens Arabic cleanly). Columns match the ImportPlayersAction
 * header so export → edit → re-import round-trips without surprises.
 */
final class ExportPlayersAction
{
    public const COLUMNS = [
        'name_ar', 'name_en', 'club_name_en', 'sport_name_en',
        'position', 'jersey_number', 'is_captain',
        'national_id', 'mobile_number', 'status',
    ];

    public function execute(): StreamedResponse
    {
        $filename = 'players-'.now()->format('Y-m-d-His').'.csv';

        return new StreamedResponse(function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM → Excel treats the file as UTF-8 instead of Windows-1252.
            fwrite($out, "\xEF\xBB\xBF");
            // Excel-specific hint: force comma as separator even on locales
            // (e.g. ar-SA, fr-FR) where the system default is semicolon.
            fwrite($out, "sep=,\r\n");
            fputcsv($out, self::COLUMNS);

            Player::with(['club', 'sport'])->orderBy('id')->chunk(500, function ($players) use ($out) {
                foreach ($players as $p) {
                    fputcsv($out, [
                        $p->name_ar,
                        $p->name_en,
                        $p->club?->name_en ?? '',
                        $p->sport?->name_en ?? '',
                        $p->position?->value ?? '',
                        $p->jersey_number ?? '',
                        $p->is_captain ? '1' : '0',
                        $p->national_id ?? '',
                        $p->mobile_number ?? '',
                        $p->status?->value ?? 'active',
                    ]);
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** Empty template with just the header row — used as a starter CSV. */
    public function template(): StreamedResponse
    {
        return new StreamedResponse(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fwrite($out, "sep=,\r\n");
            fputcsv($out, self::COLUMNS);
            // One sample row so users see the expected shape.
            fputcsv($out, [
                'أحمد علي', 'Ahmed Ali', 'Al-Hilal', 'Football',
                'attack', '9', '0', '1012345678', '0501234567', 'active',
            ]);
            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="players-template.csv"',
        ]);
    }
}
