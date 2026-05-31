<?php

namespace Modules\NexcoreAddress\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\NexcoreAddress\Models\NxAddress;
use Modules\NexcoreAddress\Models\NxAddressDetail;
use Modules\NexcoreAddress\Models\NxAddressLink;
use Modules\NexcoreAddress\Models\NxAddressType;
use Modules\CIMS_PMPRO\Models\PmproProvince;

class AddressController extends Controller
{
    // ─── ADDRESS REGISTRY (standalone list) ───

    public function index(Request $request)
    {
        $query = NxAddress::with(['province', 'suburb', 'links.addressType'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('street_name', 'like', "%{$s}%")
                  ->orWhere('street_number', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('complex_name', 'like', "%{$s}%")
                  ->orWhere('postal_code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('province')) {
            $query->where('province_id', $request->province);
        }

        if ($request->filled('category')) {
            $query->where('address_category', $request->category);
        }

        $addresses = $query->orderBy('street_name')->paginate(25);
        $provinces = PmproProvince::where('is_active', true)->orderBy('name')->get();

        return view('nexcore_address::index', compact('addresses', 'provinces'));
    }

    public function create()
    {
        $addressTypes = NxAddressType::active()->orderBy('name')->get();
        $provinces    = PmproProvince::where('is_active', true)->orderBy('name')->get();

        return view('nexcore_address::form', compact('addressTypes', 'provinces'));
    }

    public function store(Request $request)
    {
        $request->validate($this->addressValidationRules());

        $address = $this->saveAddress($request);
        $this->saveDetails($request, $address);

        return redirect()->route('nexcore.addresses.index')
            ->with('success', 'Address created successfully.');
    }

    public function edit($id)
    {
        $address      = NxAddress::with(['details', 'province', 'suburb'])->findOrFail($id);
        $addressTypes = NxAddressType::active()->orderBy('name')->get();
        $provinces    = PmproProvince::where('is_active', true)->orderBy('name')->get();

        return view('nexcore_address::form', compact('address', 'addressTypes', 'provinces'));
    }

    public function update(Request $request, $id)
    {
        $address = NxAddress::findOrFail($id);
        $request->validate($this->addressValidationRules());

        $this->updateAddress($request, $address);
        $this->saveDetails($request, $address);

        return redirect()->route('nexcore.addresses.index')
            ->with('success', 'Address updated successfully.');
    }

    public function destroy($id)
    {
        $address = NxAddress::findOrFail($id);

        $linkCount = NxAddressLink::where('address_id', $id)->count();
        if ($linkCount > 0) {
            return redirect()->route('nexcore.addresses.index')
                ->with('error', "Cannot delete — this address is linked to {$linkCount} record(s). Unlink them first.");
        }

        $address->delete();

        return redirect()->route('nexcore.addresses.index')
            ->with('success', 'Address deleted successfully.');
    }

    public function toggle($id)
    {
        $address = NxAddress::findOrFail($id);
        $address->update(['is_active' => !$address->is_active]);

        $status = $address->is_active ? 'activated' : 'deactivated';
        return redirect()->route('nexcore.addresses.index')
            ->with('success', "Address {$status} successfully.");
    }

    // ─── CONTEXTUAL (called from other modules) ───

    public function createForEntity($linkableType, $linkableId)
    {
        $addressTypes = NxAddressType::active()->orderBy('name')->get();
        $provinces    = PmproProvince::where('is_active', true)->orderBy('name')->get();

        return view('nexcore_address::form', compact('addressTypes', 'provinces', 'linkableType', 'linkableId'));
    }

    public function storeForEntity(Request $request, $linkableType, $linkableId)
    {
        $request->validate(array_merge($this->addressValidationRules(), [
            'address_type_id' => 'required|integer',
            'address_label'   => 'nullable|string|max:100',
            'is_primary'      => 'nullable|boolean',
            'notes'           => 'nullable|string',
        ]));

        if ($request->filled('existing_address_id')) {
            $address = NxAddress::findOrFail($request->existing_address_id);
        } else {
            $address = $this->saveAddress($request);
            $this->saveDetails($request, $address);
        }

        if ($request->boolean('is_primary')) {
            NxAddressLink::where('linkable_type', $linkableType)
                ->where('linkable_id', $linkableId)
                ->update(['is_primary' => false]);
        }

        NxAddressLink::create([
            'address_id'      => $address->id,
            'linkable_type'   => $linkableType,
            'linkable_id'     => $linkableId,
            'address_type_id' => $request->address_type_id,
            'address_label'   => $request->address_label,
            'notes'           => $request->notes,
            'is_primary'      => $request->boolean('is_primary'),
            'is_active'       => true,
        ]);

        $returnUrl = $request->input('return_url', route('nexcore.addresses.index'));
        return redirect()->to($returnUrl)->with('success', 'Address linked successfully.');
    }

    public function editLink($linkId)
    {
        $link = NxAddressLink::with(['address.details', 'address.province', 'address.suburb'])
            ->findOrFail($linkId);

        $address      = $link->address;
        $addressTypes = NxAddressType::active()->orderBy('name')->get();
        $provinces    = PmproProvince::where('is_active', true)->orderBy('name')->get();

        return view('nexcore_address::form', compact('link', 'address', 'addressTypes', 'provinces'));
    }

    public function updateLink(Request $request, $linkId)
    {
        $link = NxAddressLink::findOrFail($linkId);

        $request->validate(array_merge($this->addressValidationRules(), [
            'address_type_id' => 'required|integer',
            'address_label'   => 'nullable|string|max:100',
            'is_primary'      => 'nullable|boolean',
            'notes'           => 'nullable|string',
        ]));

        $this->updateAddress($request, $link->address);
        $this->saveDetails($request, $link->address);

        if ($request->boolean('is_primary')) {
            NxAddressLink::where('linkable_type', $link->linkable_type)
                ->where('linkable_id', $link->linkable_id)
                ->where('id', '!=', $linkId)
                ->update(['is_primary' => false]);
        }

        $link->update([
            'address_type_id' => $request->address_type_id,
            'address_label'   => $request->address_label,
            'notes'           => $request->notes,
            'is_primary'      => $request->boolean('is_primary'),
        ]);

        $returnUrl = $request->input('return_url', route('nexcore.addresses.index'));
        return redirect()->to($returnUrl)->with('success', 'Address updated successfully.');
    }

    public function destroyLink($linkId)
    {
        $link = NxAddressLink::findOrFail($linkId);
        $wasPrimary = $link->is_primary;
        $linkableType = $link->linkable_type;
        $linkableId = $link->linkable_id;

        $link->delete();

        if ($wasPrimary) {
            $nextLink = NxAddressLink::where('linkable_type', $linkableType)
                ->where('linkable_id', $linkableId)
                ->where('is_active', true)
                ->first();

            if ($nextLink) {
                $nextLink->update(['is_primary' => true]);
            }
        }

        $returnUrl = request()->input('return_url', route('nexcore.addresses.index'));
        return redirect()->to($returnUrl)->with('success', 'Address unlinked successfully.');
    }

    public function toggleLink($linkId)
    {
        $link = NxAddressLink::findOrFail($linkId);

        if ($link->is_active && $link->is_primary) {
            $otherActive = NxAddressLink::where('linkable_type', $link->linkable_type)
                ->where('linkable_id', $link->linkable_id)
                ->where('id', '!=', $linkId)
                ->where('is_active', true)
                ->first();

            if (!$otherActive) {
                return back()->with('error', 'This is the only active address — it cannot be deactivated.');
            }

            $link->update(['is_active' => false, 'is_primary' => false]);
            $otherActive->update(['is_primary' => true]);

            return back()->with('success', 'Address deactivated. Primary switched to "' . ($otherActive->address_label ?? 'Address') . '".');
        }

        $link->update(['is_active' => !$link->is_active]);

        return back()->with('success', 'Address status updated.');
    }

    // ─── AJAX: Search Registry ───

    public function searchRegistry(Request $request)
    {
        $q = $request->get('q', '');

        $query = NxAddress::where('is_active', true);

        if (strlen($q) >= 2) {
            $query->where(function ($qb) use ($q) {
                $qb->where('street_name', 'like', "%{$q}%")
                    ->orWhere('street_number', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('complex_name', 'like', "%{$q}%")
                    ->orWhere('postal_code', 'like', "%{$q}%");
            });
        }

        $addresses = $query->with(['province', 'suburb'])
            ->orderBy('street_name')
            ->limit(50)
            ->get()
            ->map(function ($addr) {
                $line1 = trim(
                    ($addr->unit_number ? 'Unit ' . $addr->unit_number . ', ' : '') .
                    ($addr->complex_name ? $addr->complex_name . ', ' : '') .
                    $addr->street_number . ' ' . $addr->street_name
                );
                $line2 = trim(
                    ($addr->suburb ? $addr->suburb->name . ', ' : '') .
                    $addr->city . ', ' .
                    ($addr->province ? $addr->province->name : '') . ', ' .
                    $addr->postal_code
                );
                return [
                    'id'       => $addr->id,
                    'line1'    => $line1,
                    'line2'    => $line2,
                    'category' => $addr->address_category,
                ];
            });

        return response()->json($addresses);
    }

    // ─── PRIVATE HELPERS ───

    private function addressValidationRules()
    {
        return [
            'street_number'             => 'required|string|max:50',
            'street_name'               => 'required|string|max:255',
            'city'                      => 'required|string|max:200',
            'postal_code'               => 'required|string|max:10',
            'province_id'               => 'required|integer',
            'address_category'          => 'required|string',
            'unit_number'               => 'nullable|string|max:50',
            'complex_name'              => 'nullable|string|max:200',
            'suburb_id'                 => 'nullable|integer',
            'municipality_id'           => 'nullable|integer',
            'ward_id'                   => 'nullable|integer',
            'latitude'                  => 'nullable|numeric',
            'longitude'                 => 'nullable|numeric',
            'google_formatted_address'  => 'nullable|string',
        ];
    }

    private function saveAddress(Request $request)
    {
        return NxAddress::create([
            'unit_number'               => $request->unit_number,
            'complex_name'              => $request->complex_name,
            'street_number'             => $request->street_number,
            'street_name'               => $request->street_name,
            'suburb_id'                 => $request->suburb_id ?: null,
            'city'                      => $request->city,
            'postal_code'               => $request->postal_code,
            'province_id'               => $request->province_id,
            'municipality_id'           => $request->municipality_id ?: null,
            'ward_id'                   => $request->ward_id ?: null,
            'country'                   => $request->country ?? 'ZA',
            'latitude'                  => $request->latitude,
            'longitude'                 => $request->longitude,
            'google_formatted_address'  => $request->google_formatted_address,
            'address_category'          => $request->address_category,
            'is_active'                 => true,
            'created_by'                => auth()->id(),
            'updated_by'                => auth()->id(),
        ]);
    }

    private function updateAddress(Request $request, NxAddress $address)
    {
        $address->update([
            'unit_number'               => $request->unit_number,
            'complex_name'              => $request->complex_name,
            'street_number'             => $request->street_number,
            'street_name'               => $request->street_name,
            'suburb_id'                 => $request->suburb_id ?: null,
            'city'                      => $request->city,
            'postal_code'               => $request->postal_code,
            'province_id'               => $request->province_id,
            'municipality_id'           => $request->municipality_id ?: null,
            'ward_id'                   => $request->ward_id ?: null,
            'country'                   => $request->country ?? 'ZA',
            'latitude'                  => $request->latitude,
            'longitude'                 => $request->longitude,
            'google_formatted_address'  => $request->google_formatted_address,
            'address_category'          => $request->address_category,
            'updated_by'                => auth()->id(),
        ]);
    }

    private function saveDetails(Request $request, NxAddress $address)
    {
        $fields = array_filter($request->only([
            'floor_level', 'building_name', 'estate_name', 'section_number',
            'farm_name', 'farm_number', 'stand_number',
            'erf_number', 'sg_code', 'municipal_account_number',
            'plus_code', 'what3words', 'google_place_id', 'map_url', 'address_source',
        ]));

        if (!empty($fields)) {
            NxAddressDetail::updateOrCreate(
                ['address_id' => $address->id],
                $fields
            );
        }
    }
}
