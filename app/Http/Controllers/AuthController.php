<?php

namespace App\Http\Controllers;

use App\Enum\OtpType;
use App\Mail\OtpEmail;
use App\Models\Role;
use App\Models\Users;
use App\Services\OtpService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

use function Symfony\Component\Clock\now;

class AuthController extends Controller
{
    // Fetch OTP Service
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
        // $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyOtp', 'refresh', 'ForgotPassword', 'ResetPassword']]);
    }

    /**
     * Summary of registerPage
     *
     * @return \Illuminate\Contracts\View\View
     *                                         Purpose
     *                                         Untuk menampilkan halaman register
     */
    public function registerPage()
    {
        return view('auth.register');
    }

    /**
     * Summary of register
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * Purpose
     * Untuk proses validasi dan generate OTP
     */
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'aggree_terms' => 'required|accepted',
        ]);

        if ($validate->fails()) {
            // return response()->json([
            //     'status' => 'errors',
            //     'message' => 'Validation error',
            //     'data' => $validate->errors(),
            // ], 422);
            return redirect()->back()
                ->withErrors($validate)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {

            if (
                RateLimiter::tooManyAttempts('register-otp:' . $request->ip() . '|' . $request->email, 3)
            ) {
                return back()->with('error', 'Terlalu banyak percobaan, coba lagi nanti.');
            }

            RateLimiter::hit("register-otp:" . $request->ip() . '|' . $request->email, 300);

            $otp = $this->otpService->generateForRegister($request->email);

            Mail::to($request->email)->send(new OtpEmail($otp, 'Verifikasi Email'));


            // $request->session()->put('otp_email', $request->email);
            session([
                'register_payload' => [
                    'email' => $request->email,
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                ],
                'otp_type' => OtpType::Register,
                'otp_message' => 'Verifikasi email anda'
            ]);

            // return response()->json([
            //     'status' => 'success',
            //     'message' => 'Registrasi berhasil. Silakan verifikasi email dengan OTP yang dikirim.',
            //     'data' => [
            //         'user_id' => $user->user_id,
            //         'email' => $user->email,
            //     ],
            // ], 201);

            return redirect()->route('verify-otp.form');

        } catch (Exception $e) {
            // return response()->json([
            //     'status' => 'error',
            //     'message' => 'Registrasi gagal: '.$e->getMessage(),
            // ], 500);
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Summary of loginPage
     *
     * @return \Illuminate\Contracts\View\View
     *
     * Puspose
     * Untuk menampilkan halaman login
     */
    public function loginPage()
    {
        return view('auth.login');
    }

    /**
     * Summary of login
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * Purpose
     * Untuk proses validasi dan generate OTP
     */
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validate->fails()) {
            // return response()->json([
            //     'status' => 'error',
            //     'message' => 'Validation error',
            //     'data' => $validate->errors(),
            // ], 422);
            return redirect()->back()
                ->withErrors($validate)
                ->withInput($request->except('password'));
        }

        try {
            $userAccount = Users::where('email', '=', $request->email)->first();

            if (!$userAccount) {
                return redirect()->back()
                    ->with('error', 'Akun tidak ditemukan, silahkan buat akun')
                    ->withInput($request->except('password'));
            }

            if (!Hash::check($request->password, $userAccount->password)) {
                return redirect()->back()
                    ->with('error', 'Password salah, silahkan coba lagi')
                    ->withInput($request->except('password'));
            }

            if ($userAccount->is_active == false) {
                return redirect()->back()
                    ->with('error', 'AKun anda dalam sedang tidak aktif, silahkan hubungi admin')
                    ->withInput($request->except('password'));
            }

            $otp = $this->otpService->generate($userAccount, OtpType::Login);

            Mail::to($userAccount->email)->send(new OtpEmail($otp, 'Verifikasi Login'));

            session([
                'otp_user_id' => $userAccount->user_id,
                'otp_type' => OtpType::Login,
                'otp_message' => 'Verifikasi login'
            ]);

            return redirect()->route('verify-otp.form');
        } catch (Exception $e) {
            Log::error('Something went wrong: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput($request->except('password'));
        }

    }

    /**
     * Summary of verifyOtpForm
     *
     * @return \Illuminate\Contracts\View\View
     *
     * Purpose
     * Menampilkan halaman verifikasi OTP
     */
    public function verifyOtpForm()
    {
        return view('auth.verifyOtp');
    }

    public function verifyOtp(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'otp' => 'required|size:6',
        ]);

        if (!$validate) {
            return redirect()->back()
                ->withErrors($validate)
                ->withInput();
        }

        $otpType = session('otp_type');

        if (!$otpType) {
            return redirect()->route('login.form')->withErrors('OTP tidak valid atau telah kadaluarsa.');
        }

        return match ($otpType) {
            OtpType::Register => $this->verifyEmailOtp($request->otp),
            OtpType::Login => $this->verifyLoginOtp($request->otp),
            OtpType::ResetPassword => $this->verifyForgotPasswordOtp($request->otp),
            default => abort(403),
        };
    }

    /**
     * Purpose
     * Reset dan kirim ulang OTP
     */
    public function resetOtp()
    {
        try {
            $user = Users::find(session('otp_user_id'));
            $type = session('otp_type');

            if (!$user || !$type) {
                return redirect()->route('login.form')
                    ->with('error', 'Sesi OTP berakhir');
            }

            $otp = $this->otpService->generate($user, $type);

            Mail::to($user->email)->send(new OtpEmail($otp, 'Kode OTP'));

            return back()->with('success', 'OTP baru telah dikirim');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Summary of verifyEmailOtp
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * Purpose
     * Untuk proses halaman verifikasi OTP untuk registrasi
     */
    public function verifyEmailOtp($otp)
    {
        try {
            $payload = session('register_payload');

            if (!$payload) {
                return redirect()->route('register.form')
                    ->with('error', 'Sesi registrasi berakhir. Mulai ulang registrasi');
            }

            $result = $this->otpService->verifyRegister($payload['email'], $otp);

            if (!$result['success']) {
                return back()->with('error', $result['message']);
            }

            $defaultRole = Role::where('role_name', 'User')->first();


            Users::create([
                ...$payload,
                'role_id' => $defaultRole,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            session()->forget(['otp_type', 'otp_message', 'register_payload']);
            // $request->session()->put('otp_verified', true);

            return redirect()->route('login.form');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Summary of verifyLoginOtp
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * Purpose
     * Untuk verifikasi OTP untuk login
     */
    public function verifyLoginOtp($request)
    {
        try {
            $otpType = session('otp_type');

            $user = Users::find(session('otp_user_id'));

            if (!$user) {
                return redirect()->route('login.form')
                    ->with('error', 'User tidak ditemukan. Silahkan masukkan email anda')
                    ->withInput();
            }

            $is_valid = $this->otpService->verify($user, $otpType, $request);

            if (!$is_valid['success']) {
                return redirect()->back()
                    ->with('error', $is_valid['message']);
            }

            Auth::guard('web')->login($user);
            session()->save();

            session()->forget(['otp_user_id', 'otp_type', 'otp_message']);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Verifikasi berhasil',
            //     'data' => [
            //         'access_token' => $token,
            //         'token_type' => 'bearer',
            //         'user' => $user
            //     ]
            // ]);\
            return redirect()->route('welcome');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

    }

    /**
     * Summary of logout
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * Purpose
     * Untuk proses logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form')
            ->with('success', 'Anda telah log-out');
    }

    /**
     * Summary of forgotPasswordForm
     *
     * @return \Illuminate\Contracts\View\View
     *
     * Purpose
     * Menampilkan halaman lupa password
     */
    public function forgotPasswordForm()
    {
        return view('auth.forgotPassword');
    }

    /**
     * Summary of forgotPassword
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * Purpose
     * Proses validasi email dan generate OTP
     */
    public function forgotPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validate->fails()) {
            return redirect()->back()
                ->withErrors($validate)
                ->withInput();
        }

        $user = Users::where('email', $request->email)->first();

        // Generate OTP untuk reset password
        $otp = $this->otpService->generate($user, OtpType::ResetPassword);

        // Kirim OTP via email
        Mail::to($user->email)->send(new OtpeMail($otp, 'Reset Password'));

        // Simpan email di session
        session([
            'otp_user_id' => $user->user_id,
            'otp_type' => OtpType::ResetPassword,
            'otp_message' => 'Verifikasi login'
        ]);

        // $request->session()->put('reset_otp_required', true);

        return redirect()->route('verify-otp.form')
            ->with('success', 'OTP telah dikirim ke email Anda.');
    }

    /**
     * Summary of verifyForgotPasswordOtp
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * Purpose
     * Proses verifikasi OTP lupa password
     */
    public function verifyForgotPasswordOtp($request)
    {
        try {
            $otpType = session('otp_type');

            $user = Users::find(session('otp_user_id'));


            if (!$user || !$otpType) {
                return redirect()->back()
                    ->with('error', 'Sesi reset password berakhir')
                    ->withInput();
            }

            $is_valid = $this->otpService->verify($user, $otpType, $request);

            if (!$is_valid['success']) {
                return redirect()->back()
                    ->with('error', $is_valid['message'])
                    ->withInput();
            }

            session()->forget(['otp_type', 'otp_message']);

            return redirect()->route('reset-password.form');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function resetPasswordForm()
    {
        if (!session('reset_email')) {
            return redirect()->route('forgot-password')
                ->with('error', 'Sesi reset password telah berakhir. Silahkan coba lagi');
        }

        return view('auth.resetPassword');
    }

    public function resetPassword(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validate->fails()) {
                return redirect()->back()
                    ->withErrors($validate);
            }

            $email = session('reset_email');

            $user = Users::where('email', $email)->first();

            if (!$user) {
                return redirect()->back()
                    ->with('error', 'User tidak ditemukan. Sesi reset telah berakhir');
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            $request->session()->forget('reset_email');

            return redirect()->route('login.form')
                ->with('success', 'Password telah direset. Silahkan login kembali');
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }

    }

    public function showProfile()
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username,' . $user->user_id . ',user_id',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $update = Users::findOrFail($user->user_id);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->only(['username', 'phone', 'bio']);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $update->update($data);

        return redirect()->route('profile')
            ->with('success', 'Profile berhasil diperbarui.');
    }
}
