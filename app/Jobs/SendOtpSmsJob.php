<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TwilioSmsService;
use Exception;
use Illuminate\Support\Facades\Log;

class SendOtpSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $phone;
    public string $code;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $phone, string $code)
    {
        $this->phone = $phone;
        $this->code = $code;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $message = "Votre code de vérification Om-Pay est : {$this->code}. Il expire dans 15 minutes.";

        try {
            $sms = new TwilioSmsService();
            $sms->sendSms($this->phone, $message);
        } catch (Exception $e) {
            Log::error('SendOtpSmsJob error: ' . $e->getMessage(), ['phone' => $this->phone]);
            // Ne pas relancer l'exception pour éviter de bloquer le flow; la job peut être analysée/retryée via la queue.
        }
    }
}
