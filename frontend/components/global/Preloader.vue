<template>
  <div class="preloader" ref="container" :class="{'preloader--block': block}">
    <div class="preloader__outer">
      <div class="preloader__inner">
        <div class="progress-circular progress-circular--indeterminate" :class="{'progress-circular--big': isBig}" :style="circleStyle">
          <svg xmlns="http://www.w3.org/2000/svg" :viewBox="`${viewBoxSize} ${viewBoxSize} ${2 * viewBoxSize} ${2 * viewBoxSize}`" style="transform: rotate(0deg);">
            <circle :stroke="color" fill="transparent" :cx="2*viewBoxSize"  :cy="2*viewBoxSize" :r="radius" :stroke-width="strokeWidth" :stroke-dasharray="strokeDashArray" :stroke-dashoffset="strokeDashOffset" class="progress-circular__overlay"></circle>
          </svg>
        </div>
      </div>
    </div>


  </div>
</template>
<style lang="scss">
.preloader {
  &__outer {
    position: relative;
    width: 100%;
    height: 100%;
  }
  &--block &__outer:before {
    content: '';
    background: var(--darken-4);
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9;
  }
  &--block {
    z-index: 10;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }
  &__inner {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.progress-circular {
  position: relative;
  display: inline-flex;
  stroke: #fff;
  &--big {
    margin: 1.25em 0;
  }
  svg {
    width: 100%;
    height: 100%;
    margin: auto;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 0;
  }
  &__overlay {
    z-index: 2;
    transition: all .6s ease-in-out;
  }

  &--indeterminate {
    svg {
      animation: progress-circular-rotate 1.4s linear infinite;
      transform-origin: center center;
      transition: all .2s ease-in-out;
    }
    .progress-circular__overlay {
      animation: progress-circular-dash 1.4s ease-in-out infinite;
      stroke-linecap: round;
      stroke-dasharray: 80,200;
      stroke-dashoffset: 0;
    }
  }


  @keyframes progress-circular-dash {
    0% {
      stroke-dasharray: 1,200;
      stroke-dashoffset: 0;
    }
    50% {
      stroke-dasharray: 100,200;
      stroke-dashoffset: -15px;
	}
    100% {
      stroke-dasharray: 100,200;
      stroke-dashoffset: -125px;
    }
  }

  @keyframes progress-circular-rotate {
    100% {
      transform: rotate(360deg)
	  }
  }
}
</style>
<script>
export default {
  props: {
    block: Boolean,
    button: Boolean,
    indeterminate: Boolean,
    color: String,
    size: {
      type: Number,
      default: 32
    },
    width: {
      type: Number,
      default: 4
    },
    strokeWidth: {
      type: Number,
      required: false,
      default: 5,
    },
    circleWidth: {
      type: Number,
      required: false,
      default: 24,
    },
  },
  data() {
    return {
      circleSize: this.circleWidth,
    }
  },
  mounted() {
    const height = this.$refs.container.offsetHeight;
    if (height < 36) {
      this.circleSize = height;
    }
  },
  computed: {
    isBig() {
      let width = (this.circleSize ? (typeof this.circleSize !== "number" ? parseFloat(this.circleSize) * 16 : this.circleSize) : 16);
      return width > 36;
    },
    circleStyle() {
      let width = (this.circleSize ? (typeof this.circleSize !== "number" ? this.circleSize : this.circleSize + 'px') : '1em');
      let height = width;
      return {
        width,
        height
      }
    },
    calculatedSize () {
      return (this.circleSize ?  Number(this.circleSize) : 75) + (this.button ? 8 : 0)
    },
    circumference () {
      return 2 * Math.PI * this.radius
    },
    normalizedValue () {
      if (this.value < 0) {
        return 0
      }
      if (this.value > 100) {
        return 100
      }
	    return this.value
    },
    radius() {
		  return 20;
    },
    strokeDashArray() {
      return Math.round(this.circumference * 1000) / 1000
    },

    strokeDashOffset() {
      return ((100 - this.normalizedValue) / 100) * this.circumference + 'px'
    },
    styles() {
      return {
        height: `${this.calculatedSize}px`,
        width: `${this.calculatedSize}px`
      }
    },


    viewBoxSize () {
      return this.radius / (1 - (this.width/(this.width*3.14)))
    }
  },
}
</script>
