<?php

namespace App\Http\Controllers\Web;

use App\Enum\OtpType;
use App\Http\Controllers\Controller;
use App\Mail\OtpEmail;
use App\Models\Location;
use App\Models\Role;
use App\Models\Users;
use App\Services\GeocodingService;
use App\Services\OtpService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

use function Symfony\Component\Clock\now;

class AuthController extends Controller
{
    // Fetch Service
    protected $otpService;

    protected $geocodingService;

    public function __construct(OtpService $otpService, GeocodingService $geocodingService)
    {
        $this->otpService = $otpService;
        $this->geocodingService = $geocodingService;
        // $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyOtp', 'refresh', 'ForgotPassword', 'ResetPassword']]);
    }

    private function logInfo(string $message, array $context = [])
    {
        Log::info('[Web] [AuthController] '.$message, $context);
    }

    private function logError(string $message, ?\Throwable $e = null, array $context = [])
    {
        Log::error(
            '[Web] [AuthController] '.$message,
            array_merge(
                [
                    'exception' => $e?->getMessage(),
                    'trace' => $e?->getTraceAsString(),
                ],
                $context,
            ),
        );
    }

    /**
     * Summary of registerPage
     * Purpose
     * Untuk menampilkan halaman register
     */
    public function registerPage()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.register');
    }

    /**
     * Summary of register
     *
     * Purpose
     * Untuk proses validasi dan generate OTP
     */
    public function register(Request $request)
    {
        $this->logInfo('Register attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        $validate = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'aggree_terms' => 'required|accepted',
        ]);

        if ($validate->fails()) {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors()
            ]);
            // return response()->json([
            //     'status' => 'errors',
            //     'message' => 'Validation error',
            //     'data' => $validate->errors(),
            // ], 422);
            return redirect()
                ->back()
                ->withErrors($validate)
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Mohon periksa form anda');
        }

        try {
            if (RateLimiter::tooManyAttempts('register-otp:'.$request->ip().'|'.$request->email, 3)) {
                return back()->with('error', 'Terlalu banyak percobaan, coba lagi nanti.');
            }

            RateLimiter::hit('register-otp:'.$request->ip().'|'.$request->email, 300);

            $otp = $this->otpService->generateForRegister($request->email);

            $this->logInfo('OTP register generated', [
                'email' => $request->email,
            ]);

            Mail::to($request->email)->send(new OtpEmail($otp, 'Verifikasi Email'));

            $this->logInfo('OTP register sent', [
                'email' => $request->email,
            ]);

            // $request->session()->put('otp_email', $request->email);
            session([
                'register_payload' => [
                    'email' => $request->email,
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                    'phone' => $request->phone,
                ],
                'otp_type' => OtpType::Register,
                'otp_message' => 'Verifikasi email anda',
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
            $this->logError('Register failed', $e, [
                'email' => $request->email,
            ]);

            return redirect()->back()->with('error', 'Terjadi Kesalahan dalam sistem. Coba lagi nanti');
        }
    }

    /**
     * Summary of loginPage
     *
     * Puspose
     * Untuk menampilkan halaman login
     */
    public function loginPage()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Summary of login
     *
     * Purpose
     * Untuk proses validasi dan generate OTP
     */
    public function login(Request $request)
    {
        $this->logInfo('Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'remember_me' => $request->remember_me
        ]);

        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
            'remember_me' => 'nullable',
        ]);

        // dd($request->remember_me);

        if ($validate->fails()) {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors()
            ]);
            // return response()->json([
            //     'status' => 'error',
            //     'message' => 'Validation error',
            //     'data' => $validate->errors(),
            // ], 422);
            return redirect()
                ->back()
                ->withErrors($validate)
                ->withInput($request->except('password'))
                ->with('error', 'Mohon periksa form anda');
        }

        try {
            $userAccount = Users::where('email', '=', $request->email)->first();

            if (! $userAccount) {
                return redirect()->back()->with('error', 'Akun tidak ditemukan, silahkan buat akun')->withInput($request->except('password'));
            }

            if (! Hash::check($request->password, $userAccount->password)) {
                return redirect()->back()->with('error', 'Password salah, silahkan coba lagi')->withInput($request->except('password'));
            }

            if ($userAccount->is_active == false) {
                return redirect()->back()->with('error', 'AKun anda dalam sedang tidak aktif, silahkan hubungi admin')->withInput($request->except('password'));
            }

            $otp = $this->otpService->generate($userAccount, OtpType::Login);

            $this->logInfo('OTP login generated', [
                'user_id' => $userAccount->user_id,
            ]);

            Mail::to($userAccount->email)->send(new OtpEmail($otp, 'Verifikasi Login'));

            $this->logInfo('OTP register sent', [
                'email' => $request->email,
            ]);

            // session([
            //     'otp_user_id' => $userAccount->user_id,
            //     'otp_type' => OtpType::Login,
            //     'otp_message' => 'Verifikasi login',
            //     'remember_me' => $request->remember_me
            // ]);
            $request->session()->put('otp_user_id', $userAccount->user_id);
            $request->session()->put('otp_type', OtpType::Login);
            $request->session()->put('otp_message', 'Verifikasi login');
            $request->session()->put('remember_me', $request->remember_me);

            return redirect()->route('verify-otp.form');
        } catch (Exception $e) {
            $this->logError('Login error', $e, [
                'email' => $request->email,
            ]);

            return redirect()->back()->with('error', $e->getMessage())->withInput($request->except('password'));
        }
    }

    /**
     * Summary of verifyOtpForm
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
        $this->logInfo('OTP verification attempt', [
            'otp_type' => session('otp_type'),
            'user_id' => session('otp_user_id'),
        ]);

        $validate = Validator::make($request->all(), [
            'otp' => 'required|size:6',
        ]);

        if (! $validate) {
            return redirect()
                ->back()
                ->withErrors($validate)
                ->withInput()
                ->with('error', 'Mohon periksa form anda');
        }

        $otpType = session('otp_type');

        if (! $otpType) {
            return redirect()->route('login.form')->with('error', 'Tipe OTP tidak diketahui.');
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
            $this->logInfo('Reset OTP', [
                'otp_user_id' => session('otp_user_id'),
                'otp_type' => session('otp_type'),
            ]);

            $user = Users::find(session('otp_user_id'));
            $type = session('otp_type');

            switch ($type) {
                case OtpType::Login:
                    $subject = 'Verifikasi Login';
                    break;
                case OtpType::Register:
                    $subject = 'Verifikasi Email';
                    break;
                case OtpType::ResetPassword:
                    $subject = 'Reset Password';
                    break;
                default:
                    $subject = 'Kode OTP';
            }

            if (! $user || ! $type) {
                return redirect()->route('login.form')->with('error', 'Sesi OTP berakhir');
            }

            $otp = $this->otpService->generate($user, $type);

            $this->logInfo('OTP Regenerate', [
                'otp_user_id' => session('otp_user_id'),
                'otp_type' => session('otp_type'),
            ]);

            Mail::to($user->email)->send(new OtpEmail($otp, $subject));

            return back()->with('success', 'OTP baru telah dikirim');
        } catch (Exception $e) {
            $this->logError('Error reset OTP', $e, [
                'otp_user_id' => session('otp_user_id'),
                'otp_type' => session('otp_type'),
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Summary of verifyEmailOtp
     *
     * Purpose
     * Untuk proses halaman verifikasi OTP untuk registrasi
     */
    public function verifyEmailOtp($otp)
    {
        $this->logInfo('Register verification attempt', [
            'payload' => session('register_payload'),
        ]);
        try {
            $payload = session('register_payload');

            if (! $payload) {
                return redirect()->route('register.form')->with('error', 'Sesi registrasi berakhir. Mulai ulang registrasi');
            }

            $result = $this->otpService->verifyRegister($payload['email'], $otp);

            if (! $result['success']) {
                return back()->with('error', $result['message']);
            }

            $defaultRole = Role::where('role_name', 'User')->first();

            Users::create([...$payload, 'role_id' => $defaultRole, 'email_verified_at' => now(), 'is_active' => true]);

            $this->logInfo('Register complete', [
                'payload' => $payload,
                'otp_type' => session('otp_type'),
            ]);

            session()->forget(['otp_type', 'otp_message', 'register_payload']);
            // $request->session()->put('otp_verified', true);

            return redirect()->route('login.form');
        } catch (Exception $e) {
            $this->logError('Error while trying to veriify register', $e, [
                'payload' => session('register_payload'),
                'otp_type' => session('otp_type'),
            ]);

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Summary of verifyLoginOtp
     *
     * Purpose
     * Untuk verifikasi OTP untuk login
     */
    public function verifyLoginOtp($request)
    {
        try {
            $this->logInfo('Login verificatin attempt', [
                'user_id' => session('otp_user_id'),
                'otp_type' => session('otp_type'),
            ]);
            $otpType = session('otp_type');

            $rememberMe = session('remember_me');

            $user = Users::find(session('otp_user_id'));

            if (! $user) {
                $this->logError('User not found', null, [
                    'user_id' => session('otp_user_id'),
                ]);

                return redirect()->route('login.form')->with('error', 'User tidak ditemukan. Silahkan masukkan email anda')->withInput();
            }

            $this->logInfo('User found', [
                'user' => $user,
            ]);

            $is_valid = $this->otpService->verify($user, $otpType, $request);

            if (! $is_valid['success']) {
                return redirect()->back()->with('error', $is_valid['message']);
            }

            Auth::guard('web')->login($user, $rememberMe);
            session()->save();

            session()->forget(['otp_user_id', 'otp_type', 'otp_message']);

            $this->logInfo('Login successfull', [
                'user_id' => $user->user_id,
                'email' => $user->email,
            ]);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Verifikasi berhasil',
            //     'data' => [
            //         'access_token' => $token,
            //         'token_type' => 'bearer',
            //         'user' => $user
            //     ]
            // ]);\
            return redirect()->route('admin.dashboard');
        } catch (Exception $e) {
            $this->logError('Error while trying to verify login OTP', $e, [
                'otp_type' => session('otp_type'),
                'user_id' => session('otp_user_id'),
                'email' => $user->email,
            ]);

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Summary of logout
     *
     * Purpose
     * Untuk proses logout user
     */
    public function logout(Request $request)
    {
        try {
            $this->logInfo('Logout attempt', [
                'user_id' => Auth::id(),
            ]);

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->logInfo('Logout successfull', [
                'success' => true,
            ]);

            return redirect()->route('login.form')->with('success', 'Anda telah log-out');
        } catch (Exception $e) {
            $this->logError('Logout failed', $e, [
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Gagal logout karena kesalahan sisteme. Coba lagi nanti');
        }
    }

    /**
     * Summary of forgotPasswordForm
     *
     * Purpose
     * Menampilkan halaman lupa password
     */
    public function forgotPasswordForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.forgotPassword');
    }

    /**
     * Summary of forgotPassword
     *
     * Purpose
     * Proses validasi email dan generate OTP
     */
    public function forgotPassword(Request $request)
    {
        try {
            $this->logInfo('Forgot password attempt', [
                'email' => $request->email,
            ]);

            $validate = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validate->fails()) {
                $this->logError('Validation Error', null, [
                'error' => $validate->errors()
            ]);
                return redirect()
                    ->back()
                    ->withErrors($validate)
                    ->withInput()
                    ->with('error', 'Mohon periksa form anda');
            }

            $user = Users::where('email', $request->email)->first();

            if (! $user) {
                $this->logError('User not found', null, [
                    'email' => $request->email,
                ]);

                return redirect()->back()->with('error', 'Akun pengguna tidak ditemukan.');
            }

            $this->logInfo('User found', [
                'user' => $user,
            ]);

            // Generate OTP untuk reset password
            $otp = $this->otpService->generate($user, OtpType::ResetPassword);

            $this->logInfo('OTP Has been created', [
                'user_id' => $user->user_id,
                'otp_type' => OtpType::ResetPassword,
            ]);

            // Kirim OTP via email
            Mail::to($user->email)->send(new OtpeMail($otp, 'Reset Password'));

            // Simpan email di session
            session([
                'otp_user_id' => $user->user_id,
                'otp_type' => OtpType::ResetPassword,
                'otp_message' => 'Verifikasi login',
            ]);

            $this->logInfo('OTP Has been sended', [
                'email' => $request->email,
                'otp_type' => session('otp_type'),
                'user_id' => $user->user_id,
            ]);

            // $request->session()->put('reset_otp_required', true);

            return redirect()->route('verify-otp.form')->with('success', 'OTP telah dikirim ke email Anda.');
        } catch (Exception $e) {
            $this->logError('Something wrong with the system', $e, [
                'otp_type' => session('otp_type'),
                'user_id' => session('otp_user_id'),
            ]);

            return redirect()->back()->with('error', 'Kesalahan dalam sistem. Coba lagi nanti');
        }
    }

    /**
     * Summary of verifyForgotPasswordOtp
     *
     * Purpose
     * Proses verifikasi OTP lupa password
     */
    public function verifyForgotPasswordOtp($request)
    {
        try {
            $this->logInfo('Verify forgot password attempt', [
                'otp_user_id' => session('otp_user_id'),
                'otp_type' => session('otp_type'),
            ]);

            $otpType = session('otp_type');

            $user = Users::find(session('otp_user_id'));

            $this->logInfo('User found', [
                'user' => $user,
            ]);

            if (! $user || ! $otpType) {
                return redirect()->back()->with('error', 'Sesi reset password berakhir')->withInput();
            }

            $is_valid = $this->otpService->verify($user, $otpType, $request);

            $this->logInfo('OTP valid', [
                'success' => true,
            ]);

            if (! $is_valid['success']) {
                return redirect()->back()->with('error', $is_valid['message'])->withInput();
            }

            session()->forget(['otp_type', 'otp_message']);

            return redirect()->route('reset-password.form');
        } catch (Exception $e) {
            $this->logError('OTP reset password verification failed', $e, [
                'otp_type' => session('otp_type'),
                'otp_user_id' => session('otp_user_id'),
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function resetPasswordForm()
    {
        if (! session('otp_user_id')) {
            return redirect()->route('forgot-password.form')->with('error', 'Sesi reset password telah berakhir. Silahkan coba lagi');
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
                $this->logError('Validation Error', null, [
                    'error' => $validate->errors(),
                ]);

                return redirect()
                    ->back()
                    ->withErrors($validate)
                    ->with('error', 'Mohon periksa form anda');
            }

            $id = session('otp_user_id');

            $user = Users::findOrFail($id);

            if (! $user) {
                return redirect()->back()->with('error', 'User tidak ditemukan. Sesi reset telah berakhir');
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            $this->logInfo('Password reset success', [
                'user_id' => session('otp_user_id'),
            ]);

            Auth::logout();

            return redirect()->route('login.form')->with('success', 'Password telah direset. Silahkan login kembali');
        } catch (Exception $e) {
            $this->logError('Reset password failed', $e);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function showProfile()
    {
        try {
            $user = Auth::user()->load('location')->orderBy('created_at', 'desc');

            return view('dashboard.profile.index', compact('user'));
        } catch (Exception $e) {
            $this->logError('Failed fetching data', $e, [
                'success' => false,
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan dalam sistem. Coba lagi nanti');
        }
    }

    public function updateProfile(Request $request)
    {
        $this->logInfo('Profile update attempt', [
            'user_id' => Auth::id(),
        ]);

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login.form')->with('error', 'Anda belum login. Silahkan login terlebih dahulu');
        }

        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:100|unique:users,username,'.$user->user_id.',user_id',
            'email' => 'nullable|string|email|unique:users,email,'.$user->user_id.',user_id',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'location' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            $this->logError('Validation Error', null, [
                'error' => $validator->errors(),
            ]);

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Mohon periksa form anda');
        }

        try {
            $update = Users::findOrFail($user->user_id);

            $this->logInfo('User Found', [
                'user' => $update,
            ]);

            $location = $request->location;

            $data = $request->only(['username', 'phone', 'bio', 'email']);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {

                $avatarPath = $request->file('avatar')->store('avatars', 'public');

                $data['avatar'] = $avatarPath;

                $this->logInfo('User avatar data', [
                    'path' => $avatarPath,
                ]);
            }

            $update->update($data);

            $this->logInfo('Profile updated', [
                'user_id' => Auth::id(),
            ]);

            if ($location) {
                foreach ($location as $item) {
                    $updateLocation = Location::findOrFail($item['id']);

                    $coordinate = $this->geocodingService->getCoordinatesFromAddress($item['address']);

                    // dd($coordinate);

                    $updateLocation->update([
                        'address' => $item['address'],
                        'latitude' => $coordinate['latitude'],
                        'longitude' => $coordinate['longitude'],
                    ]);
                    $this->logInfo('User Location updated', [
                        'location_id' => $item['id'],
                    ]);
                }
                $this->logInfo('No error accured', [
                    'location_id' => $item,
                ]);
            }

            return redirect()->route('admin.profile')->with('success', 'Profile berhasil diperbarui.');
        } catch (Exception $e) {
            $this->logError('Profile update error', $e, [
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan dalam sistem. Coba lagi nanti');
        }
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login.form')->with('error', 'Anda belum login. Diharapkan login terlebih dahulu');
        }

        $validate = Validator::make($request->all(), [
            'current_password' => ['required', 'string', 'min:8'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        if ($validate->fails()) {
            $this->logError('Validation Error', null, [
                'error' => $validate->errors()
            ]);
            return redirect()->back()->withErrors($validate)->with('error', 'Mohon perikas kembali form anda');
        }

        if (! Hash::check($request->current_password, $user->password)) {
            // dd(Hash::check($request->current_password, $user->password));
            return redirect()->back()->with('error', 'Password anda salah.');
        }

        try {
            $update = Users::findOrFail($user->user_id);

            $this->logInfo('User Found', [
                'user' => $update,
            ]);

            $update->update([
                'password' => Hash::make($request->password),
            ]);

            $this->logInfo('Password changed', [
                'success' => true,
            ]);

            Auth::logout();

            return redirect()->route('login.form')->with('success', 'Ganti password telah berhasil. Silahkan login kembali');
        } catch (Exception $e) {
            $this->logError('Change password error', $e, [
                'success' => false,
            ]);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
