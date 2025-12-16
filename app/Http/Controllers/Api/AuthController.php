<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpEmail;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Role;
use App\Enum\OtpType;
use App\Models\Users;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Payload;

class AuthController extends Controller
{
    /**
     * Summary of otpService
     * @var 
     * 
     * Purpose
     * Memanggil class OtpService
     */
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Summary of register
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Purpose
     * Register via API
     */
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'string', 'max:100', 'unique:users,email', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'aggree_terms' => ['required', 'accepted']
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal validasi data',
                'data' => $validate->errors()
            ], 422);
        }

        try {
            if (
                RateLimiter::tooManyAttempts('register-otp:' . $request->ip() . '|' . $request->email, 3)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Coba lagi nanti',
                    'data' => []
                ], 429);
            }

            RateLimiter::hit("register-otp:{$request->ip()}|{$request->email}", 300);

            $otp = $this->otpService->generateForRegister($request->email);

            Mail::to($request->email)->send(new OtpEmail($otp, 'Verifikasi Email'));

            return response()->json([
                'success' => true,
                'message' => 'OTP telah dikirim. Silahkan melanjutkan registrasi.',
                'data' => [
                    'otp_context' => encrypt([
                        'type' => OtpType::Register,
                        'email' => $request->email,
                        'payload' => [
                            'username' => $request->username,
                            'password' => Hash::make($request->password),
                            'phone' => $request->phone,
                        ],
                    ]),
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kirim OTP gagal',
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Summary of login
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * Purpose
     * Login via API
     */
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal validasi data',
                'data' => $validate->errors()
            ], 422);
        }

        try {
            $user = Users::where('email', '=', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan. Silahkan registrasi',
                    'data' => [],
                ], 404);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password tidak sesuai. Tolong coba lagi',
                    'data' => [],
                ], 401);
            }

            if ($user->is_active == false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun anda dalam keadaan tidak aktif. Silahkan hubungi admin',
                    'data' => [],
                ], 403);
            }

            $otp = $this->otpService->generate($user, OtpType::Login);

            Mail::to($user->email)->send(new OtpEmail($otp, 'Verifikasi Login'));

            session()->put('otp_email', $user->email);
            session()->put('otp_type', OtpType::Login);
            session()->put('otp_message', 'Verifikasi login');
            session()->save();

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email anda. Silahkan verifikasi login anda',
                'data' => [
                    'email' => $user->email,
                    'requires_otp' => true,
                    'otp_context' => encrypt([
                        'otp_type' => OtpType::Login,
                        'user_id' => $user->user_id
                    ])
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kirim OTP gagal',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => ['required', 'email', 'string', 'exists:users,email']
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi data gagal',
                    'data' => $validate->errors()
                ], 422);
            }

            $user = Users::where('email', '=', $request->email)->first();

            $otp = $this->otpService->generate($user, OtpType::ResetPassword);

            Mail::to($user->email)->send(new OtpEmail($otp, 'Verifikasi Lupa Password'));

            session()->put('reset_email', $user->email);
            session()->put('otp_type', OtpType::ResetPassword);
            session()->put('otp_message', 'Verifikasi lupa password');
            session()->save();

            return response()->json([
                'success' => true,
                'message' => 'OTP telah dikirim. Silahkan reset password anda'
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kirim OTP gagal',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout gagal. Silahkan coba lagi dalam beberapa menit',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $request->user()->load('role')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mendapatkan data pengguna. Silahkan coba lagi dalam beberapa menit',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'username' => 'sometimes|string|max:100|unique:users,username,' . $user->user_id . ',user_id',
                'phone' => 'nullable|string|max:20',
                'bio' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update($request->only(['username', 'phone', 'bio']));

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui',
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengambli data pengguna. Silahkan coba lagi dalam beberapa menit',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'otp' => ['required', 'size:6'],
            'otp_context' => ['required|string']
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal validasi data',
                'data' => $validate->errors()
            ], 422);
        }

        $context = decrypt($request->otp_request);

        if (!$context) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau kadaluarsa',
                'data' => []
            ], 401);
        }

        return match ($context['type']) {
            OtpType::Register => $this->verifyEmailOtp($request),
            OtpType::Login => $this->verifyLoginOtp($request),
            OtpType::ResetPassword => $this->verifyForgotPasswordOtp($request),
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
            if (session('otp_type') == OtpType::Register) {
                $payload = session('register_payload');

                Cache::forget("register_otp:{$payload['email']}");

                $otp = random_int(100000, 999999);

                Mail::to($payload['email'])->send(new OtpEmail($otp, 'Verifikasi Email'));

                Cache::put("register_otp:{$payload['email']}", Hash::make($otp), Carbon::now()->addMinutes(5));

                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim',
                    'data' => [
                        'email' => $payload['email'],
                        'otp_type' => session('otp_type')
                    ]
                ], 200);
            }
            if (session('otp_type') == OtpType::Login) {
                $email = session('otp_email');
                $otpType = session('otp_type');

                $userAccount = Users::where('email', '=', $email)->first();

                $otp = $this->otpService->generate($userAccount, $otpType);

                Mail::to($userAccount->email)->send(new OtpEmail($otp, 'Verifikasi Login'));

                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim',
                    'data' => [
                        'email' => $userAccount->email,
                        'otp_type' => session('otp_type')
                    ]
                ], 200);
            }
            if (session('otp_type') == OtpType::ResetPassword) {
                $email = session('reset_email');
                $otpType = session('otp_type');

                $userAccount = Users::where('email', '=', $email)->first();

                $otp = $this->otpService->generate($userAccount, $otpType);

                Mail::to($userAccount->email)->send(new OtpEmail($otp, 'Verifikasi Reset Password'));

                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP telah dikirim',
                    'data' => [
                        'email' => $userAccount->email,
                        'otp_type' => session('otp_type')
                    ]
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset OTP',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyEmailOtp($request)
    {
        try {
            $context = decrypt($request->otp_context);

            $result = $this->otpService->verifyRegister(
                $context['email'],
                $request->otp
            );

            if (!$result['success']) {
                return response()->json($result, 401);
            }

            $defaultRole = Role::where('role_name', 'User')->first();

            $user = Users::create([
                'email' => $context['email'],
                ...$context['payload'],
                'role_id' => $defaultRole,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi email berhasil dan akun anda telah dibuat',
                'data' => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi email gagal. Regitrasi telah gagal.',
                'data' => $e->getMessage()
            ], 500);
        }

    }
    public function verifyLoginOtp($request)
    {
        try {
            $context = decrypt($request->otp_context);

            $user = Users::find($context['user_id']);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun anda tidak ditemukan.',
                    'data' => []
                ], 403);
            }

            $result = $this->otpService->verify($user, OtpType::Login, $request->otp);

            if (!$result['success']) {
                return response()->json($result, 401);
            }

            $token = $user->createToken('pwa')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi OTP Berhasil. Login telah berhasil.',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'user' => $user
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi OTP gagal. Login telah gagal',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    public function verifyForgotPasswordOtp($otp)
    {
        try {
            $email = session('reset_email');
            $otpType = session('otp_type');

            $user = Users::where('email', '=', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun anda tidak ditemukan',
                    'data' => []
                ], 403);
            }

            $is_valid = $this->otpService->verify($user, $otpType, $otp);

            if (!$is_valid['success']) {
                return response()->json($is_valid, 401);
            }

            session()->forget(['otp_type', 'otp_message']);
            session()->put('otp_verified', true);

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi OTP berhasil. Silahkan melanjutkan reset password anda',
                'data' => [
                    $user
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi OTP telah gagal. Coba lagi dalam beberapa menit',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $allow = session('otp_verified');

        if (!$allow) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terverifikasi untuk reset password.'
            ], 403);
        }

        $validate = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi data error',
                'data' => $validate->errors()
            ], 422);
        }
        try {

            $email = session('reset_email');

            $user = Users::where('email', '=', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun anda tidak ditemukan.',
                    'data' => []
                ], 403);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            session()->forget('reset_email');

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password. Silahkan coba lagi nanti',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
