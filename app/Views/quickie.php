<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- 
    1. IMMERSIVE SNAP SCROLLING VIEWPORT
    Uses Tailwind variables `snap-y snap-mandatory` to forcefully snap the screen vertically to each media container.
    Mimics native apps like Tiktok or Instagram Reels.
-->
<div class="h-[calc(100vh-60px)] md:h-screen w-full bg-black overflow-y-scroll snap-y snap-mandatory hide-scrollbar">
    
    <?php if(empty($quickies)): ?>
        <!-- 
            1A. EMPTY QUICKIE STATE
            Gives the user a direct path to create the first Quickie if no records are flagged.
        -->
        <div class="h-full flex flex-col items-center justify-center text-white">
            <div class="text-6xl mb-4">🎬</div>
            <h2 class="text-2xl font-bold">No Quickies Yet</h2>
            <p class="text-gray-400 mt-2">Be the first to upload a short video!</p>
            <a href="/post/create" class="mt-6 bg-blue-500 text-white px-6 py-2 rounded-full font-bold">Create Post</a>
        </div>
    <?php endif; ?>

    <?php foreach($quickies as $q): ?>
        <!-- 
            1B. QUICKIE SLIDE
            Each post gets a full viewport snap panel so scrolling moves one media item at a time.
        -->
        <div class="h-[calc(100vh-60px)] md:h-screen w-full snap-start relative flex justify-center items-center bg-white">
            
            <?php 
            /**
             * 1C. MEDIA TYPE DETECTION
             * Quickie supports both videos and image posts with optional music tracks.
             */
            $ext = pathinfo($q['image'], PATHINFO_EXTENSION); 
            $isVideo = in_array(strtolower($ext), ['mp4', 'webm', 'mov']);
            ?>

            <!-- Media Player -->
            <?php if ($isVideo): ?>
                    <video src="/uploads/posts/<?= $q['image'] ?>?v=<?= $q['id'] ?>" 
                        class="h-full w-full max-w-md object-contain quickie-video" 
                        loop muted playsinline 
                        onclick="this.paused ? this.play() : this.pause()"></video>
                <?php else: ?>
                    <div class="relative h-full w-full max-w-md">
                        <img src="/uploads/posts/<?= $q['image'] ?>" class="h-full w-full object-contain">
                        <?php if(!empty($q['music'])): ?>
                            <audio src="/uploads/music/<?= $q['music'] ?>" class="quickie-audio hidden" loop muted playsinline preload="auto"></audio>
                        <?php endif; ?>
                    </div>
             <?php endif; ?>

            <!-- 
                1D. QUICKIE OVERLAYS
                Holds author metadata, caption, and right-side actions above the media player.
            -->
            <div class="absolute bottom-0 w-full max-w-md left-1/2 -translate-x-1/2 p-4 flex justify-between items-end bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                
                <!-- User Info & Caption (Bottom Left) -->
                <div class="flex-1 text-white pr-10">
                    <a href="/profile/<?= $q['user_id'] ?>" class="flex items-center gap-2 mb-2 hover:opacity-80">
                        <img src="<?= $q['profile_pic'] ? '/uploads/profile_pics/'.$q['profile_pic'] : 'https://via.placeholder.com/40' ?>" class="w-10 h-10 rounded-full border border-gray-600 object-cover">
                        <span class="font-bold text-[15px]"><?= esc($q['username']) ?></span>
                        <span class="bg-transparent border border-white px-2 py-0.5 rounded-md text-xs font-semibold ml-2">Visit</span>
                    </a>
                    <p class="text-[14px] leading-tight line-clamp-2"><?= esc($q['caption']) ?></p>
                </div>

                <!-- Action Bar (Bottom Right) -->
                <div class="flex flex-col items-center gap-5 pb-2 text-white drop-shadow-md">
                    <!-- Like -->
                    <button onclick="toggleLike(<?= $q['id'] ?>)" class="flex flex-col items-center group">
                        <div id="like-btn-<?= $q['id'] ?>" class="p-2.5 bg-black/30 rounded-full group-active:scale-90 transition <?= $q['has_liked'] ? 'text-red-500' : '' ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 <?= $q['has_liked'] ? 'fill-current' : 'fill-none' ?>"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                        </div>
                        <span class="text-xs font-semibold mt-1" id="like-count-<?= $q['id'] ?>"><?= $q['like_count'] ?></span>
                    </button>

                    <!-- Comment -->
                    <button onclick="openComments(<?= $q['id'] ?>)" class="flex flex-col items-center group">
                        <div class="p-2.5 bg-black/30 rounded-full group-active:scale-90 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" /></svg>
                        </div>
                        <span class="text-xs font-semibold mt-1">Chat</span>
                    </button>

                    
                    <!-- Mute Toggle -->
                    <button onclick="toggleGlobalMute()" class="flex flex-col items-center group mt-2" id="muteToggleBtn">
                        <div class="p-2.5 bg-black/30 rounded-full group-active:scale-90 transition">
                            <!-- Speaker Icon (Muted by default) -->
                            <svg id="muteIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                            </svg>
                        </div>
                    </button>

                    <!-- Sound Icon -->
                    <div class="mt-2 w-9 h-9 border-2 border-white rounded-full overflow-hidden animate-[spin_4s_linear_infinite]">
                        <img src="<?= $q['profile_pic'] ? '/uploads/profile_pics/'.$q['profile_pic'] : 'https://via.placeholder.com/40' ?>" class="w-full h-full object-cover">
                    </div>
                </div>

            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
/* Hide scrollbar for cleanly snapping  */
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<!-- 
    2. COMMENTS DRAWER
    Slide-over panel populated by Comment::fetch and submitted through Comment::store.
-->
<div id="commentDrawer" class="fixed inset-y-0 right-0 w-full md:w-96 bg-white z-[60] transform translate-x-full transition-transform duration-300 flex flex-col shadow-2xl">
    <div class="p-4 font-bold border-b text-center relative text-black">
        Comments
        <button onclick="closeDrawer('commentDrawer')" class="absolute left-4 top-4 text-xl">&times;</button>
    </div>
    <div id="commentList" class="flex-1 overflow-y-auto p-4 space-y-4 text-black text-left">
        <!-- From JS -->
    </div>
    <div class="p-3 border-t flex gap-2 text-black">
        <input type="text" id="commentInput" placeholder="Add a comment..." class="flex-1 px-4 py-2 bg-gray-100 rounded-full outline-none focus:ring-1 focus:ring-gray-300" onkeypress="if(event.key === 'Enter') submitComment()">
        <button onclick="submitComment()" class="text-blue-500 font-bold px-2">Post</button>
    </div>
</div>


<!-- DRAWER BACKDROP -->
<div id="drawerBackdrop" class="fixed inset-0 bg-black/50 z-[50] hidden" onclick="closeAllDrawers()"></div>

<script>
/**
 * 2. SLIDING DRAWER UI ENGINE
 * Replaces the traditional center pop-up modal with slick side-panel slider animations.
 */
let currentPostId = null;

function openDrawer(id) {
    document.getElementById(id).classList.remove('translate-x-full');
    document.getElementById('drawerBackdrop').classList.remove('hidden');
}
function closeDrawer(id) {
    document.getElementById(id).classList.add('translate-x-full');
    document.getElementById('drawerBackdrop').classList.add('hidden');
}
function closeAllDrawers() {
    document.getElementById('commentDrawer').classList.add('translate-x-full');
    document.getElementById('drawerBackdrop').classList.add('hidden');
}

function toggleLike(postId) {
    // Same Like::toggle JSON workflow as the normal feed, with icon updates.
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

function openComments(postId) {
    // Store the active post ID and hydrate the drawer with the post's current comments.
    currentPostId = postId;
    openDrawer('commentDrawer');
    document.getElementById('commentList').innerHTML = '<div class="text-center text-gray-500 mt-4">Loading...</div>';
    
    fetch('/api/comments/' + postId)
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                let html = res.comments.map(c => `
                    <div class="flex items-start gap-3 mb-4">
                        <img src="${c.profile_pic ? '/uploads/profile_pics/'+c.profile_pic : 'https://via.placeholder.com/40'}" class="w-8 h-8 rounded-full object-cover">
                        <div class="text-sm flex-1">
                            <b>${c.username}</b> ${c.comment}
                        </div>
                        <div class="flex flex-col items-center">
                            <button onclick="toggleCommentLike(${c.id}, this)" class="active:scale-90 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ${c.has_liked ? 'text-red-500 fill-current' : 'text-gray-400 fill-none'}"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                            </button>
                            ${c.like_count > 0 ? `<span class="text-xs text-gray-500">${c.like_count}</span>` : `<span class="text-xs text-gray-500"></span>`}
                        </div>
                    </div>
                `).join('');
                document.getElementById('commentList').innerHTML = html || '<div class="text-center text-gray-500 mt-4">No comments yet.</div>';
            }
        });
}

function submitComment() {
    // Sends the current drawer input to Comment::store and immediately paints the returned row.
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
    // Calls Comment::toggleLike and updates the tiny heart/count beside the comment.
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

let isGlobalMuted = true;

function applyQuickieMuteState() {
    // Applies the global sound preference without starting off-screen media playback.
    let medias = document.querySelectorAll('.quickie-video, .quickie-audio');
    medias.forEach(v => v.muted = isGlobalMuted);
    
    // Update all mute icons
    document.querySelectorAll('#muteIcon').forEach(icon => {
        if (isGlobalMuted) { // Muted Icon Mute slash
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75 19.5 12m0 0 2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6 4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />';
        } else { // Unmuted Icon Volume
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />';
        }
    });
}

function toggleGlobalMute() {
    // User-controlled sound toggle. Scroll observer remains responsible for autoplay boundaries.
    isGlobalMuted = !isGlobalMuted;
    applyQuickieMuteState();
}

/**
 * 3. ADVANCED DOM INTERSECTION OBSERVER (AUTOPLAY ENGINE)
 * The most critical part of the Reels feed. 
 * As the user furiously flicks through videos, this actively calculates which single video 
 * is taking up the majority of the screen space, plays it, and brutally pauses the others to save RAM.
 */
// AUTO-PLAY QUICKIE VIDEOS ON SCROLL
document.addEventListener("DOMContentLoaded", function() {
    let medias = document.querySelectorAll('.quickie-video, .quickie-audio');
    applyQuickieMuteState();
    
    let observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            let media = entry.target;
            if (entry.isIntersecting) {
                media.muted = isGlobalMuted; // Sync global state before playing
                let playPromise = media.play();
                if (playPromise !== undefined) {
                    playPromise.catch(e => {
                        isGlobalMuted = true;
                        applyQuickieMuteState();
                        media.play().catch(err => console.log('Autoplay blocked completely'));
                    });
                }
            } else {
                media.pause();
                media.currentTime = 0; // Optional: Reset video to start when scrolling past
            }
        });
    }, {
        threshold: 0.6 // Play when 60% of the video is visible
    });

    medias.forEach(media => {
        if (media.classList.contains('quickie-audio')) {
            observer.observe(media.parentElement);
            media.parentElement.play = () => media.play();
            media.parentElement.pause = () => media.pause();
            Object.defineProperty(media.parentElement, 'muted', {
                get: () => media.muted,
                set: (val) => media.muted = val
            });
            Object.defineProperty(media.parentElement, 'currentTime', {
                get: () => media.currentTime,
                set: (val) => media.currentTime = val
            });
        } else {
            observer.observe(media);
        }
    });
});


</script>

<?= $this->endSection() ?>
