<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailJsService;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

use App\Mail\SecretCodeMail;
use Illuminate\Support\Facades\Mail;

class RegisteredUserController extends Controller
{
    protected $emailJs;

    public function __construct(EmailJsService $emailJs)
    {
        $this->emailJs = $emailJs;
    }

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|Response
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'accepts_terms' => 'required|boolean',
        ]);

        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user = User::create([
            'accepted_terms' => $request->accepts_terms,
            'accepted_terms_at' => now(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'joueur',
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10),
            'is_verified' => false,
            'secret_code' => 'CP-' . strtoupper(Str::random(8)), // Générer un code secret
        ]);

        event(new Registered($user));

        // Envoi de l'OTP (via EmailJS + Laravel Mail)
        $this->emailJs->sendOtp($user->email, $user->name, $otpCode);

        return Inertia::render('Auth/Register', [
            'status' => 'otp_sent',
            'email' => $user->email,
        ]);
    }

    /**
     * Verify OTP and login user.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'otp_code' => 'Le code OTP est incorrect.',
            ]);
        }

        if ($user->otp_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'otp_code' => 'Le code OTP a expiré.',
            ]);
        }

        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Envoyer le code secret par mail une fois vérifié
        try {
            Mail::to($user->email)->send(new SecretCodeMail($user));
        } catch (\Exception $e) {
            Log::error("Erreur envoi code secret : " . $e->getMessage());
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Resend OTP.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Utilisateur non trouvé.');
        }

        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->emailJs->sendOtp($user->email, $user->name, $otpCode);

        return back()->with('status', 'Un nouveau code OTP a été envoyé.');
    }
}
