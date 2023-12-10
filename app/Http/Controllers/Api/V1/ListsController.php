<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lists;
use App\Models\UserBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ListsController extends Controller
{
    protected $list;

    /* ------------- GET ALL LISTS ------------ */
    /**
     * @OA\Get(
     *     path="/api/lists",
     *     tags={"Lists"},
     *     summary="Get list of all board's lists",
     *     description="Returns list of all board's lists",
     *     @OA\Response(response="200", description="Array[] : contain all lists"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
     */
    public function index()
    {   
        $lists = Lists::all();

        if ($lists->count() === 0){
            return Response::json('No list to return.', 200);
        } else {
            return $lists;
        }
    }

    /* ------------- GET LIST BY ID ------------ */
    /**
     * @OA\Get(
     *     path="/api/lists/{id}",
     *     tags={"Lists"},
     *     summary="Get info for specific list by id",
     *     description="Returns list by id",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          @OA\Schema(type="integer"),
     *          required=true
     *     ),
     *     @OA\Response(response="200", description="Array[] : Target list"),
     *     @OA\Response(response="400", description="Bad request : not connected"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function show(string $id)
    {

        $currentUser = Auth::user();

        if (!isset($currentUser)){
            return Response::json('Error : You are not connected.', 400);
        }

        if (!isset($currentUser->boards)){
            return Response::json('Error : You have no board.', 400);
        }

        $isUserList = false;

        foreach($currentUser->boards as $board){
            
            if (!isset($board->lists)){
                return;
            }

            foreach($board->lists as $list){
                if ($list->id == $id){
                    $isUserList = true;
                }
            }
        }

        if ($isUserList = true){
            $lists = Lists::find($id);

            if (empty($lists)){
                return Response::json('No list to return.', 200);
            } else {
                return $lists;
            }
     
        } else {
            return Response::json('You have not the correct rights to see this list.', 200);
        }
        
    }

    /* ------------- STORE LIST ------------ */
    /**
     * @OA\Post(
     *     path="/api/lists",
     *     tags={"Lists"},
     *     summary="Insert new list",
     *     description="Create new list to DB",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name",type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="String -> modified list id"),
     *     @OA\Response(response="400", description="Bad request : No name || not connected"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function addList(Request $request, $id)
    {
        $list = new Lists;

        if (empty($request->title)){
            return Response::json('Error : Please insert a title for the board.', 400);
        }

        if (!$id){
            return Response::json('Error : Nedd a board id target.', 400);
        }

        if (empty(Auth::user())){
            return Response::json('Error : You are not conected.', 400);
        }

        $currentUser = Auth::user();

        $isUserBoard = false;

        foreach($currentUser->boards as $board){

            $userRight = UserBoard::where([['user_id' , $board->pivot->user_id], ['board_id', $board->pivot->board_id]])->get()->first();

            if ($board->id == $id){
                if ($userRight->board_right_id === 1){
                    $isUserBoard = true;
                } else {
                    return Response::json('Error : You have not the correct rights to modify this board.', 400);
                }
            }
        }

        if(!$isUserBoard){
            return Response::json("Error : This board is not one of yours.", 400);
        }

        $list->title = $request->title;
        $list->board_id = $id;
        $list->save();
        return $list::find($list);
        
    }

    /* ------------- UPDATE LIST BY ID ------------ */
    /**
     * @OA\Put(
     *     path="/api/lists/{id}",
     *     tags={"Lists"},
     *     summary="Modify info for specific list by id",
     *     description="Modify list by id",
     *     @OA\Parameter(
     *          description="Id of target list",
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
     *                 @OA\Property(property="title",type="string"),
     *                 @OA\Property(property="is_archived",type="boolean"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="String -> modified list id"),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function update(Request $request, string $id)
    {
        $inputs = $request->all();

        $authUser = Auth::user();

        $list = Lists::find($id);

        if (empty($list)){
            return Response::json('Error : No list find.', 400);
        }

        if (empty($authUser)){
            return Response::json("Error : you are not connected.", 400);
        }

        if (!isset($authUser->boards)){
            return Response::json("Error : You have no board.", 400); 
        }

        $isUserList = false;

        foreach($authUser->boards as $board){

            $userRight = UserBoard::where([['user_id' , $board->pivot->user_id], ['board_id', $board->pivot->board_id]])->get()->first();

            if (!isset($board->lists)){
                return;
            }

            foreach($board->lists as $list){
                if ($list->id == $id){

                    if ($userRight->board_right_id === 1){
                        $isUserList = true;
                    } else {
                        return Response::json('Error : You have not the correct rights to modify this board.', 400);
                    }
                }
            }
        }
        
        if ($isUserList){
            if (isset($inputs['title'])){
                $list->title = $inputs['title'];
            }

            if (isset($inputs['is_archives'])){
                $list->is_archived = $inputs['is_archives'];
            }

            $list->updated_at = now();
            $list->update();
        } else {
            return Response::json("Error : This is not one of your list.", 400);
        }

        return Response::json("List update succed.", 200);

    }

    /**
     * @OA\delete(
     *     path="/api/lists/{id}",
     *     tags={"Lists"},
     *     summary="Delete list",
     *     description="Delete list by id",
     *      @OA\Parameter(
     *          description="Id of target list",
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

        $list = Lists::find($id);

        $currentUser = Auth::user();

        if (!isset($currentUser)){
            return Response::json('Error : You are not connected.', 400);
        }

        if (!isset($currentUser->boards)){
            return Response::json('You have no board.', 400);
        }

        $isUserList = false;

        foreach($currentUser->boards as $board){

            $userRight = UserBoard::where([['user_id' , $board->pivot->user_id], ['board_id', $board->pivot->board_id]])->get()->first();

            if (!isset($board->lists)){
                return;
            }

            foreach($board->lists as $list){
                if ($list->id == $id){
                    if ($userRight->board_right_id === 1){
                        $isUserList = true;
                    } else {
                        return Response::json('Error : You have not the correct rights to modify this board.', 400);
                    }
                }
            }
        }

        if ($isUserList){
            $list->delete();
            return Response::json('The list have been deleted.', 200);
        } else {
            return Response::json('Error : You cannot delete this list.', 400);
        }
        return $list;
    }
}
