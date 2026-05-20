<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailJsService
{
    protected $serviceId;
    protected $templateId;
    protected $publicKey;
    protected $privateKey;

    public function __construct()
    {
        $this->serviceId = env('EMAILJS_SERVICE_ID');
        $this->templateId = env('EMAILJS_TEMPLATE_ID');
        $this->publicKey = env('EMAILJS_PUBLIC_KEY');
        $this->privateKey = env('EMAILJS_PRIVATE_KEY');
    }

    /**
     * Send an OTP email via EmailJS REST API.
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $otpCode
     * @return bool
     */
    public function sendOtp($toEmail, $toName, $otpCode)
    {
        // Toujours loguer l'OTP en local pour le développement
        Log::info("CODE OTP pour $toEmail ($toName) : $otpCode");

        // Si les identifiants ne sont pas configurés, on s'arrête là sans erreur
        if (!$this->serviceId || !$this->publicKey || $this->serviceId === 'service_id') {
            Log::warning('EmailJS n\'est pas configuré. Le code OTP a été envoyé uniquement dans les logs.');
            return true;
        }

        try {
            $response = Http::withoutVerifying()->post('https://api.emailjs.com/api/v1.0/email/send', [
                'service_id' => $this->serviceId,
                'template_id' => $this->templateId,
                'user_id' => $this->publicKey,
                'accessToken' => $this->privateKey,
                'template_params' => [
                    'to_email' => $toEmail,
                    'to_name' => $toName,
                    'otp_code' => $otpCode,
                    'app_name' => config('app.name'),
                ],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('EmailJS Error Details', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('EmailJS Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
