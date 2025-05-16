{{ Form::open(['url' => 'users', 'method' => 'post', 'class' => 'needs-validation', 'novalidate']) }}
<div class="modal-body">
    <div class="row">
        @if (\Auth::user()->type == 'super admin')
            <h2 class="text-md fw-bold">Company Details</h2>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Name'),  'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('name')
                        <small class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('email', __('Email Id'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Email'), 'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('email')
                        <small class="invalid-email" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            {!! Form::hidden('role', 'company', null, ['class' => 'form-control select2', 'required' => 'required']) !!}
            <div class="col-md-12 mb-3 form-group mt-4">
                <label for="password_switch">{{ __('Login is enable') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer"
                        value="on" id="password_switch">
                    <label class="form-check-label" for="password_switch"></label>
                </div>
            </div>
            <div class="col-md-12 ps_div d-none">
                <div class="form-group">
                    {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                    {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Company Password'), 'autocomplete' => 'new-password', 'minlength' => '6']) }}
                    @error('password')
                        <small class="invalid-password" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('mobile', __('Mobile No'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => __('Enter Company Mobile'), 'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('mobile')
                        <small class="invalid-mobile" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('address', __('Full Address'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::textarea('address', null, ['class' => 'form-control', 'rows'=>'2', 'placeholder' => __('Enter Full Address'), 'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('address')
                        <small class="invalid-address" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('landmark', __('Landmark'), ['class' => 'form-label']) }}
                    {{ Form::text('landmark', null, ['class' => 'form-control', 'placeholder' => __('Enter Company landmark'), 'autocomplete' => 'off']) }}
                    @error('landmark')
                        <small class="invalid-landmark" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('postalcode', __('Postal Code'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('postalcode', null, ['class' => 'form-control', 'placeholder' => __('Enter PostalCodd'), 'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('postalcode')
                        <small class="invalid-postalcode" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('city', __('City'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter City'), 'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('city')
                        <small class="invalid-mobile" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('state', __('State'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter State'), 'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('state')
                        <small class="invalid-mobile" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Country'), 'autocomplete' => 'off', 'required' => 'required']) }}
                    @error('mobile')
                        <small class="invalid-mobile" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>

         

            <div class="form-group col-md-12">
                {{ Form::label('business_type', __('Business Type'), ['class' => 'form-label']) }}<x-required></x-required>
                {!! Form::select('business_type', $roles, null, ['class' => 'form-control select', 'required' => 'required']) !!}
                @error('business_type')
                    <small class="invalid-role" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        @else
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter User Name'), 'required' => 'required']) }}
                    @error('name')
                        <small class="invalid-name" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<x-required></x-required>
                    {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Enter User Email'), 'required' => 'required']) }}
                    @error('email')
                        <small class="invalid-email" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
            <div class="form-group col-md-12">
                {{ Form::label('role', __('User Role'), ['class' => 'form-label']) }}<x-required></x-required>
                {!! Form::select('role', $roles, null, ['class' => 'form-control select', 'required' => 'required']) !!}
                @error('role')
                    <small class="invalid-role" role="alert">
                        <strong class="text-danger">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
            <div class="col-md-12 mb-3 form-group mt-4">
                <label for="password_switch">{{ __('Login is enable') }}</label>
                <div class="form-check form-switch custom-switch-v1 float-end">
                    <input type="checkbox" name="password_switch" class="form-check-input input-primary pointer"
                        value="on" id="password_switch">
                    <label class="form-check-label" for="password_switch"></label>
                </div>
            </div>
            <div class="col-md-12 ps_div d-none">
                <div class="form-group">
                    {{ Form::label('password', __('Password'), ['class' => 'form-label']) }}
                    {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('Enter Company Password'), 'minlength' => '6']) }}
                    @error('password')
                        <small class="invalid-password" role="alert">
                            <strong class="text-danger">{{ $message }}</strong>
                        </small>
                    @enderror
                </div>
            </div>
        @endif
        @if (!$customFields->isEmpty())
            <div class="col-md-6">
                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                    @include('customFields.formBuilder')
                </div>
            </div>
        @endif
    </div>

</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>

{{ Form::close() }}
