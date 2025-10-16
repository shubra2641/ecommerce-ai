<div class="col-12">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('First Name') }}<span>*</span></label>
                <input type="text" name="first_name" placeholder="" value="{{ old('first_name', auth()->user()->first_name ?? '') }}">
                @error('first_name')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('Last Name') }}<span>*</span></label>
                <input type="text" name="last_name" placeholder="" value="{{ old('last_name', auth()->user()->last_name ?? '') }}">
                @error('last_name')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('Email') }}<span>*</span></label>
                <input type="email" name="email" placeholder="" value="{{ old('email', auth()->user()->email ?? '') }}">
                @error('email')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('Phone') }}<span>*</span></label>
                <input type="text" name="phone" placeholder="" value="{{ old('phone', auth()->user()->phone ?? '') }}">
                @error('phone')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('Country') }}<span>*</span></label>
                <select name="country" id="country" class="form-select">
                    <option value="">{{ __('Select Country') }}</option>
                    <option value="EG" {{ old('country') == 'EG' ? 'selected' : '' }}>Egypt</option>
                    <option value="SA" {{ old('country') == 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                    <option value="AE" {{ old('country') == 'AE' ? 'selected' : '' }}>UAE</option>
                    <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>United States</option>
                    <option value="GB" {{ old('country') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                    <option value="NP" {{ old('country') == 'NP' ? 'selected' : '' }}>Nepal</option>
                </select>
                @error('country')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('Address Line 1') }}<span>*</span></label>
                <input type="text" name="address1" placeholder="" value="{{ old('address1') }}">
                @error('address1')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('Address Line 2') }}</label>
                <input type="text" name="address2" placeholder="" value="{{ old('address2') }}">
                @error('address2')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-12">
            <div class="form-group">
                <label>{{ __('Postal Code') }}</label>
                <input type="text" name="post_code" placeholder="" value="{{ old('post_code') }}">
                @error('post_code')
                    <span class='text-danger'>{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>
</div>