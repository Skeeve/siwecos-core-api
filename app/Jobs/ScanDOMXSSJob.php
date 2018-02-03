<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use App\Scan;
use GuzzleHttp\Psr7\Request;

class ScanDOMXSSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scan;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Scan $scan)
    {
        $this->scan = $scan;        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->scan->update([
            'status' => 2
        ]);

        $scanResult = $this->scan->results()->create([
            'scanner_type' => 'domxss',
        ]);

        $callbackUrl = route('callback', [ 'scanId' => $scanResult->id ]);

        $client = new Client();
        $request = new Request('POST', env('DOMXSS_SCANNER_URL') . '/api/v1/domxss', [], \GuzzleHttp\json_encode([
                'url' => $this->scan->url,
                'callbackurls' => [$callbackUrl]
        ]));

        $response = $client->send($request);
    }

}
