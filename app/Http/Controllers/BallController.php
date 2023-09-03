<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Ball;
use Illuminate\Http\Request;

class BallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $balls = Ball::all();
        return response()->json([
            "response" => "success",
            "data" => $balls->toArray(),
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        //
        try {
            Ball::create($request->all());
            DB::table('suggest_buckets')->truncate();
            return response()->json([
                "response" => "success",
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "response" => "failed",
                "message" => $e->getMessage(),
                "trace" => $e->getTrace()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ball $ball)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ball $ball)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ball $ball)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ball $ball)
    {
        //
    }
}
