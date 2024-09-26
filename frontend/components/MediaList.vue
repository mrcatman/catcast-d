<template>
  <div :class="config.inPage ? 'page-container' : ''">
    <c-thumbs-list ref="list" :data="data" :config="getConfig">
      <template slot="filters" v-if="$slots.filters">
        <slot name="filters"></slot>
      </template>
      <template slot="item" slot-scope="props">
        <video-thumb :data="props.item" :config="getConfigForItem(props.item)" />
      </template>
    </c-thumbs-list>
  </div>
</template>

<script>
import VideoThumb from "@/components/thumbs/VideoThumb";
export default {
  components: {
    VideoThumb,
  },
  methods: {
    getConfigForItem(item) {
      return {
        ...this.config,
        highlighted: this.config.playlistIndex >= 0 && item.index_in_playlist === this.config.playlistIndex
      }
    }
  },
  props: {
    title: {
      type: String,
      required: false
    },
    url: {
      type: String,
      required: false,
      default: '/media'
    },

    config: {
      type: Object,
      required: false,
      default: () => {
        return {}
      }
    },
    data: {
      type: Object,
      required: false
    }
  },
  computed: {
    getConfig() {
      return {
        title: this.title,
        url: this.url,
        canChangeView: true,
        paginate: true,
        infiniteScroll: true,
        queryParams: this.getQueryParams,
        ...this.config,
      }
    },
    getQueryParams() {
      const params = {
        ...this.queryParams
      };
      if (this.type) {
        params.type = this.type;
      }
      return params;
    }
  },
}
</script>
