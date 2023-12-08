<?php
 
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
 
class AuthController extends Controller
{   

    /* ------------- REGISTER USER ------------ */
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register new user",
     *     @OA\Response(response="200", description="User registration"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="firstname",type="string"),
     *                 @OA\Property(property="lastname",type="string"),
     *                 @OA\Property(property="email",type="string"),
     *                 @OA\Property(property="password",type="string"),
     *             )
     *         )
     *     ),
     * )
    */
    public function register(Request $request)
    {
        $user = new User;

        if (empty($request->firstname)){
            return 'A lastname is required';

        } else {
            $firstname = $request->firstname;
            if (!preg_match("/^[a-zA-Z-' ]*$/",$firstname)) {
            return "Only letters and white space allowed";
            }
        }

        if (empty($request->lastname)){
            return 'A lastname is required';
        } else {
            $lastname = $request->lastname;
            if (!preg_match("/^[a-zA-Z-' ]*$/",$lastname)) {
            return "Only letters and white space allowed";
            }
        }

        if (empty($request->email)){
            return 'An email is required';
        } else {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format";
            }
        }

        if (empty($request->password)){
            return 'A password is required';
        } else {

            $password = $request->password;

            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
    
            if(!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
                return 'Password not correct, need : 8 caracters, 1 uppercase, 1 lowercase and 1 number';
            }
        }

        if (User::where('email', '=', $request->email)->exists()) {
            return 'Email already use.';
        } else {
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->right_id = 1;
            $user->save();
            
            return $user::find($user->id);
        }

    }


    /* ------------- Login USER ------------ */
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Log the user",
     *     @OA\Response(response="200", description="Handle an authentication attempt"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="email",type="string"),
     *                 @OA\Property(property="password",type="string"),
     *             )
     *         )
     *     ),
     * )
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