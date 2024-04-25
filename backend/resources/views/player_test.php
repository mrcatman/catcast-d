<script src="https://cdn.bootcss.com/flv.js/1.5.0/flv.min.js"></script>
<video id="videoElement" controls></video>
<script>
    if (flvjs.isSupported()) {
        var videoElement = document.getElementById('videoElement');
        var flvPlayer = flvjs.createPlayer({
            type: 'flv',
            url: 'http://185.17.120.181:8000/live/kdtv_ch.flv'
        });
        flvPlayer.attachMediaElement(videoElement);
        flvPlayer.load();
    }
</script>