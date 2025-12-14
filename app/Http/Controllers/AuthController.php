<?php

namespace App\Http\Controllers;

use App\Enum\OtpType;
use App\Http\Controllers\Controller;
use App\Mail\OtpEmail;
use App\Models\Role;
use App\Models\Users;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
        // $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyOtp', 'refresh', 'ForgotPassword', 'ResetPassword']]);
    }

    /**
     * Register dan generate otp untuk verifikasi email
     */
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'errors',
                'message' => 'Validation error',
                'data' => $validate->errors(),
            ], 422);
        }

        try {

            $defaultRole = Role::where('role_name', '=', 'User')->get();
            $user = Users::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role_id' => $defaultRole->role_id,
                'is_actice' => true,
            ]);

            $otp = $this->otpService->generate($user, OtpType::Register);

            Mail::to($user->email)->send(new OtpEmail($otp, 'Verifikasi Email'));

            return response()->json([
                'status' => 'success',
                'message' => 'Registrasi berhasil. Silakan verifikasi email dengan OTP yang dikirim.',
                'data' => [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registrasi gagal: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user dan generate otp
     */
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'data' => $validate->errors(),
            ], 422);
        }

        $userAccount = Users::where('email', $request->email)->first();

        if (! $userAccount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account with this email is not exist',
            ], 401);
        }

        if (! Hash::check($request->password, $userAccount->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wrong password',
            ], 401);
        }

        if ($userAccount->is_active == false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is inactive. Please contact admin',
            ], 401);
        }

        $otp = $this->otpService->generate($userAccount, OtpType::Login);

        Mail::to($userAccount->email)->send(new OtpEmail($otp, 'OTP Login'));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP telah dikirim ke email Anda',
            'data' => [
                'user_id' => $userAccount->user_id,
                'email' => $userAccount->email,
                'requires_otp' => true
            ]
        ]);
    }
    public function verifyOtp(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|string|size:6',
            'otp_type' => 'required|in:login,register,reset_password'
        ]);

        if ($validate->fails())
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'data' => $validate->errors()
            ], 422);
        }

        $user = Users::where('email', $request->email)->first();

        if (!$user)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $otpType = OtpType::from($request->otp_type);
        $is_valid = $this->otpService->verify($user, $otpType, $request->otp);

        if (!$is_valid)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'Otp tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        // Untuk verifikasi email pengguna baru
        if ($request->otp_type === 'register') {
            $user->update(['email_verified_at' => now()]);
            return response()->json([
                'status' => 'success',
                'message' => 'Email telah terverifikasi. Silahkan login ke dengan akun anda',
            ], 200);
        }

        // Generate JWT token
        $token = Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi berhasil',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
                'user' => $user
            ]
        ]);
    }

    public function me()
    {
        $user = Auth::user()->load('role');

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Users::where('email', $request->email)->first();

        // Generate OTP untuk reset password
        $otp = $this->otpService->generate($user, OtpType::ResetPassword);

        // Kirim OTP via email
        Mail::to($user->email)->send(new OtpEMail($otp, 'Reset Password'));

        return response()->json([
            'success' => true,
            'message' => 'OTP untuk reset password telah dikirim ke email Anda'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Users::where('email', $request->email)->first();

        // Verify OTP
        $isValid = $this->otpService->verify($user, OtpType::ResetPassword, $request->otp);

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau telah kadaluarsa'
            ], 400);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login kembali.'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|max:100|unique:users,username,' . $user->user_id . ',user_id',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['username', 'phone', 'bio', 'avatar']));

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diperbarui',
            'data' => $user
        ]);
    }
}
