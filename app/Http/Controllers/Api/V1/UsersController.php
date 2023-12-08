<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

/**
 * @OA\Info(title="Laravel Kanban API", version="0.1")
 */
class UsersController extends Controller
{

    protected $user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $this->user = Auth::user();

            return $next($request);
        });
    }

    /* ------------- GET ALL USERS ------------ */
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get list of all users",
     *     description="Returns list of all users",
     *     @OA\Response(response="200", description="Array[] : contain all users"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
     */
    public function index()
    {
        return User::all();
    }

    /* ------------- STORE USER ------------ */
    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Insert new user",
     *     description="Create new user to DB",
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
     *     @OA\Response(response="200", description="String -> modified user id"),
     *     @OA\Response(response="400", description="Bad request : All fields are required"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function store(Request $request)
    {
        $user = new User;

        if (empty($request->firstname) || empty($request->lastname) || empty($request->email) || empty($request->password)){
            return Response::json('Please all the fields are required.', 400);
        }

        if (empty($request->email)){
            return 'An email is required';
        } else {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return "Invalid email format";
            }
        }

        if (User::where('email', '=', $request->email)->exists()) {
            return Response::json('Email already use.', 400);
        } else {
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->save();

            return $user::find($user);
        }
    }


    /* ------------- GET USER BY ID ------------ */
    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Get info for specific user by id",
     *     description="Returns user by id",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Array[] : Target user"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function show(string $id)
    {
        return User::find($id);
 
    }

    /* ------------- UPDATE USER BY ID ------------ */
    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Modify info for specific user by id",
     *     description="Modify user by id",
     *     @OA\Parameter(
     *          description="Id of target user",
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="firstname",type="string"),
     *                 @OA\Property(property="lastname",type="string"),
     *                 @OA\Property(property="email",type="string"),
     *                 @OA\Property(property="password",type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="String -> modified user id"),
     *     @OA\Response(response="400", description="Bad request : No user || Not connected || Not correct rights || No password || Bad password format"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function update(Request $request, string $id)
    {
        $inputs = $request->all();

        $authUser = Auth::user();

        $user = User::find($id);

        if (empty($user)){
            return Response::json('Error : No user find.', 400);
        }

        if (empty($authUser)){
            return Response::json("Error : you are not connected.", 400);
        }

        if ($user->id !== $authUser->id){
            return Response::json("Error : you can't modify an other user without rights.", 400);
        } else {

            if (!empty($inputs['password'])){

                $password = $inputs['password'];

                $uppercase = preg_match('@[A-Z]@', $password);
                $lowercase = preg_match('@[a-z]@', $password);
                $number    = preg_match('@[0-9]@', $password);
        
                if(!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
                    return Response::json('Password not correct, need : 8 caracters, 1 uppercase, 1 lowercase and 1 number', 400);
                } else {
                    $inputs['password'] = Hash::make($inputs['password']);
                }
            }
            
            DB::table('users')->where('id', $user->id)->update($inputs);
            $user = User::find($user->id);
            $request->session()->regenerate();
            
            return Response::json('Vos données ont bien été mises à jour : ' . $user->firstname, 200);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /* ------------- REGISTER USER ------------ */
    /**
     * @OA\Get(
     *     path="/api/v1/users/register",
     *     @OA\Response(response="200", description="User registration")
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
}
