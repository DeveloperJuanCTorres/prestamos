<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('calendar.index');
    }

    public function store(Request $request)
    {
        return Event::create([
            'title'  => $request->title,
            'start'  => Carbon::parse($request->start),
            'end'    => $request->end ? Carbon::parse($request->end) : null,
            'allDay' => $request->allDay ? 1 : 0
        ]);
    }

    public function load()
    {
        return Event::all();
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $event->update([
            'title'  => $request->title,
            'start'  => Carbon::parse($request->start),
            'end'    => $request->end ? Carbon::parse($request->end) : null,
            'allDay' => $request->allDay ? 1 : 0
        ]);
        return $event;
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return response()->json(['success'=>true]);
    }
}
