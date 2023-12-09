<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Card;
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
     *     summary="Get card of all cards",
     *     description="Returns card of all cards",
     *     @OA\Response(response="200", description="Array[] : contain all cards"),
     *     @OA\Response(response="419", description="Delay error : CSRF Token missed ?")
     * )
     */
    public function index()
    {
        return Card::all();
    }

     /* ------------- STORE card ------------ */
    /**
     * @OA\Post(
     *     path="/api/cards",
     *     tags={"cards"},
     *     summary="Insert new card",
     *     description="Create new card to DB",
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
        $card = new Card;

        if (empty($request->title)){
            return Response::json('Error : Please insert a title for the board.', 400);
        }

        if (!$id){
            return Response::json('Error : Nedd a card id target.', 400);
        }

        if (empty(Auth::user())){
            return Response::json('Error : You are not conected.', 400);
        }

        $currentUser = Auth::user();

        $card->title = $request->title;
        $card->list_id = $id;
        $card->save();
        return $card::find($card);
        
    }
}
