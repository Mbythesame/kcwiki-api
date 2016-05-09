<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use App\Tyku, App\Path, App\EnemyFleet, App\Enemy, App\ShipAttr, App\InitEquip, App\MapEvent;

// Reporter API
$app->post('/tyku', ['middleware' => 'cache',function(Request $request){
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3',
        'tyku' => 'required|digits_between:1,4',
        'rank' => 'required|size:1',
        'version' => 'required'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    Tyku::create([
       'mapAreaId' => $request->input('mapAreaId'),
       'mapId' => $request->input('mapId'),
       'cellId' => $request->input('cellId'),
       'tyku' => $request->input('tyku'),
       'rank' => $request->input('rank')
    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/path', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'path' => 'required|array',
        'decks' => 'required|array'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    Path::create([
       'mapAreaId' => $request->input('mapAreaId'),
       'mapId' => $request->input('mapId'),
       'path' => json_encode($request->input('path')),
       'decks' => json_encode($request->input('decks'))
    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/enemy', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'enemyId' => 'required|array',
        'maxHP' => 'required|array',
        'slots' => 'required|array',
        'param' => 'required|array',
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    EnemyFleet::create([
        'mapAreaId' => $request->input('mapAreaId'),
        'mapId' => $request->input('mapId'),
        'cellId' => $request->input('cellId'),
        'fleets' => json_encode($request->input('enemyId'))
    ]);
    $enemies = $request->input('enemyId');
    $maxHP = $request->input('maxHP');
    $slots = $request->input('slots');
    $param = $request->input('param');
    for ($i = 0; $i < count($enemies); $i++) {
        if ($enemies[$i] == -1) continue;
        $row = [
           'enemyId' => $enemies[$i],
           'maxHP' => $maxHP[$i],
           'slot1' => $slots[$i][0],
           'slot2' => $slots[$i][1],
           'slot3' => $slots[$i][2],
           'slot4' => $slots[$i][3],
           'slot5' => $slots[$i][4],
           'houg' => $param[$i][0],
           'raig' => $param[$i][1],
           'tyku' => $param[$i][2],
           'souk' => $param[$i][3]
        ];
        $hash = md5(json_encode($row));
        if (Cache::has($hash)) continue;
        Cache::forever($hash, 1);
        Enemy::create($row);
    }
    return response()->json(['result'=>'success']);
}]);

$app->post('/shipAttr', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'sortno' => 'required|digits_between:1,4',
        'taisen' => 'required|digits_between:1,3',
        'kaihi' => 'required|digits_between:1,3',
        'sakuteki' => 'required|digits_between:1,3',
        'luck' => 'required|digits_between:1,3',
        'level' => 'required|digits_between:1,3'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    ShipAttr::Create([
       'sortno' => $request->input('sortno'),
       'taisen' => $request->input('taisen'),
       'kaihi' => $request->input('kaihi'),
       'sakuteki' => $request->input('sakuteki'),
       'luck' => $request->input('luck'),
       'level' => $request->input('level')
    ]);
    return response()->json(['result'=>'success']);
}]);

$app->post('/initEquip', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'ships' => 'required|array'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $ships = $request->input('ships');
    foreach ($ships as $sortno => $ship) {
        $row = ['sortno' => $sortno];
        for ($i=0; $i<count($ship); $i++) {
            $j = $i + 1;
            $row["slot$j"] = $ship[$i];
        }
        InitEquip::create($row);
    }
    return response()->json(['result'=>'success']);
}]);

$app->post('/mapEvent', ['middleware' => 'cache', function(Request $request) {
    $rules = [
        'mapAreaId' => 'required|digits_between:1,3',
        'mapId' => 'required|digits_between:1,3',
        'cellId' => 'required|digits_between:1,3',
        'eventId' => 'required|digits_between:1,2',
        'eventType' => 'required|digits_between:1,2',
        'count' => 'required|digits_between:1,3',
        'dantan' => 'boolean'
    ];
    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) {
        return response()->json(['result'=>'error', 'reason'=> 'Data invalid']);
    }
    $inputs = $request->all();
    $dantan = array_key_exists('dantan', $inputs) ? $inputs['dantan'] : false;
    MapEvent::create([
        'mapAreaId' => $inputs['mapAreaId'],
        'mapId' => $inputs['mapId'],
        'cellId' => $inputs['cellId'],
        'eventId' => $inputs['eventId'],
        'eventType' => $inputs['eventType'],
        'count' => $inputs['count'],
        'dantan' => $dantan
    ]);
    return response()->json(['result' => 'success']);
}]);