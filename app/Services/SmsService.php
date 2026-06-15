<?php

namespace App\Services;

use App\Models\SmsGatewaySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected SmsGatewaySetting $settings;

    public function __construct()
    {
        $this->settings = SmsGatewaySetting::current();
    }

    public function send(string $to, string $message): bool
    {
        if (! $this->settings->is_enabled) {
            Log::info("SMS (gateway disabled) → {$to}: {$message}");
            return false;
        }

        try {
            return match ($this->settings->provider) {
                'twilio'  => $this->sendTwilio($to, $message),
                'vonage'  => $this->sendVonage($to, $message),
                'aws_sns' => $this->sendAwsSns($to, $message),
                default   => $this->sendCustom($to, $message),
            };
        } catch (\Throwable $e) {
            Log::error("SMS send failed [{$this->settings->provider}]: " . $e->getMessage());
            return false;
        }
    }

    protected function sendTwilio(string $to, string $message): bool
    {
        $response = Http::withBasicAuth($this->settings->api_key, $this->settings->api_secret)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->settings->api_key}/Messages.json", [
                'From' => $this->settings->from_number,
                'To'   => $to,
                'Body' => $message,
            ]);

        return $response->successful();
    }

    protected function sendVonage(string $to, string $message): bool
    {
        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key'    => $this->settings->api_key,
            'api_secret' => $this->settings->api_secret,
            'from'       => $this->settings->from_number,
            'to'         => $to,
            'text'       => $message,
        ]);

        return $response->successful()
            && ($response->json('messages.0.status') === '0');
    }

    protected function sendAwsSns(string $to, string $message): bool
    {
        // AWS SNS via SDK — requires aws/aws-sdk-php.
        // Log and return false until the SDK is installed.
        Log::warning("AWS SNS SMS: install aws/aws-sdk-php and implement sendAwsSns().");
        return false;
    }

    protected function sendCustom(string $to, string $message): bool
    {
        if (! $this->settings->endpoint_url) {
            Log::warning('Custom SMS gateway: endpoint_url is not configured.');
            return false;
        }

        $payload = array_merge($this->settings->extra_params ?? [], [
            'to'      => $to,
            'message' => $message,
            'from'    => $this->settings->from_number,
        ]);

        $request = Http::withHeaders([
            'Authorization' => "Bearer {$this->settings->api_key}",
        ]);

        if ($this->settings->api_secret) {
            $request = $request->withBasicAuth($this->settings->api_key, $this->settings->api_secret);
        }

        $response = $request->post($this->settings->endpoint_url, $payload);

        return $response->successful();
    }
}
