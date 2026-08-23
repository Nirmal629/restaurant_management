<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RealtimeNotifier
{
    public const TOPICS = [
        'pos', 'orders', 'kitchen', 'billing', 'tables', 'reservations', 'inventory', 'menu',
    ];

    public function touch(array|string $topics): void
    {
        foreach ((array) $topics as $topic) {
            if (! in_array($topic, self::TOPICS, true)) {
                continue;
            }

            Cache::forever($this->key($topic), now()->getTimestampMs());
        }
    }

    public function versions(array $topics): array
    {
        return collect($topics)
            ->filter(fn ($topic) => in_array($topic, self::TOPICS, true))
            ->mapWithKeys(fn ($topic) => [$topic => Cache::get($this->key($topic), 0)])
            ->all();
    }

    private function key(string $topic): string
    {
        return "realtime.topic.{$topic}";
    }
}
