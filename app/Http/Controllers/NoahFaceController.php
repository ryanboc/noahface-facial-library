<?php

namespace App\Http\Controllers;
use App\Services\NoahFaceService;
use App\Models\NoahFaceEvent;
use Illuminate\Support\Facades\Log;


use Illuminate\Http\Request;

class NoahFaceController extends Controller
{
    protected $api;

    public function __construct(NoahFaceService $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        return response()->json($this->api->getUsers());
    }

    public function show($guid)
    {
        return response()->json($this->api->getUserByGuid($guid));
    }

    public function receiveEvent(Request $request)
    {
        // Optional: Authenticate if needed
        //$username = $request->getUser();
        //$password = $request->getPassword();

        // Get JSON data
        //$eventData = $request->all();

        // Log or process the event
        //Log::info('NoahFace Event Received:', $eventData);

        // Respond 200 OK
        //return response()->json(['status' => 'success'], 200);

        // Optional Basic Auth Check
    $username = $request->getUser();
    $password = $request->getPassword();

    if ($username !== env('NOAHFACE_USERNAME') || $password !== env('NOAHFACE_PASSWORD')) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $eventData = $request->all();

    Log::info('NoahFace Event Received:', $eventData);

    // Save event to database
    NoahFaceEvent::create([
        'eventid' => $eventData['eventid'] ?? null,
        'utc' => $eventData['utc'] ?? null,
        'time' => $eventData['time'] ?? null,
        'org' => $eventData['org'] ?? null,
        'site' => $eventData['site'] ?? null,
        'device' => $eventData['device'] ?? null,
        'devid' => $eventData['devid'] ?? null,
        'type' => $eventData['type'] ?? null,
        'detail' => $eventData['detail'] ?? null,
        'method' => $eventData['method'] ?? null,
        'userid' => $eventData['userid'] ?? null,
        'number' => $eventData['number'] ?? null,
        'firstname' => $eventData['firstname'] ?? null,
        'lastname' => $eventData['lastname'] ?? null,
        'cardnum' => $eventData['cardnum'] ?? null,
        'latitude' => $eventData['latitude'] ?? null,
        'longitude' => $eventData['longitude'] ?? null,
        'altitude' => $eventData['altitude'] ?? null,
        'accuracy' => $eventData['accuracy'] ?? null,
        'temperature' => $eventData['temperature'] ?? null,
        'elevated' => $eventData['elevated'] ?? null,
        'timing' => $eventData['timing'] ?? null,
        'sentiment' => $eventData['sentiment'] ?? null,
        'usertype' => $eventData['usertype'] ?? null,
    ]);

    return response()->json(['status' => 'success'], 200);
    }
}
