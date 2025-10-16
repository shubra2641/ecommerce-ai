<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Exception;
use App\Http\Requests\Admin\PaymentGatewayStoreRequest;
use App\Http\Requests\Admin\PaymentGatewayUpdateRequest;

/**
 * AdminPaymentGatewayController handles payment gateway management
 * 
 * This controller manages payment gateway configurations including PayPal, Stripe,
 * offline payments, and cash on delivery options with secure credential handling.
 */
class AdminPaymentGatewayController extends Controller
{
    /**
     * Get available payment gateway configurations
     * 
     * @return array
     */
    private function getAvailableGateways(): array
    {
        return [
            'paypal' => [
                'name' => 'PayPal',
                'icon' => 'fab fa-paypal',
                'desc' => 'PayPal Express Checkout integration (sandbox/live)',
                'fields' => [
                    'credentials[sandbox][client_id]' => 'Sandbox Client ID',
                    'credentials[sandbox][client_secret]' => 'Sandbox Client Secret',
                    'credentials[live][client_id]' => 'Live Client ID',
                    'credentials[live][client_secret]' => 'Live Client Secret',
                ],
            ],
            'stripe' => [
                'name' => 'Stripe',
                'icon' => 'fas fa-credit-card',
                'desc' => 'Stripe payment gateway (test/live keys)',
                'fields' => [
                    'credentials[test][publishable_key]' => 'Test Publishable Key',
                    'credentials[test][secret_key]' => 'Test Secret Key',
                    'credentials[live][publishable_key]' => 'Live Publishable Key',
                    'credentials[live][secret_key]' => 'Live Secret Key',
                ],
            ],
            'offline' => [
                'name' => 'Offline / Bank Transfer',
                'icon' => 'fas fa-university',
                'desc' => 'Offline transfer methods (bank transfer, direct deposit). Use transfer details and optional proof upload.',
                'fields' => [],
            ],
            'cod' => [
                'name' => 'Cash On Delivery',
                'icon' => 'fas fa-truck',
                'desc' => 'Allow cash on delivery. No credentials required.',
                'fields' => [],
            ],
        ];
    }
    /**
     * Display payment gateways index page
     * 
     * Shows all configured payment gateways with their status and available options
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $gateways = PaymentGateway::orderBy('sort_order')->orderBy('name')->get();
            $availableGateways = $this->getAvailableGateways();
            
            // Map configured gateways by slug for quick lookup in views
            $configuredMap = $gateways->keyBy('slug');

            return view('backend.payment_gateways.index', compact('gateways', 'availableGateways', 'configuredMap'));
        } catch (Exception $e) {
            \Log::error('Error fetching payment gateways: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load payment gateways');
            return view('backend.payment_gateways.index', [
                'gateways' => collect(),
                'availableGateways' => $this->getAvailableGateways(),
                'configuredMap' => collect()
            ]);
        }
    }

    /**
     * Display create payment gateway form
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            $availableGateways = $this->getAvailableGateways();
            $type = request('prefill_slug') ?: request('type');
            $selected_type = $type ?: old('type');
            
            return view('backend.payment_gateways.create', compact('availableGateways', 'type', 'selected_type'));
        } catch (Exception $e) {
            \Log::error('Error loading create payment gateway form: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load create form');
            return redirect()->route('admin.payment-gateways.index');
        }
    }

    /**
     * Store a new payment gateway
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(PaymentGatewayStoreRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Generate slug: prefer explicit type, else slugify name
            $type = $validated['type'] ?? $request->input('prefill_slug');
            $slug = $type ?: Str::slug($validated['name']);

            // Check if slug already exists - if exists, update instead of creating new
            $existingGateway = PaymentGateway::where('slug', $slug)->first();
            if ($existingGateway) {
                // Update existing gateway instead of creating new one
                $this->updateGatewayData($existingGateway, $request, $data);
                request()->session()->flash('success', 'Payment gateway updated successfully');
                return redirect()->route('admin.payment-gateways.index');
            }

            // Prepare data with only allowed fields
            $allowedFields = ['name', 'mode', 'transfer_details'];
            $data = array_intersect_key($validated, array_flip($allowedFields));
            
            $data['slug'] = $slug;
            $data['enabled'] = $request->has('enabled');
            $data['require_proof'] = $request->has('require_proof');
            $data['mode'] = $data['mode'] ?? 'sandbox';
            $data['credentials'] = [];

            // Handle credentials securely
            if ($request->has('credentials') && is_array($request->input('credentials'))) {
                $data['credentials'] = $request->input('credentials');
            } elseif (!empty($validated['credentials']) && is_string($validated['credentials'])) {
                $decoded = json_decode($validated['credentials'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data['credentials'] = $decoded;
                }
            }

            PaymentGateway::create($data);
            request()->session()->flash('success', 'Payment gateway created successfully');
            
        } catch (Exception $e) {
            \Log::error('Error creating payment gateway: ' . $e->getMessage());
            request()->session()->flash('error', 'An error occurred while creating the payment gateway');
        }
        
        return redirect()->route('admin.payment-gateways.index');
    }

    /**
     * Display edit payment gateway form
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            $availableGateways = $this->getAvailableGateways();
            $type = $gateway->slug;

            // Precompute credential input keys and existing values
            $credentialKeys = [];
            $credentialExisting = [];
            
            if ($type && isset($availableGateways[$type]) && !empty($availableGateways[$type]['fields'])) {
                foreach ($availableGateways[$type]['fields'] as $inputName => $label) {
                    $key = str_replace(['[', ']'], ['.', ''], $inputName);
                    $credentialKeys[$inputName] = $key;
                    $credentialExisting[$inputName] = data_get(
                        $gateway->credentials, 
                        str_replace(['.', 'credentials.'], ['.', ''], $key)
                    );
                }
            }

            return view('backend.payment_gateways.edit', compact(
                'gateway', 
                'availableGateways', 
                'type', 
                'credentialKeys', 
                'credentialExisting'
            ));
        } catch (Exception $e) {
            \Log::error('Error loading edit payment gateway form: ' . $e->getMessage());
            request()->session()->flash('error', 'Payment gateway not found');
            return redirect()->route('admin.payment-gateways.index');
        }
    }

    /**
     * Update an existing payment gateway
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(PaymentGatewayUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            
            // Log the request data for debugging
            \Log::info('Payment Gateway Update Request', [
                'gateway_id' => $id,
                'request_data' => $request->all(),
                'credentials' => $request->input('credentials')
            ]);
            
            $validated = $request->validated();

            // Prepare data with only allowed fields
            $allowedFields = ['name', 'mode', 'transfer_details'];
            $data = array_intersect_key($validated, array_flip($allowedFields));
            
            // Preserve existing slug; slug is not editable
            $data['slug'] = $gateway->slug ?: Str::slug($validated['name']);
            $data['enabled'] = $request->has('enabled');
            $data['require_proof'] = $request->has('require_proof');
            $data['mode'] = $data['mode'] ?? 'sandbox';
            $data['credentials'] = $gateway->credentials ?? [];

            // Handle credentials securely
            if ($request->has('credentials') && is_array($request->input('credentials'))) {
                // Merge submitted credentials over existing to preserve values not resubmitted
                $submitted = $request->input('credentials');
                $data['credentials'] = array_replace_recursive($gateway->credentials ?? [], $submitted);
            } elseif (isset($validated['credentials']) && !empty($validated['credentials']) && is_string($validated['credentials'])) {
                $decoded = json_decode($validated['credentials'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data['credentials'] = $decoded;
                }
            }

            $gateway->update($data);
            
            // Log the updated data
            \Log::info('Payment Gateway Updated', [
                'gateway_id' => $id,
                'updated_data' => $data,
                'final_credentials' => $gateway->fresh()->credentials
            ]);
            
            request()->session()->flash('success', 'Payment gateway updated successfully');
            
        } catch (Exception $e) {
            \Log::error('Error updating payment gateway: ' . $e->getMessage());
            request()->session()->flash('error', 'An error occurred while updating the payment gateway');
        }
        
        return redirect()->route('admin.payment-gateways.index');
    }

    /**
     * Delete a payment gateway
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            $gateway->delete();
            request()->session()->flash('success', 'Payment gateway removed successfully');
        } catch (Exception $e) {
            \Log::error('Error deleting payment gateway: ' . $e->getMessage());
            request()->session()->flash('error', 'An error occurred while removing the payment gateway');
        }
        
        return redirect()->route('admin.payment-gateways.index');
    }

    /**
     * Toggle enabled state for a gateway (quick action from index)
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function toggle(int $id): RedirectResponse
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            $gateway->enabled = !$gateway->enabled;
            $gateway->save();
            
            $msg = $gateway->enabled ? 'Payment gateway enabled' : 'Payment gateway disabled';
            request()->session()->flash('success', $msg);
        } catch (Exception $e) {
            \Log::error('Error toggling payment gateway: ' . $e->getMessage());
            request()->session()->flash('error', 'An error occurred while updating the payment gateway status');
        }
        
        return redirect()->route('admin.payment-gateways.index');
    }

    /**
     * Update gateway data helper method
     * 
     * @param PaymentGateway $gateway
     * @param Request $request
     * @param array $data
     * @return void
     */
    private function updateGatewayData(PaymentGateway $gateway, Request $request, array $data): void
    {
        // Update basic fields
        $gateway->name = $data['name'] ?? $gateway->name;
        $gateway->mode = $data['mode'] ?? $gateway->mode;
        $gateway->enabled = $request->has('enabled');
        $gateway->require_proof = $request->has('require_proof');
        
        // Update transfer details for offline gateways
        if ($gateway->slug === 'offline') {
            $gateway->transfer_details = $data['transfer_details'] ?? $gateway->transfer_details;
        }
        
        // Handle credentials securely
        if ($request->has('credentials') && is_array($request->input('credentials'))) {
            // Merge submitted credentials over existing to preserve values not resubmitted
            $submitted = $request->input('credentials');
            $gateway->credentials = array_replace_recursive($gateway->credentials ?? [], $submitted);
        }
        
        $gateway->save();
    }
}