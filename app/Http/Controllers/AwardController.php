<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Http\Requests\StoreAwardRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $awards = Award::with(['conditions', 'rates'])->paginate(10);
        return view('awards.index', compact('awards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('awards.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAwardRequest $request)
    {
        DB::transaction(function () use ($request) {
            
            $award = Award::create($request->safe()->only(['name', 'pay_guide_link']));

            
            $award->conditions()->create($request->input('conditions'));

            
            $award->rates()->createMany($request->input('rates'));
        });

        return redirect()->route('awards.index')
            ->with('success', 'Award created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Award $award)
    {
        $award->load(['conditions', 'rates']);
        return view('awards.show', compact('award'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Award $award)
    {
        $award->load(['conditions', 'rates']);
        return view('awards.edit', compact('award'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAwardRequest $request, Award $award)
    {
        DB::transaction(function () use ($request, $award) {
            
            // 1. Update Basic Details
            $award->update($request->safe()->only(['name', 'pay_guide_link']));

            // 2. Update Conditions
            $award->conditions()->updateOrCreate(
                ['award_id' => $award->id],
                $request->input('conditions')
            );

            // 3. SYNC RATES (The Missing Part)
            // Because your form manages the whole list, the safest way to update is:
            // Wipe the old rates -> Re-create the new list.
            $award->rates()->delete();

            // Get rates from the form
            $rates = $request->input('rates', []);

            // Optional: Filter out any empty rows just to be safe
            $validRates = collect($rates)->filter(function ($rate) {
                return !empty($rate['employment_type']) && !empty($rate['category']);
            })->toArray();

            if (!empty($validRates)) {
                $award->rates()->createMany($validRates);
            }
        });

        return redirect()->route('awards.index')
            ->with('success', 'Award updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Award $award)
    {
        
        $award->delete();

        return redirect()->route('awards.index')
            ->with('success', 'Award deleted successfully.');
    }
}
