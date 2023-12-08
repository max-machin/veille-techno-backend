<?php
 
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
 
class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
 
        if (empty($request->email)){
            return 'An email is required';
        } else {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format";
            }
        }

        if (empty($password)){
            return 'Password field is required';
        }

        if (Auth::attempt(['email' => $email, 'password' => $password])) {

            $user = Auth::user();

            $request->session()->regenerate();
            
            return $user;

        } else {
            return 'Bad credentials.';
        }
    }
}