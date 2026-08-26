<?php

namespace App\Http\Controllers;

use App\Services\RealtimeNotifier;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeController extends Controller
{
    public function versions(Request $request, RealtimeNotifier $realtime)
    {
        $topics = $this->topics($request);

        return response()->json(['versions' => $realtime->versions($topics)]);
    }

    public function stream(Request $request, RealtimeNotifier $realtime): StreamedResponse
    {
        $topics = $this->topics($request);

        return response()->stream(function () use ($realtime, $topics) {
            $last = [];
            $started = time();

            while (! connection_aborted() && time() - $started < 55) {
                $versions = $realtime->versions($topics);
                $changed = array_filter(
                    $versions,
                    fn ($version, $topic) => ($last[$topic] ?? null) !== $version,
                    ARRAY_FILTER_USE_BOTH
                );

                if ($changed) {
                    echo "event: modules\n";
                    echo 'data: ' . json_encode(['versions' => $changed]) . "\n\n";
                    $last = $versions;
                } else {
                    echo ": ping\n\n";
                }

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function topics(Request $request): array
    {
        return collect(explode(',', (string) $request->query('topics')))
            ->map(fn ($topic) => trim($topic))
            ->filter()
            ->values()
            ->all();
    }
}
