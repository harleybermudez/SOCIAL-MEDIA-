<h2>Edit Profile</h2>

<!-- 
    1. PROFILE UPDATE FORM
    Posts basic account text fields and an optional avatar image to Profile::update.
    This view is a lightweight fallback; the main profile screen opens a richer modal-style editor.
-->
<form method="post" action="/profile/update" enctype="multipart/form-data">

    <input type="text" name="username" value="<?= esc($user['username']) ?>"><br><br>

    <textarea name="bio"><?= esc($user['bio']) ?></textarea><br><br>

    <!-- Existing avatar filename is preserved if the user does not upload a replacement. -->
    <input type="hidden" name="old_pic" value="<?= $user['profile_pic'] ?>">

    <input type="file" name="profile_pic"><br><br>

    <button type="submit">Save</button>

</form>
