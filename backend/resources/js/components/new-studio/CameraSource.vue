<template>
  <div class="camera-source" ref="container" >
    <BaseSource :disabled="disabled" :zIndex="zIndex" :data="val" @change="onChangePosition" v-if="hasStream">
      <video class="camera-source__video" ref="video" autoplay muted></video>
    </BaseSource>
  </div>
</template>
<style lang="scss">
  .camera-source {
    width: 100%;
    height: 100%;
    &__video {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }
</style>
<script>
  import BaseSource from "./BaseSource";
  export default {
      props: ['value', 'object', 'zIndex', 'disabled', 'audio'],
      beforeDestroy() {
          if (this.mirrorElement) {
              this.mirrorElement.remove();
          }
          if (this.audio && this.source) {
              this.source.disconnect();
          }
      },
      watch: {
          zIndex(newIndex) {
              if (this.mirrorElement) {
                  this.mirrorElement.style.zIndex = newIndex;
              }
          },
          disabled(isDisabled) {
              if (this.mirrorElement) {
                  this.mirrorElement.style.opacity = isDisabled ? 0 : 1;
                  if (isDisabled) {
                      this.mirrorElement.pause();
                  } else {
                      this.mirrorElement.play();
                  }
                  console.log('mirror element', this.mirrorElement);
              }
          },
          volume(newVolume) {
              if (newVolume > 1) {
                  this.volume = newVolume / 100;
              } else {
                  if (this.$refs.video) {
                      this.$refs.video.volume = newVolume;
                  }
              }
          },
          value: {
              handler(newVal) {
                  this.val = newVal;
                  this.setCamera();
                  this.volume = newVal.volume || 1;
                  if (this.mirrorElement) {
                      this.setMirrorElementPosition();
                  }
              },
              deep: true
          }
      },
      data() {
          return {
              val: this.value,
              volume: this.value ? this.value.volume / 100 : 1,
              devices: {
                  video: [],
                  audio: [],
                  loading: true,
                  error: null,
              },
              activeDevices: {
                  video: null,
                  audio: null
              },
              hasStream: false,
              stream: null,
              updateTimeout: null,
              mirrorContainer: null,
              mirrorElement: null,
              loadingCamera: false,
              source: null
          }
      },
      methods: {
          onChangePosition(position) {
              for (let key in position) {
                  this.value[key] = position[key];
              }
          },
          setMirrorElementPosition() {
              let mirrorElement = this.mirrorElement;
              if (!mirrorElement) {
                  return;
              }
              mirrorElement.style.width = this.value.width * 100 + '%';
              mirrorElement.style.height = this.value.height * 100 + '%';
              mirrorElement.style.left = this.value.x * 100 + '%';
              mirrorElement.style.top = this.value.y * 100 + '%';
          },
          bindElement(mirrorContainer) {
              this.mirrorContainer = mirrorContainer;
              let mirrorElement = document.createElement('video');
              this.mirrorElement = mirrorElement;
              mirrorElement.className = 'video_need_resume';
              mirrorElement.autoplay = true;
              mirrorElement.srcObject = this.stream;
              mirrorElement.style.position = 'absolute';
              mirrorElement.style.objectFit = 'cover';
              mirrorElement.muted = true;
              mirrorElement.style.zIndex = this.zIndex;
              this.setMirrorElementPosition();
              mirrorContainer.appendChild(mirrorElement);
          },
          getDevicesList() {
              this.$emit('devices-list', {audioDevicesList: this.devices.audio, videoDevicesList: this.devices.video});
          },
          setCamera(firstTime = false) {
              if (this.loadingCamera) {
                  return;
              }
              if (firstTime || this.val.videoDeviceId !== this.activeDevices.video || this.val.audioDeviceId !== this.activeDevices.audio) {
                  this.loadingCamera = true;
                  let params = {};
                  if (this.val.videoDeviceId) {
                      params.video = {
                          deviceId: {
                              exact: this.val.videoDeviceId
                          }
                      }
                  }
                  if (this.val.audioDeviceId) {
                      params.audio = {
                          deviceId: {
                              exact: this.val.audioDeviceId
                          }
                      }
                  }
                  if (!this.val.videoDeviceId || !this.val.audioDeviceId) {
                      params = {
                          video: true,
                          audio: true
                      }
                  }
                  navigator.mediaDevices.getUserMedia(params).then(stream => {
                      this.loadingCamera = false;
                      let videoTrack = stream.getVideoTracks()[0];
                      let audioTrack = stream.getAudioTracks()[0];
                      if (videoTrack) {
                          this.activeDevices.video = videoTrack.getSettings().deviceId;
                          if (!this.value.videoDeviceId) {
                              this.value.videoDeviceId = videoTrack.getSettings().deviceId;
                          }
                          let containerWidth = this.$refs.container.clientWidth;
                          let containerHeight = this.$refs.container.clientHeight;
                          if (!this.value.width) {
                              this.value.width = containerWidth / videoTrack.getSettings().width;
                          }
                          if (!this.value.height) {
                              this.value.height = containerHeight / videoTrack.getSettings().height;
                          }
                      }
                      if (audioTrack) {
                          this.activeDevices.audio = audioTrack.getSettings().deviceId;
                          if (!this.value.audioDeviceId) {
                              this.value.audioDeviceId = audioTrack.getSettings().deviceId;
                          }
                      }

                      this.hasStream = true;
                      if (this.audio && this.source) {
                          this.source.disconnect();
                      }
                      this.stream = stream;
                      if (this.audio) {
                          this.source = this.audio.ctx.createMediaStreamSource(stream);
                          this.source.connect(this.audio.merger, 0, 0);
                          this.source.connect(this.audio.merger, 0, 1);
                          console.log(this);
                      }
                      this.$nextTick(() => {
                          if (this.mirrorElement) {
                              this.mirrorElement.srcObject = stream;
                          }
                          this.$refs.video.srcObject = stream;
                          this.$refs.video.volume = this.volume;
                      })
                  }).catch(e => {
                      this.loadingCamera = false;
                      console.log(e);
                  })
              }
          }
      },
      mounted() {
          navigator.mediaDevices.enumerateDevices().then((devices) => {
              let audioDevicesList = [];
              let videoDevicesList = [];
              let foundDevices = {};
              let audioCount = 0;
              let videoCount = 0;
              devices.forEach(device => {
                  if (!foundDevices[device.deviceId]) {
                      if (device.kind === 'audioinput') {
                          audioDevicesList.push({
                              value: device.deviceId,
                              name: device.label || `Audio ${audioCount++}`
                          })
                      }
                      if (device.kind === 'videoinput') {
                          videoDevicesList.push({
                              value: device.deviceId,
                              name: device.label || `Video ${videoCount++}`
                          })
                      }
                      foundDevices[device.deviceId] = true;
                  }
              });
              this.devices.audio = audioDevicesList;
              this.devices.video = videoDevicesList;
              this.devices.loading = false;
              this.$emit('devices-list', {audioDevicesList, videoDevicesList});
              this.setCamera(true);
          }).catch(e => {
              this.devices.error = e.message;
              this.devices.loading = false;
          });
      },
      components: {
          BaseSource,
      }
  }
</script>
