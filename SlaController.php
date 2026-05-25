<?php

namespace Modules\NexcoreClientManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\NexcoreClientManager\Models\NexcoreClient;
use Modules\NexcoreClientManager\Models\NexcoreClientSla;

class SlaController extends Controller
{
    public function index($clientId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $slas = NexcoreClientSla::where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->get();

        return view('nexcore_client_manager::sla.index', compact('client', 'slas'));
    }

    public function create($clientId)
    {
        $client = NexcoreClient::with(['contacts' => function ($q) {
            $q->where('is_active', true)->orderByDesc('is_primary');
        }, 'addresses' => function ($q) {
            $q->where('is_active', true)->orderByDesc('is_primary');
        }])->findOrFail($clientId);

        $clients = NexcoreClient::where('is_active', true)
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'client_code', 'registration_number', 'tax_number', 'vat_number', 'paye_number', 'uif_number', 'coida_number']);

        $reference = NexcoreClientSla::generateReference();

        return view('nexcore_client_manager::sla.form', compact('client', 'clients', 'reference'));
    }

    public function store($clientId, Request $request)
    {
        $client = NexcoreClient::findOrFail($clientId);

        $data = $request->validate([
            'signatory_name' => 'required|string|max:255',
            'signatory_id_number' => 'nullable|string|max:20',
            'signatory_email' => 'required|email|max:255',
            'signatory_cellphone' => 'required|string|max:20',
            'signatory_designation' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:50',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_email' => 'nullable|email|max:255',
            'emergency_consent' => 'nullable|boolean',
            'tax_reference_number' => 'nullable|string|max:30',
            'coida_rma_number' => 'nullable|string|max:30',
            'vat_number' => 'nullable|string|max:30',
            'paye_number' => 'nullable|string|max:30',
            'uif_number' => 'nullable|string|max:30',
            'applying_for' => 'nullable|in:individual,company',
            'company_reg_number' => 'nullable|string|max:50',
            'business_name' => 'nullable|string|max:255',
            'nature_of_business' => 'nullable|string|max:255',
            'physical_address' => 'nullable|string',
            'postal_address' => 'nullable|string',
            'work_telephone' => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:50',
            'selected_package' => 'nullable|string|max:50',
            'service_consent' => 'nullable|boolean',
            'bank_account_holder' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_branch_code' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_account_type' => 'nullable|string|max:30',
            'debit_order_date' => 'nullable|string|max:10',
            'debit_order_consent' => 'nullable|boolean',
            'signature_data' => 'nullable|string',
            'signature_type' => 'nullable|in:drawn,typed',
            'signed_at_location' => 'nullable|string|max:255',
            'signed_date' => 'nullable|date',
            'status' => 'nullable|in:draft,sent,signed,active',
            'notes' => 'nullable|string',
        ]);

        $data['client_id'] = $clientId;
        $data['sla_reference'] = NexcoreClientSla::generateReference();
        $data['emergency_consent'] = $request->boolean('emergency_consent');
        $data['service_consent'] = $request->boolean('service_consent');
        $data['debit_order_consent'] = $request->boolean('debit_order_consent');
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        if ($request->has('signature_data') && $request->signature_data) {
            $data['status'] = 'signed';
            $data['signed_date'] = $data['signed_date'] ?? now()->toDateString();
        } else {
            $data['status'] = $data['status'] ?? 'draft';
        }

        $sla = NexcoreClientSla::create($data);

        return redirect()->route('nexcore.clients.show.sla.show', [$clientId, $sla->id])
            ->with('success', 'Engagement Letter created successfully.');
    }

    public function show($clientId, $slaId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $sla = NexcoreClientSla::where('client_id', $clientId)->findOrFail($slaId);

        return view('nexcore_client_manager::sla.show', compact('client', 'sla'));
    }

    public function edit($clientId, $slaId)
    {
        $client = NexcoreClient::with(['contacts' => function ($q) {
            $q->where('is_active', true)->orderByDesc('is_primary');
        }, 'addresses' => function ($q) {
            $q->where('is_active', true)->orderByDesc('is_primary');
        }])->findOrFail($clientId);

        $clients = NexcoreClient::where('is_active', true)
            ->orderBy('company_name')
            ->get(['id', 'company_name', 'client_code', 'registration_number', 'tax_number', 'vat_number', 'paye_number', 'uif_number', 'coida_number']);

        $sla = NexcoreClientSla::where('client_id', $clientId)->findOrFail($slaId);

        return view('nexcore_client_manager::sla.form', compact('client', 'clients', 'sla'));
    }

    public function update($clientId, $slaId, Request $request)
    {
        $sla = NexcoreClientSla::where('client_id', $clientId)->findOrFail($slaId);

        $data = $request->validate([
            'signatory_name' => 'required|string|max:255',
            'signatory_id_number' => 'nullable|string|max:20',
            'signatory_email' => 'required|email|max:255',
            'signatory_cellphone' => 'required|string|max:20',
            'signatory_designation' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:50',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'emergency_email' => 'nullable|email|max:255',
            'emergency_consent' => 'nullable|boolean',
            'tax_reference_number' => 'nullable|string|max:30',
            'coida_rma_number' => 'nullable|string|max:30',
            'vat_number' => 'nullable|string|max:30',
            'paye_number' => 'nullable|string|max:30',
            'uif_number' => 'nullable|string|max:30',
            'applying_for' => 'nullable|in:individual,company',
            'company_reg_number' => 'nullable|string|max:50',
            'business_name' => 'nullable|string|max:255',
            'nature_of_business' => 'nullable|string|max:255',
            'physical_address' => 'nullable|string',
            'postal_address' => 'nullable|string',
            'work_telephone' => 'nullable|string|max:20',
            'marital_status' => 'nullable|string|max:50',
            'selected_package' => 'nullable|string|max:50',
            'service_consent' => 'nullable|boolean',
            'bank_account_holder' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_branch_code' => 'nullable|string|max:20',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_account_type' => 'nullable|string|max:30',
            'debit_order_date' => 'nullable|string|max:10',
            'debit_order_consent' => 'nullable|boolean',
            'signature_data' => 'nullable|string',
            'signature_type' => 'nullable|in:drawn,typed',
            'signed_at_location' => 'nullable|string|max:255',
            'signed_date' => 'nullable|date',
            'status' => 'nullable|in:draft,sent,signed,active',
            'notes' => 'nullable|string',
        ]);

        $data['emergency_consent'] = $request->boolean('emergency_consent');
        $data['service_consent'] = $request->boolean('service_consent');
        $data['debit_order_consent'] = $request->boolean('debit_order_consent');
        $data['updated_by'] = Auth::id();

        if ($request->has('signature_data') && $request->signature_data && !$sla->signature_data) {
            $data['status'] = 'signed';
            $data['signed_date'] = $data['signed_date'] ?? now()->toDateString();
        }

        $sla->update($data);

        return redirect()->route('nexcore.clients.show.sla.show', [$clientId, $sla->id])
            ->with('success', 'Engagement Letter updated successfully.');
    }

    public function destroy($clientId, $slaId)
    {
        $sla = NexcoreClientSla::where('client_id', $clientId)->findOrFail($slaId);
        $sla->delete();

        return redirect()->route('nexcore.clients.show.sla', $clientId)
            ->with('success', 'Engagement Letter deleted.');
    }

    public function updateStatus($clientId, $slaId, Request $request)
    {
        $sla = NexcoreClientSla::where('client_id', $clientId)->findOrFail($slaId);
        $request->validate(['status' => 'required|in:draft,sent,viewed,signed,active,terminated,expired']);

        $sla->update([
            'status' => $request->status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'status' => $request->status]);
    }

    public function generatePdf($clientId, $slaId)
    {
        $client = NexcoreClient::findOrFail($clientId);
        $sla = NexcoreClientSla::where('client_id', $clientId)->findOrFail($slaId);

        $pdf = Pdf::loadView('nexcore_client_manager::sla.proposal-pdf', compact('client', 'sla'));
        $pdf->setPaper('a4');

        return $pdf->download('ATP-Proposal-' . $sla->sla_reference . '.pdf');
    }

    public function clientData($clientId)
    {
        $client = NexcoreClient::with(['contacts' => function ($q) {
            $q->where('is_active', true)->orderByDesc('is_primary');
        }, 'addresses' => function ($q) {
            $q->where('is_active', true)->orderByDesc('is_primary');
        }])->findOrFail($clientId);

        $primary = $client->contacts->first();
        $address = $client->addresses->first();

        return response()->json([
            'company_name' => $client->company_name,
            'trading_name' => $client->trading_name,
            'registration_number' => $client->registration_number,
            'tax_number' => $client->tax_number,
            'vat_number' => $client->vat_number,
            'paye_number' => $client->paye_number,
            'uif_number' => $client->uif_number,
            'coida_number' => $client->coida_number,
            'signatory_name' => $primary ? trim($primary->first_name . ' ' . $primary->last_name) : '',
            'signatory_email' => $primary->email ?? '',
            'signatory_cellphone' => $primary->mobile_number ?? '',
            'signatory_id_number' => $primary->id_number ?? '',
            'signatory_designation' => $primary->designation ?? '',
            'physical_address' => $address ? implode(', ', array_filter([
                $address->address_line_1,
                $address->address_line_2,
                $address->suburb,
                $address->city,
                $address->postal_code,
            ])) : '',
        ]);
    }
}
