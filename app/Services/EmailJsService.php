<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;

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
     * Send an OTP email via Laravel Mail or EmailJS.
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

        // Tenter l'envoi via Laravel Mail (recommandé pour SMTP/Mailtrap)
        try {
            Mail::to($toEmail)->send(new OTPMail($toName, $otpCode));
            Log::info("OTP envoyé avec succès via Laravel Mail à $toEmail");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi via Laravel Mail : " . $e->getMessage());
        }

        // Si EmailJS est configuré, on tente aussi via leur API
        if (!$this->serviceId || !$this->publicKey || $this->serviceId === 'service_id') {
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
                Log::info("OTP envoyé via EmailJS à $toEmail");
                return true;
            }

            Log::error('EmailJS API Error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error("EmailJS Connection Error: " . $e->getMessage());
        }

        return true;
    }
}
