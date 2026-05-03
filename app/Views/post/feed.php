<h2>Feed</h2>

<!-- 
    1. LEGACY POST FEED
    Simple fallback feed view kept for early PostController experiments.
    The primary production timeline is app/Views/feed.php.
-->
<a href="/post/create">Create Post</a>
<a href="/logout">Logout</a>

<hr>

<?php foreach($posts as $post): ?>

    <!-- Each post renders only the uploaded image and caption in this stripped-down fallback. -->
    <div style="margin-bottom:20px;">
        
        <img src="/uploads/posts/<?= $post['image'] ?>" width="300"><br>

        <p><?= esc($post['caption']) ?></p>

    </div>

<?php endforeach; ?>
