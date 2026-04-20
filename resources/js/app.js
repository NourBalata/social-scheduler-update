import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// resources/js/app.js
document.addEventListener('DOMContentLoaded', function() {
    const postForm = document.getElementById('postForm');
    
    if (postForm) {
        // Elements
        const contentArea = document.getElementById('content');
        const charCountSpan = document.getElementById('charCount');
        const charCountDiv = document.querySelector('.char-count');
        const pageSelect = document.getElementById('page');
        const mediaUrlInput = document.getElementById('media_url');
        const mediaTypeSelect = document.getElementById('media_type');
        const scheduledAtInput = document.getElementById('scheduled_at');
        const submitBtn = document.getElementById('submitBtn');
        
        // Preview elements
        const previewAvatar = document.getElementById('previewAvatar');
        const previewPageName = document.getElementById('previewPageName');
        const previewTime = document.getElementById('previewTime');
        const previewContent = document.getElementById('previewContent');
        const previewMedia = document.getElementById('previewMedia');
        const previewImage = document.getElementById('previewImage');

        // Character counter
        function updateCharCount() {
            const length = contentArea.value.length;
            const remaining = 5000 - length;
            
            charCountSpan.textContent = length;
            
            // Update styling
            charCountDiv.classList.remove('warning', 'error');
            if (remaining < 500) {
                charCountDiv.classList.add('warning');
            }
            if (remaining < 100) {
                charCountDiv.classList.add('error');
            }
            
            // Disable submit if over limit
            submitBtn.disabled = length > 5000 || length === 0;
        }

        // Update preview content
        function updatePreviewContent() {
            const content = contentArea.value.trim();
            
            if (content) {
                previewContent.textContent = content;
                previewContent.classList.remove('empty');
            } else {
                previewContent.textContent = 'Your post content will appear here...';
                previewContent.classList.add('empty');
            }
        }

        // Update preview page
        function updatePreviewPage() {
            const selectedOption = pageSelect.options[pageSelect.selectedIndex];
            
            if (selectedOption.value) {
                const pageName = selectedOption.dataset.name;
                previewPageName.textContent = pageName;
                previewAvatar.textContent = pageName.charAt(0).toUpperCase();
            } else {
                previewPageName.textContent = 'Select a page';
                previewAvatar.textContent = '?';
            }
        }

        // Update preview time
        function updatePreviewTime() {
            const scheduledDate = scheduledAtInput.value;
            
            if (scheduledDate) {
                const date = new Date(scheduledDate);
                const now = new Date();
                const diff = date - now;
                
                if (diff > 0) {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const days = Math.floor(hours / 24);
                    
                    if (days > 0) {
                        previewTime.textContent = `Scheduled for ${days} day${days > 1 ? 's' : ''} from now`;
                    } else if (hours > 0) {
                        previewTime.textContent = `Scheduled for ${hours} hour${hours > 1 ? 's' : ''} from now`;
                    } else {
                        previewTime.textContent = 'Scheduled soon';
                    }
                } else {
                    previewTime.textContent = 'Just now';
                }
            } else {
                previewTime.textContent = 'Just now';
            }
        }

        // Update preview media
        function updatePreviewMedia() {
            const mediaUrl = mediaUrlInput.value.trim();
            const mediaType = mediaTypeSelect.value;
            
            if (mediaUrl && mediaType === 'image') {
                previewImage.src = mediaUrl;
                previewMedia.style.display = 'block';
                
                // Handle image load error
                previewImage.onerror = function() {
                    previewMedia.style.display = 'none';
                };
            } else {
                previewMedia.style.display = 'none';
            }
        }

        // Validate media URL format
        function validateMediaUrl() {
            const mediaUrl = mediaUrlInput.value.trim();
            
            if (mediaUrl) {
                try {
                    new URL(mediaUrl);
                    mediaUrlInput.setCustomValidity('');
                } catch (e) {
                    mediaUrlInput.setCustomValidity('Please enter a valid URL');
                }
            } else {
                mediaUrlInput.setCustomValidity('');
            }
        }

        // Auto-set media type from URL
        function detectMediaType() {
            const url = mediaUrlInput.value.toLowerCase();
            
            if (url) {
                if (url.match(/\.(jpg|jpeg|png|gif|webp)$/)) {
                    mediaTypeSelect.value = 'image';
                } else if (url.match(/\.(mp4|mov|avi|webm)$/)) {
                    mediaTypeSelect.value = 'video';
                }
                
                updatePreviewMedia();
            }
        }

        // Event listeners
        contentArea.addEventListener('input', function() {
            updateCharCount();
            updatePreviewContent();
        });

        pageSelect.addEventListener('change', updatePreviewPage);
        
        mediaUrlInput.addEventListener('input', function() {
            validateMediaUrl();
            detectMediaType();
        });
        
        mediaTypeSelect.addEventListener('change', updatePreviewMedia);
        
        scheduledAtInput.addEventListener('change', updatePreviewTime);

        // Form submission validation
        postForm.addEventListener('submit', function(e) {
            const content = contentArea.value.trim();
            
            if (content.length === 0) {
                e.preventDefault();
                alert('Please enter post content');
                contentArea.focus();
                return false;
            }
            
            if (content.length > 5000) {
                e.preventDefault();
                alert('Post content is too long (max 5000 characters)');
                contentArea.focus();
                return false;
            }
            
            // Disable button to prevent double submission
            submitBtn.disabled = true;
            submitBtn.textContent = 'Scheduling...';
        });

        // Initialize
        updateCharCount();
        updatePreviewPage();
        updatePreviewTime();
        
        // Set default scheduled time (1 hour from now)
        if (!scheduledAtInput.value) {
            const defaultTime = new Date();
            defaultTime.setHours(defaultTime.getHours() + 1);
            defaultTime.setMinutes(0);
            scheduledAtInput.value = defaultTime.toISOString().slice(0, 16);
            updatePreviewTime();
        }
    }

    // Auto-refresh posts status (existing code)
    const postsTable = document.getElementById('postsTable');
    if (postsTable) {
        setInterval(() => {
            fetch('/api/posts')
                .then(r => r.json())
                .then(data => {
                    data.forEach(post => {
                        const row = document.querySelector(`tr[data-post-id="${post.id}"]`);
                        if (row) {
                            const statusCell = row.querySelector('.status');
                            if (statusCell && statusCell.textContent !== post.status) {
                                statusCell.textContent = post.status;
                                statusCell.style.transition = 'background 0.3s';
                                statusCell.style.background = '#e8f5e9';
                                setTimeout(() => {
                                    statusCell.style.background = '';
                                }, 1000);
                            }
                        }
                    });
                })
                .catch(err => console.error('Failed to refresh posts:', err));
        }, 30000);
    }
});
