<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BoardRight;
use App\Models\Card;
use App\Models\UserBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class CardController extends Controller
{
    protected $card;

    /* ------------- GET ALL CARDS ------------ */
    /**
     * @OA\Get(
     *     path="/api/cards",
     *     tags={"Cards"},
     *     summary="Get list of all cards",
     *     description="Returns list of all cards",
     *     @OA\Response(response="200", description="Array[] : contain all cards"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
     */
    public function index()
    {
        return Card::all();
    }

    /* ------------- ADD NEW CARD ------------ */
    /**
     * @OA\Post(
     *     path="/api/lists/{id}/cards",
     *     tags={"Cards"},
     *     summary="Insert new card into list",
     *     description="Create new card into list to DB",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name",type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="String -> modified card id"),
     *     @OA\Response(response="400", description="Bad request : No name || not connected"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function addCard(Request $request, $id)
    {

        $inputs = $request->all();

        $card = new Card;

        if (empty($request->title)){
            return Response::json('Error : Please insert a title for the board.', 400);
        }

        if (empty($request->description)){
            return Response::json('Error : Please insert a description for the board.', 400);
        }

        if (!$id){
            return Response::json('Error : Nedd a card id target.', 400);
        }

        if (empty(Auth::user())){
            return Response::json('Error : You are not conected.', 400);
        }

        $currentUser = Auth::user();

        if (!isset($currentUser->boards)){
            return Response::json('Error : Create a board before.', 400);
        }

        $listId = 0;
        $isUserList = false;

        foreach($currentUser->boards as $board){

            $boardLists = $board->lists;

            foreach($boardLists as $lists){
                if ($lists->id == $id){

                    $userRight = UserBoard::where([['user_id' , $board->pivot->user_id], ['board_id', $board->pivot->board_id]])->get()->first();

                    if ($userRight->board_right_id === 1){
                        $isUserList = true;
                        $listId = $lists->id;
                    } else {
                        return Response::json('Error : You have not the correct rights to modify this board.', 400);
                    }
                    
                }
            }
        }

        if (!$isUserList || $listId === 0 ){
            return Response::json('Error : This lists is not one of youre.', 400);
        } else {
            $card->title = $inputs['title'];
            $card->description = $inputs['description'];

            if (isset($inputs['priority'])){
                $card->priority = $inputs['priority'];
            }

            if (isset($inputs['limit_date'])){
                $card->limit_date = $inputs['limit_date'];
            }

            $card->list_id = $listId;
            $card->save();
            return $card::find($card);
        }
        
    }

    /* ------------- GET CARDS LIST ------------ */
    /**
     * @OA\Get(
     *     path="/api/lists/{id}/cards",
     *     tags={"Cards"},
     *     summary="Get all cards for one list",
     *     description="Return all cards for specific list",
     *     @OA\Response(response="200", description="array => list of cards"),
     *     @OA\Response(response="400", description="Bad request : not connected"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function getListCards(Request $request, $id){

        if (empty(Auth::user())){
            return Response::json('Error : You are not conected.', 400);
        }

        $currentUser = Auth::user();

        $isUserList = false;

        foreach($currentUser->boards as $board){
            foreach($board->lists as $list){
                if ($list->id == $id){
                    $isUserList = true;
                }
            }
        }

        if (!$isUserList){
            return Response::json('Error : This lists is not one of youre.', 400);
        } else {
            return $list::with('cards')->get();
        }
    }

    /* ------------- UPDATE CARD BY ID ------------ */
    /**
     * @OA\Put(
     *     path="/api/cards/{id}",
     *     tags={"Cards"},
     *     summary="Modify info for specific card by id",
     *     description="Modify card by id",
     *     @OA\Parameter(
     *          description="Id of target card",
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
     *     @OA\Response(response="200", description="String -> modified card id"),
     *     @OA\Response(response="400", description="Bad request"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
    */
    public function update(Request $request, string $id)
    {
        $inputs = $request->all();

        $authUser = Auth::user();

        $card = Card::find($id);

        if (empty($card)){
            return Response::json('Error : No card find.', 400);
        }

        if (empty($authUser)){
            return Response::json("Error : you are not connected.", 400);
        }

        if (!isset($authUser->boards)){
            return Response::json("Error : You have no board.", 400); 
        }

        $isUserCard = false;

        foreach($authUser->boards as $board){

            $userRight = UserBoard::where([['user_id' , $board->pivot->user_id], ['board_id', $board->pivot->board_id]])->get()->first();

            if (!isset($board->lists)){
                return;
            }

            foreach($board->lists as $list){
                if (!isset($list->cards)){
                    return;
                } 

                foreach($list->cards as $card){

                    if ($id == $card->id){

                        if ($userRight->board_right_id === 1){
                            $isUserCard = true;
                        } else {
                            return Response::json('Error : You have not the correct rights to modify this board.', 400);
                        }    
                    }   
                }
            }
        }
        
        if ($isUserCard){

            if (isset($inputs['title'])){
                $card->title = $inputs['title'];
            }

            if (isset($inputs['description'])){
                $card->description = $inputs['description'];
            }

            if (isset($inputs['priority'])){
                $card->description = $inputs['priority'];
            }

            if (isset($inputs['limit_date'])){
                $card->description = $inputs['limit_date'];
            }

            $card->updated_at = now();
            $card->update();
            return Response::json("Card update succed.", 200);
        } else {
            return Response::json("Error : This is not one of your list.", 400);
        }
    }

    /**
     * @OA\delete(
     *     path="/api/cards/{id}",
     *     tags={"Cards"},
     *     summary="Delete card",
     *     description="Delete card by id",
     *      @OA\Parameter(
     *          description="Id of target card",
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description=""),
     * )
    */
    public function destroy(string $id)
    {

        $card = Card::find($id);

        $currentUser = Auth::user();

        if (!isset($currentUser)){
            return Response::json('Error : You are not connected.', 400);
        }

        if (!isset($currentUser->boards)){
            return Response::json('You have no board.', 400);
        }

        $isUserCard = false;

        foreach($currentUser->boards as $board){

            $userRight = UserBoard::where([['user_id' , $board->pivot->user_id], ['board_id', $board->pivot->board_id]])->get()->first();

            if (!isset($board->lists)){
                return;
            }

            foreach($board->lists as $list){

                if (!isset($list->cards)){
                    return;
                }

                foreach($list->cards as $card){

                    if ($card->id == $id){

                        if ($userRight->board_right_id === 1){
                            $isUserCard = true;
                        } else {
                            return Response::json('Error : You have not the correct rights to modify this board.', 400);
                        }
                    }
                }
            }
        }

        if ($isUserCard){
            $card->delete();
            return Response::json('The card have been deleted.', 200);
        } else {
            return Response::json('Error : You cannot delete this list.', 400);
        }
    }

}
