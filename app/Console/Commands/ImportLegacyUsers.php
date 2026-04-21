<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyUsers extends Command
{
    protected $signature = 'import:legacy-users
                            {directory : Path with users_legacy_chunk_*.csv files}';

    protected $description = 'Import legacy users from CSV chunks into the users table (idempotent, skips duplicates by email)';

    public function handle(): int
    {
        $directory = rtrim($this->argument('directory'), DIRECTORY_SEPARATOR);

        if (!is_dir($directory)) {
            $this->error("Directory not found: {$directory}");
            return self::FAILURE;
        }

        $files = glob($directory . '/users_legacy_chunk_*.csv');
        sort($files);

        if (empty($files)) {
            $this->error("No users_legacy_chunk_*.csv files matched in {$directory}");
            return self::FAILURE;
        }

        $this->info(sprintf('Found %d CSV file(s) in %s', count($files), $directory));
        $this->newLine();

        $totals = [
            'seen' => 0,
            'inserted' => 0,
            'skipped_duplicate' => 0,
            'skipped_invalid' => 0,
            'file_errors' => 0,
        ];

        $now = Carbon::now();
        $batchSize = 500;

        foreach ($files as $file) {
            $basename = basename($file);
            $rowsInFile = max(0, $this->countDataRows($file));

            $this->line("Processing {$basename} ({$rowsInFile} rows)...");

            $handle = fopen($file, 'r');
            if ($handle === false) {
                $this->error("  Could not open {$basename}");
                $totals['file_errors']++;
                continue;
            }

            // Read header
            $header = fgetcsv($handle);
            if ($header === false || empty($header)) {
                $this->warn("  Empty or malformed header, skipping");
                fclose($handle);
                $totals['file_errors']++;
                continue;
            }

            // Find email column (case-insensitive, strip BOM)
            $emailIdx = null;
            foreach ($header as $i => $col) {
                $normalized = strtolower(trim($col, " \t\n\r\0\x0B\xEF\xBB\xBF"));
                if ($normalized === 'email') {
                    $emailIdx = $i;
                    break;
                }
            }

            if ($emailIdx === null) {
                $this->error("  No 'email' column in header: " . implode(',', $header));
                fclose($handle);
                $totals['file_errors']++;
                continue;
            }

            $bar = $this->output->createProgressBar($rowsInFile);
            $bar->start();

            $batch = [];
            $fileStats = ['seen' => 0, 'inserted' => 0, 'dup' => 0, 'invalid' => 0];

            while (($row = fgetcsv($handle)) !== false) {
                $fileStats['seen']++;
                $totals['seen']++;

                $email = isset($row[$emailIdx]) ? trim($row[$emailIdx]) : '';

                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $fileStats['invalid']++;
                    $totals['skipped_invalid']++;
                    $bar->advance();
                    continue;
                }

                // Lowercase email for consistency (matches typical normalization)
                $email = strtolower($email);

                $localPart = explode('@', $email)[0];
                $name = $localPart !== '' ? $localPart : 'user';

                $batch[] = [
                    'id' => Str::uuid()->toString(),
                    'name' => $name,
                    'email' => $email,
                    'source' => 'legacy_community',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= $batchSize) {
                    [$ins, $skip] = $this->flushBatch($batch);
                    $fileStats['inserted'] += $ins;
                    $fileStats['dup'] += $skip;
                    $totals['inserted'] += $ins;
                    $totals['skipped_duplicate'] += $skip;
                }

                $bar->advance();
            }

            fclose($handle);

            // Flush remaining
            if (!empty($batch)) {
                [$ins, $skip] = $this->flushBatch($batch);
                $fileStats['inserted'] += $ins;
                $fileStats['dup'] += $skip;
                $totals['inserted'] += $ins;
                $totals['skipped_duplicate'] += $skip;
            }

            $bar->finish();
            $this->newLine();
            $this->line(sprintf(
                '  seen=%d  inserted=%d  dup=%d  invalid=%d',
                $fileStats['seen'],
                $fileStats['inserted'],
                $fileStats['dup'],
                $fileStats['invalid']
            ));
        }

        $this->newLine();
        $this->info('===== IMPORT SUMMARY =====');
        $this->line(sprintf('Files processed:   %d', count($files) - $totals['file_errors']));
        $this->line(sprintf('Files errored:     %d', $totals['file_errors']));
        $this->line(sprintf('Rows seen:         %d', $totals['seen']));
        $this->line(sprintf('Inserted:          %d', $totals['inserted']));
        $this->line(sprintf('Skipped (dup):     %d', $totals['skipped_duplicate']));
        $this->line(sprintf('Skipped (invalid): %d', $totals['skipped_invalid']));

        return $totals['file_errors'] === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Insert batch via insertOrIgnore. Returns [inserted_count, skipped_count].
     * By reference batch is cleared after insert.
     */
    private function flushBatch(array &$batch): array
    {
        $size = count($batch);
        if ($size === 0) {
            return [0, 0];
        }
        $inserted = DB::table('users')->insertOrIgnore($batch);
        $batch = [];
        return [$inserted, $size - $inserted];
    }

    /**
     * Quick count of data rows (excluding header) for progress bar.
     */
    private function countDataRows(string $file): int
    {
        $count = 0;
        $handle = fopen($file, 'r');
        if ($handle === false) {
            return 0;
        }
        while (fgets($handle) !== false) {
            $count++;
        }
        fclose($handle);
        return max(0, $count - 1); // minus header
    }
}
