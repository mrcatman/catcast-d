<template>
  <div class="media-manager__disk-space">
    <div class="media-manager__disk-space__occupied">{{bytesToFileSize(data.space_occupied)}}</div>
    <div class="media-manager__disk-space__bar">
      <div :style="barStyle" class="media-manager__disk-space__bar__occupied"></div>
    </div>
    <div class="media-manager__disk-space__total">{{bytesToFileSize(data.space_total)}}</div>
  </div>
</template>
<script>
import {bytesToFileSize} from '@/helpers/file-size';

export default {
  computed: {
    barStyle() {
      const widthPercent = this.data.space_total > 0 ? (this.data.space_occupied / this.data.space_total * 100) : 0;
      return {
        width: widthPercent + '%'
      }
    }
  },
  methods: {
    async load() {
      this.data = await this.$api.get(`channels/${this.channel.id}/media/disk-space`);
    },
    bytesToFileSize
  },
  mounted() {
    this.load();
  },
  data() {
    return {
      data: {
        space_occupied: 0,
        space_total: 0
      },
    }
  },
  props: {
    channel: {
      type: Object,
      required: true
    }
  }
}
</script>
<style lang="scss">
.media-manager {
  &__disk-space {
    height: 2em;
    display: flex;
    align-items: center;
    &__bar {
      width: 5em;
      height: 1em;
      background: var(--input-bg-color);
      margin: 0 .5em;
      border-radius: 5em;
      overflow: hidden;
      &__occupied {
        height: 100%;
        background: var(--active-color);
      }
    }
    &__occupied {
      white-space: nowrap;
      font-weight: 600;
    }

    &__total {
      white-space: nowrap;
      font-weight: 600;
    }
  }
}
</style>
