<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function index()
    {
        return view('pages.login', [
            "title" => "Login",
            "url" => url('/assets')
        ]);
    }

    public function login(Request $request)
    {
        $validate = $request->validate([
            "email" => "required",
            "password" => "required"
        ]);

        // dd($validate);

        if (Auth::attempt($validate)) {
            $request->session()->regenerate();
            return redirect()->intended('master-users')->with('success', 'Login Berhasil!');
        }

        return redirect('/login')->with('error', "Login gagal, email atau password anda tidak sesuai!");
    }

    public function logout(Request $request)
    {
        Auth::Logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function register()
    {
        return view('pages.register', [
            "title" => "Register",
            "url" => url('/assets')
        ]);
    }

    public function registerStore(Request $request)
    {
        $validateData = $request->validate([
            "name_user" => 'required',
            "email" => 'required',
            "password" => 'required',
            "image" => 'required|file|image|max:2048'
        ]);

        $validateData['password'] = Hash::make($validateData['password']);
        $validateData['image'] = $request->file('image')->store('profile-images');

        User::create($validateData);

        return redirect('/login')->with('success', 'Registrasi berhasil!, silahkan login.');
    }

    public function forgotPassword()
    {
        return view('pages.forgot-password', [
            "title" => "Forgot Password",
            "url" => url('/assets')
        ]);
    }

    public function submitEmail(Request $request)
    {
        $request->validate(["email" => "required|email"]);
        // dd($validateData);
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
    }

    public function resetToken(string $token) {
        return view('pages.reset', [
            'token' => $token,
            'title' => "Reset Password",
            'url' => url('/assets')
        ]);
    }

    public function storeResetPassword(Request $request) {
        // return "hallo";
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // dd($request);
     
        $status = Password::reset(
            $request->only('email', 'password',  'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );
     
        return $status === Password::PASSWORD_RESET
                    ? redirect('/login')->with('success', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
