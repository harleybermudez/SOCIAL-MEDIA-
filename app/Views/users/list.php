<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-md mx-auto bg-white md:mt-8 md:border md:rounded-lg min-h-screen md:min-h-0">
    <div class="p-4 border-b font-bold text-lg text-center">All Users</div>

    <!-- 
        1. USER DIRECTORY LOOP
        Lists every account except the active session user and posts requests to Friend::request.
    -->
    <?php foreach ($users as $user): ?>
        <div class="flex items-center justify-between p-4 border-b border-gray-100 hover:bg-gray-50 transition">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                    <span class="text-gray-500">👤</span>
                </div>
                <b><?= esc($user['username']) ?></b>
            </div>

            <form method="post" action="/friend/request">
                <input type="hidden" name="receiver_id" value="<?= $user['id'] ?>">
                <button class="bg-blue-500 text-white px-4 py-1.5 rounded-lg text-sm font-bold">Add Friend</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
