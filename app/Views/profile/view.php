<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- 
    1. PROFILE HEADER SECTION
    Renders Top-level User stats and handles the complicated Friendship State Machine UI.
    If viewing someone else, displays conditional Add/Pending/Remove buttons.
-->
<div class="bg-white border-b">
    <div class="max-w-5xl mx-auto px-4 py-8 md:py-12">
        <div class="flex gap-8 md:gap-16 items-start mb-8">
            <!-- Profile Picture -->
            <div class="flex-shrink-0">
                <?php if (!empty($user['profile_pic'])): ?>
                    <img class="w-32 h-32 md:w-48 md:h-48 rounded-full object-cover border-2 border-gray-200" src="/uploads/profile_pics/<?= $user['profile_pic'] ?>">
                <?php else: ?>
                    <img class="w-32 h-32 md:w-48 md:h-48 rounded-full border-2 border-gray-200" src="https://via.placeholder.com/200">
                <?php endif; ?>
            </div>

            <!-- Profile Info -->
            <div class="flex-1 min-w-0">
                <!-- Username and Action Buttons -->
                <div class="flex items-center gap-4 mb-4 flex-wrap">
                    <h1 class="text-2xl md:text-4xl font-bold"><?= esc($user['username']) ?></h1>
                    
                    <div class="flex gap-2">
                        <?php if ($user['id'] == session()->get('user_id')): ?>
                            <button onclick="openEditProfileModal()" class="bg-blue-500 text-white px-8 py-2 rounded-lg font-semibold hover:bg-blue-600">Edit Profile</button>
                        <?php else: ?>
                            <?php if (!$friendship): ?>
                                <form method="post" action="/friend/request" style="margin: 0;">
                                    <input type="hidden" name="receiver_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="bg-blue-500 text-white px-8 py-2 rounded-lg font-semibold hover:bg-blue-600">Add Friend</button>
                                </form>
                            <?php elseif ($friendship['status'] == 'pending'): ?>
                                <button class="bg-gray-200 text-gray-800 px-8 py-2 rounded-lg font-semibold cursor-default">Request Pending</button>
                            <?php elseif ($friendship['status'] == 'accepted'): ?>
                                <form method="post" action="/friend/remove" style="margin: 0;">
                                    <input type="hidden" name="id" value="<?= $friendship['id'] ?>">
                                    <button type="submit" class="bg-gray-100 text-red-500 px-8 py-2 rounded-lg font-semibold hover:bg-gray-200">Remove Friend</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex gap-8 md:gap-12 mb-6">
                    <div>
                        <span class="font-bold text-lg"><?= count($posts) ?></span>
                        <span class="text-gray-600 text-sm ml-2">posts</span>
                    </div>
                    <div class="cursor-pointer hover:text-gray-600" onclick="openFriendsModal(<?= $user['id'] ?>, <?= $user['id'] == session()->get('user_id') ? 'true' : 'false' ?>)">
                        <span class="font-bold text-lg"><?= $friendCount ?></span>
                        <span class="text-gray-600 text-sm ml-2">friends</span>
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <p class="text-base text-gray-800"><?= esc($user['bio']) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 
    2. MEDIA GRID SECTION
    Maps all Posts belonging strictly to this user.
    Clicking a grid item fires the same Unified Modal system used in feed.php.
-->
<div class="bg-white">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <!-- Posts Section Header -->
        <div class="border-t pt-6">
            <h2 class="text-sm font-bold text-gray-600 uppercase tracking-wider mb-6">Posts</h2>
            
            <!-- Grid -->
            <div class="grid grid-cols-3 md:grid-cols-4 gap-4">
                <?php foreach ($posts as $post): ?>
                    <?php if ($post['image']): ?>
                        <div onclick="openPostView(<?= $post['id'] ?>)" class="aspect-square bg-gray-200 rounded-lg overflow-hidden cursor-pointer hover:opacity-80 transition">
                            <div id="post-media-<?= $post['id'] ?>" class="w-full h-full" data-username="<?= esc($post['username']) ?>" data-profile-pic="<?= $post['profile_pic'] ?>" data-caption="<?= esc($post['caption']) ?>" data-created-at="<?= $post['created_at'] ?? date('Y-m-d H:i:s') ?>">
                                <?php $ext = pathinfo($post['image'], PATHINFO_EXTENSION); ?>
                                <?php if (in_array(strtolower($ext), ['mp4', 'webm', 'mov'])): ?>
                                    <video src="/uploads/posts/<?= $post['image'] ?>?v=<?= $post['id'] ?>" class="w-full h-full object-cover bg-black" loop muted playsinline></video>
                                <?php else: ?>
                                    <img src="/uploads/posts/<?= $post['image'] ?>" class="w-full h-full object-cover">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if (empty($posts)): ?>
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg">No posts yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- 
    3. UNIFIED POST / COMMENT MODAL
    Reuses the feed-style modal to view profile media and its comments without leaving the profile.
-->
<div id="postModal" class="fixed inset-0 z-[100] bg-black/80 md:bg-black/80 flex items-end md:items-center justify-center hidden" onclick="if(event.target===this) closeModal('postModal')">
    <div id="postInnerContainer" class="bg-white w-full h-[100dvh] md:h-[90vh] md:max-w-5xl md:rounded-xl flex flex-col md:flex-row overflow-hidden relative transition-all">
        <button onclick="closeModal('postModal')" class="absolute top-4 right-4 md:right-4 md:left-auto z-50 bg-black/60 text-white shadow-md rounded-full w-8 h-8 flex items-center justify-center font-bold hover:scale-110 transition">&times;</button>
        
        <!-- Media Area -->
        <div id="postModalMedia" class="w-full md:w-3/5 bg-black flex flex-col h-[50vh] md:h-full overflow-hidden relative border-b md:border-r border-gray-200">
            <!-- Header with user info -->
            <div class="bg-white p-3 border-b flex items-center gap-3">
                <img id="postHeaderProfilePic" src="" class="w-10 h-10 rounded-full object-cover">
                <div class="flex-1">
                    <p id="postHeaderUsername" class="font-semibold text-sm"></p>
                    <p id="postHeaderTime" class="text-gray-500 text-xs"></p>
                </div>
            </div>
            <!-- Media Cloned Here -->
            <div id="postMediaContent" class="flex-1 flex items-center justify-center bg-black overflow-hidden">
                
            </div>
            <!-- Caption at bottom -->
            <div id="postCaption" class="bg-white p-3 border-t text-sm">
            </div>
        </div>

        <!-- Comments Area -->
        <div id="postCommentArea" class="w-full md:w-2/5 flex flex-col h-[50vh] md:h-full bg-white relative">
            <div class="p-4 font-bold border-b text-center hidden md:block" id="postCommentHeader">Comments</div>
            <!-- Scrollable Comments -->
            <div id="commentList" class="flex-1 overflow-y-auto p-4 space-y-4 pb-20 md:pb-4">
                <!-- Comments Injected Here -->
            </div>
            <!-- Sticky Input -->
            <div class="p-3 border-t bg-white absolute bottom-0 w-full flex gap-2 border-t-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <input type="text" id="commentInput" placeholder="Add a comment..." class="flex-1 px-4 py-2 bg-gray-100 rounded-full outline-none focus:ring-1 focus:ring-gray-300" onkeypress="if(event.key === 'Enter') submitComment()">
                <button onclick="submitComment()" class="text-blue-500 font-bold px-3">Post</button>
            </div>
        </div>
    </div>
</div>

<!-- 
    3. EXTERNAL MODAL COMPONENTS
    Includes the Unified Media Viewer, the Edit Profile form, and the Friends List overlay.
-->
<div id="editProfileModal" class="fixed inset-0 z-[102] bg-black/60 flex items-end md:items-center justify-center hidden" onclick="if(event.target===this) closeEditProfileModal()">
    <div class="bg-white w-full max-w-md rounded-t-2xl md:rounded-xl flex flex-col max-h-[90vh] overflow-hidden relative">
        <div class="p-4 text-center font-bold border-b relative">
            Edit Profile
            <span class="absolute right-4 top-4 cursor-pointer text-xl" onclick="closeEditProfileModal()">&times;</span>
        </div>
        <form method="POST" action="/profile/update" class="flex-1 overflow-y-auto p-4 space-y-4" enctype="multipart/form-data">
            <div>
                <label class="block text-sm font-semibold mb-2">Username</label>
                <input type="text" name="username" value="<?= esc($user['username']) ?>" class="w-full px-3 py-2 border rounded-lg outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Bio</label>
                <textarea name="bio" rows="4" class="w-full px-3 py-2 border rounded-lg outline-none focus:ring-1 focus:ring-blue-500"><?= esc($user['bio']) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Profile Picture</label>
                <input type="file" name="profile_pic" accept="image/*" class="w-full">
            </div>
            <div class="flex gap-2 pt-4">
                <button type="button" onclick="closeEditProfileModal()" class="flex-1 px-4 py-2 border rounded-lg font-semibold hover:bg-gray-50">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600">Save</button>
            </div>
        </form>
    </div>
</div>
<!-- 1D.FOOTER -->
<footer class="w-full bg-white border-t border-gray-200 mt-12 py-12">
    <div class="max-w-4xl mx-auto px-6">
        <!-- Links Grid -->
        <div class="flex flex-wrap justify-center gap-x-8 gap-y-4 text-[13px] text-gray-500 font-medium transition-colors">
            <a href="#" class="hover:text-gray-900">About</a>
            <a href="#" class="hover:text-gray-900">Help Center</a>
            <a href="#" class="hover:text-gray-900">Privacy Policy</a>
            <a href="#" class="hover:text-gray-900">Terms of Service</a>
            <a href="#" class="hover:text-gray-900">Cookie Settings</a>
            <a href="#" class="hover:text-gray-900">Community Guidelines</a>
            <a href="#" class="hover:text-gray-900">Advertising</a>
            <a href="#" class="hover:text-gray-900">Careers</a>
        </div>

        <div class="mt-10 flex flex-col md:flex-row items-center justify-between border-t border-gray-100 pt-8 gap-4">
            <!-- Branding -->
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500" style="font-family: inherit;">Unyun</h1>
                <span class="text-gray-300">|</span>
                <span class="text-sm text-gray-500"> 2026</span>
            </div>

        </div>
    </div>
</footer>

<script>
/**
 * 4. JAVASCRIPT: INITIALIZATION OVERRIDES
 * Force-hides modals.
 */
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Reset all modals to hidden state
    const modals = ['postModal', 'friendsModal', 'editProfileModal'];
    modals.forEach(id => {
        let el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
        }
    });
});

function openEditProfileModal() {
    // Opens the in-page editor for the authenticated user's own profile.
    document.getElementById('editProfileModal').classList.remove('hidden');
}

function closeEditProfileModal() {
    // Hides the editor without submitting changes.
    document.getElementById('editProfileModal').classList.add('hidden');
}

let currentPostId = null;
let currentViewedUserId = null;
let isOwnProfile = false;

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return Math.floor(seconds) + 's ago';
    
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + (minutes > 1 ? 'min ago' : 'min ago');
    
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + (hours > 1 ? 'hrs ago' : 'hr ago');
    
    const days = Math.floor(hours / 24);
    if (days < 7) return days + (days > 1 ? 'days ago' : 'day ago');
    
    const weeks = Math.floor(days / 7);
    if (weeks < 6) return weeks + (weeks > 1 ? 'weeks ago' : 'week ago');
    
    const months = Math.floor(days / 30);
    if (months < 12) return months + (months > 1 ? 'months ago' : 'month ago');
    
    const years = Math.floor(months / 12);
    return years + (years > 1 ? 'years ago' : 'year ago');
}

/**
 * 5. JAVASCRIPT: THE UNIFIED MODAL ENGINE (Cloned from feed.php)
 * Repurposes the exact same code to allow full immersion from the Grid View.
 */
function showUnifiedModal(postId, withMedia) {
    // Rebuilds a clicked grid post inside the modal and then fetches comments for that post.
    currentPostId = postId;
    
    let modal = document.getElementById('postModal');
    if (!modal) return;
    
    let mediaContainer = document.getElementById('post-media-' + postId);
    if (!mediaContainer) return;
    
    // Get post data
    let username = mediaContainer.dataset.username;
    let profilePic = mediaContainer.dataset.profilePic;
    let caption = mediaContainer.dataset.caption;
    let createdAt = mediaContainer.dataset.createdAt;
    
    let innerModal = document.getElementById('postInnerContainer');
    let commentArea = document.getElementById('postCommentArea');
    let header = document.getElementById('postCommentHeader');
    let postModalMedia = document.getElementById('postModalMedia');
    let postMediaContent = document.getElementById('postMediaContent');
    let postCaption = document.getElementById('postCaption');
    
    // Null check all modal elements
    if (!innerModal || !commentArea || !postMediaContent || !postCaption || !postModalMedia || !header) {
        console.error('Modal elements not found');
        return;
    }
    
    // Update header with user info
    document.getElementById('postHeaderUsername').textContent = username;
    document.getElementById('postHeaderProfilePic').src = profilePic ? '/uploads/profile_pics/' + profilePic : 'https://via.placeholder.com/40';
    
    // Format timestamp
    let date = new Date(createdAt);
    let timeStr = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    document.getElementById('postHeaderTime').textContent = timeStr;
    
    // Update caption
    postCaption.textContent = caption;
    
    if (withMedia) {
        // FULL IMMERSIVE VIEW
        postModalMedia.classList.remove('hidden');
        postModalMedia.classList.add('md:flex', 'flex-col');
        
        innerModal.className = "bg-white w-full h-[100dvh] md:h-[90vh] md:max-w-5xl md:rounded-xl flex flex-col md:flex-row overflow-hidden relative transition-all";
        commentArea.className = "w-full md:w-2/5 flex flex-col h-[50vh] md:h-full bg-white relative";
        header.classList.add('hidden', 'md:block');
        header.classList.remove('block');
        
        postMediaContent.innerHTML = mediaContainer.innerHTML;
        
        let mediaEl = postMediaContent.querySelector('img, video');
        if(mediaEl) {
            mediaEl.className = mediaEl.tagName === 'VIDEO' ? "w-full h-full object-contain bg-black" : "w-full h-full object-contain bg-black";
            if(mediaEl.tagName === 'VIDEO') {
                mediaEl.controls = true;
                mediaEl.muted = false;
                mediaEl.currentTime = 0;
                mediaEl.play().catch(e=>console.error("Video play failed:", e));
            }
        }
        let audioEl = postMediaContent.querySelector('.feed-audio');
        if(audioEl) audioEl.play().catch(e=>{});
        
    } else {
        // COMMENT POPUP ONLY
        postModalMedia.classList.add('hidden');
        postModalMedia.classList.remove('md:flex', 'flex-col');
        
        innerModal.className = "bg-white w-full max-w-md h-[75vh] md:max-h-[80vh] rounded-t-2xl md:rounded-xl flex flex-col overflow-hidden relative mx-auto transition-all animate-[slideUp_0.3s_ease-out]";
        commentArea.className = "w-full flex-1 flex flex-col bg-white relative";
        header.classList.remove('hidden', 'md:block');
        header.classList.add('block');
    }

    modal.classList.remove('hidden');
    
    let commentList = document.getElementById('commentList');
    if (commentList) {
        commentList.innerHTML = '<div class="text-center text-gray-500 mt-4">Loading comments...</div>';
    }
    
    // Fetch Comments
    fetch('/api/comments/' + postId)
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                let html = res.comments.map(c => `
                    <div class="flex items-start gap-3 mb-4">
                        <img src="${c.profile_pic ? '/uploads/profile_pics/'+c.profile_pic : 'https://via.placeholder.com/40'}" class="w-8 h-8 rounded-full object-cover">
                        <div class="text-sm flex-1">
                            <div class="flex items-center gap-2">
                                <b>${c.username}</b>
                                <span class="text-xs text-gray-400">${formatRelativeTime(c.created_at)}</span>
                            </div>
                            <p>${c.comment}</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <button onclick="toggleCommentLike(${c.id}, this)" class="active:scale-90 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ${c.has_liked ? 'text-red-500 fill-current' : 'text-gray-400 fill-none'}"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                            </button>
                            ${c.like_count > 0 ? `<span class="text-xs text-gray-500">${c.like_count}</span>` : `<span class="text-xs text-gray-500"></span>`}
                        </div>
                    </div>
                `).join('');
                let commentListEl = document.getElementById('commentList');
                if (commentListEl) {
                    commentListEl.innerHTML = html || '<div class="text-center text-gray-500 mt-4">No comments yet.</div>';
                }
            }
        })
        .catch(err => {
            console.error('Error fetching comments:', err);
            let commentListEl = document.getElementById('commentList');
            if (commentListEl) {
                commentListEl.innerHTML = '<div class="text-center text-gray-500 mt-4">Failed to load comments</div>';
            }
        });
}

function openPostView(postId) { 
    // Convenience wrapper used by profile grid thumbnails.
    showUnifiedModal(postId, true); 
}

function closeModal(id) { 
    let modal = document.getElementById(id);
    if (!modal) return;
    
    modal.classList.add('hidden'); 
    
    // If closing post modal, stop cloned media
    if (id === 'postModal') {
        let postMediaContent = document.getElementById('postMediaContent');
        if (postMediaContent) {
            let v = postMediaContent.querySelector('video');
            if(v) {
                v.pause();
                v.currentTime = 0;
            }
            let a = postMediaContent.querySelector('audio');
            if(a) {
                a.pause();
                a.currentTime = 0;
            }
            postMediaContent.innerHTML = '';
        }
    }
}

function submitComment() {
    // Posts a new comment through Comment::store and prepends the returned payload to the modal list.
    let val = document.getElementById('commentInput').value.trim();
    if(!val || !currentPostId) return;

    let fd = new FormData();
    fd.append('post_id', currentPostId);
    fd.append('comment', val);

    document.getElementById('commentInput').value = '';

    fetch('/comment/store', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                let list = document.getElementById('commentList');
                if(list.innerHTML.includes('No comments yet.')) list.innerHTML = '';
                
                let pic = res.comment.profile_pic ? '/uploads/profile_pics/'+res.comment.profile_pic : 'https://via.placeholder.com/40';
                let div = document.createElement('div');
                div.className = 'flex items-start gap-3 mb-4';
                div.innerHTML = `
                    <img src="${pic}" class="w-8 h-8 rounded-full object-cover">
                    <div class="text-sm flex-1"><b>${res.comment.username}</b> ${res.comment.comment}</div>
                    <div class="flex flex-col items-center">
                        <button onclick="toggleCommentLike(${res.comment.id}, this)" class="active:scale-90 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400 fill-none"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                        </button>
                        <span class="text-xs text-gray-500"></span>
                    </div>`;
                list.insertBefore(div, list.firstChild);
            }
        });
}

function toggleCommentLike(commentId, btnElement) {
    // Toggles a comment like and updates the inline heart/count without refreshing the modal.
    let fd = new FormData();
    fd.append('comment_id', commentId);
    
    fetch('/comment/like/toggle', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                let svg = btnElement.querySelector('svg');
                let countSpan = btnElement.nextElementSibling;
                
                if (res.liked) {
                    svg.classList.add('text-red-500', 'fill-current');
                    svg.classList.remove('text-gray-400', 'fill-none');
                } else {
                    svg.classList.remove('text-red-500', 'fill-current');
                    svg.classList.add('text-gray-400', 'fill-none');
                }
                countSpan.innerText = res.like_count > 0 ? res.like_count : '';
            }
        });
}

/**
 * 6. JAVASCRIPT: FRIEND LIST API
 * Asynchronously pulls the Friendship network for the selected profile.
 * Contains dynamic logic to render 'Remove' buttons ONLY if viewing your own list.
 */
function openFriendsModal(userId, isOwn) {
    // Fetches the selected user's accepted friendships and renders them into the friends modal.
    currentViewedUserId = userId;
    isOwnProfile = isOwn;
    
    document.getElementById('friendsModal').classList.remove('hidden');
    document.getElementById('friendsList').innerHTML = '<div class="text-center text-gray-500 mt-4">Loading friends...</div>';
    
    fetch('/api/friends/' + userId)
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                if (res.friends.length === 0) {
                    document.getElementById('friendsList').innerHTML = '<div class="text-center text-gray-500 mt-4">No friends yet</div>';
                } else {
                    let html = res.friends.map(f => `
                        <div class="flex items-center justify-between mb-4 pb-4 border-b" data-friendship-id="${f.friendship_id}">
                            <div class="flex items-center gap-3">
                                <a href="/profile/${f.id}" onclick="closeFriendsModal()">
                                    <img src="${f.profile_pic ? '/uploads/profile_pics/'+f.profile_pic : 'https://via.placeholder.com/40'}" class="w-10 h-10 rounded-full object-cover cursor-pointer hover:opacity-80">
                                </a>
                                <a href="/profile/${f.id}" onclick="closeFriendsModal()" class="font-semibold hover:underline">${f.username}</a>
                            </div>
                            ${isOwnProfile ? `<button onclick="removeFriend(${f.friendship_id}, this)" class="text-red-500 text-sm font-bold hover:text-red-700">Remove</button>` : ''}
                        </div>
                    `).join('');
                    document.getElementById('friendsList').innerHTML = html;
                }
            }
        })
        .catch(err => {
            console.error('Error fetching friends:', err);
            document.getElementById('friendsList').innerHTML = '<div class="text-center text-gray-500 mt-4">Failed to load friends</div>';
        });
}

function closeFriendsModal() {
    // Closes the friends overlay without changing any relationship state.
    document.getElementById('friendsModal').classList.add('hidden');
}

function removeFriend(friendshipId, btnElement) {
    // Calls the secured API remover, then fades out the row from the active friends modal.
    if (!confirm('Remove this friend?')) return;
    
    let fd = new FormData();
    fd.append('friendship_id', friendshipId);
    
    fetch('/api/friend/remove', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                // Remove the friend from the list
                btnElement.closest('div').remove();
                
                // Update friend count if no friends left
                let remaining = document.getElementById('friendsList').querySelectorAll('[data-friendship-id]').length;
                if (remaining === 0) {
                    document.getElementById('friendsList').innerHTML = '<div class="text-center text-gray-500 mt-4">No friends yet</div>';
                }
            }
        })
        .catch(err => console.error('Error removing friend:', err));
}
</script>

<?= $this->endSection() ?>
