@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">{{ trans('app.settings') }}</h5>
    <div class="card-body">
        <form method="post" action="{{route('settings.update')}}">
            @csrf 
            {{-- @method('PATCH') --}}
            {{-- {{dd($data)}} --}}

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-general" data-toggle="tab" href="#pane-general" role="tab" aria-controls="pane-general" aria-selected="true">
                        <i class="fas fa-info-circle"></i> {{ trans('app.general_information') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-site" data-toggle="tab" href="#pane-site" role="tab" aria-controls="pane-site" aria-selected="false">
                        <i class="fas fa-globe"></i> {{ trans('app.site_settings') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-ai" data-toggle="tab" href="#pane-ai" role="tab" aria-controls="pane-ai" aria-selected="false">
                        <i class="fas fa-robot"></i> {{ trans('app.ai_content_generation') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-social" data-toggle="tab" href="#pane-social" role="tab" aria-controls="pane-social" aria-selected="false">
                        <i class="fas fa-share-alt"></i> {{ trans('app.social_login_settings') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-fonts" data-toggle="tab" href="#pane-fonts" role="tab" aria-controls="pane-fonts" aria-selected="false">
                        <i class="fas fa-font"></i> {{ trans('app.font_settings') }}
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-3" id="settingsTabsContent">
                
                <!-- General Information Tab -->
                <div class="tab-pane fade show active" id="pane-general" role="tabpanel" aria-labelledby="tab-general">
                    <h6 class="text-primary mb-3"><i class="fas fa-info-circle"></i> {{ trans('app.general_information') }}</h6>

                    {{-- multilingual short description and description --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                @foreach($languages as $lang)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('app.short_description') }} - {{ $lang->name }} ({{ $lang->code }})</label>
                                        <input type="text" class="form-control" name="short_des[{{ $lang->code }}]" value="{{ $data->translations[$lang->code]['short_des'] ?? ($data->short_des ?? '') }}">
                                        @error('short_des.' . $lang->code)
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('app.description') }} - {{ $lang->name }} ({{ $lang->code }})</label>
                                        <textarea class="form-control" name="description[{{ $lang->code }}]">{{ $data->translations[$lang->code]['description'] ?? ($data->description ?? '') }}</textarea>
                                        @error('description.' . $lang->code)
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-save"></i> {{ trans('app.update') }}
                        </button>
                    </div>
                </div>

                <!-- Site Settings Tab -->
                <div class="tab-pane fade" id="pane-site" role="tabpanel" aria-labelledby="tab-site">
                    <h6 class="text-primary mb-3"><i class="fas fa-globe"></i> {{ trans('app.site_settings') }}</h6>
                    {{-- Site Name (multilingual) --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="col-form-label">{{ trans('app.site_name') }} <span class="text-danger">*</span></label>
                            <div class="row">
                                @foreach($languages as $lang)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ $lang->name }} ({{ $lang->code }})</label>
                                        <input type="text" class="form-control" name="site_name[{{ $lang->code }}]" value="{{ isset($data->translations[$lang->code]['site_name']) ? $data->translations[$lang->code]['site_name'] : ($data->site_name ?? '') }}">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputPhoto" class="col-form-label">{{ trans('app.logo') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="lfm1" data-input="thumbnail1" data-preview="holder1" class="btn btn-primary">
                                        <i class="fa fa-picture-o"></i> {{ trans('app.choose') }}
                                        </a>
                                    </span>
                                    <input id="thumbnail1" class="form-control" type="text" name="logo" value="{{$data->logo}}">
                                </div>
                                <div id="holder1" style="margin-top:15px;max-height:100px;"></div>
                                @error('logo')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inputPhoto" class="col-form-label">{{ trans('app.photo') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                                        <i class="fa fa-picture-o"></i> {{ trans('app.choose') }}
                                        </a>
                                    </span>
                                    <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$data->photo}}">
                                </div>
                                <div id="holder" style="margin-top:15px;max-height:100px;"></div>
                                @error('photo')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="address" class="col-form-label">{{ trans('app.address') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" required value="{{$data->address}}">
                                @error('address')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="email" class="col-form-label">{{ trans('app.email') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required value="{{$data->email}}">
                                @error('email')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone" class="col-form-label">{{ trans('app.phone_number') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone" required value="{{$data->phone}}">
                                @error('phone')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Settings Tab -->
                <div class="tab-pane fade" id="pane-ai" role="tabpanel" aria-labelledby="tab-ai">
                    <h6 class="text-primary mb-3"><i class="fas fa-robot"></i> {{ trans('app.ai_content_generation') }}</h6>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="ai_enabled" id="ai_enabled" value="1" class="form-check-input" {{ (!empty($data) && $data->ai_enabled) ? 'checked' : '' }}>
                            <label for="ai_enabled" class="form-check-label">{{ trans('app.enable_ai_content_generation') }}</label>
                        </div>
                    </div>

                    <div id="ai_settings" class="" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ai_provider">AI Provider</label>
                                    <select name="ai_provider" id="ai_provider" class="form-control">
                                        <option value="">-- Select Provider --</option>
                                        <option value="openai" {{ (!empty($data) && $data->ai_provider=='openai')? 'selected' : '' }}>OpenAI</option>
                                        <option value="azure" {{ (!empty($data) && $data->ai_provider=='azure')? 'selected' : '' }}>Azure OpenAI</option>
                                    </select>
                                </div>
                            <div class="form-group mt-3">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-save"></i> {{ trans('app.update') }}
                                </button>
                            </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ai_model">AI Model</label>
                                    <select name="ai_model" id="ai_model" class="form-control">
                                        <option value="gpt-4" {{ (!empty($data) && $data->ai_model=='gpt-4')? 'selected' : '' }}>gpt-4</option>
                                        <option value="gpt-3.5-turbo" {{ (!empty($data) && $data->ai_model=='gpt-3.5-turbo')? 'selected' : '' }}>gpt-3.5-turbo</option>
                                        <option value="text-davinci-003" {{ (!empty($data) && $data->ai_model=='text-davinci-003')? 'selected' : '' }}>text-davinci-003</option>
                                    </select>
                                    <small class="form-text text-muted">Choose model for generation.</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ai_api_key">AI API Key</label>
                            <input type="text" class="form-control" name="ai_api_key" id="ai_api_key" value="{{ $data->ai_api_key ?? '' }}" placeholder="Paste API key here">
                            <small class="form-text text-muted">Keep this key secret. Example: Paste OpenAI key here to test.</small>
                        </div>

                        <div id="azure_settings" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="azure_endpoint">Azure Endpoint</label>
                                        <input type="text" class="form-control" name="azure_endpoint" id="azure_endpoint" value="{{ $data->azure_endpoint ?? '' }}" placeholder="https://your-resource.openai.azure.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="azure_deployment">Azure Deployment Name</label>
                                        <input type="text" class="form-control" name="azure_deployment" id="azure_deployment" value="{{ $data->azure_deployment ?? '' }}" placeholder="deployment-name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="ai_max_tokens">Max Tokens</label>
                                <input type="number" class="form-control" name="ai_max_tokens" id="ai_max_tokens" value="{{ $data->ai_max_tokens ?? 200 }}" min="1">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="ai_temperature">Temperature</label>
                                <input type="number" step="0.01" min="0" max="1" class="form-control" name="ai_temperature" id="ai_temperature" value="{{ $data->ai_temperature ?? 0.7 }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Login Settings Tab -->
                <div class="tab-pane fade" id="pane-social" role="tabpanel" aria-labelledby="tab-social">
                    <h6 class="text-primary mb-3"><i class="fas fa-share-alt"></i> {{ trans('app.social_login_settings') }}</h6>
                    
                    <!-- Google Login Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fab fa-google text-danger"></i> {{ trans('app.google_login') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="google_login_enabled" id="google_login_enabled" value="1" class="form-check-input" {{ (!empty($data) && $data->google_login_enabled) ? 'checked' : '' }}>
                                    <label for="google_login_enabled" class="form-check-label">{{ trans('app.enable_google_login') }}</label>
                                </div>
                            </div>

                            <div id="google_settings" class="" style="display: none;">
                                <div class="form-group">
                                    <label for="google_client_id">{{ trans('app.google_client_id') }}</label>
                                    <input type="text" class="form-control" name="google_client_id" id="google_client_id" value="{{ $data->google_client_id ?? '' }}" placeholder="{{ trans('app.enter_google_client_id') }}">
                                </div>
                                <div class="form-group">
                                    <label for="google_client_secret">{{ trans('app.google_client_secret') }}</label>
                                    <input type="password" class="form-control" name="google_client_secret" id="google_client_secret" value="{{ $data->google_client_secret ?? '' }}" placeholder="{{ trans('app.enter_google_client_secret') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Facebook Login Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fab fa-facebook text-primary"></i> {{ trans('app.facebook_login') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="facebook_login_enabled" id="facebook_login_enabled" value="1" class="form-check-input" {{ (!empty($data) && $data->facebook_login_enabled) ? 'checked' : '' }}>
                                    <label for="facebook_login_enabled" class="form-check-label">{{ trans('app.enable_facebook_login') }}</label>
                                </div>
                            </div>

                            <div id="facebook_settings" class="" style="display: none;">
                                <div class="form-group">
                                    <label for="facebook_client_id">{{ trans('app.facebook_client_id') }}</label>
                                    <input type="text" class="form-control" name="facebook_client_id" id="facebook_client_id" value="{{ $data->facebook_client_id ?? '' }}" placeholder="{{ trans('app.enter_facebook_client_id') }}">
                                </div>
                                <div class="form-group">
                                    <label for="facebook_client_secret">{{ trans('app.facebook_client_secret') }}</label>
                                    <input type="password" class="form-control" name="facebook_client_secret" id="facebook_client_secret" value="{{ $data->facebook_client_secret ?? '' }}" placeholder="{{ trans('app.enter_facebook_client_secret') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GitHub Login Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fab fa-github text-dark"></i> {{ trans('app.github_login') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="github_login_enabled" id="github_login_enabled" value="1" class="form-check-input" {{ (!empty($data) && $data->github_login_enabled) ? 'checked' : '' }}>
                                    <label for="github_login_enabled" class="form-check-label">{{ trans('app.enable_github_login') }}</label>
                                </div>
                            </div>

                            <div id="github_settings" class="" style="display: none;">
                                <div class="form-group">
                                    <label for="github_client_id">{{ trans('app.github_client_id') }}</label>
                                    <input type="text" class="form-control" name="github_client_id" id="github_client_id" value="{{ $data->github_client_id ?? '' }}" placeholder="{{ trans('app.enter_github_client_id') }}">
                                </div>
                                <div class="form-group">
                                    <label for="github_client_secret">{{ trans('app.github_client_secret') }}</label>
                                    <input type="password" class="form-control" name="github_client_secret" id="github_client_secret" value="{{ $data->github_client_secret ?? '' }}" placeholder="{{ trans('app.enter_github_client_secret') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-save"></i> {{ trans('app.update') }}
                        </button>
                    </div>
                </div>

                <!-- Font Settings Tab -->
                <div class="tab-pane fade" id="pane-fonts" role="tabpanel" aria-labelledby="tab-fonts">
                    <h6 class="text-primary mb-3"><i class="fas fa-font"></i> {{ trans('app.font_settings') }}</h6>
                    
                    <!-- Google Fonts Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fab fa-google text-primary"></i> {{ trans('app.use_google_fonts') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" name="use_google_fonts" id="use_google_fonts" value="1" class="form-check-input" {{ (!empty($data) && $data->use_google_fonts) ? 'checked' : '' }}>
                                    <label for="use_google_fonts" class="form-check-label">{{ trans('app.use_google_fonts') }}</label>
                                </div>
                            <div class="form-group mt-3">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-save"></i> {{ trans('app.update') }}
                                </button>
                            </div>
                            </div>
                            <div id="google_fonts_settings" class="" style="display: none;">
                                <div class="form-group">
                                    <label for="google_fonts_url">{{ trans('app.google_fonts_url') }}</label>
                                    <input type="url" class="form-control" name="google_fonts_url" id="google_fonts_url" value="{{ $data->google_fonts_url ?? '' }}" placeholder="{{ trans('app.enter_google_fonts_url') }}">
                                    <small class="form-text text-muted">Example: https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Frontend Font Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-globe text-success"></i> {{ trans('app.frontend_font_settings') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="frontend_font_family">{{ trans('app.font_family') }}</label>
                                    <select class="form-control" name="frontend_font_family" id="frontend_font_family">
                                        <option value="Arial, sans-serif" {{ ($data->frontend_font_family ?? '') == 'Arial, sans-serif' ? 'selected' : '' }}>{{ trans('app.arial') }}</option>
                                        <option value="Helvetica, sans-serif" {{ ($data->frontend_font_family ?? '') == 'Helvetica, sans-serif' ? 'selected' : '' }}>{{ trans('app.helvetica') }}</option>
                                        <option value="Times New Roman, serif" {{ ($data->frontend_font_family ?? '') == 'Times New Roman, serif' ? 'selected' : '' }}>{{ trans('app.times_new_roman') }}</option>
                                        <option value="Georgia, serif" {{ ($data->frontend_font_family ?? '') == 'Georgia, serif' ? 'selected' : '' }}>{{ trans('app.georgia') }}</option>
                                        <option value="Verdana, sans-serif" {{ ($data->frontend_font_family ?? '') == 'Verdana, sans-serif' ? 'selected' : '' }}>{{ trans('app.verdana') }}</option>
                                        <option value="Tahoma, sans-serif" {{ ($data->frontend_font_family ?? '') == 'Tahoma, sans-serif' ? 'selected' : '' }}>{{ trans('app.tahoma') }}</option>
                                        <option value="Courier New, monospace" {{ ($data->frontend_font_family ?? '') == 'Courier New, monospace' ? 'selected' : '' }}>{{ trans('app.courier_new') }}</option>
                                        <option value="Cairo, sans-serif" {{ ($data->frontend_font_family ?? '') == 'Cairo, sans-serif' ? 'selected' : '' }}>{{ trans('app.cairo') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="frontend_font_size">{{ trans('app.font_size') }}</label>
                                    <select class="form-control" name="frontend_font_size" id="frontend_font_size">
                                        <option value="12px" {{ ($data->frontend_font_size ?? '') == '12px' ? 'selected' : '' }}>{{ trans('app.font_size_small') }}</option>
                                        <option value="14px" {{ ($data->frontend_font_size ?? '') == '14px' ? 'selected' : '' }}>{{ trans('app.font_size_medium') }}</option>
                                        <option value="16px" {{ ($data->frontend_font_size ?? '') == '16px' ? 'selected' : '' }}>{{ trans('app.font_size_large') }}</option>
                                        <option value="18px" {{ ($data->frontend_font_size ?? '') == '18px' ? 'selected' : '' }}>{{ trans('app.font_size_xlarge') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="frontend_font_weight">{{ trans('app.font_weight') }}</label>
                                    <select class="form-control" name="frontend_font_weight" id="frontend_font_weight">
                                        <option value="normal" {{ ($data->frontend_font_weight ?? '') == 'normal' ? 'selected' : '' }}>{{ trans('app.normal') }}</option>
                                        <option value="bold" {{ ($data->frontend_font_weight ?? '') == 'bold' ? 'selected' : '' }}>{{ trans('app.bold') }}</option>
                                        <option value="lighter" {{ ($data->frontend_font_weight ?? '') == 'lighter' ? 'selected' : '' }}>{{ trans('app.lighter') }}</option>
                                        <option value="bolder" {{ ($data->frontend_font_weight ?? '') == 'bolder' ? 'selected' : '' }}>{{ trans('app.bolder') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ trans('app.font_preview') }}</label>
                                <div id="frontend_font_preview" class="p-3 border rounded" style="font-family: {{ $data->frontend_font_family ?? 'Arial, sans-serif' }}; font-size: {{ $data->frontend_font_size ?? '14px' }}; font-weight: {{ $data->frontend_font_weight ?? 'normal' }};"
                                    {{ trans('app.font_preview') }} - هذا نص تجريبي لمعاينة الخط
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backend Font Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cogs text-warning"></i> {{ trans('app.backend_font_settings') }}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="backend_font_family">{{ trans('app.font_family') }}</label>
                                    <select class="form-control" name="backend_font_family" id="backend_font_family">
                                        <option value="Arial, sans-serif" {{ ($data->backend_font_family ?? '') == 'Arial, sans-serif' ? 'selected' : '' }}>{{ trans('app.arial') }}</option>
                                        <option value="Helvetica, sans-serif" {{ ($data->backend_font_family ?? '') == 'Helvetica, sans-serif' ? 'selected' : '' }}>{{ trans('app.helvetica') }}</option>
                                        <option value="Times New Roman, serif" {{ ($data->backend_font_family ?? '') == 'Times New Roman, serif' ? 'selected' : '' }}>{{ trans('app.times_new_roman') }}</option>
                                        <option value="Georgia, serif" {{ ($data->backend_font_family ?? '') == 'Georgia, serif' ? 'selected' : '' }}>{{ trans('app.georgia') }}</option>
                                        <option value="Verdana, sans-serif" {{ ($data->backend_font_family ?? '') == 'Verdana, sans-serif' ? 'selected' : '' }}>{{ trans('app.verdana') }}</option>
                                        <option value="Tahoma, sans-serif" {{ ($data->backend_font_family ?? '') == 'Tahoma, sans-serif' ? 'selected' : '' }}>{{ trans('app.tahoma') }}</option>
                                        <option value="Courier New, monospace" {{ ($data->backend_font_family ?? '') == 'Courier New, monospace' ? 'selected' : '' }}>{{ trans('app.courier_new') }}</option>
                                        <option value="Cairo, sans-serif" {{ ($data->backend_font_family ?? '') == 'Cairo, sans-serif' ? 'selected' : '' }}>{{ trans('app.cairo') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="backend_font_size">{{ trans('app.font_size') }}</label>
                                    <select class="form-control" name="backend_font_size" id="backend_font_size">
                                        <option value="12px" {{ ($data->backend_font_size ?? '') == '12px' ? 'selected' : '' }}>{{ trans('app.font_size_small') }}</option>
                                        <option value="14px" {{ ($data->backend_font_size ?? '') == '14px' ? 'selected' : '' }}>{{ trans('app.font_size_medium') }}</option>
                                        <option value="16px" {{ ($data->backend_font_size ?? '') == '16px' ? 'selected' : '' }}>{{ trans('app.font_size_large') }}</option>
                                        <option value="18px" {{ ($data->backend_font_size ?? '') == '18px' ? 'selected' : '' }}>{{ trans('app.font_size_xlarge') }}</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="backend_font_weight">{{ trans('app.font_weight') }}</label>
                                    <select class="form-control" name="backend_font_weight" id="backend_font_weight">
                                        <option value="normal" {{ ($data->backend_font_weight ?? '') == 'normal' ? 'selected' : '' }}>{{ trans('app.normal') }}</option>
                                        <option value="bold" {{ ($data->backend_font_weight ?? '') == 'bold' ? 'selected' : '' }}>{{ trans('app.bold') }}</option>
                                        <option value="lighter" {{ ($data->backend_font_weight ?? '') == 'lighter' ? 'selected' : '' }}>{{ trans('app.lighter') }}</option>
                                        <option value="bolder" {{ ($data->backend_font_weight ?? '') == 'bolder' ? 'selected' : '' }}>{{ trans('app.bolder') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>{{ trans('app.font_preview') }}</label>
                                <div id="backend_font_preview" class="p-3 border rounded" style="font-family: {{ $data->backend_font_family ?? 'Arial, sans-serif' }}; font-size: {{ $data->backend_font_size ?? '14px' }}; font-weight: {{ $data->backend_font_weight ?? 'normal' }};"
                                    {{ trans('app.font_preview') }} - This is a sample text for font preview
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3 mt-4">
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save"></i> {{ trans('app.update') }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />

@endpush
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
  // Toggle AI settings visibility
  document.addEventListener('DOMContentLoaded', function(){
    var aiCheckbox = document.getElementById('ai_enabled');
    var aiSettings = document.getElementById('ai_settings');
    if(aiCheckbox){
      // set initial state from server-side value
      if(aiCheckbox.checked){ aiSettings.style.display = 'block'; }
      aiCheckbox.addEventListener('change', function(){
        aiSettings.style.display = this.checked ? 'block' : 'none';
      });
    }
    var provider = document.getElementById('ai_provider');
    var azureDiv = document.getElementById('azure_settings');
    if(provider){
      // initial
      if(provider.value === 'azure') azureDiv.style.display = 'block';
      provider.addEventListener('change', function(){
        azureDiv.style.display = this.value === 'azure' ? 'block' : 'none';
      });
    }

    // Toggle Social Login settings visibility
    var googleCheckbox = document.getElementById('google_login_enabled');
    var googleSettings = document.getElementById('google_settings');
    if(googleCheckbox){
      if(googleCheckbox.checked){ googleSettings.style.display = 'block'; }
      googleCheckbox.addEventListener('change', function(){
        googleSettings.style.display = this.checked ? 'block' : 'none';
      });
    }

    var facebookCheckbox = document.getElementById('facebook_login_enabled');
    var facebookSettings = document.getElementById('facebook_settings');
    if(facebookCheckbox){
      if(facebookCheckbox.checked){ facebookSettings.style.display = 'block'; }
      facebookCheckbox.addEventListener('change', function(){
        facebookSettings.style.display = this.checked ? 'block' : 'none';
      });
    }

    var githubCheckbox = document.getElementById('github_login_enabled');
    var githubSettings = document.getElementById('github_settings');
    if(githubCheckbox){
      if(githubCheckbox.checked){ githubSettings.style.display = 'block'; }
      githubCheckbox.addEventListener('change', function(){
        githubSettings.style.display = this.checked ? 'block' : 'none';
      });
    }

    // Toggle Google Fonts settings visibility
    var googleFontsCheckbox = document.getElementById('use_google_fonts');
    var googleFontsSettings = document.getElementById('google_fonts_settings');
    if(googleFontsCheckbox){
      if(googleFontsCheckbox.checked){ googleFontsSettings.style.display = 'block'; }
      googleFontsCheckbox.addEventListener('change', function(){
        googleFontsSettings.style.display = this.checked ? 'block' : 'none';
      });
    }

    // Font Preview Updates
    function updateFontPreview(previewId, familyId, sizeId, weightId) {
      var preview = document.getElementById(previewId);
      var family = document.getElementById(familyId);
      var size = document.getElementById(sizeId);
      var weight = document.getElementById(weightId);
      
      if(preview && family && size && weight) {
        preview.style.fontFamily = family.value;
        preview.style.fontSize = size.value;
        preview.style.fontWeight = weight.value;
      }
    }

    // Frontend font preview
    var frontendFamily = document.getElementById('frontend_font_family');
    var frontendSize = document.getElementById('frontend_font_size');
    var frontendWeight = document.getElementById('frontend_font_weight');
    
    if(frontendFamily) {
      frontendFamily.addEventListener('change', function(){
        updateFontPreview('frontend_font_preview', 'frontend_font_family', 'frontend_font_size', 'frontend_font_weight');
      });
    }
    if(frontendSize) {
      frontendSize.addEventListener('change', function(){
        updateFontPreview('frontend_font_preview', 'frontend_font_family', 'frontend_font_size', 'frontend_font_weight');
      });
    }
    if(frontendWeight) {
      frontendWeight.addEventListener('change', function(){
        updateFontPreview('frontend_font_preview', 'frontend_font_family', 'frontend_font_size', 'frontend_font_weight');
      });
    }

    // Backend font preview
    var backendFamily = document.getElementById('backend_font_family');
    var backendSize = document.getElementById('backend_font_size');
    var backendWeight = document.getElementById('backend_font_weight');
    
    if(backendFamily) {
      backendFamily.addEventListener('change', function(){
        updateFontPreview('backend_font_preview', 'backend_font_family', 'backend_font_size', 'backend_font_weight');
      });
    }
    if(backendSize) {
      backendSize.addEventListener('change', function(){
        updateFontPreview('backend_font_preview', 'backend_font_family', 'backend_font_size', 'backend_font_weight');
      });
    }
    if(backendWeight) {
      backendWeight.addEventListener('change', function(){
        updateFontPreview('backend_font_preview', 'backend_font_family', 'backend_font_size', 'backend_font_weight');
      });
    }
  });
</script>

<script>
    $('#lfm').filemanager('image');
    $('#lfm1').filemanager('image');
    $(document).ready(function() {
    $('#summary').summernote({
      placeholder: "Write short description.....",
        tabsize: 2,
        height: 150
    });
    });

    $(document).ready(function() {
      $('#quote').summernote({
        placeholder: "Write short Quote.....",
          tabsize: 2,
          height: 100
      });
    });
    $(document).ready(function() {
      $('#description').summernote({
        placeholder: "Write detail description.....",
          tabsize: 2,
          height: 150
      });
    });
</script>
@endpush