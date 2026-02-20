<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\EventStaff;
use Illuminate\Http\Request;

class EventStaffController extends Controller
{
    /* =====================================================
     * LIST STAFF DALAM 1 EVENT
     * ===================================================== */
    public function show($eventId)
    {
        $event = EventTraining::with(['training','participants'])
            ->findOrFail($eventId);

        $staffs = EventStaff::where('event_training_id',$eventId)
            ->get()
            ->groupBy('role')
            ->map(fn($items)=>$items->pluck('name')->implode(', '));

        // 🔥 hitung jumlah (buat guard UI)
        $instrukturCount = EventStaff::where('event_training_id',$eventId)
            ->where('role','Instruktur')->count();

        $officerCount = EventStaff::where('event_training_id',$eventId)
            ->where('role','Training Officer')->count();

        return view('event_training.show',[
            'event'=>$event,
            'staffs'=>$staffs,
            'instrukturCount'=>$instrukturCount,
            'officerCount'=>$officerCount
        ]);
    }

    /* =====================================================
     * LIST EVENT UNTUK PILIH
     * ===================================================== */
    public function eventIndex(Request $request)
    {
        $search = $request->search;

        $events = EventTraining::with('training')
            ->when($search,function($q) use ($search){
                $q->whereHas('training',function($qq) use ($search){
                    $qq->where('name','like',"%$search%");
                });
            })
            ->latest()
            ->paginate(5);

        return view('event_staff.event_index',compact('events','search'));
    }

    /* =====================================================
     * FORM TAMBAH STAFF
     * ===================================================== */
    public function create($eventId)
    {
        $event = EventTraining::with('training')->findOrFail($eventId);

        $instrukturCount = EventStaff::where('event_training_id',$eventId)
            ->where('role','Instruktur')->count();

        $officerCount = EventStaff::where('event_training_id',$eventId)
            ->where('role','Training Officer')->count();

        return view('event_staff.create',[
            'event'=>$event,
            'instrukturCount'=>$instrukturCount,
            'officerCount'=>$officerCount
        ]);
    }

    /* =====================================================
     * STORE STAFF (ANTI BUG MAX 2)
     * ===================================================== */
    public function store(Request $request,$eventId)
    {
        $event = EventTraining::findOrFail($eventId);

        $existingInstruktur = EventStaff::where('event_training_id',$eventId)
            ->where('role','Instruktur')->count();

        $existingOfficer = EventStaff::where('event_training_id',$eventId)
            ->where('role','Training Officer')->count();

        /* ===== GUARD KERAS ===== */
        if($existingInstruktur >= 2 && $existingOfficer >= 2){
            return back()->with('error','Instruktur & Officer sudah maksimal');
        }

        /* ===== SAVE INSTRUKTUR ===== */
        foreach($request->instrukturs ?? [] as $nama){
            if(!$nama) continue;
            if($existingInstruktur >= 2) break;

            EventStaff::create([
                'event_training_id'=>$eventId,
                'name'=>$nama,
                'role'=>'Instruktur'
            ]);

            $existingInstruktur++;
        }

        /* ===== SAVE OFFICER ===== */
        foreach($request->training_officers ?? [] as $nama){
            if(!$nama) continue;
            if($existingOfficer >= 2) break;

            EventStaff::create([
                'event_training_id'=>$eventId,
                'name'=>$nama,
                'role'=>'Training Officer'
            ]);

            $existingOfficer++;
        }

        return redirect()
            ->route('event-staff.show',$eventId)
            ->with('success','Staff berhasil disimpan');
    }

    /* =====================================================
     * DELETE STAFF
     * ===================================================== */
    public function destroy($id)
    {
        $staff = EventStaff::findOrFail($id);
        $eventId = $staff->event_training_id;

        $staff->delete();

        return redirect()
            ->route('event-staff.show',$eventId)
            ->with('success','Staff berhasil dihapus');
    }
}