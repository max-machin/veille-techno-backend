<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\User;
use App\Models\UserBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class BoardController extends Controller
{
    protected $board;

    /* ------------- GET ALL BOARDS ------------ */
    /**
     * @OA\Get(
     *     path="/api/boards",
     *     tags={"Boards"},
     *     summary="Get list of all boards",
     *     description="Returns list of all boards",
     *     @OA\Response(response="200", description="Array[] : contain all boards"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
     */
    public function index()
    {
        return Board::all();
    }

    /* ------------- GET BOARD BY ID ------------ */
    /**
     * @OA\Get(
     *     path="/api/boards/{id}",
     *     tags={"Boards"},
     *     summary="Get info for specific board by id",
     *     description="Returns board by id",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Array[] : Target board"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function show(string $id)
    {

        return Board::find($id);
 
    }

    /* ------------- STORE BOARD ------------ */
    /**
     * @OA\Post(
     *     path="/api/boards",
     *     tags={"Boards"},
     *     summary="Insert new board",
     *     description="Create new board to DB",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name",type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="String -> modified board id"),
     *     @OA\Response(response="400", description="Bad request : No name || not connected"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function store(Request $request)
    {
        $board = new Board;

        if (empty($request->name)){
            return Response::json('Error : Please insert a name for the board.', 400);
        }

        if (empty(Auth::user())){
            return Response::json('Error : You are not conected.', 400);
        }

        $board->name = $request->name;
        $board->save();
        $board->users()->attach(Auth::user());
        return $board::find($board);
        
    }

    /* ------------- GET USERS LINKED TO BOARD ------------ */
    /**
     * @OA\Get(
     *     path="/api/boards/{id}/users",
     *     tags={"Boards"},
     *     summary="Get users for specific board by id",
     *     description="Returns users by board id",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          @OA\Schema(type="integer"),
     *          required=true
     *     ),
     *     @OA\Response(response="200", description="Array[] : Target board user's"),
     *     @OA\Response(response="400", description="Bad request : No user "),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function getBoardUsers(string $id)
    {
        if(!$id){
            return Response::json('Error : Id is required to get users of board.', 400);
        } else {

            if (empty(Board::find($id)->users)){
                return Response::json('Error : No users find, have you specify board Id in query ?', 400);
            }
            return Board::find($id)->users;
        }
    }

     /* ------------- GET LISTS LINKED TO BOARD ------------ */
    /**
     * @OA\Get(
     *     path="/api/boards/{id}/lists",
     *     tags={"Lists", "Boards"},
     *     summary="Get lists for specific board by id",
     *     description="Returns lists by board id",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          @OA\Schema(type="integer"),
     *          required=true
     *     ),
     *     @OA\Response(response="200", description="Array[] : Target board list's"),
     *     @OA\Response(response="400", description="Bad request : No list "),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function getBoardLists(string $id)
    {
        if(!$id){
            return Response::json('Error : Id is required to get lists of board.', 400);
        } else {

            if (empty(Board::find($id)->lists)){
                return Response::json('Error : No lists find, have you specify board Id in query ?', 400);
            }
            return Board::find($id)->lists;
        }
    }

    
    /* ------------- UPDATE BOARD BY ID ------------ */
    /**
     * @OA\Put(
     *     path="/api/boards/{id}",
     *     tags={"Boards"},
     *     summary="Modify info for specific board by id",
     *     description="Modify board by id",
     *     @OA\Parameter(
     *          description="Id of target board",
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name",type="string"),
     *                 @OA\Property(property="is_archived",type="boolean"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="String -> modified board id"),
     *     @OA\Response(response="400", description="Bad request : No user || Not connected || Not correct rights"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function update(Request $request, string $id)
    {
        $inputs = $request->all();

        $authUser = Auth::user();

        if (empty($authUser)){
            return Response::json('Error : You are not connected.', 400);
        }

        $currentBoard = Board::find($id);

        $isInUserBoards = false;

        foreach($authUser->boards as $oneBoard){
            
            $userRight = UserBoard::where([['user_id' , $oneBoard->pivot->user_id], ['board_id', $oneBoard->pivot->board_id]])->get()->first();

            if ($oneBoard->id === $currentBoard->id){

                if ($userRight->board_right_id === 1){

                    $is_archived = $currentBoard->is_archived;

                    DB::table('boards')->where('id', $currentBoard->id)->update([
                        "name" => $inputs['name'],
                        "is_archived" => $input['is_archived'] ?? $is_archived
                    ]);
                    return Response::json('Board update with success : ' . $currentBoard->id , 200);
                } else {
                    return Response::json('Error : You have not the correct rights to modify this board.', 400);
                }
                $isInUserBoards = true;
            } else {
                return Response::json('Error : The current board is not accessible.', 400);
            }
        }

        if ($isInUserBoards === true){
            
        } else {
            return Response::json('Error : The current board is not accessible', 400);
        }
    }

    /**
     * @OA\delete(
     *     path="/api/boards/{id}",
     *     tags={"Boards"},
     *     summary="Delete board",
     *     description="Delete board by id",
     *      @OA\Parameter(
     *          description="Id of target board",
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

}
