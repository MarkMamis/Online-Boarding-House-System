<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LandlordSetupController extends Controller
{
    public function show(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $setupSnapshot = $this->getSetupSnapshot($user);
        $checklist = $this->buildChecklist($setupSnapshot);
        $completedCount = collect($checklist)->where('completed', true)->count();
        $totalCount = count($checklist);

        if ($setupSnapshot['setup_submitted'] && $setupSnapshot['permit_approved'] && !$request->has('step')) {
            return redirect()
                ->route('landlord.dashboard')
                ->with('success', 'Landlord setup is already submitted. You can update details from Profile anytime.');
        }

        $stepMap = [
            'profile' => 0,
            'permit' => 1,
            'billing' => 2,
        ];

        $requestedStep = strtolower((string) $request->query('step', ''));
        $initialStep = array_key_exists($requestedStep, $stepMap)
            ? $stepMap[$requestedStep]
            : $this->firstIncompleteStep($setupSnapshot);

        return view('landlord.setup.index', compact(
            'user',
            'setupSnapshot',
            'checklist',
            'completedCount',
            'totalCount',
            'initialStep'
        ));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $user->loadMissing('landlordProfile');
        $landlordProfile = $user->landlordProfile;

        $permitRule = filled(optional($landlordProfile)->business_permit_path)
            ? 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
            : 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'contact_number' => 'required|regex:/^09\d{9}$/',
            'boarding_house_name' => 'required|string|max:255',
            'about' => 'required|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'contract_signature_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'business_permit' => $permitRule,
            'preferred_payment_methods' => 'required|array|min:1',
            'preferred_payment_methods.*' => 'in:bank,gcash,cash',
            'payment_bank_name' => 'nullable|string|max:255',
            'payment_account_name' => 'nullable|string|max:255',
            'payment_account_number' => 'nullable|string|max:100',
            'payment_gcash_number' => 'nullable|regex:/^09\d{9}$/',
            'payment_gcash_name' => 'nullable|string|max:255',
            'payment_gcash_qr' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'payment_instructions' => 'nullable|string|max:1000',
        ], [
            'contact_number.regex' => 'Contact number must use 11-digit PH mobile format (09XXXXXXXXX).',
            'payment_gcash_number.regex' => 'GCash number must use 11-digit PH mobile format (09XXXXXXXXX).',
            'preferred_payment_methods.required' => 'Select at least one payment method.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $preferredPaymentMethods = collect($request->input('preferred_payment_methods', []));

            if ($preferredPaymentMethods->contains('bank')) {
                if (!filled($request->input('payment_bank_name'))) {
                    $validator->errors()->add('payment_bank_name', 'Bank name is required when Bank is selected.');
                }

                if (!filled($request->input('payment_account_name'))) {
                    $validator->errors()->add('payment_account_name', 'Account name is required when Bank is selected.');
                }
            }

            if ($preferredPaymentMethods->contains('gcash')) {
                if (!filled($request->input('payment_gcash_name'))) {
                    $validator->errors()->add('payment_gcash_name', 'GCash name is required when GCash is selected.');
                }

                if (!filled($request->input('payment_gcash_number'))) {
                    $validator->errors()->add('payment_gcash_number', 'GCash number is required when GCash is selected.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $preferredPaymentMethods = collect($request->input('preferred_payment_methods', []))
            ->filter(fn ($method) => in_array($method, ['bank', 'gcash', 'cash'], true))
            ->values();

        $user->fill([
            'full_name' => $request->input('full_name'),
            'name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'contact_number' => $request->input('contact_number'),
            'boarding_house_name' => $request->input('boarding_house_name'),
        ]);

        if ($request->hasFile('profile_image')) {
            if (!empty($user->profile_image_path)) {
                Storage::disk('public')->delete($user->profile_image_path);
            }

            $user->profile_image_path = str_replace('\\', '/', $request->file('profile_image')->store('profiles', 'public'));
        }

        $user->save();

        $landlordProfile = $user->landlordProfile()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'contact_number' => $user->contact_number,
                'boarding_house_name' => $user->boarding_house_name,
            ]
        );

        $profileData = [
            'contact_number' => $request->input('contact_number'),
            'boarding_house_name' => $request->input('boarding_house_name'),
            'about' => $request->input('about'),
            'payment_bank_name' => $request->input('payment_bank_name'),
            'payment_account_name' => $request->input('payment_account_name'),
            'payment_account_number' => $request->input('payment_account_number'),
            'payment_gcash_number' => $request->input('payment_gcash_number'),
            'payment_gcash_name' => $request->input('payment_gcash_name'),
            'payment_instructions' => $request->input('payment_instructions'),
            'preferred_payment_methods' => $preferredPaymentMethods->all(),
        ];

        if ($request->hasFile('business_permit')) {
            if (!empty($landlordProfile->business_permit_path)) {
                Storage::disk('public')->delete($landlordProfile->business_permit_path);
            }

            $profileData['business_permit_path'] = str_replace('\\', '/', $request->file('business_permit')->store('business_permits', 'public'));
            $profileData['business_permit_status'] = 'pending';
            $profileData['business_permit_reviewed_at'] = null;
            $profileData['business_permit_reviewed_by'] = null;
            $profileData['business_permit_rejection_reason'] = null;
        }

        if ($request->hasFile('payment_gcash_qr')) {
            if (!empty($landlordProfile->payment_gcash_qr_path)) {
                Storage::disk('public')->delete($landlordProfile->payment_gcash_qr_path);
            }

            $profileData['payment_gcash_qr_path'] = str_replace('\\', '/', $request->file('payment_gcash_qr')->store('payment_qr_codes', 'public'));
        }

        if ($request->hasFile('contract_signature_image')) {
            if (!empty($landlordProfile->contract_signature_path)) {
                Storage::disk('public')->delete($landlordProfile->contract_signature_path);
            }

            $profileData['contract_signature_path'] = str_replace('\\', '/', $request->file('contract_signature_image')->store('landlord-signatures', 'public'));
        }

        $profileComplete = filled($request->input('contact_number'))
            && filled($request->input('boarding_house_name'))
            && filled($request->input('about'));

        $requiresBank = $preferredPaymentMethods->contains('bank');
        $requiresGcash = $preferredPaymentMethods->contains('gcash');
        $requiresCash = $preferredPaymentMethods->contains('cash');

        $bankReady = !$requiresBank || (
            filled($request->input('payment_bank_name'))
            && filled($request->input('payment_account_name'))
        );

        $gcashReady = !$requiresGcash || (
            filled($request->input('payment_gcash_name'))
            && filled($request->input('payment_gcash_number'))
        );

        $cashReady = !$requiresCash || true;
        $billingComplete = $preferredPaymentMethods->isNotEmpty() && $bankReady && $gcashReady && $cashReady;

        $profileData['profile_completed'] = $profileComplete;
        $profileData['billing_completed'] = $billingComplete;

        if (empty($profileData['business_permit_status']) && empty($landlordProfile->business_permit_status)) {
            $profileData['business_permit_status'] = filled($profileData['business_permit_path'] ?? $landlordProfile->business_permit_path)
                ? 'pending'
                : 'not_submitted';
        }

        $landlordProfile->update($profileData);

        $setupSnapshot = $this->getSetupSnapshot($user->fresh('landlordProfile'));
        if ($setupSnapshot['setup_submitted']) {
            return redirect()
                ->route('landlord.dashboard')
                ->with('success', 'Landlord setup completed. Permit review may still be pending admin approval.');
        }

        return back()->with('success', 'Landlord setup was saved successfully.');
    }

    private function firstIncompleteStep(array $setupSnapshot): int
    {
        if (!$setupSnapshot['profile_complete']) {
            return 0;
        }

        if (!$setupSnapshot['permit_uploaded']) {
            return 1;
        }

        if (in_array($setupSnapshot['permit_status'], ['pending', 'rejected'], true)) {
            return 1;
        }

        if (!$setupSnapshot['billing_methods_complete']) {
            return 2;
        }

        return 0;
    }

    private function getSetupSnapshot(User $user): array
    {
        $user->loadMissing('landlordProfile');
        $landlordProfile = $user->landlordProfile;

        $propertyIds = Property::where('landlord_id', $user->id)->pluck('id');
        $totalRooms = Room::whereIn('property_id', $propertyIds)->count();
        $roomsPricedCount = Room::whereIn('property_id', $propertyIds)
            ->where('price', '>', 0)
            ->count();

        $preferredPaymentMethods = collect(optional($landlordProfile)->preferred_payment_methods ?? [])
            ->filter(fn ($method) => in_array($method, ['bank', 'gcash', 'cash'], true))
            ->values();

        $bankSetupComplete = filled(optional($landlordProfile)->payment_bank_name)
            && filled(optional($landlordProfile)->payment_account_name);

        $gcashSetupComplete = filled(optional($landlordProfile)->payment_gcash_name)
            && filled(optional($landlordProfile)->payment_gcash_number);

        $requiresBank = $preferredPaymentMethods->contains('bank');
        $requiresGcash = $preferredPaymentMethods->contains('gcash');
        $requiresCash = $preferredPaymentMethods->contains('cash');

        $bankRequirementMet = !$requiresBank || $bankSetupComplete;
        $gcashRequirementMet = !$requiresGcash || $gcashSetupComplete;
        $cashRequirementMet = !$requiresCash || true;

        $billingMethodsComplete = $preferredPaymentMethods->isNotEmpty() && $bankRequirementMet && $gcashRequirementMet && $cashRequirementMet;

        $profileComplete = filled($user->contact_number)
            && filled($user->boarding_house_name)
            && filled(optional($landlordProfile)->about);

        $permitUploaded = filled(optional($landlordProfile)->business_permit_path);
        $permitStatus = (string) (optional($landlordProfile)->business_permit_status ?: ($permitUploaded ? 'pending' : 'not_submitted'));
        if (!$permitUploaded) {
            $permitStatus = 'not_submitted';
        }

        $permitApproved = $permitUploaded && $permitStatus === 'approved';

        $pricingBasisComplete = $totalRooms > 0 && $roomsPricedCount === $totalRooms;

        return [
            'profile_complete' => $profileComplete,
            'permit_uploaded' => $permitUploaded,
            'permit_approved' => $permitApproved,
            'permit_status' => $permitStatus,
            'permit_rejection_reason' => (string) (optional($landlordProfile)->business_permit_rejection_reason ?? ''),
            'billing_methods_complete' => $billingMethodsComplete,
            'pricing_basis_complete' => $pricingBasisComplete,
            'setup_submitted' => $profileComplete && $permitUploaded && $billingMethodsComplete,
        ];
    }

    private function buildChecklist(array $setupSnapshot): array
    {
        return [
            [
                'title' => 'Profile details',
                'description' => 'Set identity, contact details, and boarding house information.',
                'completed' => $setupSnapshot['profile_complete'],
            ],
            [
                'title' => 'Business permit',
                'description' => 'Upload your permit for admin review and approval.',
                'completed' => $setupSnapshot['permit_uploaded'],
            ],
            [
                'title' => 'Billing methods',
                'description' => 'Add preferred payment methods and required account details.',
                'completed' => $setupSnapshot['billing_methods_complete'],
            ],
        ];
    }
}
