<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Brand rename (owner, 2026-11-09): ব্লাডবন্ড → রক্তদূত in the admin-editable
 * About sections that were seeded before the rename.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->replace('ব্লাডবন্ড', 'রক্তদূত');
    }

    public function down(): void
    {
        $this->replace('রক্তদূত', 'ব্লাডবন্ড');
    }

    private function replace(string $from, string $to): void
    {
        DB::table('about_sections')->orderBy('id')->get(['id', 'title', 'body'])
            ->each(function ($row) use ($from, $to) {
                $title = str_replace($from, $to, $row->title);
                $body = str_replace($from, $to, $row->body);

                if ($title !== $row->title || $body !== $row->body) {
                    DB::table('about_sections')->where('id', $row->id)->update(['title' => $title, 'body' => $body]);
                }
            });

        Cache::forget('content.about');
    }
};
