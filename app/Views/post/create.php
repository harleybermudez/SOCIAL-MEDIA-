<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-md mx-auto p-6 bg-white md:mt-8 md:border md:rounded-lg">
    <h2 class="text-2xl font-bold mb-6 text-center">Create New Post</h2>
    
    <?php if(session()->getFlashdata('error')): ?>
        <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-center text-sm font-bold shadow-sm">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- 
        1. MEDIA UPLOAD FORM
        Forces multipart/form-data specifically to handle 30MB+ raw video/image ingestions.
        Submits directly to PostController::store.
    -->
    <form method="post" action="/post/store" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block mb-2 font-medium text-gray-700">Select Media</label>
            <input type="file" name="image" id="mediaUpload" accept="image/*,video/mp4,video/webm" required class="w-full p-2 border border-gray-300 bg-gray-50 rounded-lg cursor-pointer">
        </div>

        <div id="musicContainer" class="hidden">
            <label class="block mb-2 font-medium text-gray-700">Add Music 🎵 <span class="text-xs font-normal text-gray-500">(Optional, Max 30s)</span></label>
            <input type="file" name="music" id="musicUpload" accept="audio/mpeg,audio/mp3,audio/wav" class="w-full p-2 border border-blue-300 bg-blue-50 rounded-lg cursor-pointer">
        </div>

        <div>
            <label class="block mb-2 font-medium text-gray-700">Caption</label>
            <textarea name="caption" placeholder="Write something..." class="w-full p-3 border border-gray-300 rounded-lg h-32 resize-none outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="flex items-center justify-between border-t border-b py-3">
            <span class="font-medium text-gray-700">Share as Quickie 🎬</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_quickie" value="1" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
            </label>
        </div>

        <button type="submit" onclick="this.innerHTML='Uploading Media... Please Wait ⏳'; this.classList.add('opacity-50'); this.disabled=true; this.form.submit();" class="w-full bg-blue-500 text-white font-bold py-3 rounded-lg hover:bg-blue-600 transition">Share Post</button>
    </form>
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

<script>
/**
 * 2. CONDITIONAL MUSIC UPLOADER UI
 * Listens for file selections. If the file is an Image, it un-hides the "Add Music" panel.
 * If the user uploads a video instead, music is forced hidden because videos have native sound.
 */
document.getElementById('mediaUpload').addEventListener('change', function(e) {
    let file = e.target.files[0];
    let musicContainer = document.getElementById('musicContainer');
    let musicUpload = document.getElementById('musicUpload');
    
    if (file && file.type.startsWith('image/')) {
        musicContainer.classList.remove('hidden');
    } else {
        musicContainer.classList.add('hidden');
        musicUpload.value = ''; // clear any selected music
    }
});

document.getElementById('musicUpload').addEventListener('change', async function(e) {
    let file = e.target.files[0];
    if (!file) return;

    let hintLabel = this.previousElementSibling.querySelector('span');
    
    let audio = new Audio();
    audio.src = URL.createObjectURL(file);
    audio.onloadedmetadata = async function() {
        if (audio.duration > 31) {
            hintLabel.innerText = "(Trimming to 30s... Please wait ⏳)";
            hintLabel.classList.add('text-orange-500');
            this.disabled = true; // Disable input while processing
            
            try {
                let trimmedFile = await trimAudioFile(file, 30);
                
                // Replace the physical input file with the trimmed buffer!
                let dt = new DataTransfer();
                dt.items.add(trimmedFile);
                e.target.files = dt.files;
                
                // Reset UI
                hintLabel.innerText = "(Trimmed down perfectly! ✅)";
                hintLabel.className = "text-xs font-bold text-green-500";
            } catch (err) {
                console.error(err);
                alert("Failed to auto-trim audio. Please upload a file under 30 seconds.");
                e.target.value = '';
                hintLabel.innerText = "(Optional, Max 30s)";
            }
            e.target.disabled = false;
        } else {
            hintLabel.innerText = "(Audio looks good! ✅)";
            hintLabel.className = "text-xs font-bold text-green-500";
        }
    }.bind(this);
});

/**
 * 3. ZERO SERVER AUDIO PROCESSING MAGIC!
 * Completely circumvents PHP / FFMPEG limitations by natively trimming the audio 
 * inside the browser BEFORE it is converted to a packet and sent to the server.
 * Uses the navigator AudioContext API to rip the buffer and strip any frames past 30 seconds.
 */
async function trimAudioFile(file, maxDurationSec) {
    const arrayBuffer = await file.arrayBuffer();
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const audioBuffer = await audioContext.decodeAudioData(arrayBuffer);

    const offlineCtx = new OfflineAudioContext(
        audioBuffer.numberOfChannels,
        audioContext.sampleRate * maxDurationSec,
        audioContext.sampleRate
    );

    const source = offlineCtx.createBufferSource();
    source.buffer = audioBuffer;
    source.connect(offlineCtx.destination);
    source.start(0);

    const renderedBuffer = await offlineCtx.startRendering();
    const wavBlob = bufferToWave(renderedBuffer, renderedBuffer.length);
    return new File([wavBlob], file.name.split('.')[0] + "_trim.wav", { type: 'audio/wav' });
}

function bufferToWave(abuffer, len) {
  var numOfChan = abuffer.numberOfChannels,
      length = len * numOfChan * 2 + 44,
      buffer = new ArrayBuffer(length),
      view = new DataView(buffer),
      channels = [], i, sample, offset = 0, pos = 0;

  function setUint16(data) { view.setUint16(pos, data, true); pos += 2; }
  function setUint32(data) { view.setUint32(pos, data, true); pos += 4; }

  setUint32(0x46464952); setUint32(length - 8); setUint32(0x45564157); // RIFF WAVE
  setUint32(0x20746d66); setUint32(16); setUint16(1); setUint16(numOfChan); // fmt
  setUint32(abuffer.sampleRate); setUint32(abuffer.sampleRate * 2 * numOfChan);
  setUint16(numOfChan * 2); setUint16(16);
  setUint32(0x61746164); setUint32(length - pos - 4); // data

  for(i = 0; i < abuffer.numberOfChannels; i++) channels.push(abuffer.getChannelData(i));

  while(pos < length) {
    for(i = 0; i < numOfChan; i++) {
      sample = Math.max(-1, Math.min(1, channels[i][offset]));
      sample = (0.5 + sample < 0 ? sample * 32768 : sample * 32767)|0;
      view.setInt16(pos, sample, true); pos += 2;
    }
    offset++;
  }
  return new Blob([buffer], {type: "audio/wav"});
}
</script>
<?= $this->endSection() ?>