@extends('backend.layouts.master')
@section('main-content')

<div class="card">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ trans('app.edit_file') }}: {{$filename}} ({{$language->name}})</h6>
    </div>
    <div class="card-body">
        <form method="post" action="{{route('language.update-file', [$language->id, $filename])}}">
            @csrf
            <div class="form-group">
                <label for="content" class="col-form-label">{{ trans('app.file_content') }} <span class="text-danger">*</span></label>
                <textarea id="content" name="content" rows="20" class="form-control" style="font-family: 'Courier New', monospace;">{{$content}}</textarea>
                @error('content')
                <span class="text-danger">{{$message}}</span>
                @enderror
            </div>

            <div class="form-group mb-3">
                <a href="{{route('language.show', $language->id)}}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ trans('app.back_to_language') }}
                </a>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save"></i> {{ trans('app.update_file') }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ trans('app.file_information') }}</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th>{{ trans('app.language') }}</th>
                        <td>{{$language->name}} ({{$language->code}})</td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.file_name') }}</th>
                        <td>{{$filename}}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('app.file_path') }}</th>
                        <td><code>resources/lang/{{$language->code}}/{{$filename}}</code></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> {{ trans('app.instructions') }}:</h6>
                    <ul class="mb-0">
                        <li>{{ trans('app.php_syntax_warning') }}</li>
                        <li>{{ trans('app.single_quotes_warning') }}</li>
                        <li>{{ trans('app.comma_warning') }}</li>
                        <li>{{ trans('app.test_changes_warning') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Add syntax highlighting for PHP code
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('content');
        
        // Add line numbers
        const lines = textarea.value.split('\n');
        const lineNumbers = lines.map((_, index) => index + 1).join('\n');
        
        // Create a wrapper for line numbers
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = 'flex';
        
        const lineNumbersDiv = document.createElement('div');
        lineNumbersDiv.style.background = '#f8f9fa';
        lineNumbersDiv.style.padding = '10px';
        lineNumbersDiv.style.border = '1px solid #dee2e6';
        lineNumbersDiv.style.borderRight = 'none';
        lineNumbersDiv.style.fontFamily = 'Courier New, monospace';
        lineNumbersDiv.style.fontSize = '14px';
        lineNumbersDiv.style.lineHeight = '1.5';
        lineNumbersDiv.style.color = '#6c757d';
        lineNumbersDiv.style.userSelect = 'none';
        lineNumbersDiv.style.minWidth = '50px';
        lineNumbersDiv.textContent = lineNumbers;
        
        textarea.style.borderLeft = 'none';
        textarea.style.flex = '1';
        
        textarea.parentNode.insertBefore(wrapper, textarea);
        wrapper.appendChild(lineNumbersDiv);
        wrapper.appendChild(textarea);
    });
</script>
@endpush
