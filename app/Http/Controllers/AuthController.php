<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showAuth()
    {
        if (Auth::check()) {
            return $this->redirectAfterLogin();
        }

        return view('auth');
    }

    public function showProfile()
    {
        if (!session('otp_verified_phone')) {
            return redirect()->route('auth');
        }

        return view('auth-profile');
    }

    public function sendOtp(Request $request)
    {
        $raw = $request->input('phone', '');
        $phone = '09' . preg_replace('/[^0-9]/', '', $raw);

        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            return response()->json([
                'success' => false,
                'message' => 'شماره تلفن نادرست است',
            ], 422);
        }

        $code = OtpCode::generate($phone);

        Log::info("OTP for {$phone}: {$code}");

        session(['otp_phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'کد تایید ارسال شد',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $raw = $request->input('phone', '');
        $phone = '09' . preg_replace('/[^0-9]/', '', $raw);
        $code = $request->input('code', '');

        if (!preg_match('/^09[0-9]{9}$/', $phone) || !preg_match('/^[0-9]{6}$/', $code)) {
            return response()->json([
                'success' => false,
                'message' => 'اطلاعات وارد شده نادرست است',
            ], 422);
        }

        if (!OtpCode::verify($phone, $code)) {
            return response()->json([
                'success' => false,
                'message' => 'کد تایید نادرست یا منقضی شده است',
            ], 422);
        }

        session(['otp_verified_phone' => $phone]);

        $user = User::where('phone', $phone)->first();

        if ($user) {
            Auth::login($user);
            session()->forget(['otp_phone', 'otp_verified_phone']);

            return response()->json([
                'success' => true,
                'redirect' => route('board'),
                'message' => 'ورود موفقیت‌آمیز بود',
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('auth.profile'),
            'message' => 'لطفا اطلاعات خود را تکمیل کنید',
        ]);
    }

    public function completeProfile(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'national_code' => ['nullable', 'string', 'size:10'],
        ]);

        $phone = session('otp_verified_phone');

        if (!$phone) {
            return redirect()->route('auth');
        }

        $user = User::create([
            'phone' => $phone,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'national_code' => $request->national_code,
            'password' => bcrypt(Str::random(32)),
        ]);

        Auth::login($user);
        session()->forget(['otp_phone', 'otp_verified_phone']);

        return redirect()->route('board');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth');
    }

    private function redirectAfterLogin()
    {
        $user = Auth::user();

        if (!$user->isProfileComplete()) {
            return redirect()->route('auth.profile');
        }

        return redirect()->route('board');
    }
}
