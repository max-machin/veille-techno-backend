<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;

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
}
