<template>
  <div class="directory-view">
    <h2 class="directory-view__heading" v-if="directory.children">
      {{$t(directory.heading)}}
    </h2>
    <div class="directory-view__section" v-for="(config, $index) in getConfig">
      <welcome v-if="config.entity === 'welcome'" />
      <c-thumbs-list :config="config" v-else :key="getKey(config)">
        <template slot="item" slot-scope="props">
          <channel-thumb :data="props.item" v-if="config.entity === 'channels'" />
          <video-thumb :data="props.item" v-else-if="config.entity === 'media'" />
          <category-thumb :data="props.item" v-else-if="config.entity === 'categories'" />
          <div v-else>
            {{props.item}}
          </div>
          <!-- todo: feeds of news, announces, etc-->
        </template>
      </c-thumbs-list>
      <div class="vertical-delimiter directory-view__delimiter" v-if="$index < getConfig.length - 1"></div>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.directory-view {
  &__heading {
    font-size: 1.75em;
    margin: 0;
    padding: 1.125em .5em;
  }
  &__delimiter {
    margin: 1em;
  }
}
</style>
<script>
import ChannelThumb from "@/components/thumbs/ChannelThumb.vue";
import VideoThumb from "@/components/thumbs/VideoThumb.vue";
import CategoryThumb from "@/components/thumbs/CategoryThumb.vue";
import Welcome from "@/components/index/Welcome.vue";

const baseConfig = {
  view: 'grid'
};
const baseItemConfig = {
  canChangeView: true,
  paginate: true,
  infiniteScroll: true,
};

export default {
  components: {
    Welcome,
    CategoryThumb,
    VideoThumb,
    ChannelThumb
  },
  methods: {
    getKey(config) {
      return JSON.stringify(config);
    },
    generateDirectoryConfig(config, one = false) {
      const fullConfig = {
        ...config,
        title: this.$t(config.heading),
        url: `/${config.entity}?${(new URLSearchParams(config.params || []).toString())}`,
      }
      if (config.entity === 'categories') {
        fullConfig.itemWidth = 300; //todo: move to constants
      }
      if (!one) {
        fullConfig.rowsToLoad = config.entity === 'categories' ? 1 : 2;
      } else {
        fullConfig.search = true;
      }
      return fullConfig;
    }
  },
  computed: {
    getConfig() {
      if (this.directory.children) {
        return this.directory.children.map(config => {
          const fullConfig = {
            ...baseConfig,
            ...this.generateDirectoryConfig(config),
          }
          const path = this.path.replace('directory/','');
          fullConfig.expandLink = `/${config.entity}?${(new URLSearchParams(config.params || []).toString())}`;
          return fullConfig;
        });
      } else {
        return [{
          ...baseItemConfig,
          ...this.generateDirectoryConfig(this.directory, true),
        }]
      }
    }
  },
  props: {
    directory: {
      type: [Object, Array],
      required: true
    },
    path: {
      type: String,
      required: true
    }
  }
}
</script>
