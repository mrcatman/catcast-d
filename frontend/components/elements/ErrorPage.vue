<template>
<div class="error-page" :class="{'error-page--not-found': isNotFoundPage}">
  <canvas ref="textCanvas" class="error-page__canvas" style="display:none"></canvas>
  <canvas v-show="isNotFoundPage" ref="canvas" class="error-page__canvas"></canvas>
  <div class="error-page__texts">
    <div class="error-page__header">{{$t(data.text)}}</div>
    <div class="error-page__text" v-html="$t('errors.write_admin')"></div>
    <m-btn v-if="!inIframe" to="/">{{$t('global.back_to_main')}}</m-btn>
    <a target="_blank" href="https://catcast.tv" class="error-page__logo-container" v-else>
      <svg class="error-page__logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 732.1 265.15">
        <path class="cls-1" d="M256.39,318.7s-20.38,47-72.85,47c-51.65,0-76.18-44.33-76.18-60.17,0-3.64-.82-84.4-.38-98.7a18.42,18.42,0,0,1,.06-2.36c1.41,1.15,32.7,26.31,79.88,26.68,35.49.27,72.65-26.67,72.38-26.45,0,0,0-.24,0-.23-.07,1.16-.08,47.51-.08,47.51,9.7-6.28,42.31-26.08,41.87-27-16.58-35.65-61.55-72.19-117.53-72.19a133,133,0,0,0-133,133c0,73.46,59.43,132.16,132.88,132.16,80,0,120.27-67.49,120.27-78.25" transform="translate(-50.54 -152.74)"/><path class="cls-2" d="M107,207" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M353.53,311.58a30.84,30.84,0,0,1-12-11.62,34.69,34.69,0,0,1,0-33.6,30.49,30.49,0,0,1,12-11.56,35.86,35.86,0,0,1,17.4-4.15,33.34,33.34,0,0,1,15.47,3.49,24.86,24.86,0,0,1,10.54,10.12l-8.79,5.66a19.73,19.73,0,0,0-7.47-6.87,21.35,21.35,0,0,0-9.87-2.29,22.88,22.88,0,0,0-11.26,2.77,19.82,19.82,0,0,0-7.83,7.89,26,26,0,0,0,0,23.6,19.69,19.69,0,0,0,7.83,7.89,22.78,22.78,0,0,0,11.26,2.77,21.23,21.23,0,0,0,9.87-2.29,19.63,19.63,0,0,0,7.47-6.86l8.79,5.54a25.15,25.15,0,0,1-10.54,10.17,32.88,32.88,0,0,1-15.47,3.56A35.46,35.46,0,0,1,353.53,311.58Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M455.4,257.15q7.05,6.51,7.05,19.39v38.53h-11v-8.43a18.29,18.29,0,0,1-8.25,6.81,31.57,31.57,0,0,1-12.7,2.35q-10.73,0-17.16-5.18a17.79,17.79,0,0,1-.3-27.16q6.14-5.11,19.5-5.11h18.31v-2.29q0-7.47-4.34-11.44t-12.76-4a33.88,33.88,0,0,0-11.08,1.87,28.63,28.63,0,0,0-9.15,5l-4.82-8.67a34.89,34.89,0,0,1,11.8-6.08,49.22,49.22,0,0,1,14.57-2.11Q448.36,250.65,455.4,257.15Zm-11.5,46.79a17,17,0,0,0,7-8.37v-8.91H433.07q-14.7,0-14.7,9.87a9,9,0,0,0,3.74,7.59c2.48,1.84,5.94,2.77,10.35,2.77A21.52,21.52,0,0,0,443.9,303.94Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M519.77,311.34a17.23,17.23,0,0,1-6.26,3.31,25.94,25.94,0,0,1-7.71,1.15q-9.63,0-14.93-5.18t-5.3-14.81V260.76H474.73v-9.51h10.84v-14h11.56v14h18.3v9.51h-18.3v34.57q0,5.18,2.59,7.94t7.41,2.77a13.92,13.92,0,0,0,9-3Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M544.15,311.76a32,32,0,0,1-12.88-11.86,34.27,34.27,0,0,1,0-34.44,32,32,0,0,1,12.88-11.86,39.84,39.84,0,0,1,18.61-4.28,36.06,36.06,0,0,1,17.88,4.28,25.34,25.34,0,0,1,11.14,12.22l-14.57,7.83q-5.06-8.91-14.57-8.91a16.5,16.5,0,0,0-12.17,4.82q-4.82,4.81-4.81,13.12t4.81,13.13a16.5,16.5,0,0,0,12.17,4.81q9.63,0,14.57-8.91l14.57,8a25.85,25.85,0,0,1-11.14,12A35.66,35.66,0,0,1,562.76,316,39.84,39.84,0,0,1,544.15,311.76Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M651.87,256.49q8.07,7.17,8.07,21.61v37H642.36V307q-5.31,9-19.75,9a30.59,30.59,0,0,1-13-2.53,19.31,19.31,0,0,1-8.37-7,18.12,18.12,0,0,1-2.89-10.11q0-9,6.81-14.21t21-5.18h14.93q0-6.13-3.73-9.45t-11.2-3.31A32.83,32.83,0,0,0,616,265.88a27,27,0,0,0-8.49,4.4l-6.74-13.13a40.8,40.8,0,0,1,12.7-5.78,57.16,57.16,0,0,1,15.24-2.05Q643.79,249.32,651.87,256.49Zm-16,44.68a12.2,12.2,0,0,0,5.3-6.57V288H628.27q-11.57,0-11.56,7.59a6.76,6.76,0,0,0,2.83,5.72q2.82,2.1,7.76,2.1A16.41,16.41,0,0,0,635.85,301.17Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M682.94,314.05a41,41,0,0,1-12.29-5l6.27-13.48a39.5,39.5,0,0,0,10.47,4.51,43.73,43.73,0,0,0,12,1.75q11.93,0,11.93-5.9c0-1.85-1.09-3.17-3.26-4a48.22,48.22,0,0,0-10-2A98.05,98.05,0,0,1,685,287.14a20.43,20.43,0,0,1-9-5.54q-3.79-4-3.79-11.32a17.71,17.71,0,0,1,3.55-10.9A22.9,22.9,0,0,1,686.13,252a44.31,44.31,0,0,1,16.07-2.65,63.27,63.27,0,0,1,13.67,1.51A38.24,38.24,0,0,1,727.13,255l-6.26,13.37a37.4,37.4,0,0,0-18.67-4.82c-4,0-7,.57-9,1.69s-3,2.57-3,4.34c0,2,1.09,3.41,3.25,4.21a57.58,57.58,0,0,0,10.36,2.29,109.33,109.33,0,0,1,13,2.83,19.52,19.52,0,0,1,8.79,5.48q3.72,4,3.73,11.08a17.14,17.14,0,0,1-3.61,10.71,23.16,23.16,0,0,1-10.54,7.29A47.12,47.12,0,0,1,698.71,316,62.6,62.6,0,0,1,682.94,314.05Z" transform="translate(-50.54 -152.74)"/><path class="cls-3" d="M782.64,311.94a19.1,19.1,0,0,1-6.8,3.07,34,34,0,0,1-8.49,1q-11.57,0-17.88-5.9t-6.32-17.34V266.18h-10V251.73h10V236h18.78v15.77h16.14v14.45H761.93v26.38a8.82,8.82,0,0,0,2.11,6.32,7.82,7.82,0,0,0,6,2.23,12.12,12.12,0,0,0,7.59-2.41Z" transform="translate(-50.54 -152.74)"/>
      </svg>
    </a>
  </div>
  <svg class="error-page__testcard" v-show="!isNotFoundPage" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 24">
    <path d="m0,0h8v24H0"/>
    <path fill="#0000C0" d="m0,0h7v24H0"/>
    <path fill="#C00000" d="m0,0h6v24H0"/>
    <path fill="#C000C0" d="m0,0h5v24H0"/>
    <path fill="#00C000" d="m0,0h4v24H0"/>
    <path fill="#00C0C0" d="m0,0h3v24H0"/>
    <path fill="#C0C000" d="m0,0h2v24H0"/>
    <path fill="#FFFFFF" d="m0,0h1v24H0"/>
  </svg>
  <!--<img class="error-page__picture" v-if="data.text === 'errors.404'" src="@/assets/404.gif" />-->
</div>
</template>
<style lang="scss">
  .error-page {
    width: 100%;
    height: 100%;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex: 1;
    color: #fff;
    &__logo {
      fill: #fff;
      height: 5em;
    }

    &__logo-container {
      margin: 1em 0 0;
      display: inline-block;
    }
    @media screen and (max-width: 768px) {
      height: calc(100vh - 7em);
    }
    .player-page & {
      height: 100%!important;
    }
    &__testcard {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
    }
    &__texts {
      position: relative;
      z-index: 1;
      width: 100%;
      padding: 3em;
      background: #000;
    }
    &--not-found &__texts {
      background: rgba(0, 0, 0, 0.75);
    }

    &__header {
      font-size: 2em;
      font-weight: 500;
      margin: 0 0 .25em;
    }
    &__text {
      font-size: 1.25em;
      margin: 0 0 .5em;
    }
    &__canvas {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      image-rendering: optimizeSpeed;             /* Older versions of FF          */
      image-rendering: -moz-crisp-edges;          /* FF 6.0+                       */
      image-rendering: -webkit-optimize-contrast; /* Safari                        */
      image-rendering: -o-crisp-edges;            /* OS X & Windows Opera (12.02+) */
      image-rendering: pixelated;                 /* Awesome future-browsers       */
      -ms-interpolation-mode: nearest-neighbor;   /* IE                            */
    }
    &__picture {
      width: 100%;
    }
  }
</style>
<script>
export default {
  computed: {
    inIframe() {
      try {
        return window.self !== window.top;
      } catch (e) {
        return true;
      }
    },
    isNotFoundPage() {
      return this.data.text === "errors.404" || this.data.text === "errors.item_not_found";
    }
  },
  mounted() {
    if (this.isNotFoundPage) {
      let canvas = this.$refs.canvas;
      let textCanvas = this.$refs.textCanvas;
      let img = new Image;
      img.src = "/404.png";
      img.onload = () => {

        let context = canvas.getContext("2d"),
          scaleFactor = 2.5,
          samples = [],
          sampleIndex = 0,
          scanOffsetY = 0,
          scanSize = 60,
          FPS = 30,
          scanSpeed = FPS * 15,
          SAMPLE_COUNT = 10;
        window.onresize = function () {
          canvas.width = canvas.offsetWidth / scaleFactor;
          canvas.height = canvas.width / (canvas.offsetWidth / canvas.offsetHeight);
          textCanvas.width = canvas.width;
          textCanvas.height = canvas.height;
          scanSize = (canvas.offsetHeight / scaleFactor) / 3;
          samples = []

          let textCtx = textCanvas.getContext('2d');

          //textCtx.fillText(text, 10, 90);
          let imageWidth = canvas.width / 10;
          let imageHeight = imageWidth * img.height / img.width;
          textCtx.drawImage(img, 20, 20, imageWidth, imageHeight);
          let textImageData = textCtx.getImageData(0, 0, textCanvas.width, textCanvas.height);
          for (let i = 0; i < SAMPLE_COUNT; i++) {
            samples.push(generateRandomSample(context, canvas.width, canvas.height, textImageData, i));
          }
        };


        function interpolate(x, x0, y0, x1, y1) {
          return y0 + (y1 - y0) * ((x - x0) / (x1 - x0));
        }


        function generateRandomSample(context, w, h, textImageData, index) {
          let intensity = [];
          let factor = h / 50;
          let intensityCurve = [];
          for (let i = 0; i < Math.floor(h / factor) + factor; i++)
            intensityCurve.push(Math.floor(Math.random() * 15));
          for (let i = 0; i < h; i++) {
            let value = interpolate((i / factor), Math.floor(i / factor), intensityCurve[Math.floor(i / factor)], Math.floor(i / factor) + 1, intensityCurve[Math.floor(i / factor) + 1]);
            intensity.push(value);
          }
          if (w > 0 && h > 0) {
            let imageData = context.createImageData(w, h);
            let wCoeff = index % 4;
            for (let i = 0; i < (w * h); i++) {
              let k = i * 4;
              let color = Math.floor(36 * Math.random());
              color += intensity[Math.floor(i / w)];
              imageData.data[k] = imageData.data[k + 1] = imageData.data[k + 2] = color;
              let coeff = (Math.ceil(Math.random() * 2) - 1) * 4;
              let opacityCoeff = (Math.random() * 0.5 + 0.5);
              let wCoeffReal = wCoeff * 4;
              imageData.data[k] += textImageData.data[k + coeff + wCoeffReal] * opacityCoeff;
              imageData.data[k + 1] += textImageData.data[k + 1 + coeff + wCoeffReal] * opacityCoeff;
              imageData.data[k + 2] += textImageData.data[k + 2 + coeff + wCoeffReal] * opacityCoeff;
              imageData.data[k + 3] = 255;
              if (i % w === 0) {
                wCoeff = Math.floor(Math.random() * 4) - 8;
              }
            }
            return imageData;
          } else {
            return false;
          }
        }

        function render() {

          sampleIndex += 30 / FPS; // 1/FPS == 1 second
          if (sampleIndex >= samples.length) sampleIndex = 0;
          context.putImageData(samples[Math.floor(sampleIndex)], 0, 0);
          let grd = context.createLinearGradient(0, scanOffsetY, 0, scanSize + scanOffsetY);

          grd.addColorStop(0, 'rgba(255,255,255,0)');
          grd.addColorStop(0.1, 'rgba(255,255,255,0)');
          grd.addColorStop(0.2, 'rgba(255,255,255,0.2)');
          grd.addColorStop(0.3, 'rgba(255,255,255,0.0)');
          grd.addColorStop(0.45, 'rgba(255,255,255,0.1)');
          grd.addColorStop(0.5, 'rgba(255,255,255,1.0)');
          grd.addColorStop(0.55, 'rgba(255,255,255,0.55)');
          grd.addColorStop(0.6, 'rgba(255,255,255,0.25)');
          //grd.addColorStop(0.8, 'rgba(255,255,255,0.15)');
          grd.addColorStop(1, 'rgba(255,255,255,0)');

          context.fillStyle = grd;
          context.fillRect(0, scanOffsetY, canvas.width, scanSize + scanOffsetY);
          context.globalCompositeOperation = "lighter";

          scanOffsetY += (canvas.height / scanSpeed);
          if (scanOffsetY > canvas.height) scanOffsetY = -(scanSize / 2);

          window.setTimeout(function () {
            window.requestAnimationFrame(render);
          }, 1000 / FPS);
        }

        window.onresize();
        window.requestAnimationFrame(render);
      }
    }
  },
	props: {
		data: {
			type: Object,
			required: true,
		}
	}
}
</script>
