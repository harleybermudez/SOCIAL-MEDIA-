<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-lg mx-auto py-2 md:py-6">
    <!-- 
        1. MAIN FEED LOOP
        Iterates over the $posts array supplied by PostController.
        Each post is wrapped in a clickable card that triggers the Unified Modal when tapped.
    -->
    <?php foreach ($posts as $post): ?>
        <!-- 
            1A. FEED POST CARD
            Carries all media metadata needed by JavaScript to rebuild the same post inside the modal.
        -->
        <div class="bg-white border-b md:border md:rounded-lg mb-6 shadow-sm cursor-pointer hover:bg-gray-50 transition" onclick="openPostView(<?= $post['id'] ?>)">
            <!-- Header -->
            <div class="flex items-center p-4">
                <a href="/profile/<?= $post['user_id'] ?>" onclick="event.stopPropagation()">
                    <img src="<?= $post['profile_pic'] ? '/uploads/profile_pics/'.$post['profile_pic'] : 'https://via.placeholder.com/40' ?>" class="w-10 h-10 rounded-full object-cover mr-3 border">
                </a>
                <div class="flex-1">
                    <a href="/profile/<?= $post['user_id'] ?>" class="font-semibold text-sm hover:underline" onclick="event.stopPropagation()">
                        <?= esc($post['username']) ?>
                    </a>
                    <p class="text-gray-500 text-xs" data-time="<?= $post['created_at'] ?? date('Y-m-d H:i:s') ?>"></p>
                </div>
            </div>

            <!-- Media -->
            <div id="post-media-<?= $post['id'] ?>" class="group relative" data-username="<?= esc($post['username']) ?>" data-profile-pic="<?= $post['profile_pic'] ?>" data-caption="<?= esc($post['caption']) ?>" data-created-at="<?= $post['created_at'] ?? date('Y-m-d H:i:s') ?>">
            <?php if ($post['image']): ?>
                <?php 
                /**
                 * 1B. MEDIA TYPE SWITCH
                 * Videos render as playable muted elements; images may include a hidden audio track.
                 */
                ?>
                <?php $ext = pathinfo($post['image'], PATHINFO_EXTENSION); ?>
                <?php if (in_array(strtolower($ext), ['mp4', 'webm', 'mov'])): ?>
                    <div class="relative">
                        <video src="/uploads/posts/<?= $post['image'] ?>?v=<?= $post['id'] ?>" class="w-full max-h-[600px] object-cover bg-black aspect-[4/5] feed-video" loop muted playsinline onclick="event.stopPropagation(); this.paused?this.play():this.pause()"></video>
                        <div onclick="event.stopPropagation(); toggleFeedMute(this)" class="absolute bottom-3 left-3 bg-black/30 text-white px-3 py-1 cursor-pointer hover:bg-black/50 rounded-full text-xs font-bold flex items-center gap-2 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mute-icon">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                            </svg>
                            <span class="audio-label">Unmute</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="relative">
                        <img src="/uploads/posts/<?= $post['image'] ?>" class="w-full object-cover bg-gray-100">
                        <?php if(!empty($post['music'])): ?>
                            <audio src="/uploads/music/<?= $post['music'] ?>" class="feed-audio hidden" loop muted playsinline preload="auto"></audio>
                            <div onclick="event.stopPropagation(); toggleFeedMute(this)" class="absolute bottom-3 left-3 bg-black/30 text-white px-3 py-1 cursor-pointer hover:bg-black/50 rounded-full text-xs font-bold flex items-center gap-2 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mute-icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                                </svg>
                                <span class="audio-label">Unmute</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            </div>

            <!-- 
                1C. POST ACTIONS
                Buttons call lightweight JSON endpoints for likes, comments, and repost sharing.
            -->
            <div class="p-4">
                <div class="flex gap-4 mb-2">
                    <button id="like-btn-<?= $post['id'] ?>" onclick="event.stopPropagation(); toggleLike(<?= $post['id'] ?>)" class="hover:text-gray-500 transition-transform active:scale-90 <?= $post['has_liked'] ? 'text-red-500' : '' ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 <?= $post['has_liked'] ? 'fill-current' : 'fill-none' ?>"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    </button>
                    <button onclick="event.stopPropagation(); openComments(<?= $post['id'] ?>)" class="hover:text-gray-500 transition-transform active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" /></svg>
                    </button>
                </div>

                <div class="font-semibold text-sm mb-1">
                    <span id="like-count-<?= $post['id'] ?>"><?= $post['like_count'] ?></span> likes
                </div>

                <div class="text-sm">
                    <span class="font-semibold mr-1"><?= esc($post['username']) ?></span>
                    <?= esc($post['caption']) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
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
<!-- 
    2. UNIFIED POST / COMMENT MODAL 
    A single robust HTML modal used for BOTH "Viewing a full image/video" AND "Reading comments on a post".
    Javascript toggles CSS classes ('hidden', 'flex-col', 'w-full') dynamically to reshape this container
    depending on which button the user clicked to trigger it.
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

<script>
/**
 * 3. JAVASCRIPT: INITIALIZATION OVERRIDES
 * Force-hides modals if browser caching accidentally paints them on a back-button press.
 */
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Reset all modals to hidden state
    const modals = ['postModal'];
    modals.forEach(id => {
        let el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
        }
    });
});

let currentPostId = null;

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
 * 4. JAVASCRIPT: THE UNIFIED MODAL ENGINE
 * The most complex frontend script. Reshapes the modal and performs an asynchronous Fetch to get 
 * all comments and their specific like-counts without ever reloading the browser.
 * 
 * @param {integer} postId ID of the target database row 
 * @param {boolean} withMedia True = show media. False = strictly show comment panel block.
 */
function showUnifiedModal(postId, withMedia) {
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
        // Pause all background feed videos
        document.querySelectorAll('.feed-video, .feed-audio').forEach(m => {
            m.pause();
            m.currentTime = 0;
        });
        
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
                mediaEl.muted = false; // Unmute video in modal so user can hear sound
                mediaEl.play().catch(e=>{});
            }
        }
        let audioEl = postMediaContent.querySelector('.feed-audio');
        if(audioEl) {
            audioEl.muted = false; // Unmute audio in modal
            audioEl.play().catch(e=>{});
        }
        
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

function openPostView(postId) { showUnifiedModal(postId, true); }
function openComments(postId) { showUnifiedModal(postId, false); }

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
        
        // Let IntersectionObserver handle which videos should play based on visibility
        // Just reset muted state to global setting for videos that are in view
        document.querySelectorAll('.feed-video, .feed-audio').forEach(m => {
            m.muted = isGlobalMuted;
        });
    }
}

/**
 * 5. JAVASCRIPT: LIKE TOGGLING API
 * Uses FormData to post an asynchronous request to LikeController.
 * On success, dynamically swaps the SVG heart fill using Tailwind classes.
 */
function toggleLike(postId) {
    let fd = new FormData();
    fd.append('post_id', postId);
    fetch('/like/toggle', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                document.getElementById('like-count-'+postId).innerText = res.like_count;
                let btn = document.getElementById('like-btn-'+postId);
                if(btn) {
                    let svg = btn.querySelector('svg');
                    if (res.liked) {
                        btn.classList.add('text-red-500');
                        svg.classList.remove('fill-none');
                        svg.classList.add('fill-current');
                    } else {
                        btn.classList.remove('text-red-500');
                        svg.classList.remove('fill-current');
                        svg.classList.add('fill-none');
                    }
                }
            }
        });
}



function submitComment() {
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

// 6. JAVASCRIPT: GLOBAL AUDIO STATE & INTERSECTION OBSERVERS
// AUTO-PLAY VIDEOS ON SCROLL (INTERSECTION OBSERVER) & GLOBAL MUTE
let isGlobalMuted = true;

/**
 * Global audio toggler. One click unmutes ALL media on the current timeline 
 * so the user doesn't have to keep pressing unmute on every subsequent video.
 */
function applyFeedMuteState() {
    // Keep all feed media aligned to the global mute choice without forcing playback.
    document.querySelectorAll('.feed-video, .feed-audio').forEach(m => m.muted = isGlobalMuted);

    document.querySelectorAll('.audio-label').forEach(lbl => {
        lbl.innerText = isGlobalMuted ? 'Unmute' : 'Mute';
    });

    document.querySelectorAll('.mute-icon').forEach(icon => {
        if (isGlobalMuted) {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />';
        } else {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />';
        }
    });
}

function toggleFeedMute(btnEl) {
    // Only toggles sound permission. IntersectionObserver still controls play/pause by viewport.
    isGlobalMuted = !isGlobalMuted;
    applyFeedMuteState();
}

document.addEventListener("DOMContentLoaded", function() {
    let videos = document.querySelectorAll('.feed-video, .feed-audio'); // Target both videos AND photo music tracks
    applyFeedMuteState();
    
    // Sync mute state when any video's volume changes (e.g. user clicks the native speaker icon)
    videos.forEach(video => {
        video.addEventListener('volumechange', (e) => {
            return;
            if(video.classList.contains('feed-audio')) return; // Ignore event from audio tags being automated
            
            isGlobalMuted = e.target.muted;
            videos.forEach(v => {
                if (v !== e.target) {
                    v.muted = isGlobalMuted;
                }
            });
            // Update banners if video native controls change mute state
            document.querySelectorAll('.audio-label').forEach(lbl => {
                lbl.innerText = isGlobalMuted ? 'Unmute Audio' : 'Audio Playing 🔊';
            });
        });
    });

    /**
     * DOM INTERSECTION OBSERVER CORE ALGORITHM
     * As the user scrolls vertically, this Observer watches every media container.
     * When 60% of a container breaches the visible viewport (threshold 0.6), it fires `.play()`.
     * When the container leaves the screen, it meticulously fires `.pause()` to conserve RAM and CPU.
     */
    let observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            let media = entry.target;
            if (entry.isIntersecting) {
                media.muted = isGlobalMuted; // Apply global state before playing
                let playPromise = media.play();
                if (playPromise !== undefined) {
                    playPromise.catch(e => {
                        // If browser blocks unmuted autoplay, fallback to muted
                        isGlobalMuted = true;
                        applyFeedMuteState();
                        media.play().catch(err => console.log('Autoplay blocked completely'));
                    });
                }
            } else {
                media.pause();
                // Optional: rewind photo music slightly so it feels fresh next scroll
                if(media.classList.contains('feed-audio')) media.currentTime = 0; 
            }
        });
    }, {
        threshold: 0.6 // Play when 60% of the media is visible
    });

    videos.forEach(media => {
        // For audio tags within divs, observe their parent container image wrapper
        if (media.classList.contains('feed-audio')) {
            observer.observe(media.parentElement);
            // small hack to map the intersection entry target back to the audio tag
            media.parentElement.play = () => media.play();
            media.parentElement.pause = () => media.pause();
            Object.defineProperty(media.parentElement, 'muted', {
                get: () => media.muted,
                set: (val) => media.muted = val
            });
        } else {
            observer.observe(media);
        }
    });
});

// Initialize post timestamps
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('p[data-time]').forEach(el => {
        el.textContent = formatRelativeTime(el.dataset.time);
    });
});


document.querySelectorAll('a[href^="/profile/"]').forEach(link => {
    link.addEventListener('mouseenter', () => {
        // Prefetch the page HTML when the user hovers over the username
        const prefetchLink = document.createElement('link');
        prefetchLink.href = link.href;
        prefetchLink.rel = 'prefetch';
        document.head.appendChild(prefetchLink);
    });
});
</script>
<?= $this->endSection() ?>
