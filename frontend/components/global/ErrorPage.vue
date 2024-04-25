<template>
<div class="error-page" :class="{'error-page--not-found': isNotFoundPage}">
  <canvas ref="textCanvas" class="error-page__canvas" style="display:none"></canvas>
  <canvas v-show="isNotFoundPage" ref="canvas" class="error-page__canvas"></canvas>
  <div class="error-page__texts">
    <div class="error-page__header">{{data.message ? $t(data.message) : data.title}}</div>
    <div class="error-page__text" v-html="data.text || $t('errors.write_admin')"></div>
    <c-button v-if="!inIframe" flat to="/">{{$t('global.back_to_main_page')}}</c-button>
    <a target="_blank" href="/" class="error-page__logo-container" v-else>
      <img :src="siteLogo" class="error-page__logo" />
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
      margin-top: 1em;
      height: 3em;
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
import {mapGetters} from "vuex";

export default {
  computed: {
    ...mapGetters('config', ['siteLogo']),
    inIframe() {
      try {
        return window.self !== window.top;
      } catch (e) {
        return true;
      }
    },
    isNotFoundPage() {
      return this.data.code === 404;
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
