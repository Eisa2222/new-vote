<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Actions;

use App\Modules\Clubs\Models\Club;
use App\Modules\Leagues\Models\League;
use App\Modules\Shared\Enums\ActiveStatus;
use App\Modules\Sports\Models\Sport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Parses a UTF-8 CSV of clubs and upserts them by name_en.
 * Sports and leagues are matched by name_en and synced on the pivot
 * tables. Missing sports/leagues are reported in `skipped` and not
 * auto-created (data hygiene beats convenience here).
 */
final class ImportClubsAction
{
    public function execute(UploadedFile $file): array
    {
        $created = 0;
        $updated = 0;
        $skipped = [];

        $fh = fopen($file->getRealPath(), 'r');
        if (! $fh) {
            return ['created' => 0, 'updated' => 0, 'skipped' => [['row' => 0, 'error' => 'Cannot open file']]];
        }

        $bom = fread($fh, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($fh);

        // Detect the column delimiter. Arabic Excel saves CSV files with
        // ';' instead of ',', so we can't assume comma. Either:
        //   - An explicit "sep=X" hint line is present, OR
        //   - We sniff the first real line and pick whichever of , ; \t
        //     appears most often.
        $delimiter  = ',';
        $peek       = ftell($fh);
        $first      = fgets($fh);
        if ($first !== false && preg_match('/^\s*sep=(.)/i', ltrim($first), $m)) {
            $delimiter = $m[1];
            // consumed the hint line — continue from next line
        } else {
            // Put the line back, then sniff it.
            fseek($fh, $peek);
            $sample = $first ?: '';
            $counts = [',' => substr_count($sample, ','), ';' => substr_count($sample, ';'), "\t" => substr_count($sample, "\t")];
            arsort($counts);
            $delimiter = array_key_first($counts) ?: ',';
            if ($counts[$delimiter] === 0) $delimiter = ',';
        }

        $header = fgetcsv($fh, 0, $delimiter);
        if (! $header) {
            fclose($fh);
            return ['created' => 0, 'updated' => 0, 'skipped' => [['row' => 1, 'error' => 'Empty file']]];
        }
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        $rowNum = 1;
        DB::transaction(function () use ($fh, $header, $delimiter, &$created, &$updated, &$skipped, &$rowNum) {
            while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
                $rowNum++;
                if (count($row) === 1 && trim((string) $row[0]) === '') continue;

                $data = array_combine($header, array_pad($row, count($header), ''));
                $name_ar   = trim((string)($data['name_ar'] ?? ''));
                $name_en   = trim((string)($data['name_en'] ?? ''));
                $shortName = trim((string)($data['short_name'] ?? ''));
                $status    = strtolower(trim((string)($data['status'] ?? 'active')));
                $sportsRaw = trim((string)($data['sports_en'] ?? ''));
                $leaguesRaw = trim((string)($data['leagues_en'] ?? ''));

                if (! $name_en) {
                    $skipped[] = ['row' => $rowNum, 'error' => 'Missing name_en'];
                    continue;
                }

                $club = Club::where('name_en', $name_en)->first();
                $payload = [
                    'name_ar'    => $name_ar ?: $name_en,
                    'name_en'    => $name_en,
                    'short_name' => $shortName ?: null,
                    'status'     => in_array($status, ['active', 'inactive'], true)
                                        ? $status : ActiveStatus::Active->value,
                ];

                try {
                    if ($club) {
                        $club->update($payload);
                        $updated++;
                    } else {
                        $club = Club::create($payload);
                        $created++;
                    }

                    // Resolve sports (pipe-separated list of English names).
                    if ($sportsRaw !== '') {
                        $names = array_filter(array_map('trim', explode('|', $sportsRaw)));
                        $ids   = Sport::whereIn('name_en', $names)->pluck('id', 'name_en');
                        $club->sports()->sync($ids->values()->all());
                        foreach (array_diff($names, $ids->keys()->all()) as $missing) {
                            $skipped[] = ['row' => $rowNum, 'error' => "Sport '{$missing}' not found (club saved without it)"];
                        }
                    }

                    // Resolve leagues (pipe-separated).
                    if ($leaguesRaw !== '') {
                        $names = array_filter(array_map('trim', explode('|', $leaguesRaw)));
                        $ids   = League::whereIn('name_en', $names)->pluck('id', 'name_en');
                        $club->leagues()->sync($ids->values()->all());
                        foreach (array_diff($names, $ids->keys()->all()) as $missing) {
                            $skipped[] = ['row' => $rowNum, 'error' => "League '{$missing}' not found (club saved without it)"];
                        }
                    }
                } catch (\Throwable $e) {
                    $skipped[] = ['row' => $rowNum, 'error' => $e->getMessage()];
                }
            }
        });

        fclose($fh);

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }
}
