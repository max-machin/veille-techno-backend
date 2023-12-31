<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

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

            if ($this->user->right_id == 3){

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
                DB::table('users')->where('id', $user->id)->update(['updated_at' => now()]);
                $user = User::find($user->id);
                $request->session()->regenerate();
                
                return Response::json('Data updated for : ' . $user->firstname, 200);
            } else {
                return Response::json("Error : you can't modify an other user without rights.", 400);
            }
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
            DB::table('users')->where('id', $user->id)->update(['updated_at' => now()]);
            $user = User::find($user->id);
            $request->session()->regenerate();
            
            return Response::json('Vos données ont bien été mises à jour : ' . $user->firstname, 200);
        }

    }

    /**
     * @OA\delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Delete user",
     *     description="Delete user by id",
     *      @OA\Parameter(
     *          description="Id of target user",
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Method not ready now"),
     * )
    */
    public function destroy(string $id)
    {
        return 'Method not ready';
    }

    /* ------------- GET BOARD LINKED TO USER ------------ */
    /**
     * @OA\Get(
     *     path="/api/users/{id}/boards",
     *     tags={"Users"},
     *     summary="Get boards for specific user by id",
     *     description="Returns boards by user id",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          @OA\Schema(type="integer"),
     *          required=true
     *     ),
     *     @OA\Response(response="200", description="Array[] : Target user board's"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function getUserBoards(string $id)
    {
        if(!$id){
            return Response::json('Error : Id is required to get boards of user.', 400);
        } else {
            
            if ($this->user->id != $id){
                return Response::json('Error : Is not your account.', 400);
            } 

            if (empty(User::find($id)->boards)){
                return Response::json('Error : No boards find, have you specify user Id in query ?', 400);
            }
            return User::find($id)->boards;
        }
    }
}
