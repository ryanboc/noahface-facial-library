<?php

namespace App\Http\Controllers;

use App\Services\NoahFaceService;
use App\Models\NoahFaceEvent;   // The Raw Log
use App\Models\Employee;        // <--- REQUIRED for Payroll
use App\Models\AttendanceLog;   // <--- REQUIRED for Payroll
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;              // <--- REQUIRED for Time

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
        // 1. Log IMMEDIATELY to confirm iPad reached server
        // This helps you see if the request arrived, even if password fails later
        Log::info('NoahFace Hit Received. User: ' . $request->getUser());

        // 2. Authentication (Using CONFIG, not ENV)
        $username = $request->getUser();
        $password = $request->getPassword();

        // FIX: Use config() helper to read from services.php
        if ($username !== config('services.noahface.username') || 
            $password !== config('services.noahface.password')) {
            
            Log::warning("NoahFace Auth Failed. Provided: [$username]");
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $eventData = $request->all();
        Log::info('NoahFace Payload:', $eventData);

        // 3. Save RAW Event (Audit Trail)
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

        // 4. SYNC TO PAYROLL SYSTEM (The Missing Piece)
        // This puts the data into the 'attendance_logs' table for your Dashboard
        $incomingId = $eventData['userid'] ?? null;

        if ($incomingId) {
            // Find the Employee linked to this NoahFace ID
            $employee = Employee::where('noahface_id', $incomingId)->first();

            if ($employee) {
                AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'clock_time'  => isset($eventData['time']) ? Carbon::parse($eventData['time']) : now(),
                    'event_type'  => $eventData['type'] ?? 'unknown',
                    'location'    => $eventData['site'] ?? null,
                    'raw_payload' => $eventData
                ]);
                Log::info("Payroll Log Saved for: {$employee->name}");
            } else {
                Log::warning("Payroll Skipped: No employee found for ID [{$incomingId}]");
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}