@php
    use App\Models\Settings;
    $title = 'Notifications | Super Admin';
    $settings = Settings::first();
@endphp

@extends('layouts/layoutMaster')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
    'resources/assets/vendor/libs/@form-validation/form-validation.scss',
    'resources/assets/vendor/libs/animate-css/animate.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/moment/moment.js',
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
    'resources/assets/vendor/libs/@form-validation/popular.js',
    'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
    'resources/assets/vendor/libs/@form-validation/auto-focus.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
  ])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite([
    'resources/js/main-select2.js'
])
@endsection

@section('title', $title)

@section('content')
    @include('nav')      
    <div class="d-flex justify-content-between align-items-center mb-4 py-3">
            <div>
                <h5 class="mb-0 text-primary">{{ $title }}</h5>
            </div>
            <!-- <div>
                <a href="{{ route('superadmin.packages') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div> -->
        </div>

    <div class="card">
        <div class="card-header">
            <h3>Compose Message</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('superadmin.communicator.store') }}">
                @csrf
                <!-- Recipients -->
                <div class="mb-3">
                    <label for="recipients" class="form-label">Recipients:</label>
                    <div class="d-flex mb-2">
                        <button type="button" id="select-all" class="btn btn-outline-primary btn-sm me-2">Select All</button>
                        <button type="button" id="deselect-all" class="btn btn-outline-danger btn-sm">Deselect All</button>
                    </div>
                    <select class="form-select select2" id="recipients" name="recipients[]" multiple required>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @error('recipients')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Subject -->
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject:</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                    @error('subject')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Message -->
                <div class="mb-3">
                    <label for="message" class="form-label">Message:</label>
                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                    @error('message')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                
                <!-- Submit Button -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>    
    <div class="card my-5">
        <div class="card-header">
            <h3>Message History</h3>
        </div>
        <div class="card-body">
            <div class="card-datatable table-responsive">
                <table id="datatable" class="table table-top">
                    <thead>
                        <tr>
                           <th>Subject</th>
                           <th>Message</th>
                           <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @foreach($messages as $message)
                            <tr>
                                <td>{{ $message->subject }}</td>
                                <td>{{ \Illuminate\Support\Str::limit(strip_tags(html_entity_decode($message->message)), 120) }}</td>

                                <td>{{ $message->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Pagination links --}}
            <div class="mt-3">
                {{ $messages->links('pagination::bootstrap-4') }} {{-- This will render the pagination links --}}
            </div>
        </div>
    </div>    
    
    
@endsection





@section('scripts')
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- TinyMCE Script -->
    <script src="https://cdn.tiny.cloud/1/pq95enacejyy91w16ggytzb262wguctyz7tk7okgwib7ecog/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        
        
        tinymce.init({
            selector: '#message',
            setup: function (editor) {
            editor.on('change', function () {
                editor.save(); // Save content back to the textarea
            });
            },
            plugins: [
              // Core editing features
              'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'image', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
              // Your account includes a free trial of TinyMCE premium features
             
              // Early access to document converters
              ],
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'Author name',
            mergetags_list: [
              { value: 'First.Name', title: 'First Name' },
              { value: 'Email', title: 'Email' },
            ],
            ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
            exportpdf_converter_options: { 'format': 'Letter', 'margin_top': '1in', 'margin_right': '1in', 'margin_bottom': '1in', 'margin_left': '1in' },
            exportword_converter_options: { 'document': { 'size': 'Letter' } },
            importword_converter_options: { 'formatting': { 'styles': 'inline', 'resets': 'inline',	'defaults': 'inline', } },
        });
        
        // Ensure the editor content is validated
        document.querySelector('form').addEventListener('submit', function (e) {
            const editorContent = tinymce.get('message').getContent({ format: 'text' }).trim();
            if (!editorContent) {
                alert('The message field is required.');
                e.preventDefault(); // Stop form submission
                tinymce.get('message').focus(); // Focus on the editor
            }
        });
        
    </script>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select recipients",
                allowClear: true
            });

            $('#select-all').on('click', function() {
                $('.select2 > option').prop("selected", true).trigger("change");
            });

            $('#deselect-all').on('click', function() {
                $('.select2 > option').prop("selected", false).trigger("change");
            });
        });
</script>
@endsection
@endsection
