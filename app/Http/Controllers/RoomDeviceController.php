<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomDevice;
use Illuminate\Http\Request;
use App\Exports\RoomDeviceExport;
use Maatwebsite\Excel\Facades\Excel;

class RoomDeviceController extends Controller
{
    private function getMyRoom(): Room
    {
        $room = Room::where('manager_id', auth()->id())->first();
        if (!$room) abort(403, 'Ban chua duoc gan quan ly phong nao.');
        return $room;
    }

    public function index(Request $request)
    {
        $room  = $this->getMyRoom();
        $query = $room->devices()->latest();

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('name', 'like', "%{$q}%");
        }

        if ($request->filled('condition')) {
            switch ($request->condition) {
                case 'broken':
                    $query->where('broken_qty', '>', 0);
                    break;
                case 'consumed':
                    $query->where('consumed_qty', '>', 0);
                    break;
                case 'lost':
                    $query->where('lost_qty', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->whereRaw('quantity - broken_qty - consumed_qty - lost_qty <= 0');
                    break;
            }
        }

        // Calculate global totals for the filtered query
        $totalStats = [
            'quantity' => (clone $query)->sum('quantity'),
            'broken'   => (clone $query)->sum('broken_qty'),
            'consumed' => (clone $query)->sum('consumed_qty'),
            'lost'     => (clone $query)->sum('lost_qty'),
        ];
        $totalStats['available'] = (clone $query)->get()->sum(fn($d) => $d->availableQty());

        $devices = $query->paginate(10)->withQueryString();
        return view('room-devices.index', compact('room', 'devices', 'totalStats'));
    }

    /**
     * Xuất Excel Room Devices
     */
    public function export(Request $request)
    {
        $room = $this->getMyRoom();
        $filters = $request->only(['search', 'condition']);
        
        return Excel::download(new RoomDeviceExport($room, $filters), 'thiet-bi-phong-' . $room->id . '.xlsx');
    }

    /** GET /room-devices-status — form cap nhat tinh trang hang loat */
    public function statusForm()
    {
        $room    = $this->getMyRoom();
        $devices = $room->devices()->latest()->get();
        return view('room-devices.status', compact('room', 'devices'));
    }

    /** POST /room-devices-status — luu cap nhat */
    public function batchUpdate(Request $request)
    {
        $room = $this->getMyRoom();

        $request->validate([
            'devices'                => ['required', 'array'],
            'devices.*.broken_qty'   => ['required', 'integer', 'min:0'],
            'devices.*.consumed_qty' => ['required', 'integer', 'min:0'],
            'devices.*.lost_qty'     => ['required', 'integer', 'min:0'],
        ]);

        $errors = [];
        foreach ($request->devices as $id => $data) {
            $device = RoomDevice::find($id);
            if (!$device || $device->room_id !== $room->id) continue;

            $broken   = (int) ($data['broken_qty']   ?? 0);
            $consumed = (int) ($data['consumed_qty']  ?? 0);
            $lost     = (int) ($data['lost_qty']      ?? 0);

            if ($broken + $consumed + $lost > $device->quantity) {
                $errors[] = $device->name . ': tong hong+tieu hao+mat vuot qua so luong (' . $device->quantity . ').';
                continue;
            }

            $device->update([
                'broken_qty'   => $broken,
                'consumed_qty' => $consumed,
                'lost_qty'     => $lost,
            ]);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->with('warning', 'Mot so thiet bi khong duoc cap nhat.');
        }

        return redirect()->route('room-devices.status')
                         ->with('success', 'Da cap nhat tinh trang thiet bi thanh cong!');
    }

    public function create()
    {
        $room = $this->getMyRoom();
        return view('room-devices.create', compact('room'));
    }

    public function store(Request $request)
    {
        $room      = $this->getMyRoom();
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:200'],
            'unit'     => ['required', 'string', 'max:30'],
            'quantity' => ['required', 'integer', 'min:0'],
            'note'     => ['nullable', 'string'],
        ], [
            'name.required'     => 'Ten thiet bi khong duoc de trong.',
            'unit.required'     => 'Don vi khong duoc de trong.',
            'quantity.required' => 'So luong khong duoc de trong.',
        ]);

        $room->devices()->create($validated);
        return redirect()->route('room-devices.index')
                         ->with('success', 'Da them thiet bi: ' . $validated['name']);
    }

    public function edit(RoomDevice $roomDevice)
    {
        $room = $this->getMyRoom();
        if ($roomDevice->room_id !== $room->id) abort(403);
        return view('room-devices.edit', compact('room', 'roomDevice'));
    }

    public function update(Request $request, RoomDevice $roomDevice)
    {
        $room = $this->getMyRoom();
        if ($roomDevice->room_id !== $room->id) abort(403);

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:200'],
            'unit'         => ['required', 'string', 'max:30'],
            'quantity'     => ['required', 'integer', 'min:0'],
            'broken_qty'   => ['required', 'integer', 'min:0'],
            'consumed_qty' => ['required', 'integer', 'min:0'],
            'lost_qty'     => ['required', 'integer', 'min:0'],
            'note'         => ['nullable', 'string'],
        ]);

        $roomDevice->update($validated);
        return redirect()->route('room-devices.index')
                         ->with('success', 'Da cap nhat: ' . $roomDevice->name);
    }

    public function destroy(RoomDevice $roomDevice)
    {
        $room = $this->getMyRoom();
        if ($roomDevice->room_id !== $room->id) abort(403);
        $roomDevice->delete();
        return redirect()->route('room-devices.index')->with('success', 'Da xoa thiet bi.');
    }
}
