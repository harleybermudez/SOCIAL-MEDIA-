<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto py-4 md:py-8 px-4">
    <!-- 
        1. ACCEPTED FRIENDS LIST
        Displays the current user's accepted relationships and posts removal forms back to Friend::remove.
    -->
    <div class="bg-white md:border md:rounded-lg overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h1 class="text-xl font-bold">Friends</h1>
            <p class="text-sm text-gray-500">People you are connected with.</p>
        </div>

        <?php if (empty($friends)): ?>
            <div class="p-8 text-center text-gray-500">No friends yet.</div>
        <?php endif; ?>

        <?php foreach ($friends as $friend): ?>
            <div class="flex items-center justify-between p-4 border-b border-gray-100 last:border-b-0">
                <a href="/profile/<?= $friend['id'] ?>" class="flex items-center min-w-0">
                    <img src="<?= $friend['profile_pic'] ? '/uploads/profile_pics/'.$friend['profile_pic'] : 'https://via.placeholder.com/48' ?>" class="w-12 h-12 rounded-full object-cover mr-3 border">
                    <span class="font-semibold truncate"><?= esc($friend['username']) ?></span>
                </a>

                <form method="post" action="/friend/remove">
                    <input type="hidden" name="id" value="<?= $friend['friendship_id'] ?>">
                    <button type="submit" class="bg-gray-100 text-red-500 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 
        2. INCOMING FRIEND REQUESTS
        Shows pending requests where the active user is the receiver, with accept/delete form actions.
    -->
    <div class="bg-white md:border md:rounded-lg overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-xl font-bold">Friend Requests</h2>
            <p class="text-sm text-gray-500">New requests waiting for your response.</p>
        </div>

        <?php if (empty($requests)): ?>
            <div class="p-8 text-center text-gray-500">No new friend requests.</div>
        <?php endif; ?>

        <?php foreach ($requests as $request): ?>
            <div class="flex items-center justify-between p-4 border-b border-gray-100 last:border-b-0 gap-3">
                <a href="/profile/<?= $request['sender_id'] ?>" class="flex items-center min-w-0">
                    <img src="<?= $request['profile_pic'] ? '/uploads/profile_pics/'.$request['profile_pic'] : 'https://via.placeholder.com/48' ?>" class="w-12 h-12 rounded-full object-cover mr-3 border">
                    <div class="min-w-0">
                        <div class="font-semibold truncate"><?= esc($request['username']) ?></div>
                        <div class="text-xs text-gray-500"><?= date('M j, g:i a', strtotime($request['created_at'])) ?></div>
                    </div>
                </a>

                <div class="flex gap-2 shrink-0">
                    <form method="post" action="/friend/accept">
                        <input type="hidden" name="id" value="<?= $request['friendship_id'] ?>">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-600">Accept</button>
                    </form>
                    <form method="post" action="/friend/reject">
                        <input type="hidden" name="id" value="<?= $request['friendship_id'] ?>">
                        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 1D.FOOTER -->
<footer class="w-full bg-white border-t border-gray-200 mt-12 py-12">
    <div class="max-w-4xl mx-auto px-6">
        <!-- Links Grid -->
        <div class="flex flex-wrap justify-center gap-x-8 gap-y-4 text-[13px] text-gray-500 font-medium transition-colors">
            <a href="#" class="hover:text-gray-900">Tallayo, Princess Mae</a>
            <a href="#" class="hover:text-gray-900">Bermudez, John Harley</a>
            <a href="#" class="hover:text-gray-900">Acosta, Rjay Luis</a>
            <a href="#" class="hover:text-gray-900">Batao-ey, Dale</a>
            <a href="#" class="hover:text-gray-900">Lasan, Steve</a>
        </div>

        <div class="mt-10 flex flex-col md:flex-row items-center justify-between border-t border-gray-100 pt-8 gap-4">
            <!-- Branding -->
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-500 to-pink-500" style="font-family: inherit;">Unyun</h1>
                <span class="text-gray-300">|</span>
                <span class="text-sm text-gray-500"> MCO-Finals Web Development 2 </span>
                <span class="text-gray-300">2026</span>
            </div>

        </div>
    </div>
</footer>
<?= $this->endSection() ?>
