
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Schedule New Post</h1>

    @if($errors->any())
    <div class="error-message">
        <strong>Oops!</strong> Please fix the following:
        <ul style="margin-top: 8px; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Form -->
        <div class="card">
            <form action="{{ route('posts.store') }}" method="POST" id="postForm">
                @csrf
                
                <div class="form-group">
                    <label for="page">Facebook Page *</label>
                    <select name="facebook_page_id" id="page" required>
                        <option value="">Select a page...</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" 
                                    data-name="{{ $page->page_name }}"
                                    {{ old('facebook_page_id') == $page->id ? 'selected' : '' }}>
                                {{ $page->page_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">Post Content *</label>
                    <textarea 
                        name="content" 
                        id="content" 
                        rows="6" 
                        placeholder="What's on your mind?"
                        required>{{ old('content') }}</textarea>
                    <div class="char-count">
                        <span id="charCount">0</span> / 5,000 characters
                    </div>
                </div>

                <div class="form-group">
                    <label for="media_url">Media URL (optional)</label>
                    <input 
                        type="url" 
                        name="media_url" 
                        id="media_url"
                        placeholder="https://example.com/image.jpg"
                        value="{{ old('media_url') }}">
                    <small style="color: var(--text-secondary); font-size: 13px;">
                        Add an image or video URL
                    </small>
                </div>

                <div class="form-group">
                    <label for="media_type">Media Type</label>
                    <select name="media_type" id="media_type">
                        <option value="">None</option>
                        <option value="image" {{ old('media_type') == 'image' ? 'selected' : '' }}>Image</option>
                        <option value="video" {{ old('media_type') == 'video' ? 'selected' : '' }}>Video</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="scheduled_at">Schedule Date & Time *</label>
                    <input 
                        type="datetime-local" 
                        name="scheduled_at" 
                        id="scheduled_at"
                        min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                        value="{{ old('scheduled_at') }}"
                        required>
                    <small style="color: var(--text-secondary); font-size: 13px;">
                        Posts must be scheduled at least 5 minutes in the future
                    </small>
                </div>

                <button type="submit" id="submitBtn">
                    Schedule Post
                </button>
            </form>
        </div>

        <!-- Live Preview -->
        <div>
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 16px;">Preview</h2>
            <div class="card post-preview">
                <div class="post-preview-header">
                    <div class="page-avatar" id="previewAvatar">
                        ?
                    </div>
                    <div>
                        <div class="page-name" id="previewPageName">
                            Select a page
                        </div>
                        <div class="post-time" id="previewTime">
                            Just now
                        </div>
                    </div>
                </div>
                
                <div class="post-content empty" id="previewContent">
                    Your post content will appear here...
                </div>
                
                <div class="post-media" id="previewMedia" style="display: none;">
                    <img id="previewImage" src="" alt="Preview">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection