<?php

namespace App\Http\Controllers\Backend;

use App\ExhibitionGame;
use App\Game;
use App\Http\Requests\StoreOrUpdateExhibition;
use App\Exhibition;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ExhibitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all platforms except the empty one
        $exhibitions = Exhibition::orderBy('starts_at', 'asc')->get();

        return view('backend.exhibition.index', compact('exhibitions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // Initiate a new platform with some defined values
        $exhibition = new Exhibition();
        $exhibition->starts_at = Carbon::now();
        $exhibition->ends_at = Carbon::now();

        return view('backend.exhibition.create', compact('exhibition'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrUpdateExhibition $request)
    {
        $data = $request->validated();

        // make that slug readable
        $data['slug'] = str_replace(" ", "-", $data['slug']);
        $data['slug'] = preg_replace("/[^a-zA-Z0-9-]+/", "", $data['slug']);
        $data['starts_at'] = Carbon::parse($data['starts_at']);
        $data['ends_at'] = Carbon::parse($data['ends_at']);

        // Save into another databse
        //        DB::purge('mysql');
        //        Config::set('database.connections.mysql.database', 'db_test');

        // Save
        Exhibition::create($data);

        return redirect('/admin/exhibitions');
    }

    /**
     * Display the specified resource.
     *
     * @param Exhibition $exhibition
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Exhibition $exhibition)
    {
        return view('backend.exhibition.show', compact('exhibition'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Exhibition $exhibition
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Exhibition $exhibition)
    {
        $games = Game::all();

        return view('backend.exhibition.edit', compact('exhibition', 'games'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Exhibition $exhibition
     *
     * @return \Illuminate\Http\Response
     */
    public function update(StoreOrUpdateExhibition $request, Exhibition $exhibition)
    {
        // make that slug readable
        $request['slug'] = str_replace(" ", "-", $request['slug']);
        $request['slug'] = preg_replace("/[^a-zA-Z0-9-]+/", "", $request['slug']);
        $request['starts_at'] = Carbon::parse($request['starts_at']);
        $request['ends_at'] = Carbon::parse($request['ends_at']);

        // Save the updates
        $exhibition->update($request->all());

        return Redirect::to('/admin/exhibitions');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Exhibition $exhibition
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Exhibition $exhibition)
    {
        // Delete the game
        $exhibition->delete();

        return Redirect::to('/admin/exhibitions');

    }

    public function save_exhibition_game(Request $request) {

        $data = $request->except('_token');

        $data['developer_id'] = $data['developer_id-' . $data['game_id']];
        $data['publisher_id'] = $data['publisher_id-' . $data['game_id']];

        unset($data['developer_id-' . $data['game_id']]);
        unset($data['publisher_id-' . $data['game_id']]);

        if($data['developer_id']) {
            foreach($data['developer_id'] as $developer) {
                foreach($data['publisher_id'] as $publisher) {
                    $data['developer_id'] = (is_numeric($developer) ? $developer : null);
                    $data['publisher_id'] = (is_numeric($publisher) ? $publisher : null);

                    ExhibitionGame::create($data->toArray());
                }
            }
        } else {
            foreach($data['publisher_id'] as $publisher) {
                $data['developer_id'] = null;
                $data['publisher_id'] = (is_numeric($publisher) ? $publisher : null);

                ExhibitionGame::create($data);
            }
        }

        $exhibition = Exhibition::find($data['exhibition_id']);


        return redirect('/admin/exhibitions/' . $exhibition->slug . '/edit');
    }
}
