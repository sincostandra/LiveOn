@extends('layouts.app')

@section('title', 'Chat with ' . $otherUser->first_name . ' - Messages')

@section('content')

<div style="display: flex; height: calc(100vh - 80px); background: #f5f5f5; font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;">
    <!-- Left Sidebar - Back to Messages -->
    <div style="width: 80px; background: #fafbfc; border-right: 1px solid #eee; display: flex; flex-direction: column; align-items: center; padding: 20px 0;">
        <a href="{{ route('messages') }}" style="width: 48px; height: 48px; background: white; border: 1px solid #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #7C5CEE; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f5f5f5';" onmouseout="this.style.backgroundColor='white';" title="Back to Messages">
            <i class="fas fa-arrow-left" style="font-size: 1.2rem;"></i>
        </a>
    </div>

    <!-- Right Side - Chat Area -->
    <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
        <!-- Header -->
        <div style="background: white; padding: 16px 20px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 12px;">
            @if ($otherUser->profile_image)
                <img src="{{ asset('storage/' . $otherUser->profile_image) }}" alt="Profile" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover;">
            @else
                <div style="width: 48px; height: 48px; background: #00BCD4; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1rem;">
                    {{ strtoupper(substr($otherUser->first_name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h3 style="font-weight: 700; color: #1a1a1a; margin: 0; font-size: 1.05rem;">
                    {{ $otherUser->first_name }} {{ $otherUser->last_name }}
                </h3>
                <p style="color: #999; font-size: 0.85rem; margin: 0;">
                    Direct Message
                </p>
            </div>
        </div>

        <!-- Messages Container -->
        <div style="flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column;">
            @forelse ($messages as $message)
                @php
                    $isOwn = $message->sender_id === Auth::id();
                @endphp

                <!-- Date Separator -->
                @if (isset($previousDate) && $previousDate !== $message->created_at->format('Y-m-d'))
                    <div style="text-align: center; margin: 20px 0 10px 0;">
                        <span style="background: #e0e0e0; color: #666; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem;">{{ $message->created_at->format('M d, Y') }}</span>
                    </div>
                @endif

                <!-- Message Group -->
                <div style="display: flex; {{ $isOwn ? 'justify-content: flex-end;' : 'justify-content: flex-start;' }} margin-bottom: 12px;">
                    <div style="display: flex; gap: 10px; max-width: 70%; {{ $isOwn ? 'flex-direction: row-reverse;' : '' }}">
                        <!-- Avatar -->
                        @if ($message->sender->profile_image)
                            <img src="{{ asset('storage/' . $message->sender->profile_image) }}" alt="Profile" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                        @else
                            <div style="width: 28px; height: 28px; {{ $isOwn ? 'background: #7C5CEE;' : 'background: #00BCD4;' }} color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem; flex-shrink: 0;">
                                {{ strtoupper(substr($message->sender->first_name, 0, 1)) }}
                            </div>
                        @endif

                        <!-- Message Bubble -->
                        <div>
                            @if ($message->image_path)
                                <img src="{{ asset('storage/' . $message->image_path) }}" alt="Image" style="max-width: 300px; max-height: 300px; border-radius: 10px; margin-bottom: 6px; display: block; object-fit: cover;">
                            @endif
                            
                            @if ($message->message)
                                <div style="background: {{ $isOwn ? 'linear-gradient(135deg, #7C5CEE, #9D4EDD)' : '#ffffff' }}; color: {{ $isOwn ? '#ffffff' : '#1a1a1a' }}; padding: 10px 14px; border-radius: 10px; word-break: break-word; box-shadow: 0 2px 4px rgba(0,0,0,0.08); font-size: 0.9rem; {{ $message->image_path ? 'margin-top: 6px;' : '' }}">
                                    {{ $message->message }}
                                </div>
                            @endif
                            
                            <!-- Timestamp -->
                            <span style="color: #999; font-size: 0.7rem; display: block; margin-top: 4px; {{ $isOwn ? 'text-align: right;' : '' }}">
                                {{ $message->created_at->format('H:i') }}
                            </span>
                        </div>
                    </div>
                </div>

                @php
                    $previousDate = $message->created_at->format('Y-m-d');
                @endphp
            @empty
                <div style="text-align: center; padding: 40px 20px; color: #999;">
                    <i class="fas fa-comments" style="font-size: 2.5rem; color: #ddd; margin-bottom: 12px; display: block;"></i>
                    <p style="font-size: 0.9rem;">No messages yet. Start the conversation!</p>
                </div>
            @endforelse

            <!-- Spacer -->
            <div style="flex: 1;"></div>
        </div>

        <!-- Message Input -->
        <form id="directMessageForm" action="{{ route('messages.direct.send', $otherUser) }}" method="POST" enctype="multipart/form-data" style="background: white; padding: 12px 16px; border-top: 1px solid #eee;">
            @csrf
            <!-- Image Preview -->
            <div id="imagePreviewContainerDirect" style="display: none; padding: 10px; background: #f5f5f5; border-radius: 8px; margin-bottom: 10px; position: relative;">
                <img id="imagePreviewDirect" src="" alt="Preview" style="max-width: 150px; max-height: 150px; border-radius: 8px; display: block;">
                <button type="button" onclick="clearImagePreviewDirect()" style="position: absolute; top: 5px; right: 5px; width: 24px; height: 24px; background: rgba(0,0,0,0.6); color: white; border: none; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-times" style="font-size: 0.8rem;"></i>
                </button>
            </div>
            
            <div style="display: flex; gap: 10px; align-items: flex-end;">
                <button type="button" onclick="document.getElementById('imageInputDirect').click();" style="width: 36px; height: 36px; background: white; border: 1px solid #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #7C5CEE; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f5f5f5';" onmouseout="this.style.backgroundColor='white';">
                    <i class="fas fa-image" style="font-size: 1rem;"></i>
                </button>
                <input type="file" name="image" id="imageInputDirect" accept="image/*" style="display: none;" onchange="handleImageSelectDirect(this)">
                
                <input type="text" name="message" id="messageInputDirect" placeholder="Type a message..." style="flex: 1; padding: 9px 14px; border: 1px solid #ddd; border-radius: 20px; font-size: 0.9rem; outline: none; transition: all 0.2s ease;" onfocus="this.style.borderColor='#7C5CEE';" onblur="this.style.borderColor='#ddd';">
                
                <button type="submit" style="width: 36px; height: 36px; background: linear-gradient(135deg, #7C5CEE, #9D4EDD); color: white; border: none; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s ease;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
                    <i class="fas fa-paper-plane" style="font-size: 0.95rem;"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Handle image selection and preview
function handleImageSelectDirect(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreviewDirect').src = e.target.result;
            document.getElementById('imagePreviewContainerDirect').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Clear image preview
function clearImagePreviewDirect() {
    document.getElementById('imageInputDirect').value = '';
    document.getElementById('imagePreviewContainerDirect').style.display = 'none';
    document.getElementById('imagePreviewDirect').src = '';
}

// Form validation
document.getElementById('directMessageForm').addEventListener('submit', function(e) {
    const messageInput = document.getElementById('messageInputDirect');
    const imageInput = document.getElementById('imageInputDirect');
    
    // Check if both message and image are empty
    if (!messageInput.value.trim() && !imageInput.files.length) {
        e.preventDefault();
        alert('Please enter a message or select an image.');
        return false;
    }
});

// Auto-scroll to bottom on page load
window.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.querySelector('[style*="flex: 1; overflow-y: auto"]');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>

@endsection
