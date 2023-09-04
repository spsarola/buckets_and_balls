<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Ball;
use App\Models\Bucket;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SuggestBucket;
use Illuminate\Support\Facades\DB;

class BucketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return view('form', []);
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
        try {
            Bucket::create($request->all());
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
    public function show(Bucket $bucket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bucket $bucket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bucket $bucket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bucket $bucket)
    {
        //
    }

    public function suggestBuckets(Request $request)
    {

        try {
            $buckets = Bucket::
                // select('buckets.id', 'buckets.name', 'buckets.volume AS full_volume', 'IFNULL(temp_used_volume.volume,0) AS used_volume', '(buckets.volume - IFNULL(temp_used_volume.volume,0)) AS volume')
                select(DB::raw('buckets.id, buckets.name, buckets.volume AS full_volume, IFNULL(temp_used_volume.volume,0) AS used_volume, (buckets.volume - IFNULL(temp_used_volume.volume,0)) AS volume'))
                ->leftJoin(DB::raw('(SELECT bucket_id, SUM(volume) AS volume FROM suggest_buckets GROUP BY bucket_id) AS temp_used_volume'), 'temp_used_volume.bucket_id', '=', 'buckets.id')
                ->whereRaw("(buckets.volume - IFNULL(temp_used_volume.volume,0)) > 0")
                ->orderBy('full_volume', "DESC")
                ->orderBy('volume', "DESC")
                ->get()
                ->toArray()
                // ->toSql()
            ;


            // arsort($buckets);
            $balls = $request->balls;
            $balls = array_filter($balls);
            // arsort($balls);
            $ballSizes = Ball::whereIn('color', array_keys($balls))->get()->toArray();
            // ->pluck('size', 'color');
            $gradTotalOfBalls = 0;
            $totalBallSizes = [];

            foreach ($ballSizes as $key => $ball) {
                $color = $ball['color'];
                $qty = $balls[$color];
                $ballSizes[$key]['qty'] = $qty;
                $totalBallSize = number_format($qty * $ball['size'], 2, '.', '');
                $ballSizes[$key]['total_value'] = $totalBallSize;
                $gradTotalOfBalls += $totalBallSize;
            }

            $keys = array_column($ballSizes, 'total_value');
            array_multisort($keys, SORT_DESC, $ballSizes);

            $originalBucket = $buckets;
            $originalBallSizes = $ballSizes;

            if (0) {
                return response()->json([
                    "response" => "success",
                    "balls" => $balls,
                    // "buckets" => $buckets,
                    "originalBucket" => $originalBucket,
                    "originalBallSizes" => $originalBallSizes,
                    "ballSizes" => $ballSizes,
                    "totalBallSizes" => $totalBallSizes,
                ], 200);
            }

            //check for grand total with each bucket size in this case we need to only need only onebucket
            $SuggestBucket = [];
            foreach ($ballSizes as  $ballKey => $ball) {
                $total_value = $ball['total_value'];

                foreach ($buckets as $key => $bucket) {

                    if ($total_value == $bucket['volume']) {
                        //bucket used and fixed

                        //need to insert the record in relational tables and remove the buckets
                        $inserData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'qty' => $ball['qty'], 'volume' => $total_value];
                        $SuggestBucket[] = $inserData;
                        SuggestBucket::create($inserData);
                        $SuggestBucket[] = array_merge($inserData, ['note' => 'main if']);
                        $ballSizes[$ballKey]['qty'] = $ball['qty'] = 0;
                        /**Need to remove bucket because its full */
                        unset($buckets[$key]);
                        break; //we break here due to all ball placed in the bucket now we need to take anohter balls
                    } elseif ($total_value < $bucket['volume']) {
                        //bucket used and still empty for some more balls

                        $buckets[$key]['volume'] -= $total_value;
                        //need to insert the record in relational tables
                        $inserData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'qty' => $ball['qty'], 'volume' => $total_value];
                        $SuggestBucket[] = array_merge($inserData, ['note' => '1st else if']);
                        SuggestBucket::create($inserData);
                        $ballSizes[$ballKey]['qty'] = $ball['qty'] = 0;

                        break; //we break here due to all ball placed in the bucket now we need to take anohter balls
                    } elseif ($total_value > $bucket['volume']) {
                        $occupiedQty = floor($bucket['volume'] / $ball['size']);
                        if ($occupiedQty > 0) {
                            $occupiedTotalValue = $occupiedQty * $ball['size'];

                            $inserData = ['bucket_id' => $bucket['id'], 'ball_id' => $ball['id'], 'qty' => $occupiedQty, 'volume' => $occupiedTotalValue];
                            SuggestBucket::create($inserData);
                            $SuggestBucket[] = array_merge($inserData, ['note' => 'last else if']);
                            //**Need to update total value because still some ball we need to put in another bucket*/
                            $ballSizes[$ballKey]['qty'] = $ball['qty'] = $ball['qty'] - $occupiedQty;

                            $buckets[$key]['volume'] -= $occupiedTotalValue;
                            $total_value = number_format($ball['qty'] * $ball['size'], 2, '.', '');
                        }
                    }
                }
            }

            $getWarningMessage = $this->getWarningMessage($ballSizes);
            
            return response()->json([
                "response" => "success",
                "balls" => $balls,
                // "buckets" => $buckets,
                "originalBucket" => $originalBucket,
                "originalBallSizes" => $originalBallSizes,
                "ballSizes" => $ballSizes,
                "totalBallSizes" => $totalBallSizes,
                "SuggestBucket" => $SuggestBucket,
                "getWarningMessage" => $getWarningMessage
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "response" => "failed",
                "message" => $e->getMessage(),
                "trace" => $e->getTrace()
            ], 500);
        }
    }

    private function getWarningMessage($ballSizes)
    {
        $finalMessage = "";
        $message = "";
        $messages = [];
        $numberOfBallNotPlace = 0;
        foreach ($ballSizes as $ball) {
            $ballStr = Str::plural('ball', $ball['qty']);
            $color = ucfirst($ball['color']);
            if ($ball['qty'] > 0) {
                $message .= " {$ball['qty']} {$color} {$ballStr} and";
                $numberOfBallNotPlace++;
            }
        }

        if ($message != '') {
            return trim($message, "and") . Str::plural('is', $numberOfBallNotPlace)
                . " not place due to either all bucket fulls or not required space is empty to any bucket.";
        } else {
            return '';
        }
    }

    public function suggestedBucketsList(Request $request)
    {
        $buckets = Bucket::select(DB::raw('buckets.id, buckets.name, buckets.volume AS full_volume, IFNULL(temp_used_volume.volume,0) AS used_volume
        , (buckets.volume - IFNULL(temp_used_volume.volume,0)) AS volume, balls.color, balls.size, temp_used_volume.qty'))
        ->leftJoin(DB::raw('(SELECT bucket_id, ball_id, SUM(volume) AS volume, SUM(qty) AS qty  FROM `suggest_buckets` GROUP BY bucket_id, ball_id) temp_used_volume'), 'temp_used_volume.bucket_id', '=', 'buckets.id')
        // ->whereRaw("(buckets.volume - IFNULL(temp_used_volume.volume,0)) > 0")
        ->join('balls', 'temp_used_volume.ball_id', '=', 'balls.id')
        ->orderBy('buckets.id', "ASC")
        ->get();

        if ($buckets->isEmpty()) {
            $buckets = [];
        } else {
            $listArr = [];
            foreach ($buckets as $bucket) {
                $ballStr = Str::plural('ball', $bucket['qty']);
                $color = ucfirst($bucket['color']);
                $listArr[$bucket['name']]['balls'][] = "{$bucket['qty']} {$color} {$ballStr}";
                $listArr[$bucket['name']]['used_volume'][] = $bucket['used_volume'];
                $listArr[$bucket['name']]['full_volume'] = $bucket['full_volume'];
            }

            $finalListArr = [];
            foreach ($listArr as  $bucketName => $Bucketlist) {
                $bucketName = strtoupper($bucketName);
                $allBallsString = implode(" and ", $Bucketlist['balls']);
                $totalUsedVolume = array_sum($Bucketlist['used_volume']);
                $remainingVolume = number_format($Bucketlist['full_volume'] - $totalUsedVolume, 2, '.', '');
                $finalListArr[] = "Bucket {$bucketName}: <strong>Place {$allBallsString}</strong> and remaining volume is {$remainingVolume}";
            }
        }

        return response()->json([
            "response" => "success",
            "data" => $finalListArr,
            "buckets" => $buckets,
            "listArr" => $listArr,
        ], 200);
    }
}
