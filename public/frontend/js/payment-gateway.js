/**
 * Payment Gateway Selection Handler
 * Handles payment gateway selection and form submission
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="cart.order"]');
    const onlineGateways = document.querySelectorAll('.online-gateway');
    const offlineGateways = document.querySelectorAll('.offline-gateway');
    const gatewayInfo = document.getElementById('gateway-info');
    const transferDetails = document.getElementById('gateway-transfer-details');
    const proofUpload = document.getElementById('gateway-proof-upload');
    const submitButton = document.querySelector('button[type="submit"]');

    console.log('Payment Gateway JS loaded');
    console.log('Form found:', form);
    console.log('Online gateways:', onlineGateways.length);
    console.log('Offline gateways:', offlineGateways.length);

    // Handle online gateway selection
    onlineGateways.forEach(gateway => {
        gateway.addEventListener('change', function() {
            if (this.checked) {
                // Hide offline gateway info
                hideOfflineGatewayInfo();
                
                // Update form action for online payment
                updateFormForOnlinePayment(this);
            }
        });
    });

    // Handle offline gateway selection
    offlineGateways.forEach(gateway => {
        gateway.addEventListener('change', function() {
            if (this.checked) {
                // Show offline gateway info
                showOfflineGatewayInfo(this);
                
                // Update form action for offline payment
                updateFormForOfflinePayment(this);
            }
        });
    });

    // Handle form submission - add light client-side validation to avoid server 422s
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedGateway = document.querySelector('input[name="payment_method"]:checked');

            if (!selectedGateway) {
                e.preventDefault();
                alert('Please select a payment method');
                return false;
            }

            // Basic client-side validation to match server rules and improve UX
            const address1 = (form.querySelector('input[name="address1"]') || { value: '' }).value.trim();
            const country = (form.querySelector('select[name="country"]') || { value: '' }).value.trim();
            const phone = (form.querySelector('input[name="phone"]') || { value: '' }).value.trim();
            const email = (form.querySelector('input[name="email"]') || { value: '' }).value.trim();

            if (address1.length < 10) {
                e.preventDefault();
                alert('Please enter a valid address (at least 10 characters).');
                (form.querySelector('input[name="address1"]') || {}).focus && (form.querySelector('input[name="address1"]')).focus();
                return false;
            }

            if (country.length !== 2) {
                e.preventDefault();
                alert('Please select your country.');
                (form.querySelector('select[name="country"]') || {}).focus && (form.querySelector('select[name="country"]')).focus();
                return false;
            }

            if (phone.length < 10) {
                e.preventDefault();
                alert('Please enter a valid phone number (at least 10 digits).');
                (form.querySelector('input[name="phone"]') || {}).focus && (form.querySelector('input[name="phone"]')).focus();
                return false;
            }

            if (!email.length || !email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                (form.querySelector('input[name="email"]') || {}).focus && (form.querySelector('input[name="email"]')).focus();
                return false;
            }

            const gatewayId = selectedGateway.getAttribute('data-gateway-id');

            if (gatewayId) {
                // Add gateway_id to form data (avoid duplicates)
                if (!form.querySelector('input[name="gateway_id"]')) {
                    const gatewayIdInput = document.createElement('input');
                    gatewayIdInput.type = 'hidden';
                    gatewayIdInput.name = 'gateway_id';
                    gatewayIdInput.value = gatewayId;
                    form.appendChild(gatewayIdInput);
                } else {
                    form.querySelector('input[name="gateway_id"]').value = gatewayId;
                }
            }

            // Show loading state
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            }
        });
    }

    function showOfflineGatewayInfo(gateway) {
        const transferDetailsText = gateway.getAttribute('data-transfer-details');
        const requireProof = gateway.getAttribute('data-require-proof') === '1';

        if (transferDetailsText) {
            transferDetails.innerHTML = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Transfer Instructions:</h6>
                    <div class="transfer-details">${transferDetailsText}</div>
                </div>
            `;
            transferDetails.classList.remove('d-none');
        }

        if (requireProof) {
            proofUpload.classList.remove('d-none');
        } else {
            proofUpload.classList.add('d-none');
        }

        gatewayInfo.classList.remove('d-none');
    }

    function hideOfflineGatewayInfo() {
        gatewayInfo.classList.add('d-none');
        transferDetails.classList.add('d-none');
        proofUpload.classList.add('d-none');
    }

    function updateFormForOnlinePayment(gateway) {
        // For online payments, we'll redirect to payment processing
        // The form will be submitted normally and then redirected
        console.log('Online payment selected:', gateway.value);
    }

    function updateFormForOfflinePayment(gateway) {
        // For offline payments, we'll process directly
        console.log('Offline payment selected:', gateway.value);
    }

    // Initialize gateway selection on page load
    const selectedGateway = document.querySelector('input[name="payment_method"]:checked');
    if (selectedGateway) {
        if (selectedGateway.classList.contains('offline-gateway')) {
            showOfflineGatewayInfo(selectedGateway);
        } else {
            hideOfflineGatewayInfo();
        }
    }
});

/**
 * Payment Gateway Icons and Styling
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add gateway-specific icons
    const gatewayIcons = {
        'paypal': 'fab fa-paypal',
        'stripe': 'fas fa-credit-card',
        'fawry': 'fas fa-mobile-alt',
        'thawani': 'fas fa-credit-card',
        'tap': 'fas fa-credit-card',
        'kashier': 'fas fa-credit-card',
        'paymob': 'fas fa-credit-card',
        'paytabs': 'fas fa-credit-card',
        'hyperpay': 'fas fa-credit-card',
        'aman': 'fas fa-credit-card',
        'zain_cash': 'fas fa-mobile-alt',
        'vodafone_cash': 'fas fa-mobile-alt',
        'orange_cash': 'fas fa-mobile-alt',
        'etisalat_cash': 'fas fa-mobile-alt',
        'we': 'fas fa-mobile-alt',
        'moyasar': 'fas fa-credit-card',
        'urway': 'fas fa-credit-card',
        'tamara': 'fas fa-credit-card',
        'tabby': 'fas fa-credit-card',
        'benefit': 'fas fa-credit-card',
        'knet': 'fas fa-credit-card',
        'qpay': 'fas fa-credit-card',
        'moneris': 'fas fa-credit-card',
        'square': 'fas fa-credit-card',
        'flutterwave': 'fas fa-credit-card',
        'razorpay': 'fas fa-credit-card',
        'mercadopago': 'fas fa-credit-card',
        'mollie': 'fas fa-credit-card',
        'paystack': 'fas fa-credit-card',
        'perfect_money': 'fas fa-credit-card',
        'liqpay': 'fas fa-credit-card',
        'authorize_net': 'fas fa-credit-card',
        'offline': 'fas fa-university',
        'cod': 'fas fa-truck'
    };

    // Update gateway icons
    Object.keys(gatewayIcons).forEach(gatewaySlug => {
        const gatewayInput = document.querySelector(`input[value="${gatewaySlug}"]`);
        if (gatewayInput) {
            const iconElement = gatewayInput.parentElement.querySelector('i');
            if (iconElement) {
                iconElement.className = gatewayIcons[gatewaySlug];
            }
        }
    });
});
